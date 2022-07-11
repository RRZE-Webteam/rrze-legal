<?php

namespace RRZE\Legal\Consent\Cookies;

defined('ABSPATH') || exit;

use RRZE\Legal\Utils;
use function RRZE\Legal\consentCategories;
use function RRZE\Legal\consentCookies;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class ListTable extends \WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'consent_cookie',
            'plural' => 'consent_cookies',
            'ajax' => false
        ]);
    }

    /**
     * Checkbox column.
     * @param array $item
     * @return string
     */
    protected function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[%1$s]" value="%1$s" />',
            $item['id']
        );
    }

    protected function column_name($item)
    {
        $editUrl = add_query_arg(
            [
                'page' => 'consent-cookies',
                'action' => 'consent-edit',
                'id' => $item['id'],
            ],
            admin_url('admin.php')
        );
        $trashUrl = add_query_arg(
            [
                'page' => 'consent-cookies',
                'action' => 'trash',
                'id' => $item['id'],
                '_wpnonce' => wp_create_nonce('consent-trash-' . $item['id'])
            ],
            admin_url('admin.php')
        );
        $enableUrl = add_query_arg(
            [
                'page' => 'consent-cookies',
                'action' => 'enable',
                'id' => $item['id'],
                '_wpnonce' => wp_create_nonce('consent-enable-' . $item['id'])
            ],
            admin_url('admin.php')
        );
        $disableUrl = add_query_arg(
            [
                'page' => 'consent-cookies',
                'action' => 'disable',
                'id' => $item['id'],
                '_wpnonce' => wp_create_nonce('consent-disable-' . $item['id'])
            ],
            admin_url('admin.php')
        );
        $actions = [
            'edit' => sprintf(
                '<a href="%1$s">%2$s</a>',
                $editUrl,
                __('Edit', 'rrze-legal')
            ),
        ];
        if (empty($item['status'])) {
            $actions = array_merge($actions, [
                'delete' => sprintf(
                    '<a href="%1$s">%2$s</a>',
                    $enableUrl,
                    __('Enable', 'rrze-legal')
                ),
            ]);
        } else {
            $actions = array_merge($actions, [
                'delete' => sprintf(
                    '<a href="%1$s">%2$s</a>',
                    $disableUrl,
                    __('Disable', 'rrze-legal')
                ),
            ]);
        }
        if (empty($item['static'])) {
            $actions = array_merge($actions, [
                'delete' => sprintf(
                    '<a href="%1$s">%2$s</a>',
                    $trashUrl,
                    __('Delete', 'rrze-legal')
                ),
            ]);
        }
        return sprintf(
            '%1$s %2$s',
            $item['name'],
            $this->row_actions($actions)
        );
    }

    protected function column_category($item)
    {
        return consentCategories()->getItemName($item['category']);
    }

    protected function column_status($item)
    {
        return !empty($item['status']) ?
            '<span class="dashicons dashicons-yes-alt"></span>' :
            '<span class="dashicons dashicons-no-alt"></span>';
    }

    protected function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    public function get_columns()
    {
        $columns = [
            'cb' => '<input type="checkbox" />',
            'name' => __('Name', 'rrze-legal'),
            'purpose' => __('Purpose', 'rrze-legal'),
            'category' => __('Category', 'rrze-legal'),
            'status' => __('Status', 'rrze-legal'),
            'position' => __('Position', 'rrze-legal'),
        ];
        return $columns;
    }

    /**
     * Define which columns are hidden.
     * @return array
     */
    public function get_hidden_columns()
    {
        return [];
    }

    protected function get_sortable_columns()
    {
        $columns = [
            'name' => ['name', true],
            'category' => ['category', true],
        ];
        return $columns;
    }

    protected function get_bulk_actions()
    {
        $actions = [
            'enable' => __('Enable', 'rrze-legal'),
            'disable' => __('Disable', 'rrze-legal'),
            'delete' => __('Delete', 'rrze-legal'),
        ];
        return $actions;
    }

    public function process_bulk_action()
    {
        $ids = $_POST['id'] ?? '';
        $ids = is_array($ids) ? implode(',', $ids) : '';
        $id = $_GET['id'] ?? '';
        $_wpnonce = $_GET['_wpnonce'] ?? '';

        if (!empty($ids)) {
            switch ($this->current_action()) {
                case 'enable':
                    consentCookies()->enableItems($ids);
                    break;
                case 'disable':
                    consentCookies()->disableItems($ids);
                    break;
                case 'trash':
                    consentCookies()->deleteItems($ids);
                    break;
            }
        } elseif (!empty($id) && !empty($_wpnonce)) {
            if (
                'enable' === $this->current_action() &&
                wp_verify_nonce($_wpnonce, 'consent-enable-' . $id)
            ) {
                consentCookies()->enableItems($id);
            } elseif (
                'disable' === $this->current_action() &&
                wp_verify_nonce($_wpnonce, 'consent-disable-' . $id)
            ) {
                consentCookies()->disableItems($id);
            } elseif (
                'trash' === $this->current_action() &&
                wp_verify_nonce($_wpnonce, 'consent-trash-' . $id)
            ) {
                consentCookies()->deleteItems($id);
            }
        }
    }

    /**
     * Prepare the items for the table to process.
     * @return void
     */
    public function prepare_items()
    {
        $searchTerm = '';
        if (isset($_GET['s']) && !empty($_GET['s'])) {
            $searchTerm = sanitize_text_field($_GET['s']);
        }

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, $hidden, $sortable];

        $orderby = !empty($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'category';
        $order = !empty($_GET['order']) ? sanitize_text_field($_GET['order']) : 'asc';
        $order = $order === 'asc' ? SORT_ASC : SORT_DESC;

        $data = consentCookies()->getListTableData($searchTerm);
        if ($order == 'name') {
            $data = Utils::arrayOrderby($data, $orderby, $order);
        } else {
            $data = Utils::arrayOrderby($data, $orderby, $order, 'position', SORT_ASC);
        }

        $perPage = $this->get_items_per_page('rrze_legal_consent_per_page', 20);
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args([
            'total_items' => $totalItems, // Total number of items
            'per_page' => $perPage, // How many items to show on a page
            'total_pages' => ceil($totalItems / $perPage) // Total number of pages
        ]);

        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
        $this->items = $data;
    }
}
