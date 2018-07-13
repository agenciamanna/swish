<?php

namespace ModulusPHP\Swish;

use Closure;
use Exception;

class Route
{
	/**
	 * Application routes
	 *
	 * @var $routes
	 */
	static public $routes = [];

	/**
	 * Application
	 *
	 * @var $application
	 */
	static public $application;

	/**
	 * Route id
	 *
	 * @var $id
	 */
	private $id;

	/**
	 * Route key
	 *
	 * @var $key
	 */
	private $key;

	/**
	 * Route name
	 *
	 * @var $name
	 */
	private $name;

	/**
	 * Route pattern
	 *
	 * @var $pattern
	 */
	private $pattern;

	/**
	 * Route callback
	 *
	 * @var $callback
	 */
	private $callback;

	/**
	 * Route middlware
	 *
	 * @var $middleware
	 */
	private $middleware = [];

	/**
	 * Set application provider
	 */
	public static function provider($app)
	{
		self::$application = $app;
	}

	/**
	 * Register application routes
	 *
	 * @param  string  $method
	 * @param  string  $pattern
	 * @param
	 * @return object  $route
	 */
	public static function add($method, $pattern, $callback)
	{
		$route = new Route;

		$pattern = substr($pattern, 0, 1) == "/" ? $pattern : '/' . $pattern;

		$route->id = uniqid('route_');
		$route->pattern = $pattern;
		$route->callback = $callback;

		self::$routes[] = [
			'id' => $route->id,
			'name' => '',
			'middleware' => [],
			'pattern' => $route->pattern,
			'callback' => is_string($callback) ? $callback : $callback,
			'method' => $method
		];

		end(self::$routes);
		$route->key = key(self::$routes);

		return $route;
	}

	/**
	 * Set route name
	 *
	 * @param  string $name
	 * @return object $this
	 */
	public function name($name)
	{
		$this->name = $name;
		self::$routes[$this->key]['name'] = $name;
		return $this;
	}

	/**
	 * Set route middlware
	 *
	 * @param  string $name
	 * @return object $this
	 */
	public function middleware($middleware)
	{
		$middlewares = explode(':', $middleware);

		foreach($middlewares as $middleware) {
			$this->middleware[] = $middleware;
			self::$routes[$this->key]['middleware'][] = $middleware;
		}

		return $this;
	}
}