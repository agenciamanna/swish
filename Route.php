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
}