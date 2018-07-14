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
	 * Route arguments
	 *
	 * @var $group
	 */
	static public $group = [];

	/**
	 * Current route
	 *
	 * @var $current
	 */
	static private $current;

	/**
	 * Request query
	 *
	 * @var $query
	 */
	private $query;

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
	 * Route can redirect
	 *
	 * @var $redirect
	 */
	private $redirect;

	/**
	 * Route to view
	 *
	 * @var $view
	 */
	private $view;

	/**
	 * Throw new Exception
	 */
	private function exception($message)
	{
		throw new Exception($message);
	}

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
	public static function add($method, $pattern, $callback, $redirect = false, $view = false)
	{
		$route = new Route;

		$globalMiddleware = [];
		$globalPattern = $globalNamespace = '';

		if (isset(debug_backtrace()[2]) && debug_backtrace()[2]['function'] == '{closure}') {
			if (isset(self::$group['middleware'])) {
				array_merge(
					$globalMiddleware,
					self::$group['middleware']
				);
			}

			if (isset(self::$group['prefix'])) {
				$globalPattern = substr(self::$group['prefix'], 0, 1) === "/" ?
										self::$group['prefix'] : '/' . self::$group['prefix'];
			}

			if (isset(self::$group['namespace'])) {
				$globalNamespace = self::$group['namespace'] . '\\';
			}
		};

		$pattern = substr($pattern, 0, 1) == "/" ? $pattern : '/' . $pattern;

		$route->id = uniqid('route_');
		$route->pattern = $globalPattern . $pattern;
		$route->callback = is_string($callback) ? $globalNamespace . $callback : $callback;
		$route->redirect = $redirect;
		$route->view = $view;

		self::$routes[] = [
			'id' => $route->id,
			'name' => '',
			'middleware' => [],
			'pattern' => $route->pattern,
			'callback' => is_string($callback) ? $globalNamespace . $callback : $callback,
			'method' => $method,
			'redirect' => $redirect,
			'view' => $view
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

	/**
	 * Grouped routes
	 *
	 * @param  array   $group
	 * @param  closure $callback
	 * @return
	 */
	public static function group(Array $group, Closure $callback)
	{
		self::$group = $group;
		call_user_func($callback);
	}

	/**
	 * Add a new get route
	 *
	 * @param  string $pattern
	 * @param  string $callback
	 * @return object
	 */
	public static function get($pattern, $callback)
	{
		return self::add(['GET'], $pattern, $callback);
	}

	/**
	 * Add a new post route
	 *
	 * @param  string $pattern
	 * @param  string $callback
	 * @return object
	 */
	public static function post($pattern, $callback)
	{
		return self::add(['POST'], $pattern, $callback);
	}

	/**
	 * Add a new put route
	 *
	 * @param  string $pattern
	 * @param  string $callback
	 * @return object
	 */
	public static function put($pattern, $callback)
	{
		return self::add(['PUT'], $pattern, $callback);
	}

	/**
	 * Add a new delete route
	 *
	 * @param  string $pattern
	 * @param  string $callback
	 * @return object
	 */
	public static function delete($pattern, $callback)
	{
		return self::add(['DELETE'], $pattern, $callback);
	}

	/**
	 * Add a new options route
	 *
	 * @param  string $pattern
	 * @param  string $callback
	 * @return object
	 */
	public static function options(array $methods, $pattern, $callback)
	{
		return self::add($methods, $pattern, $callback);
	}

	/**
	 * Add a new redirect route
	 *
	 * @param  string $pattern
	 * @param  string $redirect
	 * @return object
	 */
	public static function redirect($pattern, $redirect)
	{
		return self::add(['GET', 'POST', 'PUT', 'DELETE'], $pattern, $redirect, true);
	}

	public static function view($pattern, $path)
	{
		return self::add(['GET'], $pattern, $path, false, true);
	}

	/**
	 * Find matching route for the current url
	 *
	 * @param  string $name
	 * @return
	 */
	public static function dispatch($name = null)
	{
		$app = self::$application;
		$router = new Route;
		$routes = self::$routes;

		foreach($routes as $route) {
			if ($name != null && $route['name'] == $name) {
				self::$current = $route;
				$route['variables'] = [];

				if ($route['redirect']) {
					$router->to($route);
					return;
				}
				else if ($route['view']) {
					if (method_exists($app, 'view')) {
						$app->view($route['callback'], []);
					}
					else {
						call_user_func_array([$app, 'fail'], [$router->isAjax(), 405]);
					}
					return;
				}

				return $router->handle($app, (object)$route, []);
			}

			if (in_array($router->method(), $route['method'])) {
				$matches = $router->match($route['pattern']);

				if ($matches) {
					self::$current = $route;
					$route['variables'] = $matches;

					if ($route['redirect']) {
						$router->to($route);
						return;
					}
					else if ($route['view']) {
						if (method_exists($app, 'view')) {
							$app->view($route['callback'], is_bool($matches) ? [] : $matches);
						}
						else {
							call_user_func_array([$app, 'fail'], [$router->isAjax(), 405]);
						}
						return;
					}

					return $router->handle($app, (object)$route, is_bool($matches) ? [] : $matches);
				}
			}
		}

		$code = $router->isNotAllowed($router, $routes) ? 405 : 404;

		call_user_func_array([$app, 'fail'], [$router->isAjax(), $code]);
		return false;
	}

	/**
	 * Check route status
	 *
	 * @param  object  $router
	 * @param  array   $routes
	 * @return boolean
	 */
	public function isNotAllowed($router, $routes)
	{
		foreach($routes as $route) {
			if ($router->match($route['pattern'])) {
				return true;
			}
		}
	}

	/**
	 * Redirect route to another route
	 *
	 * @param array @route
	 */
	private function to($route)
	{
		header('Location: ' . $route['callback']);
	}

	/**
	 * Execute route
	 *
	 * @param  object  $app
	 * @param  object  $route
	 * @param  array   $matches
	 * @return boolean
	 */
	private function handle($app, $route, $matches)
	{
		if (is_string($route->callback)) {
			$controller = $app->controller['namespace'] . '\\' . explode('@', $route->callback)[0];

			if (!class_exists($controller)) $this->exception(
				"Class '$controller' not found."
			);

			$class = new $controller;
			$method = explode('@', $route->callback)[1];

			if (!method_exists($class, $method)) $this->exception(
				"Method $controller::$method() does not exist"
			);

			$response = $app->before($route, [$class, $method]);
			if ($response != [] || $response !== false) {
				$matches = $response;
			}

			call_user_func_array(
				[$class, $method],
				is_array($matches) ? $matches : []
			);

			$app->after($route, [$class, $method]);

			return true;
		}

		$response = $app->before($route, $route->callback);

		if ($response != [] || $response !== false) {
			$matches = $response;
		}

		call_user_func_array(
			$route->callback,
			is_array($matches) ? $matches : []
		);

		$app->after($route, $route->callback);

		return true;
	}

	/**
	 * Check if current request is xmlhttp or http
	 *
	 * @return boolean
	 */
	public function isAjax() {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
				($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
	}

	/**
	 * Return current route
	 *
	 * @return array $current
	 */
	public static function current()
	{
		return self::$current;
	}

	/**
	 * Return current url without query string
	 *
	 * @return string
	 */
	private function url()
	{
		$length = strlen($this->uri());

		return str_replace(
			$this->query(),'',
			substr($this->uri(), -1) == '/' ? substr($this->uri(), 0, $length - 1) : $this->uri()
		);
	}

	/**
	 * Return current url
	 *
	 * @return string
	 */
	private function uri()
	{
		return $_SERVER['REQUEST_URI'];
	}

	/**
	 * Return query string
	 *
	 * @return string $query
	 */
	private function query()
	{
		if (isset($_SERVER['QUERY_STRING'])) {
			$this->query = '?' . $_SERVER['QUERY_STRING'];
		}

		return $this->query;
	}

	/**
	 * Get current method
	 *
	 * @return string
	 */
	private function method()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Match route with current url
	 *
	 * @param  string $pattern
	 * @return array  $matches
	 */
	private function match($pattern)
	{
		$pattern = substr($pattern, -1) == '/' ? substr($pattern, 0, strlen($pattern) - 1) : $pattern ;

		$pattern_regex = preg_replace("/\{(.*?)\}/", "(?P<$1>[\w-]+)", $pattern);
		$pattern_regex = "#^" . trim($pattern_regex, "/") . "$#";

		preg_match(
			$pattern_regex,
			trim($this->url(), "/"),
			$matches
		);

		if (count($matches) == 1 && $pattern == '') {
			return true;
		}
		else if ($pattern == $this->url()) {
			return true;
		}

		if ($matches) {
			return array_intersect_key(
				$matches,
				array_flip(
					array_filter(array_keys(
						$matches),
						'is_string'
					)
				)
			);;
		}
	}
}