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
		$id = intval($atts['id']);

		global $wpdb;
		$coupon = $wpdb->get_row("
			SELECT c.ID, c.code, c.type, c.value, c.limit, c.activated_at, c.expired_at, COUNT(u.ID) AS number_of_uses
			FROM {$wpdb->prefix}oms_coupons AS c
			LEFT JOIN {$wpdb->prefix}oms_coupons_user AS cu ON cu.oms_coupon_id = c.ID
			LEFT JOIN {$wpdb->prefix}users AS u ON cu.user_id = u.ID
			WHERE c.active = 1 AND c.ID = {$id} AND ( c.expired_at IS NULL OR ( c.expired_at IS NOT NULL AND c.expired_at > CURDATE() ) )
			GROUP BY c.ID, c.code, c.type, c.value, c.limit, c.activated_at, c.expired_at
		", OBJECT);

		if (is_null($coupon)) {
			return '<script>console.warn("%c Coupon not found: ' . $id . '", "color:#ff5722")</script>';
		}

		$outData = [
			'code' => $coupon->code,
			'type' => $coupon->type,
			'value' => $coupon->value,
			'limit' => intval($coupon->limit),
			'number_of_uses' => intval($coupon->number_of_uses),
			'activated_at' => is_null($coupon->activated_at) ? '' : wp_strtotime($coupon->activated_at) - time(),
			'expired_at' => is_null($coupon->expired_at) ? '' : wp_strtotime($coupon->expired_at),
		];

		$remaining = $outData['limit'] - $outData['number_of_uses'];
		$is_disable = (!is_null($coupon->expired_at) && wp_strtotime($coupon->expired_at) <= time()) || $coupon->limit === $coupon->number_of_uses;
		return sprintf(
			<<<EOL
				<div class="oms-coupon-wrapper%7\$s" data-id="%1\$d" data-activation-time="%3\$d" data-expiration-time="%4\$d">
					<div class="oms-coupon-content">
					<div class="oms-coupon-code">%2\$s</div>
						<div class="oms-coupon-discount">%5\$s</div>
						<div class="oms-coupon-remaining">Remaining uses: <strong>%6\$d</strong></div>
					</div>
					<div class="oms-coupon-save">
						<button class="oms-coupon-save-btn" data-id="%1\$d" %8\$s>Save</button>
					</div>
					<div class="oms-coupon-timer"></div>
				</div>
			EOL,
			$id,
			$outData['code'],
			$outData['activated_at'],
			$outData['expired_at'],
			get_discount_string($outData),
			$remaining,
			$is_disable ? ' oms-coupon-disable' : '',
			$is_disable ? ' disabled' : ''
		);
	}

	public function options_user()
	{
		add_submenu_page(
			plugin_dir_path(dirname(__FILE__)) . 'admin/partials/coupon-admin-display.php',
			'OMS Coupon List',
			'Coupon List',
			'read',
			plugin_dir_path(__FILE__) . 'partials/coupon-public-display.php',
			null,
		);
	}
}
