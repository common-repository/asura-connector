<?php

namespace Dplugins\Asura\Connector\Utils;

use Exception;
use Medoo\Medoo;
use wpdb;

/**
 * Wrapper class of Medoo the simple database abstraction layer.
 * This class is used to run database queries with Medoo.
 * 
 * To use this class, you need to include the Medoo library in your project.
 * Run `composer require catfan/medoo` from your plugin root folder.
 * 
 * Medoo's object are available on `DB::db()` method.
 * Example usage:
 * - `DB::db()->select('*', DB::wpdb()->prefix.'users', ['user_email' => 'email@example.com']);`
 * - `DB::db()->count('*', DB::wpdb()->prefix.'options', ['optiona_name[~]' => 'mypluginprefix_' ]);`
 * 
 * @see https://medoo.in/doc Please read the Medoo's documentation for all the available methods.
 * 
 * @package Dplugins\Asura\Connector
 * @since 1.0.0
 * @author dplugins <mail@dplugins.com>
 * @copyright 2021 dplugins
 */
class DB
{
	private static $instances = [];

	private static $medoo;

	private static $wpdb;

	protected function __construct()
	{
		self::$wpdb = self::wpdb();

		$db_host = self::$wpdb->parse_db_host(self::$wpdb->dbhost);

		self::$medoo = new Medoo([
			'type'     => 'mysql',
			'host'     => $db_host[0],
			'port'     => $db_host[1],
			'socket'   => $db_host[2],
			'database' => self::$wpdb->dbname,
			'username' => self::$wpdb->dbuser,
			'password' => self::$wpdb->dbpassword,
			'prefix'   => self::$wpdb->prefix,
		]);
	}

	public static function getInstance(): DB
	{
		$cls = static::class;
		if (!isset(self::$instances[$cls])) {
			self::$instances[$cls] = new static();
		}

		return self::$instances[$cls];
	}

	public static function __callStatic(string $method, array $args)
	{
		return self::getInstance()::$medoo->{$method}(...$args);
	}

	public function __get(string $name)
	{
		return self::getInstance()::$medoo->{$name};
	}

	public function __wakeup()
	{
		throw new Exception("Cannot unserialize a singleton.");
	}

	protected function __clone()
	{
	}

	/**
	 * Get the wpdb object.
	 * 
	 * @return wpdb 
	 */
	public static function wpdb(): wpdb
	{
		/** @var wpdb $wpdb */
		global $wpdb;

		return $wpdb;
	}

	/**
	 * Get the medoo object.
	 * 
	 * example: `DB::db()->select('*', DB::wpdb()->prefix.'table_name', ['id' => 1]);`
	 * 
	 * @return Medoo 
	 */
	public static function db(): Medoo
	{
		return self::getInstance()::$medoo;
	}
}
