# Swish Router

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
 * This method gets executed afer a route is handled.
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