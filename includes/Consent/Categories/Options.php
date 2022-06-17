<?php

namespace RRZE\Legal\Consent\Categories;

defined('ABSPATH') || exit;

use RRZE\Legal\ListSettings;

class Options extends ListSettings
{
    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->optionName = 'rrze_legal_consent_categories';
        $this->settingsFilename = 'consent-categories';

        add_filter('rrze_legal_section_consent_edit_new_title', [$this, 'sectionTitle']);
    }

    public function sectionTitle($title)
    {
        $page = $_GET['page'] ?? '';
        $action = $_GET['action'] ?? '';
        $id = $_GET['id'] ?? '';
        if ($page == 'consent-categories' && $action == 'consent-edit' && $id) {
            $title = __('Edit Consent Category', 'rrze-legal');
        }
        return $title;
    }

    /**
     * Global GET Hanlder.
     * @return array
     */
    public function getHandler()
    {
        $page = $_GET['page'] ?? '';
        $action = $_GET['action'] ?? '';
        $id = $_GET['id'] ?? '';
        // Maybe edit an item
        if (
            $page == 'consent-categories' &&
            $action == 'consent-edit'
        ) {
            if ($id != '' && isset($this->options[$id])) {
                $this->options = $this->options[$id];
                foreach ($this->options as $key => $value) {
                    $this->options['consent_categories_' . $key] = $value;
                }
                $this->options = array_merge($this->options, $this->getInputData());
            } else {
                $this->addSettingsError(
                    __('No consent category selected.', 'rrze-legal'),
                    'error'
                );
                $this->setSettingsErrors();
                wp_redirect(add_query_arg(
                    [
                        'page' => 'consent-categories',
                    ],
                    admin_url('admin.php')
                ));
                exit;
            }
        } elseif (
            $page == 'consent-categories' &&
            $action == 'consent-add'
        ) {
            $this->options = $this->getInputData();
        }
    }

    /**
     * Global POST handler.
     */
    public function postHandler()
    {
        $optionPage = $_POST['option_page'] ?? '';
        if ($optionPage != 'rrze_legal_consent_categories') {
            return;
        }
        $page = $_POST['page'] ?? '';
        $editNonce = $_POST['consent-edit-nonce'] ?? '';
        $addNonce = $_POST['consent-add-nonce'] ?? '';
        $id = $_POST['id'] ?? '';
        $input = $_POST[$this->optionName] ?? '';
        $input = $this->sanitizeOptions($input);
        $this->addInputData($input);

        if (
            wp_verify_nonce($editNonce, 'consent-categories-consent-edit') &&
            !$this->hasSettingsErrors()
        ) {
            $this->setInputData();
            foreach ($this->options[$id] as $key => $value) {
                if (isset($input['consent_categories_' . $key])) {
                    $this->options[$id][$key] = $input['consent_categories_' . $key];
                }
            }
        } elseif (wp_verify_nonce($addNonce, 'consent-categories-consent-add')) {
            $this->setInputData();
            $id = $input['consent_categories_id'];
            if (isset($this->options[$id])) {
                $error = sprintf(
                    /* translators: %s: ID field value. */
                    __('ID value %s already exists. Please enter a new ID.', 'rrze-legal'),
                    $id
                );
                $this->addSettingsError($error, 'error', 'consent_categories_id');
            }
            if (!$this->hasSettingsErrors()) {
                foreach ($input as $key => $value) {
                    $k = substr($key, strlen('consent_categories_'));
                    $this->options[$id][$k] = $value;
                }
            }
        } else {
            return;
        }

        if (!$this->hasSettingsErrors()) {
            update_option($this->optionName, $this->options);
            $backToListUrl = add_query_arg(
                [
                    'page' => $page,
                    'action' => 'list',
                ],
                admin_url('admin.php')
            );
            $message = $editNonce ? __('Consent category updated.', 'rrze-legal') : __('Consent category added.', 'rrze-legal');
            $message .= sprintf(
                '<p><a href="%1$s">%2$s</a></p>',
                $backToListUrl,
                __('&larr; Go to consent categories List', 'rrze-legal')
            );
            $this->addSettingsError($message, 'success');
            $this->setSettingsErrors();
            wp_redirect(add_query_arg(
                [
                    'page' => $page,
                    'action' => 'consent-edit',
                    'id' => $id,
                ],
                admin_url('admin.php')
            ));
            exit;
        } else {
            $this->setSettingsErrors();
            $query = [
                'page' => $page,
                'action' => $editNonce ? 'consent-edit' : 'consent-add',
            ];
            $query = isset($this->options[$id]) ? array_merge($query, ['id' => $id]) : $query;
            wp_redirect(add_query_arg($query, admin_url('admin.php')));
            exit;
        }
    }

    /**
     * Set the list table.
     * @return void
     */
    public function setListTable()
    {
        $this->listTable = new ListTable();
        $this->listTable->process_bulk_action();
    }

    /**
     * Load the list table.
     * @param string $page
     * @param string $action
     */
    protected function loadListTable(string $page = '', string $action = '')
    {
        $this->listTable->prepare_items();
        $addUrl = add_query_arg(
            [
                'page' => $page,
                'action' => 'consent-add',
            ],
            admin_url('admin.php')
        );
        echo '<div class="wrap">',
        '<h2>' . esc_html(get_admin_page_title()) . ' <a class="add-new-h2" href="' . esc_url($addUrl) . '">',
        esc_html__('Add New', 'rrze-legal') . '</a></h2>';
        $this->settingsErrors();
        echo '<form method="get">',
        '<input type="hidden" name="page" value="' . $page . '">',
        $this->listTable->search_box(__('Search', 'rrze-legal'), $page),
        '</form>',
        '<form method="post">',
        $this->listTable->views(),
        $this->listTable->display(),
        '</form>',
        '</div>';
    }

    /**
     * Get the list table data.
     */
    public function getListTableData(string $searchTerm = ''): array
    {
        $data = [];
        foreach ($this->options as $key => $item) {
            if ($searchTerm !== '') {
                if (
                    stripos($item['name'], $searchTerm) !== false ||
                    stripos($item['description'], $searchTerm) !== false
                ) {
                    $data[$key] = $item;
                    continue;
                }
            } else {
                $data[$key] = $item;
            }
        }
        return $data;
    }

    public function deleteItems(string $ids)
    {
        $ids = explode(',', $ids);
        $count = 0;
        foreach ($ids as $id) {
            if (isset($this->options[$id]) && empty($this->options[$id]['static'])) {
                unset($this->options[$id]);
                $count += 1;
            }
        }
        if ($count) {
            $this->addSettingsError(
                sprintf(
                    /* translators: %s: Number of consent categories. */
                    _nx(
                        '%s category deleted.',
                        '%s categories deleted.',
                        $count,
                        'consent categories',
                        'rrze-legal'
                    ),
                    number_format_i18n($count)
                ),
                'success'
            );
            $this->setSettingsErrors();
            $this->updateItems();
        }
    }

    protected function updateItems()
    {
        update_option($this->optionName, $this->options);
        wp_redirect(
            add_query_arg(
                ['page' => $_GET['page'] ?? ''],
                admin_url('admin.php')
            )
        );
        exit;
    }

    public function getItems()
    {
        return $this->options;
    }

    public function getItemsCount()
    {
        return count($this->getItems());
    }

    public function getItemsOptions()
    {
        $options = [];
        foreach ($this->options as $value) {
            if (!empty($value['id']) && !empty($value['name'])) {
                $options[$value['id']] = $value['name'];
            }
        }
        return $options;
    }

    public function getItemName($id)
    {
        return $this->options[$id]['name'] ?? '';
    }

    public function getAllCategoriesNames($enabled = true)
    {
        $options = [];
        foreach ($this->options as $value) {
            $status = !empty($value['status']) ? true : false;
            if ($enabled && $status && !empty($value['id']) && !empty($value['name'])) {
                $options[$value['id']] = $value['name'];
            }
        }
        return $options;
    }
}
