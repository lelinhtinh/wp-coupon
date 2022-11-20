<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/lelinhtinh
 * @since      1.0.0
 *
 * @package    Coupon
 * @subpackage Coupon/admin/partials
 */

?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form action="options.php" method="post">
    <?php
    // output security fields for the registered setting "oms_coupon_options"
    settings_fields( 'oms_coupon_options' );
    // output setting sections and their fields
    // (sections are registered for "oms_coupon", each field is registered to a specific section)
    do_settings_sections( 'oms_coupon' );
    // output save settings button
    submit_button( __( 'Save Settings', 'textdomain' ) );
    ?>
    </form>
</div>
