<?php

namespace AtlantisPHP\Swish;

use Closure;

class SwishHandler
{
	/**
	 * Handle view event
	 *
	 * @param closure $callback
	 */
	public static function view(Closure $callback)
	{
		Route::assignEvent('view', $callback);;
	}

	/**
	 * Handle fail event
	 *
	 * @param closure $callback
	 */
	public static function fail(Closure $callback)
	{
		Route::assignEvent('fail', $callback);;
	}

	/**
	 * Handle route event
	 *
	 * @param closure $callback
	 */
	public static function before(Closure $callback)
	{
		Route::assignEvent('before', $callback);;
	}

	/**
	 * Handle route event
	 *
	 * @param closure $callback
	 */
	public static function after(Closure $callback)
	{
		Route::assignEvent('after', $callback);;
	}

	/**
	 * Set global namespace
	 *
	 * @param closure $callback
	 */
	public static function setNamespace(string $namespace)
	{
		Route::$controller['namespace'] = $namespace;
	}
}