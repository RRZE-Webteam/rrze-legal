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
     * Settings prefix.
     * @var string
     */
    protected $settingsPrefix = '';

    public function __construct()
    {
        $this->pagePrefix = plugin()->getSlug() . '-';
        $this->settingsPrefix = str_replace('-', '_', $this->pagePrefix);

        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
    }

    /**
     * Execute on 'plugins_loaded' API/action.
     */
    public function loaded()
    {
        if ($this->optionName === '') {
            return;
        }
        include_once(plugin()->getPath() . "settings/settings.php");
        $this->settings = $settings;
        $this->optionsPage = (object) $this->settings['options_page']['page'];
        $this->optionsMenu = (object) $this->settings['options_page']['menu'];
        $this->sections = (object) $this->settings['settings']['sections'];

        $this->setFields();
        $this->setOptions();

        add_action('admin_menu', [$this, 'adminMenu'], $this->optionsMenu->position);
        add_action('admin_init', [$this, 'adminInit']);
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
        $options = (array) get_option($this->optionName);
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
                        $this->formatField($subsection['fields'], $section['id'], $subsection['id']);
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
     * @param string  $subsection The subsection id this field belongs to
     * @return void
     */
    protected function formatField(array $fields, string $sectionId, string $subsectionId = '')
    {
        $subsection = $subsectionId !== '' ? '_' . $subsectionId : '';
        foreach ($fields as $option) {
            if (isset($option['capability']) && !current_user_can($option['capability'])) {
                continue;
            }
            if (isset($option['name'])) {
                $this->fields[$sectionId . $subsection . '_' . $option['name']] = $option;
            }
        }
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
     * Adds a submenu page to the Settings main menu.
     * @return void
     */
    public function adminMenu()
    {
        foreach ($this->sections as $key => $section) {
            $sectionId = str_replace('_', '-', $section['id']);
            if ($key == 0) {
                $this->defaultTab = $this->pagePrefix . $sectionId;
            }
            $this->allTabs[] = $this->pagePrefix . $sectionId;
        }

        $this->currentTab = array_key_exists('current-tab', $_GET) && in_array($_GET['current-tab'], $this->allTabs) ? $_GET['current-tab'] : $this->defaultTab;

        add_options_page(
            $this->optionsPage->title,
            $this->optionsMenu->title,
            $this->optionsMenu->capability,
            $this->optionsMenu->slug,
            [$this, 'pageOutput']
        );
    }

    /**
     * Display the settings page.
     * @return void
     */
    public function pageOutput()
    {
        flush_rewrite_rules(false);
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
            do_settings_sections($this->settingsPrefix . $section['id']);
            settings_fields($this->settingsPrefix . $section['id']);
            submit_button();
            echo '</form>' . PHP_EOL;
        }
    }

    /**
     * Registration of sections and fields.
     * @return void
     */
    public function adminInit()
    {
        $this->registerSetting();
    }

    /**
     * Register the settings sections and fields.
     */
    protected function registerSetting()
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
            $this->addFields($sectionId, $subsection['id']);
        }
    }

    /**
     * Add fields to the settings page.
     * @param string $sectionId
     * @param string $subsectionId
     * @param string $capability
     * @return void
     */
    protected function addFields(string $sectionId, string $subsectionId = '')
    {
        foreach ($this->fields as $key => $option) {
            $suffix = $subsectionId ? '_' . $subsectionId . '_' : '_';
            if (strpos($key, $sectionId . $suffix) !== 0) {
                continue;
            }
            $name = $option['name'];
            $type = isset($option['type']) ? strtolower($option['type']) : 'text';
            $label = isset($option['label']) ? $option['label'] : '';

            $suffix = $subsectionId ? '_' . $subsectionId : '';
            $section = $sectionId . $suffix;
            $default = isset($option['default']) ? $option['default'] : '';
            $value = $this->getOption($section, $name, $default);
            $required = isset($option['required']) ? (bool) $option['required'] : false;

            $atts = [
                'name' => $name,
                'id' => $this->settingsPrefix . $section . '_' . $name,
                'label' => $label,
                'type' => $type,
                'description' => isset($option['description']) ? $option['description'] : '',
                'options' => isset($option['options']) ? $option['options'] : '',
                'default' => $default,
                'placeholder' => isset($option['placeholder']) ? $option['placeholder'] : '',
                'section' => $section,
                'option_name' => $this->optionName,
                'value' => $value,
                'size' => isset($option['size']) ? $option['size'] : '',
                'height' => isset($option['height']) ? absint($option['height']) : 0,
                'min' => isset($option['min']) ? $option['min'] : '',
                'max' => isset($option['max']) ? $option['max'] : '',
                'step' => isset($option['step']) ? $option['step'] : '',
                'inline' => isset($option['inline']) ? (bool) $option['inline'] : false,
                'disabled' => isset($option['disabled']) ? (bool) $option['disabled'] : false,
                'sanitize_callback' => isset($option['sanitize_callback']) ? $option['sanitize_callback'] : null,
                'required' => $required,
                'errors' => get_settings_errors($this->settingsPrefix . $section),
            ];

            $callback = Fields::callback($type);
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
        $optionPage = $_POST['option_page'] ?? '';
        $prefix = substr($optionPage, strlen($this->settingsPrefix));
        if (empty($input || !is_array($input)) || $prefix == '') {
            return $this->options;
        }
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
                $input[$key] = $this->options[$key];
            } elseif (!isset($input[$key]) && $type === 'checkbox') {
                $input[$key] = '0';
            }
            $error = '';
            $section = substr($key, 0, strrpos($key, '_' . $option['name']));
            $settings = $this->settingsPrefix . $section;
            $label = $option['label'];
            $value = $input[$key];
            $required = isset($option['required']) ? (bool) $option['required'] : false;
            if ($value === '' && $required) {
                $error = sprintf(
                    /* translators: %s: label of the field. */
                    __('The field %s is required.', 'rrze-legal'),
                    $label
                );
            }
            $sanitizeCallback = $this->getSanitizeCallback($key);
            if ($sanitizeCallback !== false) {
                $sanitizedValue = call_user_func($sanitizeCallback, $value);
                if ($sanitizedValue === '' && $value !== '') {
                    $error = sprintf(
                        /* translators: %s: label of the field. */
                        __('The value of the field %s is not valid.', 'rrze-legal'),
                        $label
                    );
                } else {
                    $value = $sanitizedValue;
                }
            }
            if ($error) {
                add_settings_error($settings, $key, $error, 'error');
            } else {
                $this->options[$key] = $value;
            }
        }
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
     * Enqueue admin scripts.
     * @return void
     */
    public function adminEnqueueScripts()
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
            'optionName' => $this->optionName,
            'dateFormat' => __('yy-mm-dd', 'rrze-legal'),
        ]);
    }

    /**
     * sanitizeTextareaList
     * @param string $input
     * @param boolean $sort
     * @return mixed
     */
    protected function sanitizeTextareaList(string $input, bool $sort = true)
    {
        if (!empty($input)) {
            $inputAry = explode(PHP_EOL, sanitize_textarea_field($input));
            $inputAry = array_filter(array_map('trim', $inputAry));
            $inputAry = array_unique(array_values($inputAry));
            if ($sort) sort($inputAry);
            return !empty($inputAry) ? implode(PHP_EOL, $inputAry) : '';
        }
        return '';
    }
}
