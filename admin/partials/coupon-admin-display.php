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
$min_date = date("Y-m-d");
$min_time = date("h:i");
$min_datetime = $min_date . "T" . $min_time;

$max_date = date("Y-m-d", strtotime("+7 Days"));
$max_time = date("h:i");
$max_datetime = $max_date . "T" . $max_time;
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form id="oms_coupon_form" class="form-table">
        <table>
            <tbody>
                <tr>
                    <th scope="row"><label for="code">Coupon Code <code class="status-error" title="Require">*</code></label></th>
                    <td colspan="2">
                        <input name="code" type="text" id="code" value="" class="regular-text" minlength="3" maxlength="16" pattern="[a-z0-z_-]{3,16}" autofocus required>
                        <p class="description">From 3 to 16 characters. Allow alphanumerics, dashes and underscores.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="limit">Usage Limit <code class="status-error" title="Require">*</code></label></th>
                    <td colspan="2"><input name="limit" type="number" min="0" step="1" id="limit" value="" class="small-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="type">Discount Value</label></th>
                    <td><input name="value" type="number" min="0" step="1" id="value" value="" class="regular-text"></td>
                    <td>
                        <select name="type" id="type">
                            <option selected="selected" value="percentage">Percentage</option>
                            <option value="numeric">Numeric (₫)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="activated_at">Activation Date</label></th>
                    <td colspan="2"><input name="activated_at" type="datetime-local" id="activated_at" value="" class="regular-text" min="<?php echo $min_datetime ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="expired_at">Expiration Date</label></th>
                    <td colspan="2"><input name="expired_at" type="datetime-local" id="expired_at" value="" class="regular-text" min="<?php echo $min_datetime ?>"></td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary button-large" value="Create Coupon">
            &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="status-error" id="help_text"></span>
        </p>
    </form>
</div>

<?php
require dirname(__FILE__) . '/coupon-list-table.php';
$coupon_list_table = new Coupon_List_Table();
$coupon_list_table->prepare_items();
?>
<div class="wrap">
    <form id="movies-filter" method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <?php $coupon_list_table->display() ?>
    </form>
</div>