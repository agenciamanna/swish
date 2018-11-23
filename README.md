# Swish Router

## Installation

```
composer require atlantisphp/swish
```

## Introduction

Swish Router is a simple and easy to use Routing System for PHP. You can implement it on any PHP project.

## Getting Started

Here is a quick example.

_index.php file stored in `/public` directory_

```php
<?php

require_once __DIR__.'/../vendor/autoload.php';

// Controller namespace
AtlantisPHP\Swish\SwishHandler::setNamespace('Controllers');

/**
 * This method gets executed when an error occures.
 *
 * @param boolean $isAjax
 * @param integer $code
 */
AtlantisPHP\Swish\SwishHandler::fail(function($isAjax, $code) {
  $message = ($code == 404 ? 'Page not found' : 'Method not allowed');
  echo $message;
});

/**
 * This method is used for view routing.
 *
 * @param string $path
 * @param array  $variables
 */
AtlantisPHP\Swish\SwishHandler::view(function($path, $variables) {
  echo "display view component";
});

/**
 * This method gets executed before a route is handled.
 * Used for middleware implementation.
 *
 * @param  route  $route
 * @param  method $callback
 * @return array  $variables
 */
AtlantisPHP\Swish\SwishHandler::before(function($route, $callback) {
  return $route->variables;
});

/**
 * This method gets executed after a route is handled.
 * Used for tracking.
 *
 * @param route $route
 */
AtlantisPHP\Swish\SwishHandler::after(function($route) {
  //
});

AtlantisPHP\Swish\Route::get('/', function() {
    echo "Welcome home";
})->name('home');

AtlantisPHP\Swish\Route::get('/about', 'HomeController@about')->name('about-us');
AtlantisPHP\Swish\Route::get('/contact', 'HomeController@contact')->name('contact-us');
AtlantisPHP\Swish\Route::redirect('/home', '/')->name('go-home');

// Run the routes
AtlantisPHP\Swish\Route::dispatch();
```

_htaccess file stored in /public_

```
Options -Multiviews -Indexes
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f

RewriteRule ^(.+)$ index.php [QSA,L]
```

### Available Router Methods

The router allows you to register routes that respond to any HTTP verb:

```
Route::get(string $uri, $callback);
Route::post(string ($uri, $callback);
Route::put(string $uri, $callback);
Route::head(string $uri, $callback);
Route::delete(string $uri, $callback);
Route::patch(string $uri, $callback);
Route::options(string $uri, $callback);
Route::any(string $uri, $callback);
Route::if(array $verbs, string $uri, $callback);
Route::redirect(string $uri, string $redirect);
Route::view(string $uri, string $view); // uses the get verb
Route::group(array $args, closure $callback);
```

A \$callback can either be a closure or a controller action.

Here's what the syntax looks like:

```
Route::get('/', function() {
  view('home');
});

Route::if(['GET', 'POST'], '/profile/{id}', 'AccountController@showProfile');
```

## Named Routes

Named routes allow the convenient generation of URLs or redirects for specific routes. You may specify a name for a route by chaining the name method onto the route definition:

```
Route::get('/help/about-us', function () {
  //
})->name('about');
```

You may also specify route names for controller actions:

```
Route::get('/user/profile', 'UserProfileController@show')->name('profile');
```

## Custom variables

Custom variables are variables you can add to a route or expected variables you can replace.

Here's how you can add variables to a route.

```
Route::get('/post/{id}', 'PostController@show')->variables([
  'id' => 2
]);
```

## Domain Routing

You can easily create routes for specific domains. Here is a quick example:

```
Route::get('/', function () {

  // route to docs.example.com

})->domain('docs.example.com');
```

Using `group`:

```
Route::group(['domain' => 'api.example.com'], function () {

  Route::get('/users', 'ApiController@getUsers');
  Route::get('/admins', 'ApiController@getAdmins');

});
```

Here is a "wildcard" example:

```
Route::get('/', function ($user) {

})->domain('{user}.example.com');

// e.g. donald.example.com
```

## View Routes

To use the `Route::view` action, you must connect it to the `Medusa` package.

Here's how you can do that:

First require the medusa package:

```
composer require atlantisphp/medusa
```

Then configure `Medusa` to work with `Swish`.

_index.php file stored in `/public` directory_

```
<?php

require_once __DIR__.'/../vendor/autoload.php';

use AtlantisPHP\Swish\SwishHandler;
use AtlantisPHP\Medusa\Template as Medusa;

$medusa = new Medusa();

$medusa->setCacheDirectory('/../storage/cache');
$medusa->setViewsDirectory('/../views');
$medusa->setViewsExtension('.medusa.php');

SwishHandler::view(function($path, $variables) use ($medusa) {
  $medusa->make($path, isset($variables[0]) ? $variables[0] : []);
});
```

This should now work:

```
Route::view('/', 'home')->name('home');

// load router
Route::dispatch();
```
