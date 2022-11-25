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
<?php
$min_datetime = substr(str_replace(' ', 'T', tz_strtodate('now')), 0, -3);
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form id="oms_coupon_form" class="form-table">
        <table>
            <tbody>
                <tr>
                    <th scope="row"><label for="code"><?php esc_html_e('Coupon Code', 'oms-coupon') ?> <code class="status-error" title="<?php esc_attr_e('Require', 'oms-coupon') ?>">*</code></label></th>
                    <td colspan="2">
                        <input name="code" type="text" id="code" value="" class="regular-text" minlength="3" maxlength="16" pattern="[a-z0-z_-]{3,16}" autofocus required />
                        <p class="description"><?php esc_html_e('From 3 to 16 characters. Allow alphanumerics, dashes and underscores.', 'oms-coupon') ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="limit"><?php esc_html_e('Usage Limit', 'oms-coupon') ?> <code class="status-error" title="<?php esc_attr_e('Require', 'oms-coupon') ?>">*</code></label></th>
                    <td colspan="2"><input name="limit" type="number" min="0" step="1" id="limit" value="" class="small-text" required /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="type"><?php esc_html_e('Discount Value', 'oms-coupon') ?></label></th>
                    <td><input name="value" type="number" min="0" step="1" id="value" value="" class="regular-text" /></td>
                    <td>
                        <select name="type" id="type">
                            <option selected="selected" value="percentage"><?php esc_html_e('Percentage', 'oms-coupon') ?></option>
                            <option value="numeric"><?php esc_html_e('Numeric (₫)', 'oms-coupon') ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="activated_at"><?php esc_html_e('Activation Date', 'oms-coupon') ?></label></th>
                    <td colspan="2"><input name="activated_at" type="datetime-local" id="activated_at" value="" class="regular-text" min="<?php echo esc_attr($min_datetime) ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="expired_at"><?php esc_html_e('Expiration Date', 'oms-coupon') ?></label></th>
                    <td colspan="2"><input name="expired_at" type="datetime-local" id="expired_at" value="" class="regular-text" min="<?php echo esc_attr($min_datetime) ?>" /></td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary button-large" value="<?php esc_attr_e('Create Coupon', 'oms-coupon') ?>" />
            &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="status-error" id="help_text"></span>
        </p>
    </form>
</div>

<?php
require dirname(__FILE__) . '/coupon-admin-table.php';
$coupon_list_table = new Coupon_Admin_Table();
$coupon_list_table->prepare_items();
?>
<div class="wrap">
    <form id="movies-filter" method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>" />
        <?php $coupon_list_table->display() ?>
    </form>
</div>