<?php

/**
 * WP List Table Example class
 *
 * @package   WPListTableExample
 * @author    Matt van Andel
 * @copyright 2016 Matthew van Andel
 * @license   GPL-2.0+
 */

/**
 * Example List Table Child Class
 *
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 *
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 *
 * Our topic for this list table is going to be coupons.
 *
 * @package WPListTableExample
 * @author  Matt van Andel
 */
class Coupon_Admin_Table extends WP_List_Table
{
    /**
     * Coupone_List_Table constructor.
     *
     * REQUIRED. Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     */
    public function __construct()
    {
        // Set parent defaults.
        parent::__construct([
            'singular' => 'coupon',    // Singular name of the listed records.
            'plural'   => 'coupons',   // Plural name of the listed records.
            'ajax'     => false,       // Does this table support ajax?
        ]);
    }

    /**
     * Get a list of columns. The format is:
     * 'internal-name' => 'code'
     *
     * REQUIRED! This method dictates the table's columns and codes. This should
     * return an array where the key is the column slug (and class) and the value
     * is the column's code text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     *
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a `column_cb()` method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     *
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information.
     */
    public function get_columns()
    {
        $columns = [
            'cb'             => '<input type="checkbox" />',   // Render a checkbox instead of text.
            'code'           => esc_html__('Coupon Code', 'oms-coupon'),
            'value'          => esc_html__('Discount', 'oms-coupon'),
            'limit'          => esc_html__('Usage Limit', 'oms-coupon'),
            'activated_at'   => esc_html__('Activation Date', 'oms-coupon'),
            'expired_at'     => esc_html__('Expiration Date', 'oms-coupon'),
            'number_of_uses' => esc_html__('N. Uses', 'oms-coupon'),
            'used_by'        => esc_html__('Used By', 'oms-coupon'),
        ];

        return $columns;
    }

    /**
     * Get a list of sortable columns. The format is:
     * 'internal-name' => 'orderby'
     * or
     * 'internal-name' => ['orderby', true]
     *
     * The second format will make the initial sorting order be descending
     *
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
     * you will need to register it here. This should return an array where the
     * key is the column that needs to be sortable, and the value is db column to
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     *
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within `prepare_items()` and sort
     * your data accordingly (usually by modifying your query).
     *
     * @return array An associative array containing all the columns that should be sortable.
     */
    protected function get_sortable_columns()
    {
        $sortable_columns = [
            'code'         => ['code', false],
            'value'        => ['value', false],
            'limit'        => ['limit', false],
            'activated_at' => ['activated_at', false],
            'expired_at'   => ['expired_at', false],
        ];

        return $sortable_columns;
    }

