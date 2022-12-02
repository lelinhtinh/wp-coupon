<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress or ClassicPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/lelinhtinh
 * @since             1.0.0
 * @package           Coupon
 *
 * @wordpress-plugin
 * Plugin Name:       OMS Coupon
 * Plugin URI:        https://github.com/lelinhtinh/wp-coupon
 * Description:       WordPress Coupon plugin to promote affiliate coupon and deals on your WordPress site.
 * Version:           1.0.2
 * Author:            lelinhtinh
 * Requires at least: 4.4
 * Requires PHP:      7.0
 * Tested up to:      6.1.1
 * Author URI:        https://github.com/lelinhtinh
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       oms-coupon
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('COUPON_VERSION', '1.0.2');

/**
 * Define the Plugin basename
 */
define('COUPON_BASE_NAME', 'oms_coupon');

/**
 * The code that runs during plugin activation.
 *
 * This action is documented in includes/class-coupon-activator.php
 * Full security checks are performed inside the class.
 */
function oms_activate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-coupon-activator.php';
	Coupon_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 *
 * This action is documented in includes/class-coupon-deactivator.php
 * Full security checks are performed inside the class.
 */
function oms_deactivate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-coupon-deactivator.php';
	Coupon_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'oms_activate');
register_deactivation_hook(__FILE__, 'oms_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-coupon.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * Generally you will want to hook this function, instead of callign it globally.
 * However since the purpose of your plugin is not known until you write it, we include the function globally.
 *
 * @since    1.0.0
 */
function oms_run()
{
	$plugin = new Coupon();
	$plugin->run();
}
oms_run();
