<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

/**
 * Class Settings
 * @package RRZE\Legal
 */
class ListSettings
{
    /**
     * Option name.
     * @var string
     */
    protected $optionName = '';

    /**
     * Settings options.
     * @var array
     */
    protected $options = [];

    /**
     * Settings menu
     * 
     * @var array
     */
    protected $menuSettings = [];

    /**
     * Options parent.
     * @var object
     */
    protected $optionsParent = null;

    /**
     * Options page.
     * @var object
     */
    protected $optionsPage = null;

    /**
     * Options menu.
     * 
     * @var object
     */
    protected $optionsMenu = null;

    /**
     * Settings sections.
     * @var object
     */
    protected $sections = null;

    /**
     * Settings fields.
     * @var array
     */
    protected $fields = [];

    /**
     * Page prefix.
     * @var string
     */
    protected $pagePrefix = '';

    /**
     * Settings filename.
     * @var string
     */
    protected $settingsFilename = '';

    /**
     * Settings prefix.
     * @var string
     */
    protected $settingsPrefix = '';

    /**
     * ListTable object.
     * @var object
     */
    protected $listTable;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->pagePrefix = plugin()->getSlug() . '-';
        $this->settingsPrefix = str_replace('-', '_', $this->pagePrefix);

        add_filter('set-screen-option', [$this, 'setScreenOption'], 10, 3);
        add_action('admin_enqueue_scripts', [$this, 'adminRegisterSettingsScripts']);
    }

    /**
     * Execute on 'plugins_loaded' API/action.
     */
    public function loaded()
    {
        if ($this->optionName === '' || $this->settingsFilename === '') {
            return;
        }
        include_once(plugin()->getPath() . "settings/{$this->settingsFilename}.php");
        $this->settings = $settings ?? [];
        $this->optionsParent = (object) $this->settings['options_page']['parent'] ?? [];
        $this->optionsPage = (object) $this->settings['options_page']['page'] ?? [];
        $this->optionsMenu = (object) $this->settings['options_page']['menu'] ?? [];
        $this->sections = (object) $this->settings['settings']['sections'] ?? [];

        $this->setFields();
        $this->setOptions();
        $this->updateFromStaticData();
    }

    public function setAdminMenu()
    {
        add_action('admin_menu', [$this, 'adminSubMenu']);
        add_action('admin_init', [$this, 'addSections']);
    }

    /**
     * Returns the settings filename.
     * @return string
     */
    public function getSettingsFilename()
    {
        return $this->settingsFilename;
    }

    /**
     * Returns the page prefix.
     * @return string
     */
    public function getPagePrefix()
    {
        return $this->pagePrefix;
    }

    /**
     * Returns the page options.
     * @return string
     */
    public function getPageOptions()
    {
        return $this->optionsPage;
    }

    /**
     * Returns the page menu options.
     * @return string
     */
    public function getMenuOptions()
    {
        return $this->optionsMenu;
    }

    /**
     * Returns the sections.
     * @return object
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Returns the settings sections.
     * @return object
     */
    public function getSettingsSections()
    {
        $settingsSections = [];
        foreach ($this->sections as $section) {
            if (isset($section['id'])) {
                $settingsSections[] = $this->settingsPrefix . $section['id'];
                break;
            }
        }
        return $settingsSections;
    }

    /**
     * Returns the fields.
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Returns the default options.
     * @return array
     */
    protected function defaultOptions(): array
    {
        $defaultOptions = [];
        foreach ($this->fields as $field => $options) {
            $default = isset($options['default']) ? $options['default'] : '';
            $defaultOptions = array_merge($defaultOptions, [$field => $default]);
        }
        return $defaultOptions;
    }

    /**
     * Set the options.
     * @return array
     */
    protected function setOptions()
    {
        $langCode = is_user_logged_in() && is_admin() ? Locale::getUserLangCode() : Locale::getLangCode();
        $this->optionName = $this->optionName . '_' . $langCode;
        $this->options = get_option($this->optionName);
        $this->options = $this->options !== false ? $this->options : [];
    }

    /**
     * Returns the option name.
     * @return string
     */
    public function getOptionName(): string
    {
        return $this->optionName;
    }

    /**
     * Returns the options.
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns an option.
     * @param string  $section The section name this field belongs to
     * @param string  $name  Settings field name
     * @param mixed  $default Default text if it's not found
     * @return mixed
     */
    public function getOption(string $section, string $name, $default = '')
    {
        $option = $section . '_' . $name;
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }
        return $default;
    }

    /**
     * Set the settings fields.
     * @return void
     */
    protected function setFields()
    {
        foreach ($this->sections as $section) {
            if (isset($section['capability']) && !current_user_can($section['capability'])) {
                continue;
            }
            if (isset($section['subsections'])) {
                foreach ($section['subsections'] as $subsection) {
                    if (isset($subsection['fields'])) {
                        if (isset($subsection['capability']) && !current_user_can($subsection['capability'])) {
                            continue;
                        }
                        if (isset($subsection['hide_section']) && (bool) $subsection['hide_section']) {
                            continue;
                        }
                        $this->formatField($subsection['fields'], $section['id']);
                    }
                }
            } elseif (isset($section['fields'])) {
                $this->formatField($section['fields'], $section['id']);
            }
        }
    }

    /**
     * Format the settings fields.
     * @param array  $fields  The settings fields
     * @param string  $section The section id this field belongs to
     * @return void
     */
    protected function formatField(array $fields, string $sectionId)
    {
        foreach ($fields as $option) {
            if (isset($option['capability']) && !current_user_can($option['capability'])) {
                continue;
            }
            if (isset($option['name'])) {
                $this->fields[$sectionId . '_' . $option['name']] = $option;
            }
        }
    }

    /**
     * Adds a admin submenu page.
     * @return void
     */
    public function adminSubMenu()
    {
        $submenuPage = add_submenu_page(
            $this->optionsParent->slug,
            $this->optionsPage->title,
            $this->optionsMenu->title,
            $this->optionsMenu->capability,
            $this->optionsMenu->slug,
            [$this, 'subMenuPage'],
            $this->optionsMenu->position
        );

        add_action("load-$submenuPage", [$this, 'screenOptions']);

        $this->postHandler();
        $this->getHandler();
    }

    /**
     * Display the admin sub menu page.
     */
    public function subMenuPage()
    {
        wp_enqueue_style('rrze-legal-settings');
        wp_enqueue_style('rrze-legal-consent-settings');

        $page = $_REQUEST['page'] ?? '';
        $action = $_REQUEST['action'] ?? '';

        switch ($action) {
            case 'consent-add':
            case 'consent-edit':
                $this->editPage($page, $action);
                break;
            default:
                $this->loadListTable($page, $action);
                break;
        }
    }

    /**
     * Global GET handler.
     */
    public function getHandler()
    {
    }

    /**
     * Global POST handler.
     */
    public function postHandler()
    {
    }

    /**
     * Set the list table.
     * @return void
     */
    public function setListTable()
    {
    }

    /**
     * Load the list table.
     * @param string $page
     * @param string $action
     */
    protected function loadListTable(string $page = '', string $action = '')
    {
    }

    /**
     * Display the edit page.
     * @return void
     */
    protected function editPage(string $page = '', string $action = '')
    {
        $id = $_GET['id'] ?? '';
        echo '<div class="wrap">',
        '<h1>' . $this->settings['settings']['title'] . '</h1>';
        $this->settingsErrors();
        settings_errors();
        foreach ($this->sections as $section) {
            $sectionId = str_replace('_', '-', $section['id']);
            echo '<form id="' . $this->pagePrefix . $sectionId . '" method="post">',
            wp_nonce_field($page . '-' . $action, $action . '-nonce', false, false);
            echo '<input type="hidden" name="page" value="' . $page . '">';
            echo $id ? '<input type="hidden" name="id" value="' . $id . '">' : '';
            do_settings_sections($this->settingsPrefix . $section['id']);
            settings_fields($this->settingsPrefix . $section['id']);
            submit_button();
            echo '</form>';
            break;
        }
        echo '</div>';
    }

    /**
     * Add sections and fields.
     * @return void
     */
    public function addSections()
    {
        foreach ($this->sections as $section) {
            if (!isset($section['id']) || !isset($section['title'])) {
                continue;
            }
            $this->addSection($section);
            break;
        }
    }

    /**
     * Add a section to the settings page.
     * @param array $section
     */
    protected function addSection(array $section)
    {
        $capability = isset($section['capability']) ? $section['capability'] : 'manage_options';
        if (!current_user_can($capability)) {
            return;
        }
        if (isset($section['hide_section']) && (bool) $section['hide_section']) {
            return;
        }
        if (!empty($section['description'])) {
            $section['description'] = '<p>' . $section['description'] . '</p>';
            $callback = function () use ($section) {
                echo $section['description'];
            };
        } elseif (isset($section['callback'])) {
            $callback = $section['callback'];
        } else {
            $callback = null;
        }
        add_settings_section(
            $this->settingsPrefix . $section['id'],
            !isset($section['hide_title']) || (bool) !$section['hide_title'] ? $section['title'] : '',
            $callback,
            $this->settingsPrefix . $section['id']
        );
        if (isset($section['subsections'])) {
            $this->addSubsections($section['id'], $section['subsections'], $capability);
        } else {
            $this->addFields($section['id']);
        }
    }

    /**
     * Add a subsection to the settings page.
     * @param string $sectionId
     * @param array $subsections
     * @param string $capability
     * @return void
     */
    protected function addSubsections(string $sectionId, array $subsections, string $capability)
    {
        $defaultCap = $capability;
        foreach ($subsections as $subsection) {
            if (!isset($subsection['id']) || !isset($subsection['title'])) {
                continue;
            }
            $capability = isset($subsection['capability']) ? $subsection['capability'] : $defaultCap;
            if (!current_user_can($capability)) {
                continue;
            }
            if (isset($subsection['hide_section']) && (bool) $subsection['hide_section']) {
                continue;
            }
            if (!isset($subsection['fields'])) {
                continue;
            }
            if (!empty($subsection['description'])) {
                $section['description'] = '<div class="inside">' . $subsection['description'] . '</div>';
                $callback = function () use ($subsection) {
                    echo $subsection['description'];
                };
            } elseif (isset($subsection['callback'])) {
                $callback = $subsection['callback'];
            } else {
                $callback = null;
            }
            add_settings_section(
                $this->settingsPrefix . $sectionId . '_' . $subsection['id'],
                !isset($subsection['hide_title']) || (bool) !$subsection['hide_title'] ? $subsection['title'] : '',
                $callback,
                $this->settingsPrefix . $sectionId
            );
            $this->addFields($sectionId, $subsection);
        }
    }

    /**
     * Add fields to the settings page.
     * @param string $sectionId
     * @param string $subsectionId
     * @return void
     */
    protected function addFields(string $sectionId, string $subsectionId = '')
    {
        $action = $_GET['action'] ?? '';
        $disableFields = [];

        $fields = isset($subsection['fields']) ? $subsection['fields'] : $this->fields;
        $subsectionId = $subsection['id'] ?? '';
        foreach ($fields as $option) {
            $name = $option['name'] ?? '';
            if (!isset($this->fields[$sectionId . '_' . $option['name']])) {
                continue;
            }

            $type = isset($option['type']) ? strtolower($option['type']) : '';
            $callback = Fields::callback($type);
            if (!is_callable($callback)) {
                continue;
            }

            $label = $option['label'] ?? '';
            $section = $sectionId . ($subsectionId ? '_' . $subsectionId : '');
            $default = $option['default'] ?? '';
            $value = $this->getOption($sectionId, $name, $default);
            $required = isset($option['required']) ? (bool) $option['required'] : false;
            $disabled = isset($option['disabled']) ? (bool) $option['disabled'] : false;
            if ($name == 'id' && $action == 'consent-edit') {
                $disableFields = array_merge(['id'], $disableFields);
            }
            if ($name == 'id' && $value == 'default') {
                $disableFields = array_merge(['settings[prioritize]'], $disableFields);
            }
            if (in_array($name, $disableFields)) {
                $disabled = true;
            }
            $atts = [
                'name' => $name,
                'id' => $this->settingsPrefix . $sectionId . '_' . $name,
                'label' => $label,
                'type' => $type,
                'description' => $option['description'] ?? '',
                'options' => $option['options'] ?? '',
                'default' => $default,
                'placeholder' => $option['placeholder'] ?? '',
                'section' => $sectionId,
                'option_name' => $this->optionName,
                'value' => $value,
                'size' => $option['size'] ?? '',
                'height' => isset($option['height']) ? absint($option['height']) : 0,
                'min' => $option['min'] ?? '',
                'max' => $option['max'] ?? '',
                'step' => $option['step'] ?? '',
                'inline' => isset($option['inline']) ? (bool) $option['inline'] : false,
                'disabled' => $disabled,
                'sanitize_callback' => $option['sanitize_callback'] ?? null,
                'required' => $required,
                'errors' => get_settings_errors($this->settingsPrefix . $section),
            ];

            $atts = Fields::matchAtts($atts);

            add_settings_field(
                "{$section}[{$name}]",
                $required ? $label . ' *' : $label,
                $callback,
                $this->settingsPrefix . $sectionId,
                $this->settingsPrefix . $section,
                $atts
            );
        }
    }

    /**
     * A callback function that sanitizes the option's value.
     * @param array $input
     * @return array
     */
    public function sanitizeOptions($input)
    {
        $hasError = false;
        $id = $_POST['id'] ?? '';
        $optionPage = $_POST['option_page'] ?? '';
        $prefix = substr($optionPage, strlen($this->settingsPrefix));
        if (!empty($input) && is_array($input) && $prefix != '') {
            foreach ($this->fields as $key => $option) {
                $type = isset($option['type']) ? strtolower($option['type']) : '';
                if ($type == '') {
                    continue;
                }
                $disabled = isset($option['disabled']) ? (bool) $option['disabled'] : false;
                if (!isset($input[$key]) && $disabled) {
                    $input[$key] = $this->options[$id][$option['name']] ?? ($type === 'checkbox' ? '0' : '');
                } elseif (!isset($input[$key]) && $type === 'checkbox') {
                    $input[$key] = '0';
                }
                $error = '';
                $label = $option['label'];
                $value = $input[$key] ?? '';
                $required = isset($option['required']) ? (bool) $option['required'] : false;
                if ($value == '' && $required && !$disabled) {
                    $error = sprintf(
                        /* translators: %s: Label of the field. */
                        __('The field %s is required.', 'rrze-legal'),
                        $label
                    );
                }
                $sanitizeCallback = $this->getSanitizeCallback($key);
                if ($sanitizeCallback !== false) {
                    $sanitizedValue = call_user_func($sanitizeCallback, $value);
                    if ($sanitizedValue === '' && $value !== '' && !$disabled) {
                        $error = sprintf(
                            /* translators: 1: Invalid value, 2: Label of the field. */
                            __('The value %1$s of the field %2$s is not valid.', 'rrze-legal'),
                            $value,
                            $label
                        );
                    } else {
                        $value = $sanitizedValue;
                    }
                }
                if ($error) {
                    $hasError = true;
                    $this->addSettingsError($error, 'error');
                } else {
                    $input[$key] = $value;
                }
            }
        }
        return $this->postSanitizeOptions($input, $hasError);
    }

    /**
     * Post sanitize options.
     * @param array $input
     * @param boolean $hasError
     * @return void
     */
    protected function postSanitizeOptions($input, $hasError)
    {
        return $input;
    }

    /**
     * Returns a sanitized option for the specified option key.
     * @param string $key Option key
     * @return mixed string|boolean false
     */
    protected function getSanitizeCallback(string $key = '')
    {
        if (isset($this->fields[$key])) {
            $option = $this->fields[$key];
            if (isset($option['sanitize_callback']) && is_callable($option['sanitize_callback'])) {
                return $option['sanitize_callback'];
            }
        }
        return false;
    }

    /**
     * Set input data transient.
     * @return void
     */
    public function setInputData()
    {
        set_transient('rrze_legal_input_data', $this->getInputData(), 30);
    }

    /**
     * Adds input data to the global variable.
     * @param array $data
     * @return void
     */
    public function addInputData(array $data)
    {
        global $rrzeLegalInputData;
        $rrzeLegalInputData = $data;
    }

    /**
     * Get input data from transient.
     * @return void
     */
    public function getInputData()
    {
        global $rrzeLegalInputData;
        if (get_transient('rrze_legal_input_data')) {
            $rrzeLegalInputData = (array) get_transient('rrze_legal_input_data');
            delete_transient('rrze_legal_input_data');
        }
        if (empty($rrzeLegalInputData)) {
            return [];
        }
        return $rrzeLegalInputData;
    }

    /**
     * Set the settings errors transient.
     * @return void
     */
    public function setSettingsErrors()
    {
        set_transient('rrze_legal_settings_errors', $this->getSettingsErrors(), 30);
    }

    /**
     * Adds settings errors to the global variable.
     * @param string $message
     * @param string $type
     * @return void
     */
    public function addSettingsError(string $message, string $type = 'error')
    {
        global $rrzeLegalSettingsErrors;
        $rrzeLegalSettingsErrors[] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    /**
     * Has settings errors?
     * @return boolean
     */
    public function hasSettingsErrors()
    {
        global $rrzeLegalSettingsErrors;
        return !empty($rrzeLegalSettingsErrors);
    }

    /**
     * Get the settings errors.
     * @return array
     */
    public function getSettingsErrors()
    {
        global $rrzeLegalSettingsErrors;
        if (get_transient('rrze_legal_settings_errors')) {
            $rrzeLegalSettingsErrors = array_merge((array) $rrzeLegalSettingsErrors, get_transient('rrze_legal_settings_errors'));
            delete_transient('rrze_legal_settings_errors');
        }
        if (empty($rrzeLegalSettingsErrors)) {
            return [];
        }
        return $rrzeLegalSettingsErrors;
    }

    /**
     * Print the settings errors.
     * @return void
     */
    public function settingsErrors()
    {
        $settingsErrors = $this->getSettingsErrors();
        if (empty($settingsErrors)) {
            return;
        }
        foreach ($settingsErrors as $error) {
            if ($error['type'] === 'success') {
                printf('<div class="notice notice-success is-dismissible"><p>%s</p></div>', $error['message']);
            } else {
                printf('<div class="notice notice-warning"><p>%s</p></div>', $error['message']);
            }
        }
    }

    /**
     * Set screen options.
     * @param string $status
     * @param string $option
     * @param string $value
     * @return string
     */
    public function setScreenOption($status, $option, $value)
    {
        if ('rrze_legal_consent_per_page' == $option) {
            return $value;
        }
        return $status;
    }

    /**
     * Screen options.
     */
    public function screenOptions()
    {
        $option = 'per_page';
        $args = [
            'label' => __('Number of items per page:', 'rrze-legal'),
            'default' => 20,
            'option' => 'rrze_legal_consent_per_page'
        ];

        add_screen_option($option, $args);
        $this->setListTable();
    }

    /**
     * Register admin scripts.
     * @return void
     */
    public function adminRegisterSettingsScripts()
    {
        wp_register_style(
            'rrze-legal-consent-settings',
            plugins_url('build/consent.css', plugin()->getBasename()),
            [],
            plugin()->getVersion()
        );
    }

    /**
     * Get the static data and update the options with that data.
     */
    protected function updateFromStaticData()
    {
        $data = $this->getStaticData();
        if (empty($data)) {
            return;
        }
        $optionVersion = get_option($this->optionName . '_version', '0');
        $version = isset($data['version']) ? (string) absint($data['version']) : '0';
        $staticData = $data['items'] ?? [];
        if (
            !empty($staticData) &&
            is_array($staticData) &&
            version_compare($optionVersion, $version, '!=')
        ) {
            $this->updateWithStaticData($staticData);
            update_option($this->optionName . '_version', $version);
        }
    }

    /**
     * Get static data.
     * @return array
     */
    protected function getStaticData()
    {
        include(plugin()->getPath() . "data/{$this->settingsFilename}.php");
        return $data ?? [];
    }

    /**
     * Update options with static data.
     * @param array $data
     */
    protected function updateWithStaticData(array $data)
    {
        $isPluginActiveForNetwork = Utils::isPluginActiveForNetwork(plugin()->getBaseName());
        foreach ($this->options as $key => $option) {
            $static = !empty($option['static']) ? true : false;
            if ($static && isset($data[$key]) && $isPluginActiveForNetwork) {
                $status = $this->options[$key]['status'] ?? '0';
                $this->options[$key] = $data[$key];
                $this->options[$key]['status'] = $status;
                $this->options[$key]['static'] = '1';
            } elseif ($static && !isset($data[$key])) {
                unset($this->options[$key]['static']);
            }
        }
        foreach ($data as $key => $value) {
            if (!isset($this->options[$key]) || $isPluginActiveForNetwork) {
                $status = $data[$key]['status'] ?? '0';
                $status = $this->options[$key]['status'] ?? $status;
                $this->options[$key] = $value;
                $this->options[$key]['status'] = $status;
                $this->options[$key]['static'] = '1';
            }
        }
        update_option($this->optionName, $this->options);
    }

    /**
     * Sanitize textarea list.
     * @param string $input
     * @return mixed
     */
    public function sanitizeTextareaList(string $input)
    {
        if (empty($input)) {
            return '';
        }
        $inputAry = explode(PHP_EOL, sanitize_textarea_field($input));
        $inputAry = array_filter(array_map('trim', $inputAry));
        $inputAry = array_unique(array_values($inputAry));
        return !empty($inputAry) ? implode(PHP_EOL, $inputAry) : '';
    }

    /**
     * Validate URL.
     * @param string $input
     * @return string
     */
    public function validateUrl(string $input): string
    {
        $input = sanitize_text_field($input);
        if (filter_var(
            $input,
            FILTER_VALIDATE_DOMAIN,
            FILTER_FLAG_HOSTNAME
        ) === false) {
            return '';
        }
        return $input;
    }

    /**
     * Validate Email
     * @param string $input
     * @return string
     */
    public function validateEmail(string $input): string
    {
        $input = sanitize_text_field($input);
        if (filter_var(
            $input,
            FILTER_VALIDATE_EMAIL
        ) === false) {
            return '';
        }
        return $input;
    }

    /**
     * Validate integer range.
     * @param string $input
     * @param integer $min
     * @param integer $max
     * @return integer
     */
    public function validateIntRange(string $input, int $min, int $max): int
    {
        $integer = intval($input);
        if (filter_var(
            $integer,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => $min,
                    'max_range' => $max
                ]
            ]
        ) === false) {
            return '';
        } else {
            return $integer;
        }
    }

    /**
     * Validate identifier.
     * @param string $input
     * @return string
     */
    public function validateId(string $input): string
    {
        $input = strtolower(sanitize_text_field($input));
        if (preg_match('/^[a-z\-\_]{3,}$/', $input) === 0) {
            return '';
        }
        return $input;
    }
}
