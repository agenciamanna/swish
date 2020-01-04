<?php

namespace AtlantisPHP\Swish;

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
   * Application object routes
   *
   * @var $objects
   */
  static public $objects = [];

  /**
   * Route names
   *
   * @var $names
   */
  static public $names = [];

  /**
   * Controller settings
   *
   * @var $controller
   */
  static public $controller;

  /**
   * Route arguments
   *
   * @var $group
   */
  static public $group = [];

  /**
   * View event
   *
   * @var $viewEvent
   */
  static public $viewEvent;

  /**
   * Fail event
   *
   * @var $failEvent
   */
  static public $failEvent;

  /**
   * Fail event
   *
   * @var $beforeEvent
   */
  static public $beforeEvent;

  /**
   * Fail event
   *
   * @var $afterEvent
   */
  static public $afterEvent;

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
   * $response
   *
   * @var mixed
   */
  private $response;

  /**
   * $domain
   *
   * @var string
   */
  private $domain;

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
   * Route file
   *
   * @var $file
   */
  private $file;

  /**
   * Required variables
   *
   * @var $required
   */
  private $required;

  /**
   * Throw new Exception
   *
   * @param string $message
   */
  private function exception($message)
  {
    throw new Exception($message);
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
    $file = isset(debug_backtrace()[1]['file']) ? basename(debug_backtrace()[1]['file']) : null;

    $globalPattern = '';
    $globalDomain  = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

    $stack = array_reverse(debug_backtrace());

    foreach($stack as $trace) {
      if (isset($trace['file']) && $trace['function'] == 'group' && is_countable($trace['args'])) {
        $args = $trace['args'][0];

        if (isset($args['middleware'])) {
          $globalMiddleware = array_merge(
            $globalMiddleware,
            is_array($args['middleware']) ? $args['middleware'] : [$args['middleware']]
          );
        }

        if (isset($args['prefix'])) {
          $globalPattern .= substr($args['prefix'], 0, 1) === "/" ?
              $args['prefix'] : '/' . $args['prefix'];
        }

        if (isset($args['namespace'])) {
          $globalNamespace .= $args['namespace'] . '\\';
        }

        $globalDomain = isset($args['domain']) ? $args['domain'] : $globalDomain;
      }
    }

    $pattern = substr($pattern, 0, 1) == "/" ? $pattern : '/' . $pattern;

    $route->id         = uniqid('route_');
    $route->pattern    = $globalPattern . $pattern;
    $route->response   = null;
    $route->domain     = $globalDomain;
    $route->callback   = is_string($callback) ? $globalNamespace . $callback : $callback;
    $route->redirect   = $redirect;
    $route->view       = $view;
    $route->file       = $file;
    $route->required   = $route->countVaribales($pattern);
    $route->middleware = $globalMiddleware;

    self::$routes[] = [
      'id' => $route->id,
      'name' => '',
      'middleware' => $route->middleware,
      'pattern' => $route->pattern,
      'response' => null,
      'domain' => $route->domain,
      'callback' => $route->callback,
      'method' => $method,
      'redirect' => $route->redirect,
      'view' => $route->view,
      'file' => $route->file,
      'required' =>$route->required
    ];

    end(self::$routes);
    $route->key = key(self::$routes);

    self::$objects[] = array('id' => $route->id, 'route' => $route);

    return $route;
  }

  /**
   * Count required variables
   *
   * @param  string  $pattern
   * @return integer $c
   */
  private function countVaribales(string $pattern)
  {
    preg_match_all("/\{(.*?)\}/", $pattern, $c);
    return count($c[0]);
  }

  /**
   * Set route name
   *
   * @param  string $name
   * @return object $this
   */
  public function name(string $name)
  {
    if (in_array($name, self::$names)) return $this->exception('A route with the name "' . $name . '" has already been registered.');

    $this->name = $name;
    self::$routes[$this->key]['name'] = $name;

    self::$names[] = $name;
    return $this;
  }

  /**
   * Set route middlware
   *
   * @param  string $name
   * @return object $this
   */
  public function middleware()
  {
    $this->middleware = array_merge($this->middleware, func_get_args());
    self::$routes[$this->key]['middleware'] = array_merge(self::$routes[$this->key]['middleware'], func_get_args());

    return $this;
  }

  /**
   * Set route domain
   *
   * @param string $host
   * @return object
   */
  public function domain(string $host)
  {
    self::$routes[$this->key]['domain'] = $host;

    return $this;
  }

  /**
   * Set route regex
   *
   * @param string $regex
   * @return object
   */
  public function regex(string $regex)
  {
    self::$routes[$this->key]['regex'] = $regex;

    return $this;
  }

  /**
   * Set route variables
   *
   * @param array $variables
   * @return void
   */
  public function variables(array $variables)
  {
    if (count($variables) != $this->required) return $this->exception(
      'Expected ' . $this->required . ' arguments, ' . count($variables) . ' passed.'
    );

    self::$routes[$this->key]['variables'] = $variables;
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
  public static function get(string $pattern, $callback)
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
  public static function post(string $pattern, $callback)
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
  public static function put(string $pattern, $callback)
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
  public static function delete(string $pattern, $callback)
  {
    return self::add(['DELETE'], $pattern, $callback);
  }

  /**
   * Add a new patch route
   *
   * @param  string $pattern
   * @param  string $callback
   * @return object
   */
  public static function patch(string $pattern, $callback)
  {
    return self::add(['PATCH'], $pattern, $callback);
  }

  /**
   * Add a new head route
   *
   * @param  string $pattern
   * @param  string $callback
   * @return object
   */
  public static function head(string $pattern, $callback)
  {
    return self::add(['HEAD'], $pattern, $callback);
  }

  /**
  * Add a new options route
  *
  * @param  string $pattern
  * @param  string $callback
  * @return object
  */
  public static function options(string $pattern, $callback)
  {
    return self::add(['OPTIONS'], $pattern, $callback);
  }

  /**
   * Add a new if route
   *
   * @param  string $pattern
   * @param  string $callback
   * @return object
   */
  public static function if(array $methods, string $pattern, $callback)
  {
    return self::add($methods, $pattern, $callback);
  }

  /**
   * Add a new "any" route
   *
   * @param  string $pattern
   * @param  string $callback
   * @return object
   */
  public static function any(string $pattern, $callback)
  {
    return self::add(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'], $pattern, $callback);
  }

  /**
   * Add a new redirect route
   *
   * @param  string $pattern
   * @param  string $redirect
   * @return object
   */
  public static function redirect(string $pattern, string $redirect)
  {
    return self::add(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'], $pattern, $redirect, true);
  }

  /**
   * Add a new get route (view)
   *
   * @param string $pattern
   * @param string $path
   * @return object
   */
  public static function view(string $pattern, string $path)
  {
    return self::add(['GET'], $pattern, $path, false, true);
  }

  /**
   * Find matching route for the current url
   *
   * @param  string $name
   * @return
   */
  public static function dispatch(?string $name = null, ?array $variables = null)
  {
    $router = new Route;
    $routes = self::$routes;

    $router->checkEvents();

    if ($name != null) return $router->singleDispatch($name, null, $variables);

    foreach($routes as $route) {
      if (in_array($router->method(), $route['method'])) {
        $isDomain = $router->isDomain($route['domain']);
        $matches  = $router->match($route);

        if (isset($route['variables']) && $route['variables'] !== null) {
          foreach ($route['variables'] as $key => $value) {
            $matches[$key] = $value;
          }
        }

        if ($matches && $isDomain) {

          $patternVars = is_array($matches) ? $matches : [];
          $domainVars  = is_array($isDomain) ? $isDomain : [];
          $variables   = array_merge($domainVars, $patternVars);

          $route['required'] = count($variables);

          self::$current = $route;
          $route['variables'] = $variables;

          if ($route['redirect']) {
            return $router->to($route);
          } else if ($route['view']) {
            if (is_callable(self::$viewEvent)) {
              $router->callView(self::$viewEvent, $variables);
            } else {
              $router->failed($routes, 405);
            }

            return;
          }

          return $router->handle((object)$route, $variables);
        }
      }
    }

    $router->failed($routes);
    return false;
  }

  /**
   * Dispatch specified route.
   *
   * @param string $name
   * @param array  $singleRoute
   * @return
   */
  private function singleDispatch(string $name, $singleRoute = null, ?array $variables = null)
  {
    $routes = self::$routes;

    if ($singleRoute) {
      self::$current = $singleRoute;

      if ($singleRoute['redirect']) {
        return $this->to($singleRoute);
      } else if ($singleRoute['view']) {
        if (is_callable(self::$viewEvent)) {
          $this->callView(self::$viewEvent, isset($singleRoute['variables']) ? $singleRoute['variables'] : []);
        } else {
          $this->failed($routes, 405);
        }
        return;
      }

      return $this->handle((object)$singleRoute, isset($singleRoute['variables']) ? $singleRoute['variables'] : []);
    }

    foreach($routes as $route) {
      if ($route['name'] == $name) {
        if ($variables != null) {
          $route['variables'] = $variables;
        }

        return $this->singleDispatch($name, $route);
      }
    }

    $this->failed($routes);
    return false;
  }

  /**
   * Check if all events have been set.
   *
   * @return void
   */
  private function checkEvents()
  {
    if (!is_callable(self::$failEvent)) {
      $this->exception('Swish "fail" event is unhandled.');
    }

    if (!is_callable(self::$beforeEvent)) {
      $this->exception('Swish "before" event is unhandled.');
    }

    if (!is_callable(self::$afterEvent)) {
      $this->exception('Swish "after" event is unhandled.');
    }
  }

  /**
   * Handle fail event.
   *
   * @param  array   $routes
   * @param  integer $code
   * @return void
   */
  private function failed($routes, $code = null)
  {
    $code = $code == null ? ($this->isNotAllowed($routes) ? 405 : 404) : $code;

    header('HTTP/1.1 '.$code);
    return call_user_func_array(self::$failEvent, [$this->isAjax(), $code]);
  }

  /**
   * Handle view event.
   *
   * @param  closure $event
   * @param  array   $matches
   * @return void
   */
  private function callView($event, $matches)
  {
    return call_user_func_array($event, is_bool($matches) ? [self::$current['callback'], []] : [self::$current['callback'], [$matches]]);
  }

  /**
   * Handle before event.
   *
   * @param  route   $route
   * @param  closure $callback
   * @return void
   */
  private function callBefore($route, $callback)
  {
    return call_user_func_array(self::$beforeEvent, [$route, $callback]);
  }

  /**
   * Handle after event.
   *
   * @param  route   $route
   * @param  closure $callback
   * @return void
   */
  private function callAfter($route, $callback)
  {
    return call_user_func_array(self::$afterEvent, [$route, $callback]);
  }

  /**
   * Check route status
   *
   * @param  object  $router
   * @param  array   $routes
   * @return boolean
   */
  private function isNotAllowed($routes)
  {
    foreach($routes as $route) {
      if ($this->match($route)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Redirect route to another route
   *
   * @param array @route
   */
  private function to($route)
  {
    header('Location: ' . $route['callback']);
    return false;
  }

  /**
   * Assign events
   *
   * @param string  $name
   * @param closure $callback
   */
  public static function assignEvent($name, Closure $callback)
  {
    if ($name == 'view') self::$viewEvent = $callback;
    if ($name == 'fail') self::$failEvent = $callback;
    if ($name == 'before') self::$beforeEvent = $callback;
    if ($name == 'after') self::$afterEvent = $callback;
  }

  /**
   * Execute route
   *
   * @param  object  $app
   * @param  object  $route
   * @param  array   $matches
   * @return boolean
   */
  private function handle($route, $matches)
  {
    $callback = $route->callback;

    if (is_string($route->callback)) {
    if (!str_contains($callback, '@')) $callback = $callback . '@handle';

    $fullyQualified = explode('@', $callback)[0];

    $controller = substr($fullyQualified, 0, 1) == '\\' ? $fullyQualified : self::$controller['namespace'] . '\\' . explode('@', $callback)[0];

    if (!class_exists($controller)) $this->exception(
      "Class '$controller' not found."
    );

    $class = new $controller;
    $method = explode('@', $callback)[1];

    if (!method_exists($class, $method)) $this->exception(
      "Method $controller::$method() does not exist"
    );

    $response = $this->callBefore($route, [$class, $method]);
    if ($response != [] || $response !== false) {
      $matches = $response;
    }

    $route->response = call_user_func_array(
      [$class, $method],
      is_array($matches) ? $matches : []
    );

    $this->callAfter($route, [$class, $method]);

    return true;
    }

    $response = $this->callBefore($route, $callback);

    if ($response != [] || $response !== false) {
      $matches = $response;
    }

    $route->response = call_user_func_array(
      $callback,
      is_array($matches) ? $matches : []
    );

    $this->callAfter($route, $callback);

    return true;
  }

  /**
   * Check if current request is xmlhttp or http
   *
   * @return boolean
   */
  public function isAjax()
  {
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
   * Get query string
   *
   * @param  string $getter
   * @return string
   */
  public static function query($getter = null)
  {
    if ($getter != null && isset($_GET[$getter])) {
      return $_GET[$getter];
    }

    if (isset($_SERVER['QUERY_STRING'])) {
      return $_SERVER['QUERY_STRING'];
    }
  }

  /**
   * Return current url without query string
   *
   * @return string
   */
  private function url()
  {
    $root = isset($_SERVER["SCRIPT_NAME"]) ? $_SERVER['SCRIPT_NAME'] : '';
    $dir = pathinfo($root)['dirname'];

    $length = strlen($this->uri());

    $url = str_replace(
      $this->url_query(),'',
      substr($this->uri(), -1) == '/' ? substr($this->uri(), 0, $length - 1) : $this->uri()
    );

    if (substr($dir, -1) == '/') return $url;

    return substr($url, strlen($dir));
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
  private function url_query()
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
   * Match domains
   *
   * @param string $domain
   * @return bool
   */
  private function isDomain(string $domain)
  {
    $pattern_regex = preg_replace("/\{(.*?)\}/", "(?P<$1>[\w-]+)", $domain);
    $pattern_regex = "#^" . trim($pattern_regex, "/") . "$#";

    preg_match(
      $pattern_regex,
      $_SERVER['HTTP_HOST'],
      $matches
    );

    if ($_SERVER['HTTP_HOST'] == $domain) return true;

    if ($matches) {
      return $this->clean($matches);
    }
  }

  /**
   * Match route with current url
   *
   * @param array $route
   * @return array $matches
   */
  private function match($route)
  {
    $pattern = substr($route['pattern'], -1) == '/' ? substr($route['pattern'], 0, strlen($route['pattern']) - 1) : $route['pattern'] ;

    /** match wildcard route */
    if ($pattern == '/*') {
      $matches = $this->clean(explode('/', $this->url()), true);
      if (count($matches) == 0) return true;

      return $matches;
    }

    /** match prefixed wildcard route */
    if (substr($pattern, strlen($pattern) - 2, 2) == "/*") {
      $url = $this->url();

      if (substr($pattern, 0, -2) == substr($url, 0, strrpos( $url, '/'))) {
        return true;
      }
    }

    $url = substr($this->url(), strlen($this->url()) - 1, 1);
    $url = $url == '?' ? substr($this->url(), 0, strlen($this->url()) - 1) : $this->url();

    if (substr($url, strlen($url) - 1, 1) == '/') {
      $url = substr($url, 0, strlen($url) - 1);
    }

    if (isset($route['regex'])) {
      $pattern_regex = $route['regex'];
    } else {
      $pattern_regex = preg_replace("/\{(.*?)-.\}/", "(?P<$1>[\w-]+)", $pattern);
      $pattern_regex = "#^" . trim($pattern_regex, "/") . "$#";
    }

    preg_match($pattern_regex, trim($url, "/"), $matches);

    if ((count($matches) == 1 && $pattern == '') || $pattern == $url || $route['regex'] && $matches) {
      return true;
    }

    if ($matches) {
      return $this->clean($matches);
    }
  }

  /**
  * Clean matches
  *
  * @param  array   $matches
  * @param  boolean $values
  * @return
  */
  private function clean(array $matches, bool $values = false)
  {
    if ($values) {
      foreach($matches as $k=>$e) {
        if ($matches[$k] == '') {
          unset($matches[$k]);
        }
      }
      return $matches;
    }

    return array_intersect_key(
      $matches,
      array_flip(
        array_filter(array_keys(
          $matches),
          'is_string'
        )
      )
    );
  }
}
