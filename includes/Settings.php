<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

/**
 * Class Settings
 * @package RRZE\Legal
 */
class Settings
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
     * All tabs.
     * @var array
     */
    protected $allTabs = [];

    /**
     * Standard tab.
     * @var string
     */
    protected $defaultTab = '';

    /**
     * Current tab.
     * @var string
     */
    protected $currentTab = '';

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
     * Class constructor.
     */
    public function __construct()
    {
        $this->pagePrefix = plugin()->getSlug() . '-';
        $this->settingsPrefix = str_replace('-', '_', $this->pagePrefix);

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
    }

    public function setAdminMenu()
    {
        add_action('admin_menu', [$this, 'adminSubMenu']);
        add_action('admin_init', [$this, 'registerSetting']);
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
     * Returns the default tab.
     * @return string
     */
    public function getDefaultTab()
    {
        return $this->defaultTab;
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
        $defaults = $this->defaultOptions();
        $options = get_option($this->optionName);
        $options = $options !== false ? $options : [];
        $options = wp_parse_args($options, $defaults);
        $this->options = array_intersect_key($options, $defaults);
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
        foreach ($this->sections as $key => $section) {
            $sectionId = str_replace('_', '-', $section['id']);
            if ($key == 0) {
                $this->defaultTab = $this->pagePrefix . $sectionId;
            }
            $this->allTabs[] = $this->pagePrefix . $sectionId;
        }

        $this->currentTab = array_key_exists('current-tab', $_GET) && in_array($_GET['current-tab'], $this->allTabs) ? $_GET['current-tab'] : $this->defaultTab;

        add_submenu_page(
            $this->optionsParent->slug,
            $this->optionsPage->title,
            $this->optionsMenu->title,
            $this->optionsMenu->capability,
            $this->optionsMenu->slug,
            [$this, 'subMenuPage'],
            $this->optionsMenu->position
        );
    }

    /**
     * Display the admin sub menu page.
     * @return void
     */
    public function subMenuPage()
    {
        wp_enqueue_style('rrze-legal-settings');
        wp_enqueue_script('rrze-legal-settings');
        echo '<div class="wrap">', PHP_EOL;
        $this->sectionsTabs();
        $this->settingsForm();
        echo '</div>', PHP_EOL;
    }

    /**
     * Display the settings sections as tabs.
     * @return void
     */
    public function sectionsTabs()
    {
        $html = '<h1>' . $this->settings['settings']['title'] . '</h1>' . PHP_EOL;
        $count = 0;
        foreach ($this->sections as $section) {
            if (isset($section['capability']) && !current_user_can($section['capability'])) {
                continue;
            }
            $count++;
        }
        if ($count < 2) {
            echo $html;
            return;
        }

        $html .= '<h2 class="nav-tab-wrapper wp-clearfix">';
        foreach ($this->sections as $section) {
            $sectionId = str_replace('_', '-', $section['id']);
            if (isset($section['capability']) && !current_user_can($section['capability'])) {
                continue;
            }
            $class = $this->pagePrefix . $sectionId == $this->currentTab ? 'nav-tab-active' : $this->defaultTab;
            $html .= sprintf(
                '<a href="?page=%4$s&current-tab=%1$s" class="nav-tab %3$s" id="%1$s-tab">%2$s</a>',
                esc_attr($this->pagePrefix . $sectionId),
                $section['title'],
                esc_attr($class),
                $this->optionsMenu->slug
            );
        }
        $html .= '</h2>' . PHP_EOL;
        echo $html;
    }

    /**
     * Displays the corresponding form for each setting sections.
     * @return void
     */
    public function settingsForm()
    {
        foreach ($this->sections as $section) {
            $sectionId = str_replace('_', '-', $section['id']);
            if ($this->pagePrefix . $sectionId != $this->currentTab) {
                continue;
            }
            echo '<form id="', $this->pagePrefix . $sectionId . '" method="post" action="options.php">';
            settings_errors();
            do_settings_sections($this->settingsPrefix . $section['id']);
            settings_fields($this->settingsPrefix . $section['id']);
            submit_button();
            echo '</form>' . PHP_EOL;
        }
    }

    /**
     * Register the settings sections and fields.
     */
    public function registerSetting()
    {
        foreach ($this->sections as $section) {
            if (!isset($section['id']) || !isset($section['title'])) {
                continue;
            }
            $this->addSection($section);
            register_setting(
                $this->settingsPrefix . $section['id'],
                $this->optionName,
                ['sanitize_callback' => [$this, 'sanitizeOptions']]
            );
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
            !isset($section['hide_title']) || $section['hide_title'] === false ? $section['title'] : '',
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
     * @param array $subsection
     * @return void
     */
    protected function addFields(string $sectionId, array $subsection = [])
    {
        $fields = $subsection['fields'] ?? $this->fields;
        $subsectionId = $subsection['id'] ?? '';
        foreach ($fields as $key => $option) {
            if (!$subsectionId && strpos($key, $sectionId . '_') !== 0) {
                continue;
            }
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
                'disabled' => isset($option['disabled']) ? (bool) $option['disabled'] : false,
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
        $optionPage = $_POST['option_page'] ?? '';
        $prefix = substr($optionPage, strlen($this->settingsPrefix));
        if (!empty($input) && is_array($input) && $prefix != '') {
            foreach ($this->fields as $key => $option) {
                if (strpos($key, $prefix) !== 0) {
                    continue;
                }
                $type = isset($option['type']) ? strtolower($option['type']) : '';
                if (!$type) {
                    continue;
                }
                $disabled = isset($option['disabled']) ? (bool) $option['disabled'] : false;
                if (!isset($input[$key]) && $disabled) {
                    $input[$key] = $this->options[$key] ?? ($type === 'checkbox' ? '0' : '');
                } elseif (!isset($input[$key]) && $type === 'checkbox') {
                    $input[$key] = '0';
                }
                $error = '';
                $settings = '';
                foreach ($this->getSettingsSections() as $settingsSection) {
                    if (strpos($this->settingsPrefix . $key, $settingsSection) === 0) {
                        $settings = $settingsSection;
                        break;
                    }
                }
                $label = $option['label'];
                $value = $input[$key] ?? '';
                $required = isset($option['required']) ? (bool) $option['required'] : false;
                if ($value === '' && $required && !$disabled) {
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
                    add_settings_error($settings, $key, $error, 'error');
                } else {
                    $input[$key] = $value;
                    $this->options[$key] = $value;
                }
            }
        }
        return $this->postSanitizeOptions($input, $hasError);
    }

    protected function postSanitizeOptions($input, $hasError)
    {
        return $this->options;
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
     * Register admin scripts.
     * @return void
     */
    public function adminRegisterSettingsScripts()
    {
        wp_register_style(
            'rrze-legal-settings',
            plugins_url('build/settings.css', plugin()->getBasename()),
            [],
            plugin()->getVersion()
        );
        wp_register_script(
            'rrze-legal-settings',
            plugins_url('build/settings.js', plugin()->getBasename()),
            ['jquery', 'jquery-ui-datepicker'],
            plugin()->getVersion()
        );
        wp_localize_script('rrze-legal-settings', 'legalSettings', [
            'dateFormat' => __('yy-mm-dd', 'rrze-legal')
        ]);
    }

    /**
     * sanitizeTextareaList
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
     * Sanitize textarea IP addresses list.
     * @param string $input
     * @return string
     */
    public function sanitizeTextareaIpList(string $input = '')
    {
        if (!empty($input)) {
            $inputAry = explode(PHP_EOL, sanitize_textarea_field($input));
            $inputAry = array_filter(array_map('trim', $inputAry));
            $inputAry = array_unique(array_values($inputAry));
        }
        $inputAry = !empty($inputAry) ? Utils::sanitizeIpRange($inputAry) : '';
        return !empty($inputAry) ? implode(PHP_EOL, $inputAry) : '';
    }

    /**
     * Validate URL
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
     * Validate Integer Range
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
}
