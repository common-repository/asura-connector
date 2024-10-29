<?php
/**
 * Asura Connector
 *
 * @wordpress-plugin
 * Plugin Name:         Asura Connector
 * Description:         Access to design sets collections managed by the Asura plugin.
 * Version:             4.1.0
 * Author:              dPlugins
 * Author URI:          https://dplugins.com
 * Requires at least:   5.5
 * Tested up to:        5.8
 * Requires PHP:        7.4
 * Text Domain:         asura-connector
 * Domain Path:         /languages
 *
 * @package             Asura Connector
 * @author              dplugins <mail@dplugins.com>
 * @link                https://dplugins.com
 * @since               1.0.0
 * @copyright           2021 dplugins
 * @version             4.1.0
 */

defined( 'ABSPATH' ) || exit;

define( 'ASURA_CONNECTOR_FILE', __FILE__ );

require_once __DIR__ . '/vendor/autoload.php';

\Dplugins\Asura\Connector\Connector::run('aether_m_connector', '4.1.0', '001');
