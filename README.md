# Swish Router

## Installation
```
composer require atlantisphp/swish
```

## Introduction
Swish Router is a simple and easy to use Routing System for PHP. You can implement it on any PHP project.

## Getting Started
Here is a quick example.

*index.php file stored in `/public` directory*
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

*htaccess file stored in /public*

```
# .htaccess file stored in "/public"

Options -Multiviews -Indexes
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f

RewriteRule ^(.+)$ index.php [QSA,L]
```

### Available Router Methods
The router allows you to register routes that respond to any HTTP verb:

```
Route::get($uri, $callback);
Route::post($uri, $callback);
Route::put($uri, $callback);
Route::delete($uri, $callback);
Route::options($verbs, $uri, $callback);
Route::redirect($uri, $redirect);
Route::view($uri, $view); // uses the get verb
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
Route::get('user/profile', 'UserProfileController@show')->name('profile');
```