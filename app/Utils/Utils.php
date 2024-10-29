<?php

namespace Dplugins\Asura\Connector\Utils;

use Dplugins\Asura\Connector\SubNamespaceNames;
use League\Plates\Engine;
use Medoo\Medoo;

/**
 * Useful functions.
 * 
 * @package Dplugins\Asura\Connector
 * @since 1.0.0
 * @author dplugins <mail@dplugins.com>
 * @copyright 2021 dplugins
 */
class Utils
{
	/**
	 * The proxy of `DB::db()` method.
	 * 
	 * @return Medoo 
	 */
	public static function db(): Medoo
	{
		return DB::db();
	}

	/**
	 * The proxy of `Template::template()` method.
	 * 
	 * @return Medoo 
	 */
	public static function template(): Engine
	{
		return Template::template();
	}

	/**
	 * Prefix the `get_option()` function with `SubNamespaceNames::$slug`.
	 * 
	 * @see https://developer.wordpress.org/reference/functions/get_option/
	 * 
	 * @param mixed $option 
	 * @param bool $default 
	 * @return mixed 
	 */
	public static function get_option($option, $default = false)
	{
		return \get_option(SubNamespaceNames::$slug . '_' . $option, $default);
	}

	/**
	 * Prefix the `update_option()` function with `SubNamespaceNames::$slug`.
	 * 
	 * @see https://developer.wordpress.org/reference/functions/update_option/
	 * 
	 * @param mixed $option 
	 * @param mixed $value 
	 * @param mixed|null $autoload 
	 * @return bool 
	 */
	public static function update_option($option, $value, $autoload = null)
	{
		return \update_option(SubNamespaceNames::$slug . '_' . $option, $value);
	}

	/**
	 * Prefix the `delete_option()` function with `SubNamespaceNames::$slug`. 
	 * 
	 * @see https://developer.wordpress.org/reference/functions/delete_option/
	 * @param mixed $option 
	 * @return bool 
	 */
	public static function delete_option($option)
	{
		return \delete_option(SubNamespaceNames::$slug . '_' . $option);
	}

	public static function redirect($location)
	{
		if (headers_sent() === false) {
			wp_redirect($location);
		} else {
			echo '<meta http-equiv="refresh" content="0;url=' . $location . '">';
		}
		exit;
	}

	/**
	 * Check the type of the current request.
	 * 
	 * @param string $type The type of the request. Available values: `admin`, `ajax`, `frontend`, `rest`, `cron`.
	 * @return bool 
	 */
	public static function is_request(string $type): bool
	{
		switch ($type) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined('DOING_AJAX');
			case 'rest':
				return defined('REST_REQUEST');
			case 'cron':
				return defined('DOING_CRON');
			case 'frontend':
				return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
			default:
				return false;
				break;
		}
	}

	/**
	 * Add hyperlink on Manage Plugins page.
	 * @param mixed $links 
	 * @return mixed 
	 */
	public static function plugin_action_links($links, $setting_page_slug)
	{
		$plugin_shortcuts = [
			'<a href="' . add_query_arg( [ 'page' => $setting_page_slug ], admin_url( 'admin.php' ) ) . '">Settings</a>',
			// '<a href="https://go.oxyrealm.com/donate" target="_blank" style="color:#3db634;">Buy me a coffee ☕</a>'
		];

		return array_merge( $links, $plugin_shortcuts );
	}

	public static function localization( $domain, $plugin_file ): void {
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( $plugin_file ) ) . '/languages/' );
	}

}
