<?php

namespace Dplugins\Asura\Connector\Utils;

use Closure;

/**
 * WordPress Transient API wrapper class
 * 
 * @package Dplugins\Asura\Connector
 * @since 1.0.0
 * @author dplugins <mail@dplugins.com>
 * @copyright 2021 dplugins
 */
class Transient
{
	/**
	 * Check if transient exists.
	 * 
	 * Example:
	 * - `Transient::has('myfavoritebook');`
	 * 
	 * @param mixed $key The transient key to check if exists.
	 * @return bool 
	 */
	public static function has($key): bool
	{
		return get_transient($key) !== false ? true : false;
	}

	/**
	 * Get transient value.
	 * 
	 * Example:
	 * - `Transient::get('myfavoritebook');`
	 * - `Transient::get('myfavoritebook', ['title' => 'The Purpose Driven Life', 'author' => 'Rick Warren']);`
	 * 
	 * @param mixed $key The transient key to get
	 * @param bool $default The fallback value if the transient does not exist
	 * @return mixed 
	 */
	public static function get($key, $default = false)
	{
		return get_transient($key, $default);
	}

	/**
	 * Set transient value with expiration time.
	 * 
	 * Example:
	 * - `Transient::set('my_favorite_soccer_player', 'Ronaldinho', 7 * YEAR_IN_SECONDS);`
	 * - `Transient::set(
	 * 		['myfavoritebook', ['title' => 'The Purpose Driven Life', 'author' => 'Rick Warren']],
	 * 		['my_favorite_anime', 'Fairy Tail'],
	 * 	);`
	 * 
	 * @param string|array $key The transient key or an array of transient to set
	 * @param mixed|null $value The transient value
	 * @param int $ttl The duration of the transient in seconds. Default: `0` (no expiration)
	 * @return void 
	 */
	public static function set($key, $value = null, $ttl = 0)
	{
		if (is_array($key)) {
			foreach ($key as $k => $v) {
				if (!is_array($v)) {
					self::set($k, $v, $ttl);
				} else {
					self::set($k, $v[0], $v[1]);
				}
			}
			return;
		}

		set_transient($key, $value, $ttl);
	}

	/**
	 * Delete transient.
	 * 
	 * Example:
	 * - `Transient::delete('top_computer_brand');`
	 * 
	 * @param mixed $key The transient key to delete.
	 * @return bool 
	 */
	public static function delete($key)
	{
		return delete_transient($key);
	}

	/**
	 * Get the transient or set it if it does not exist using the given callback.
	 * 
	 * Example:
	 * - `Transient::remember('my_favorite_soccer_player', 30 * DAY_IN_SECONDS, fn() => 'Christiano Ronaldo');`
	 * 
	 * @param mixed $key The transient key to get
	 * @param mixed $ttl The duration of the transient in seconds. Default: `0` (no expiration)
	 * @param Closure $callback Function to call if the transient does not exist, return the value that will saved.
	 * @return mixed 
	 */
	public static function remember($key, $ttl, Closure $callback)
	{
		$item = self::get($key);

		if ($item !== false) {
			return $item;
		}

		self::set($key, $item = $callback(), $ttl);

		return $item;
	}
}
