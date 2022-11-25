<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://github.com/lelinhtinh
 * @since      1.0.0
 *
 * @package    Coupon
 * @subpackage Coupon/public/partials
 */
?>

<?php
require dirname(__FILE__) . '/coupon-public-table.php';
$coupon_list_table = new Coupon_Public_Table();
$coupon_list_table->prepare_items();
?>
<div class="wrap">
<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form id="movies-filter" method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>" />
        <?php $coupon_list_table->display() ?>
    </form>
</div>