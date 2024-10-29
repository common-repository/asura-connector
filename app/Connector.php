<?php

namespace Dplugins\Asura\Connector;

use Dplugins\Asura\Connector\Models\License;
use Dplugins\Asura\Connector\Models\Provider;
use Dplugins\Asura\Connector\Utils\DB;
use Dplugins\Asura\Connector\Utils\Migration;
use Dplugins\Asura\Connector\Utils\OxygenBuilder;
use Dplugins\Asura\Connector\Utils\Utils;

class Connector
{
    public static $module_id;
    public static $version;
    public static $db_version;

    public function __construct($module_id, $version, $db_version)
    {
        self::$module_id = $module_id;
        self::$version = $version;
        self::$db_version = $db_version;

        add_filter('plugin_action_links_' . plugin_basename(ASURA_CONNECTOR_FILE), function ($links) {
            return Utils::plugin_action_links($links, self::$module_id);
        });

        register_activation_hook(ASURA_CONNECTOR_FILE, [$this, 'plugin_activate']);
        register_deactivation_hook(ASURA_CONNECTOR_FILE, [$this, 'plugin_deactivate']);
        register_uninstall_hook(ASURA_CONNECTOR_FILE, [Connector::class, 'plugin_uninstall']);

		add_action('admin_enqueue_scripts', function () {
            wp_register_style(self::$module_id . "-admin", plugins_url('/public', ASURA_CONNECTOR_FILE) . '/css/admin.css', [], self::$version);
            wp_register_script(self::$module_id . "-manifest", plugins_url('/public', ASURA_CONNECTOR_FILE) . '/js/manifest.js', ['wp-i18n'], false, true);
            wp_register_script(self::$module_id . "-vendor", plugins_url('/public', ASURA_CONNECTOR_FILE) . '/js/vendor.js', [self::$module_id . "-manifest"], false, true);
            wp_register_script(self::$module_id . "-admin", plugins_url('/public', ASURA_CONNECTOR_FILE) . '/js/admin.js', [self::$module_id . "-vendor"], self::$version, true);
        });

        add_action('plugins_loaded', [$this, 'init_plugin']);
    }

    public static function run($module_id, $version, $db_version)
    {
        static $instance = false;

        if (!$instance) {
            $instance = new Connector($module_id, $version, $db_version);
        }

        return $instance;
    }

    public function init_plugin()
    {
        Utils::localization('asura-connector', ASURA_CONNECTOR_FILE);

        add_action('init', [$this, 'boot']);
    }

    public function boot()
    {
        if (Utils::is_request('ajax')) {
            new Ajax(self::$module_id);
        }

        if (OxygenBuilder::can()) {
            if (Utils::is_request('frontend')) {
                new Frontend(self::$module_id);
            }
        }

        if (OxygenBuilder::can(true)) {
            if (Utils::is_request('admin')) {
                new Admin(self::$module_id);
            }
        }
    }

    public function plugin_activate(): void
    {
        if (!get_option('asura_connector_installed')) {
            update_option('asura_connector_installed', time());
        }

        $installed_db_version = get_option('asura_connector_db_version');

        if (!$installed_db_version || intval($installed_db_version) !== intval(self::$db_version)) {
            Migration::migrate(dirname(ASURA_CONNECTOR_FILE) . '/database/migrations/', "Dplugins\\Asura\\Connector\\Database\\Migrations", $installed_db_version ?: 0, self::$db_version);
            update_option('asura_connector_db_version', self::$db_version);
        }

        update_option('asura_connector_version', self::$version);
    }

    public static function plugin_uninstall(): void
    {
        delete_option('asura_connector_installed');
        delete_option('asura_connector_version');
        delete_option('asura_connector_db_version');

        DB::db()->drop(Provider::TABLE_NAME);
        DB::db()->drop(License::TABLE_NAME);
    }

    public function plugin_deactivate(): void
    {
    }
}
