<?php

namespace Swish\Router;

use Closure;
use Exception;

class Route
{
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
}