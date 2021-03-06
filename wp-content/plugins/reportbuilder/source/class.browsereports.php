<?php
/**
 * Browse table of reports for the admin panel
 */

namespace WDTReportBuilder;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class BrowseReportsTable extends \WP_List_Table {

    function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'name' => __('Name', 'wpdatatables'),
            'functions' => __('Actions', 'wpdatatables')
        );
    }

    function get_sortable_columns() {
        return array(
            'id' => array('id', true),
            'name' => array('name', false)
        );
    }



    function prepare_items() {
        $per_page = get_option('wdtTablesPerPage') ? get_option('wdtTablesPerPage') : 10;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->set_pagination_args(
            array(
                'total_items' => \WDTReportBuilder\Admin::getReportCount(),
                'per_page' => $per_page
            )
        );

        $this->items = \WDTReportBuilder\Admin::getAllReports($per_page);
    }

    /**
     * Set default columns value
     * @param object $item
     * @param string $column_name
     * @return string
     */
    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'functions':
                ob_start();
                include(WDT_RB_ROOT_PATH . 'templates/admin_actions.tpl.php');
                $returnString = ob_get_contents();
                ob_end_clean();
                return $returnString;
                break;
            case 'id':
            case 'name':
            default:
                return $item[$column_name];
                break;
        }
    }


    function column_name($item) {

        return '<a href="admin.php?page=wpdatareports-wizard&id=' . $item['id'] . '">' . $item['name'] . '</a> ';

    }

    /**
     * Print column headers, accounting for hidden and sortable columns.
     *
     * @since 3.1.0
     * @access public
     *
     * @staticvar int $cb_counter
     *
     * @param bool $with_id Whether to set the id attribute or not
     */
    function print_column_headers($with_id = true) {
        list($columns, $hidden, $sortable, $primary) = $this->get_column_info();

        $defaultSortingOrder = get_option('wdtSortingOrderBrowseTables');
        $defaultSortingOrder = isset($defaultSortingOrder) ? $defaultSortingOrder : 'ASC';
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $current_url = remove_query_arg('paged', $current_url);

        if (isset($_GET['orderby'])) {
            $current_orderby = $_GET['orderby'];
        } else {
            $current_orderby = '';
        }

        if (isset($_GET['order']) && 'desc' === $_GET['order']) {
            $current_order = 'desc';
        } else {
            $current_order = 'asc';
        }

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                . '<div class="checkbox"><input id="cb-select-all-' . $cb_counter . '" type="checkbox" /></div>';
            $cb_counter++;
        }

        foreach ($columns as $column_key => $column_display_name) {
            $class = array('manage-column', "column-$column_key");

            if (in_array($column_key, $hidden)) {
                $class[] = 'hidden';
            }

            if ('cb' === $column_key)
                $class[] = 'check-column';
            elseif (in_array($column_key, array('posts', 'comments', 'links')))
                $class[] = 'num';

            if ($column_key === $primary) {
                $class[] = 'column-primary';
            }

            if (isset($sortable[$column_key])) {
                list($orderby, $desc_first) = $sortable[$column_key];

                if ($current_orderby === $orderby) {
                    $order = 'asc' === $current_order ? 'desc' : 'asc';
                    $class[] = 'sorted';
                    $class[] = $current_order;
                } else {
                    $order = strtolower($defaultSortingOrder) == 'desc' ? 'desc' : 'asc';
                    $class[] = ($current_orderby == '' && $column_key == 'id') ? 'sorted' : 'sortable';
                    $class[] = strtolower($defaultSortingOrder) == 'desc' ? 'desc' : 'asc';
                }

                $column_display_name = '<a href="' . esc_url(add_query_arg(compact('orderby', 'order'), $current_url)) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
            }

            $tag = ('cb' === $column_key) ? 'td' : 'th';
            $scope = ('th' === $tag) ? 'scope="col"' : '';
            $id = $with_id ? "id='$column_key'" : '';

            if (!empty($class))
                $class = "class='" . join(' ', $class) . "'";

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }

    /**
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function column_cb($item) {
        return sprintf(
            '<div class="checkbox"><input type="checkbox" name="id[]" value="%s" /></div>', $item['id']
        );
    }

    function no_items() {
        _e('No reports created yet.', 'wpdatatables');
    }

    /**
     * Display the table
     *
     * @since 3.1.0
     * @access public
     */
    function display() {
        $singular = $this->_args['singular'];

        $this->screen->render_screen_reader_content('heading_list');

        require_once(WDT_ROOT_PATH . '/templates/admin/browse/table_list.inc.php');
    }

    /**
     * Display the pagination.
     *
     * @since 3.1.0
     * @access protected
     *
     * @param string $which
     */
    protected function pagination($which) {
        if (empty($this->_pagination_args))
            return;

        $max = $this->_pagination_args['total_pages'];
        $paged = $this->get_pagenum();

        /** Stop execution if there's only 1 page */
        if ($max <= 1)
            return;

        $removable_query_args = wp_removable_query_args();
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $current_url = remove_query_arg($removable_query_args, $current_url);

        /**    Add current page to the array */
        if ($paged >= 1)
            $links[] = $paged;

        /**    Add the pages around the current page to the array */
        if ($paged >= 3) {
            $links[] = $paged - 1;
            $links[] = $paged - 2;
        }

        if (($paged + 2) <= $max) {
            $links[] = $paged + 2;
            $links[] = $paged + 1;
        }

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ($paged == 1) {
            $disable_first = true;
            $disable_prev = true;
        }
        if ($paged == 2) {
            $disable_first = true;
        }
        if ($paged == $max) {
            $disable_last = true;
            $disable_next = true;
        }
        if ($paged == $max - 1) {
            $disable_last = true;
        }

        require_once(WDT_ROOT_PATH . '/templates/admin/browse/pagination.inc.php');
    }

    /**
     * Display the bulk actions dropdown.
     *
     * @since 3.1.0
     * @access protected
     *
     * @param string $which The location of the bulk actions: 'top' or 'bottom'.
     * This is designated as optional for backward compatibility.
     */
    function bulk_actions($which = '') {
        if (is_null($this->_actions)) {
            $no_new_actions = $this->_actions = $this->get_bulk_actions();
            /**
             * Filters the list table Bulk Actions drop-down.
             *
             * The dynamic portion of the hook name, `$this->screen->id`, refers
             * to the ID of the current screen, usually a string.
             *
             * This filter can currently only be used to remove bulk actions.
             *
             * @since 3.5.0
             *
             * @param array $actions An array of the available bulk actions.
             */
            $this->_actions = apply_filters("bulk_actions-{$this->screen->id}", $this->_actions);
            $this->_actions = array_intersect_assoc($this->_actions, $no_new_actions);
            $two = '';
        } else {
            $two = '2';
        }

        if (empty($this->_actions))
            return;

        require(WDT_ROOT_PATH . '/templates/admin/browse/bulk_actions.inc.php');
    }

    /**
     * Generate the table navigation above or below the table
     *
     * @since 3.1.0
     * @access protected
     * @param string $which
     */
    function display_tablenav($which) {
        if ('top' === $which) {
            wp_nonce_field('bulk-' . $this->_args['plural']);
        }

        require(WDT_ROOT_PATH . '/templates/admin/browse/table_navigation.inc.php');
    }

    /**
     * Displays the search box.
     *
     * @since 3.1.0
     * @access public
     *
     * @param string $text The 'submit' button label.
     * @param string $input_id ID attribute value for the search input field.
     */
    function search_box($text, $input_id) {
        if (empty($_REQUEST['s']) && !$this->has_items())
            return;

        $input_id = $input_id . '-search-input';

        if (!empty($_REQUEST['orderby']))
            echo '<input type="hidden" name="orderby" value="' . esc_attr($_REQUEST['orderby']) . '" />';
        if (!empty($_REQUEST['order']))
            echo '<input type="hidden" name="order" value="' . esc_attr($_REQUEST['order']) . '" />';
        if (!empty($_REQUEST['post_mime_type']))
            echo '<input type="hidden" name="post_mime_type" value="' . esc_attr($_REQUEST['post_mime_type']) . '" />';
        if (!empty($_REQUEST['detached']))
            echo '<input type="hidden" name="detached" value="' . esc_attr($_REQUEST['detached']) . '" />';

        require_once(WDT_ROOT_PATH . '/templates/admin/browse/search_box.inc.php');
    }

}
?>