    /**
     * Get default column value.
     *
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'code', it would first see if a method named $this->column_code()
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as
     * possible.
     *
     * Since we have defined a column_code() method later on, this method doesn't
     * need to concern itself with any column with a name of 'code'. Instead, it
     * needs to handle everything else.
     *
     * For more detailed insight into how columns are handled, take a look at
     * WP_List_Table::single_row_columns()
     *
     * @param object $item        A singular item (one full row's worth of data).
     * @param string $column_name The name/slug of the column to be processed.
     * @return string Text or HTML to be placed inside the column <td>.
     */
    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'code':
            case 'value':
            case 'limit':
            case 'activated_at':
            case 'expired_at':
            case 'number_of_uses':
            case 'used_by':
                return $item[$column_name];
            default:
                return print_r($item, true); // Show the whole array for troubleshooting purposes.
        }
    }

    /**
     * Get value for checkbox column.
     *
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     *
     * @param object $item A singular item (one full row's worth of data).
     * @return string Text to be placed inside the column <td>.
     */
    protected function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],  // Let's simply repurpose the table's singular label ("coupon").
            $item['ID']                // The value of the checkbox should be the record's ID.
        );
    }

    /**
     * Get code column value.
     *
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'code'. Every time the class
     * needs to render a column, it first looks for a method named
     * column_{$column_code} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     *
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links are
     * secured with wp_nonce_url(), as an expected security measure.
     *
     * @param object $item A singular item (one full row's worth of data).
     * @return string Text to be placed inside the column <td>.
     */
    protected function column_code($item)
    {
        $page = get_request_parameter('page', 1);

        // Build hide row action.
        $hide_query_args = [
            'page'      => $page,
            'action'    => 'hide',
            $this->_args['singular'] => $item['ID'],
        ];

        $actions['hide'] = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url(wp_nonce_url(add_query_arg($hide_query_args, 'admin.php'), 'hidecoupon_' . $item['ID'])),
            esc_html_x('Hide', 'List table row action', 'oms-coupon')
        );

        // Build delete row action.
        $delete_query_args = [
            'page'      => $page,
            'action'    => 'delete',
            $this->_args['singular'] => $item['ID'],
        ];

        $actions['delete'] = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url(wp_nonce_url(add_query_arg($delete_query_args, 'admin.php'), 'deletecoupon_' . $item['ID'])),
            esc_html_x('Delete', 'List table row action', 'oms-coupon')
        );

        // Return the code contents.
        return sprintf(
            '<span class="oms-coupon-code%1$s%2$s">%3$s</span> <span data-id="%4$d" title="Click to Copy" class="oms-coupon-shortcode">[oms_coupon id="%4$d"]</span>%5$s',
            empty($item['limit']) || intval($item['limit']) > intval($item['number_of_uses']) ? '' : ' status-warning',
            !empty($item['expired_at']) && tz_strtodate($item['expired_at'], true) < tz_strtodate('now', true)  ? ' status-error' : '',
            $item['code'],
            $item['ID'],
            $this->row_actions($actions)
        );
    }

    protected function column_limit($item)
    {
        return sprintf(
            '<span class="oms-coupon-limit%1$s">%2$d</span>',
            empty($item['limit']) || intval($item['limit']) > intval($item['number_of_uses']) ? '' : ' status-warning',
            $item['limit'],
        );
    }

    protected function column_number_of_uses($item)
    {
        return sprintf(
            '<span class="oms-coupon-number_of_uses%1$s">%2$d</span>',
            empty($item['limit']) || intval($item['limit']) > intval($item['number_of_uses']) ? '' : ' status-warning',
            $item['number_of_uses'],
        );
    }

    protected function column_expired_at($item)
    {
        return sprintf(
            '<span class="oms-coupon-expired_at%1$s">%2$s</span>',
            !empty($item['expired_at']) && tz_strtodate($item['expired_at'], true) < tz_strtodate('now', true)  ? ' status-error' : '',
            $item['expired_at'],
        );
    }

    protected function column_value($item)
    {
        return get_discount_string($item);
    }

    /**
     * Get an associative [option_name => option_code] with the list
     * of bulk actions available on this table.
     *
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible code'
     *
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     *
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     *
     * @return array An associative array containing all the bulk actions.
     */
    protected function get_bulk_actions()
    {
        $actions = [
            'hide' => esc_html_x('Hide', 'List table bulk action', 'oms-coupon'),
            'delete' => esc_html_x('Delete', 'List table bulk action', 'oms-coupon'),
        ];

        return $actions;
    }

    /**
     * Handle bulk actions.
     *
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     *
     * @see $this->prepare_items()
     */
    protected function process_bulk_action()
    {
        global $wpdb;

        if (isset($_REQUEST[$this->_args['singular']])) {
            $coupon_params = get_request_parameter($this->_args['singular']);
            $ids = is_array($coupon_params)
                ? implode(',', array_map('intval', $coupon_params))
                : intval($coupon_params);

            switch ($this->current_action()) {
                case 'hide':
                    $wpdb->query($wpdb->prepare(
                        "UPDATE {$wpdb->prefix}oms_coupons SET active = 0 WHERE ID IN(%s)",
                        $ids,
                    ));
                    break;
                case 'delete':
                    $wpdb->query($wpdb->prepare(
                        "DELETE FROM {$wpdb->prefix}oms_coupons WHERE ID IN(%s)",
                        $ids,
                    ));
                    $wpdb->query($wpdb->prepare(
                        "DELETE FROM {$wpdb->prefix}oms_coupons_user WHERE oms_coupon_id IN(%s)",
                        $ids,
                    ));
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Prepares the list of items for displaying.
     *
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here.
     *
     * @global wpdb $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     */
    public function prepare_items()
    {
        global $wpdb;

        /**
         * REQUIRED for pagination.
         */
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $offset_page = ($current_page - 1) * $per_page;

        /**
         * Total active coupons.
         */
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}oms_coupons WHERE active = 1");

        /*
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & codes), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();

        /*
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * three other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = [$columns, $hidden, $sortable];

        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();

        // If no sort, default to ID.
        $orderby = get_request_parameter('orderby', 'ID');
        if (!in_array($orderby, array_keys($sortable), true)) {
            $orderby = 'ID';
        }
        $order = 'ASC' === strtoupper(get_request_parameter('order')) ? 'ASC' : 'DESC';
        $orderby_sql = 'c.' . sanitize_sql_orderby("{$orderby} {$order}");

        /*
         * GET THE DATA!
         */
        $data = $wpdb->get_results($wpdb->prepare(
            "SELECT
                c.ID, c.code, c.type, c.value, c.limit, c.activated_at, c.expired_at,
                GROUP_CONCAT(u.display_name SEPARATOR ', ') AS used_by, COUNT(u.ID) AS number_of_uses
            FROM {$wpdb->prefix}oms_coupons AS c
            LEFT JOIN {$wpdb->prefix}oms_coupons_user AS cu ON cu.oms_coupon_id = c.ID
            LEFT JOIN {$wpdb->prefix}users AS u ON cu.user_id = u.ID
            WHERE c.active = 1
            GROUP BY c.ID, c.code, c.type, c.value, c.limit, c.activated_at, c.expired_at
            ORDER BY {$orderby_sql}
            LIMIT %1\$d OFFSET %2\$d",
            $per_page,
            $offset_page,
        ), ARRAY_A);

        /*
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;

        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args([
            'total_items' => $total_items,                     // WE have to calculate the total number of items.
            'per_page'    => $per_page,                        // WE have to determine how many items to show on a page.
            'total_pages' => ceil($total_items / $per_page),   // WE have to calculate the total number of pages.
        ]);
    }
}
