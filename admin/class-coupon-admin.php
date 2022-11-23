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
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/coupon-admin.css', [], $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_scripts($hook_suffix)
	{
		if (endsWith('/admin/partials/coupon-admin-display.php', $hook_suffix)) {
			return;
		}

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/coupon-admin.js', ['jquery'], $this->version, false);

		$title_nonce = wp_create_nonce($this->plugin_prefix . $this->plugin_name . '_form');
		wp_localize_script(
			$this->plugin_name,
			$this->plugin_prefix . $this->plugin_name . '_admin_ajax',
			[
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => $title_nonce,
			]
		);
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

	public function save_coupon()
	{
		check_ajax_referer($this->plugin_prefix . $this->plugin_name . '_form');
		if (!$_POST['action'] || $_POST['action'] != 'oms_coupon_create' || !is_admin()) {
			header('Status: 403 Forbidden', true, 403);
			wp_die();
		}

		$user_id  = get_current_user_id();
		$code = sanitize_key($_POST['code']);

		global $wpdb;
		$findOne = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}oms_coupons WHERE code = '{$code}'", OBJECT);
		if (!is_null($findOne)) {
			wp_send_json([
				'status' => 'error',
				'message' => 'Coupon code already exists'
			], 400);
		}

		$type = in_array($_POST['type'], ['percentage', 'numeric']) ? $_POST['type'] : 'percentage';
		$value = !empty($_POST['value']) ? intval($_POST['value']) : null;
		$limit = !empty($_POST['limit']) ? intval($_POST['limit']) : null;
		$activated_at = !empty($_POST['activated_at']) ? date('Y-m-d\TH:i:s', strtotime($_POST['activated_at'])) : null;
		$expired_at = !empty($_POST['expired_at']) ? date('Y-m-d\TH:i:s', strtotime($_POST['expired_at'])) : null;
		if ($activated_at > $expired_at) {
			wp_send_json([
				'status' => 'error',
				'message' => 'Expiration Date must be after Activation Date'
			], 422);
		}

		$insert_data = [
			'code' => $code,
			'type' => $type,
			'value' => $value,
			'limit' => $limit,
			'activated_at' => $activated_at,
			'expired_at' => $expired_at,
		];
		$wpdb->insert(
			$wpdb->prefix . 'oms_coupons',
			$insert_data,
			['%s', '%s', '%d', '%d', '%s', '%s']
		);
		$insert_data['ID'] = $wpdb->insert_id;

		wp_send_json([
			'status' => 'ok',
			'data' => $insert_data
		], 201);
	}
}
