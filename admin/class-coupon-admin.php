<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/lelinhtinh
 * @since      1.0.0
 *
 * @package    Coupon
 * @subpackage Coupon/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks to
 * enqueue the admin-facing stylesheet and JavaScript.
 * As you add hooks and methods, update this description.
 *
 * @package    Coupon
 * @subpackage Coupon/admin
 * @author     lelinhtinh <lelinhtinh2013@gmail.com>
 */
class Coupon_Admin
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The unique prefix of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_prefix    The string used to uniquely prefix technical functions of this plugin.
	 */
	private $plugin_prefix;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $plugin_prefix    The unique prefix of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $plugin_prefix, $version)
	{
		$this->plugin_name   = $plugin_name;
		$this->plugin_prefix = $plugin_prefix;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_styles($hook_suffix)
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/coupon-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_scripts($hook_suffix)
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/coupon-admin.js', array('jquery'), $this->version, false);
	}

	public function options_page()
	{
		add_menu_page(
			'OMS Coupon Manager',
			'OMS Coupon',
			'manage_options',
			plugin_dir_path(__FILE__) . 'partials/coupon-admin-display.php',
			null,
			'dashicons-tickets-alt',
			99
		);
	}
}
