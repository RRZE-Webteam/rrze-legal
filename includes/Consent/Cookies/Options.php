<?php

namespace RRZE\Legal\Consent\Cookies;

defined('ABSPATH') || exit;

use RRZE\Legal\{ListSettings, Locale, Utils};
use function RRZE\Legal\{plugin, tos, consentCategories};

class Options extends ListSettings
{
    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->optionName = 'rrze_legal_consent_cookies';
        $this->settingsFilename = 'consent-cookies';

        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueConsentScripts']);
        add_filter('rrze_legal_section_consent_edit_new_title', [$this, 'sectionTitle']);
    }

    public function loaded()
    {
        parent::loaded();
        $this->syncPluginDependentStatuses();
    }

    public function sectionTitle($title)
    {
        $page = $_GET['page'] ?? '';
        $action = $_GET['action'] ?? '';
        $id = $_GET['id'] ?? '';
        if ($page == 'consent-cookies' && $action == 'consent-edit' && $id) {
            $title = __('Edit Consent Cookie', 'rrze-legal');
            $items = get_option('rrze_legal_consent_cookies_' . Locale::getLangCode(), []);
            $name = isset($items[$id]['name']) ? wp_strip_all_tags((string) $items[$id]['name']) : '';
            $label = $name !== '' ? $name : $id;
            $title = sprintf(
                /* translators: 1: Consent cookie name, 2: Consent cookie ID. */
                __('Edit Consent Cookie: %1$s (%2$s)', 'rrze-legal'),
                $label,
                $id
            );
        }
        return $title;
    }

    public function sanitizeId($input)
    {
        return $this->validateId($input);
    }

    public function sanitizePosition($input)
    {
        return $this->validateIntRange($input, 1, 99);
    }

    public function technicalSectionToggle()
    {
        printf(
            '<p><button type="button" class="button rrze-legal-consent-technical-toggle" aria-expanded="false">%s</button></p>',
            esc_html__('Show technical information', 'rrze-legal')
        );
    }

    public function pluginDependencyEditNotice(): void {
        $page = sanitize_key((string) ($_GET['page'] ?? ''));
        $action = sanitize_key((string) ($_GET['action'] ?? ''));
        if ($page !== 'consent-cookies' || $action !== 'consent-edit') {
            return;
        }

        $pluginSlug = trim((string) ($this->options['consent_cookies_plugin_slug'] ?? ''));
        if ($pluginSlug === '' || $this->isPluginDependencyActive(['plugin_slug' => $pluginSlug])) {
            return;
        }

        printf(
            '<div class="notice notice-info inline rrze-legal-consent-plugin-notice"><p>%s</p></div>',
            esc_html(
                sprintf(
                    /* translators: %s: Plugin slug. */
                    __('The dependent plugin "%s" is currently not active. This consent cookie is therefore not displayed.', 'rrze-legal'),
                    $pluginSlug
                )
            )
        );
    }

    public function restrictedTechnicalFieldsCapability(): string {
        if (Utils::isPluginActiveForNetwork(plugin()->getBaseName())) {
            return 'manage_network_options';
        }
        return 'manage_options';
    }

    public function canEditRestrictedTechnicalFields(): bool {
        return current_user_can($this->restrictedTechnicalFieldsCapability());
    }

    public function hasPluginDependency(array $item): bool {
        return trim((string) ($item['plugin_slug'] ?? '')) !== '';
    }

    public function isPluginDependencyActive(array $item): bool {
        $pluginSlug = trim((string) ($item['plugin_slug'] ?? ''));
        if ($pluginSlug === '') {
            return false;
        }
        return $this->isPluginActive($pluginSlug);
    }

    public function canToggleItem(array $item): bool {
        if ($this->hasPluginDependency($item)) {
            return false;
        }
        return ($item['category'] ?? '') !== 'essential';
    }

    protected function postSanitizeOptions($input, $hasError)
    {
        if (!$hasError) {
            $input = $this->setPluginDependentInputStatus($input);
            $tosOptionName = tos()->getOptionName();
            $tosOptions = tos()->getOptions();
            $cookieId = $input['consent_cookies_id'] ?? '';
            $cookieStatus = $input['consent_cookies_status'] ?? '';
            $cookieStatus = $cookieStatus ? '1' : '0';
            $cookieCategory = $input['consent_cookies_category'] ?? '';
            if ($cookieId !== '' && $cookieCategory !== 'essential') {
                $tosOptions['privacy_service_providers'][$cookieId] = $cookieStatus;
                update_option($tosOptionName, $tosOptions);
            } elseif ($cookieId !== '' && isset($tosOptions['privacy_service_providers'][$cookieId])) {
                unset($tosOptions['privacy_service_providers'][$cookieId]);
                update_option($tosOptionName, $tosOptions);
            }
        }
        return $input;
    }

    protected function setPluginDependentInputStatus(array $input): array
    {
        $pluginSlug = trim((string) ($input['consent_cookies_plugin_slug'] ?? ''));
        if ($pluginSlug === '') {
            $id = sanitize_key((string) ($_POST['id'] ?? ($input['consent_cookies_id'] ?? '')));
            $pluginSlug = trim((string) ($this->options[$id]['plugin_slug'] ?? ''));
        }
        if ($pluginSlug === '') {
            return $input;
        }

        $input['consent_cookies_status'] = $this->isPluginActive($pluginSlug) ? '1' : '0';
        return $input;
    }

    /**
     * Register admin scripts.
     * @return void
     */
    public function adminEnqueueConsentScripts($hook)
    {
        if (strpos($hook, 'consent-cookies') !== false) {
            // Enqueue code editor and settings for manipulating HTML.
            $settingsHTML = wp_enqueue_code_editor(
                ['type' => 'text/html', 'htmlhint' => ['space-tab-mixed-disabled' => false]]
            );
            if ($settingsHTML !== false) {
                wp_add_inline_script(
                    'code-editor',
                    sprintf(
                        'jQuery( function() { if (jQuery("#wpcontent [wpcode-html-editor]").length) {  jQuery("#wpcontent [wpcode-html-editor]").each(function () { wp.codeEditor.initialize(this.id, %s); }); } } );',
                        wp_json_encode($settingsHTML)
                    )
                );
            }

            // Enqueue code editor and settings for manipulating JavaScript.
            $settingsJS = wp_enqueue_code_editor(['type' => 'text/javascript']);
            if ($settingsJS !== false) {
                wp_add_inline_script(
                    'code-editor',
                    sprintf(
                        'jQuery( function() { if (jQuery("#wpcontent [wpcode-js-editor]").length) { jQuery("#wpcontent [wpcode-js-editor]").each(function () { wp.codeEditor.initialize(this.id, %s); }); } } );',
                        wp_json_encode($settingsJS)
                    )
                );
            }

            // Enqueue code editor and settings for manipulating CSS.
            $settingsCSS = wp_enqueue_code_editor(['type' => 'text/css']);
            if ($settingsCSS !== false) {
                wp_add_inline_script(
                    'code-editor',
                    sprintf(
                        'jQuery( function() { if (jQuery("#wpcontent [wpcode-css-editor]").length) { jQuery("#wpcontent [wpcode-css-editor]").each(function () { wp.codeEditor.initialize(this.id, %s); }); } } );',
                        wp_json_encode($settingsCSS)
                    )
                );
            }
        }
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
            $page == 'consent-cookies' &&
            $action == 'consent-edit'
        ) {
            if ($id != '' && isset($this->options[$id])) {
                $this->options = $this->options[$id];
                foreach ($this->options as $key => $value) {
                    $this->options['consent_cookies_' . $key] = $value;
                }
                $this->options = array_merge($this->options, $this->getInputData());
            } else {
                $this->addSettingsError(
                    __('No consent cookie selected.', 'rrze-legal'),
                    'error'
                );
                $this->setSettingsErrors();
                wp_redirect(add_query_arg(
                    [
                        'page' => 'consent-cookies',
                    ],
                    admin_url('admin.php')
                ));
                exit;
            }
        } elseif (
            $page == 'consent-cookies' &&
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
        if ($optionPage != 'rrze_legal_consent_cookies') {
            return;
        }
        if (!current_user_can($this->optionsMenu->capability)) {
            wp_die(esc_html__('You do not have permission to edit consent cookies.', 'rrze-legal'));
        }
        $page = $_POST['page'] ?? '';
        $editNonce = $_POST['consent-edit-nonce'] ?? '';
        $addNonce = $_POST['consent-add-nonce'] ?? '';
        $id = $_POST['id'] ?? '';
        $input = $_POST[$this->optionName] ?? '';
        $input = is_array($input) ? $this->filterAllowedInputFields($input) : $input;
        $input = $this->sanitizeOptions($input);
        $this->addInputData($input);

        if (
            wp_verify_nonce($editNonce, 'consent-cookies-consent-edit') &&
            !$this->hasSettingsErrors()
        ) {
            $this->setInputData();
            foreach ($this->options[$id] as $key => $value) {
                if (isset($input['consent_cookies_' . $key])) {
                    $this->options[$id][$key] = $input['consent_cookies_' . $key];
                }
            }
        } elseif (wp_verify_nonce($addNonce, 'consent-cookies-consent-add')) {
            $this->setInputData();
            $id = $input['consent_cookies_id'];
            if (isset($this->options[$id])) {
                $error = sprintf(
                    /* translators: %s: ID field value. */
                    __('ID value %s already exists. Please enter a new ID.', 'rrze-legal'),
                    $id
                );
                $this->addSettingsError($error, 'error', 'consent_cookies_id');
            }
            if (!$this->hasSettingsErrors()) {
                foreach ($input as $key => $value) {
                    $k = substr($key, strlen('consent_cookies_'));
                    $this->options[$id][$k] = $value;
                }
            }
        } else {
            return;
        }

        if (!$this->hasSettingsErrors()) {
            update_option($this->optionName, $this->options);
            $this->logConsentCookieChange($editNonce ? 'updated' : 'added', $id);
            $backToListUrl = add_query_arg(
                [
                    'page' => $page,
                    'action' => 'list',
                ],
                admin_url('admin.php')
            );
            $message = $editNonce ? __('Consent cookie updated.', 'rrze-legal') : __('Consent cookie added.', 'rrze-legal');
            $message .= sprintf(
                '<p><a href="%1$s">%2$s</a></p>',
                esc_url($backToListUrl),
                __('&larr; Go to consent cookies list', 'rrze-legal')
            );
            $this->addSettingsError($message, 'success', true);
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

    protected function filterAllowedInputFields(array $input): array
    {
        foreach (array_keys($input) as $key) {
            if (!isset($this->fields[$key])) {
                unset($input[$key]);
            }
        }
        return $input;
    }

    protected function logConsentCookieChange(string $action, string $id, array $item = []): void
    {
        $user = wp_get_current_user();
        $item = !empty($item) ? $item : ($this->options[$id] ?? []);
        $name = wp_strip_all_tags((string) ($item['name'] ?? ''));
        $name = $name !== '' ? $name : $id;
        $userLabel = $user->exists()
            ? sprintf('%1$s (ID %2$d)', $user->user_login, $user->ID)
            : __('unknown user', 'rrze-legal');
        $actionLabel = $this->getConsentCookieLogActionLabel($action);

        do_action(
            'rrze.log.info',
            sprintf(
                'RRZE Legal: Admin %1$s %5$s consent cookie "%2$s" (ID: %3$s) on site %4$d.',
                $userLabel,
                $name,
                $id,
                get_current_blog_id(),
                $actionLabel
            )
        );
    }

    protected function getConsentCookieLogActionLabel(string $action): string
    {
        switch ($action) {
            case 'added':
                return 'created';
            case 'enabled':
                return 'enabled';
            case 'disabled':
                return 'disabled';
            case 'deleted':
                return 'deleted';
            case 'updated':
            default:
                return 'updated';
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
        '<h2>' . esc_html(get_admin_page_title());
        echo ' <a class="add-new-h2" href="' . esc_url($addUrl) . '">',
        esc_html__('Add New', 'rrze-legal') . '</a>';
        echo '</h2>';
        $this->settingsErrors();
        echo '<form method="get">',
        '<input type="hidden" name="page" value="' . esc_attr($page) . '">',
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
                    stripos($item['purpose'], $searchTerm) !== false
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

    protected function syncPluginDependentStatuses(): void
    {
        $staticItems = $this->getStaticData()['items'] ?? [];
        $changed = false;

        foreach ($this->options as $key => $item) {
            if (empty($item['plugin_slug']) && !empty($staticItems[$key]['plugin_slug'])) {
                $this->options[$key]['plugin_slug'] = $staticItems[$key]['plugin_slug'];
                $item['plugin_slug'] = $staticItems[$key]['plugin_slug'];
                $changed = true;
            }
            foreach (['privacy_text_de', 'privacy_text_en'] as $field) {
                if (!array_key_exists($field, $item) && isset($staticItems[$key][$field])) {
                    $this->options[$key][$field] = $staticItems[$key][$field];
                    $item[$field] = $staticItems[$key][$field];
                    $changed = true;
                }
            }

            $pluginSlug = trim((string) ($item['plugin_slug'] ?? ''));
            if ($pluginSlug === '') {
                continue;
            }

            $status = $this->isPluginActive($pluginSlug) ? '1' : '0';
            if (($this->options[$key]['status'] ?? '') !== $status) {
                $this->options[$key]['status'] = $status;
                $changed = true;
            }
        }

        if ($changed) {
            update_option($this->optionName, $this->options);
            $this->syncTosServiceProviders();
        }
    }

    protected function isPluginActive(string $pluginSlug): bool
    {
        return Utils::isPluginActive($pluginSlug) || Utils::isPluginActiveForNetwork($pluginSlug);
    }

    protected function syncTosServiceProviders(): void
    {
        $tosOptionName = tos()->getOptionName();
        $tosOptions = tos()->getOptions();
        foreach ($this->options as $key => $item) {
            if (($item['category'] ?? '') === 'essential') {
                unset($tosOptions['privacy_service_providers'][$key]);
                continue;
            }
            $tosOptions['privacy_service_providers'][$key] = $item['status'];
        }
        update_option($tosOptionName, $tosOptions);
    }

    public function enableItems(string $ids)
    {
        $ids = explode(',', $ids);
        $count = 0;
        foreach ($ids as $id) {
            if (isset($this->options[$id]) && $this->canToggleItem($this->options[$id])) {
                $this->options[$id]['status'] = '1';
                $this->logConsentCookieChange('enabled', $id);
                $count += 1;
            }
        }
        if ($count) {
            $this->addSettingsError(
                sprintf(
                    /* translators: %s: Number of consent cookies. */
                    _nx(
                        '%s cookie enabled.',
                        '%s cookies enabled.',
                        $count,
                        'consent cookies',
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

    public function disableItems(string $ids)
    {
        $ids = explode(',', $ids);
        $count = 0;
        foreach ($ids as $id) {
            if (isset($this->options[$id]) && $this->canToggleItem($this->options[$id])) {
                $this->options[$id]['status'] = '0';
                $this->logConsentCookieChange('disabled', $id);
                $count += 1;
            }
        }
        if ($count) {
            $this->addSettingsError(
                sprintf(
                    /* translators: %s: Number of consent cookies. */
                    _nx(
                        '%s cookie disabled.',
                        '%s cookies disabled.',
                        $count,
                        'consent cookies',
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

    public function deleteItems(string $ids)
    {
        $ids = explode(',', $ids);
        $count = 0;
        foreach ($ids as $id) {
            if (isset($this->options[$id]) && empty($this->options[$id]['static'])) {
                $item = $this->options[$id];
                unset($this->options[$id]);
                $this->logConsentCookieChange('deleted', $id, $item);
                $count += 1;
            }
        }
        if ($count) {
            $this->addSettingsError(
                sprintf(
                    /* translators: %s: Number of consent cookies. */
                    _nx(
                        '%s cookie deleted.',
                        '%s cookies deleted.',
                        $count,
                        'consent cookies',
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
        $tosOptionName = tos()->getOptionName();
        $tosOptions = tos()->getOptions();
        foreach ($this->options as $key => $item) {
            if (($item['category'] ?? '') === 'essential') {
                unset($tosOptions['privacy_service_providers'][$key]);
                continue;
            }
            $tosOptions['privacy_service_providers'][$key] = $item['status'];
        }
        unregister_setting('rrze_legal_privacy', $tosOptionName);
        update_option($tosOptionName, $tosOptions);
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
        return count($this->options);
    }

    public function getAllCookiesNames($enabled = true)
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

    public function getAllCookieCategories($enabled = true)
    {
        $categories = consentCategories()->getItems();
        foreach ($categories as $key => $category) {
            foreach ($this->options as $k => $item) {
                $status = !empty($item['status']) ? true : false;
                if ($enabled && $status && isset($item['category']) && $key === $item['category']) {
                    $categories[$key]['cookies'][$k] = $item;
                }
            }
        }
        return $categories;
    }
}
