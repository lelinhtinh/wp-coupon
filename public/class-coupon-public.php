<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/lelinhtinh
 * @since      1.0.0
 *
 * @package    Coupon
 * @subpackage Coupon/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks to
 * enqueue the public-facing stylesheet and JavaScript.
 * As you add hooks and methods, update this description.
 *
 * @package    Coupon
 * @subpackage Coupon/public
 * @author     lelinhtinh <lelinhtinh2013@gmail.com>
 */
class Coupon_Public
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
	 * @param      string $plugin_name      The name of the plugin.
	 * @param      string $plugin_prefix          The unique prefix of this plugin.
	 * @param      string $version          The version of this plugin.
	 */
	public function __construct($plugin_name, $plugin_prefix, $version)
	{
		$this->plugin_name   = $plugin_name;
		$this->plugin_prefix = $plugin_prefix;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/coupon-public.css', [], $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script('goodtimer', plugin_dir_url(__FILE__) . 'js/goodtimer-3.4.0.js', [], '3.4.0', true);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/coupon-public.js', ['jquery', 'goodtimer'], $this->version, true);

		$title_nonce = wp_create_nonce($this->plugin_prefix . $this->plugin_name . '_save_nonce');
		wp_localize_script(
			$this->plugin_name,
			$this->plugin_prefix . $this->plugin_name . '_user_ajax',
			[
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => $title_nonce,
			]
		);
	}

	/**
	 * Example of Shortcode processing function.
	 *
	 * Shortcode can take attributes like [coupon-shortcode attribute='123']
	 * Shortcodes can be enclosing content [coupon-shortcode attribute='123']custom content[/coupon-shortcode].
	 *
	 * @see https://developer.wordpress.org/plugins/shortcodes/enclosing-shortcodes/
	 *
	 * @since    1.0.0
	 * @param    array  $atts    ShortCode Attributes.
	 * @param    mixed  $content ShortCode enclosed content.
	 * @param    string $tag    The Shortcode tag.
	 */
	public function oms_shortcode_func($atts = [], $content = null, $tag = '')
	{
		/**
		 * Combine user attributes with known attributes.
		 *
		 * @see https://developer.wordpress.org/reference/functions/shortcode_atts/
		 *
		 * Pass third parameter $shortcode to enable ShortCode Attribute Filtering.
		 * @see https://developer.wordpress.org/reference/hooks/shortcode_atts_shortcode/
		 */
		$atts = shortcode_atts(
			[
				"style" => "text",
				"id" => null,
			],
			$atts,
			$this->plugin_prefix . $this->plugin_name
		);

		/**
		 * Build our ShortCode output.
		 * Remember to sanitize all user input.
		 * In this case, we expect a integer value to be passed to the ShortCode attribute.
		 *
		 * @see https://developer.wordpress.org/themes/theme-security/data-sanitization-escaping/
		 */
		$user_id = get_current_user_id();
		$coupon_id = intval($atts['id']);

		global $wpdb;
		$findOne = $wpdb->get_row($wpdb->prepare(
			"SELECT
				c.ID, c.code, c.type, c.value, c.limit, c.activated_at, c.expired_at,
				COUNT(cu.user_id) AS number_of_uses, GROUP_CONCAT(cu.user_id SEPARATOR ',') AS used_by_id
			FROM {$wpdb->prefix}oms_coupons AS c
			LEFT JOIN {$wpdb->prefix}oms_coupons_user AS cu ON cu.oms_coupon_id = c.ID
			WHERE c.ID = %d AND c.active = 1
			GROUP BY c.ID, c.code, c.type, c.value, c.limit, c.activated_at, c.expired_at",
			$coupon_id,
		), OBJECT);

		if (is_null($findOne)) {
			return '<script>console.warn("%c Coupon not found: ' . esc_attr($coupon_id) . '", "color:#ff5722")</script>';
		}

		$outData = [
			'code' => $findOne->code,
			'type' => $findOne->type,
			'value' => $findOne->value,
			'limit' => intval($findOne->limit),
			'number_of_uses' => intval($findOne->number_of_uses),
			'activated_at' => is_null($findOne->activated_at) ? '' : tz_strtodate($findOne->activated_at, true) - tz_strtodate('now', true),
			'expired_at' => is_null($findOne->expired_at) ? '' : tz_strtodate($findOne->expired_at, true) - tz_strtodate('now', true),
		];

		$remaining = $outData['limit'] - $outData['number_of_uses'];
		$used_by_id = explode(',', $findOne->used_by_id ?? '');
		$is_disable = (!is_null($findOne->expired_at) && tz_strtodate($findOne->expired_at, true) < tz_strtodate('now', true))
			|| $findOne->limit === $findOne->number_of_uses
			|| (is_user_logged_in() && in_array($user_id, $used_by_id));
		$save_button = is_user_logged_in()
			? '<button class="oms-coupon-save-btn oms-coupon-user" data-id="' . $coupon_id . '"' . ($is_disable ? ' disabled' : '') . '>Save</button>'
			: '<a class="oms-coupon-save-btn oms-coupon-nopriv" href="' . wp_login_url(get_permalink()) . '">Save</a>';

		return sprintf(
			"
			<div class=\"oms-coupon-wrapper%8\$s\" data-id=\"%1\$d\" data-activation-time=\"%3\$d\" data-expiration-time=\"%4\$d\">
				<div class=\"oms-coupon-content\">
				<div class=\"oms-coupon-code\">%2\$s</div>
					<div class=\"oms-coupon-discount\">%5\$s <strong>%6\$s</strong></div>
					<div class=\"oms-coupon-remaining\">%10\$s: <strong>%7\$d</strong></div>
				</div>
				<div class=\"oms-coupon-save\">
					%9\$s
					<span class=\"oms-coupon-expire\"></span>
				</div>
				<div class=\"oms-coupon-timer\"></div>
			</div>
			",
			$coupon_id,
			$outData['code'],
			$outData['activated_at'],
			$outData['expired_at'],
			esc_html__('Discount', 'oms-coupon'),
			get_discount_string($outData),
			$remaining,
			$is_disable ? ' oms-coupon-disable' : '',
			$save_button,
			esc_html__('Remaining uses', 'oms-coupon'),
		);
	}

	public function options_user()
	{
		add_submenu_page(
			plugin_dir_path(dirname(__FILE__)) . 'admin/partials/coupon-admin-display.php',
			esc_html__('OMS Coupon List', 'oms-coupon'),
			esc_html__('Coupon List', 'oms-coupon'),
			'read',
			plugin_dir_path(__FILE__) . 'partials/coupon-public-display.php',
			null,
		);
	}

	public function save_coupon()
	{
		check_ajax_referer($this->plugin_prefix . $this->plugin_name . '_save_nonce');
		if (get_request_parameter('action') !== 'oms_coupon_save') {
			header('Status: 403 Forbidden', true, 403);
			wp_die();
		}

		$user_id  = get_current_user_id();
		$coupon_id = intval(get_request_parameter('id'));
		if (!$coupon_id) {
			header('Status: 400 Bad Request', true, 400);
			wp_die();
		}
		$now = tz_strtodate('now');

		global $wpdb;

		$findOne = $wpdb->get_row($wpdb->prepare(
			"SELECT c.ID, c.limit,
				COUNT(cu.user_id) AS number_of_uses, GROUP_CONCAT(cu.user_id SEPARATOR ',') AS used_by_id
			FROM {$wpdb->prefix}oms_coupons AS c
			LEFT JOIN {$wpdb->prefix}oms_coupons_user AS cu ON cu.oms_coupon_id = c.ID
			WHERE
				c.ID = %1\$d AND c.active = 1
				AND ( ( c.activated_at IS NOT NULL AND c.activated_at < '%2\$s' ) OR c.activated_at IS NULL )
				AND ( ( c.expired_at IS NOT NULL AND c.expired_at > '%2\$s' ) OR c.expired_at IS NULL )
			GROUP BY c.ID, c.limit
			HAVING c.limit > number_of_uses",
			$coupon_id,
			$now,
		), OBJECT);

		if (is_null($findOne)) {
			wp_send_json([
				'status' => 'error',
				'message' => esc_html__('Coupon not avaliable', 'oms-coupon'),
			]);
		}

		$used_by_id = explode(',', $findOne->used_by_id ?? '');
		if (in_array($user_id, $used_by_id)) {
			wp_send_json([
				'status' => 'error',
				'message' => esc_html__('You got it, at ', 'oms-coupon') . $findOne->saved_at,
			]);
		}

		$wpdb->insert(
			$wpdb->prefix . 'oms_coupons_user',
			[
				'user_id' => $user_id,
				'oms_coupon_id' => $coupon_id,
				'saved_at' => $now,
			],
			['%d', '%d', '%s']
		);

		wp_send_json([
			'status' => 'ok',
			'data' => [
				'saved_at' => $now,
			],
		], 201);
	}
}
