<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

/**
 * Class Fields
 * @package RRZE\Legal
 */
class Fields
{
    /**
     * Default field attributes.
     */
    const DEFAULT_ATTS = [
        'name' => '',
        'id' => '',
        'label' => '',
        'type' => 'text',
        'description' => '',
        'options' => '',
        'default' => '',
        'placeholder' => '',
        'section' => '',
        'option_name' => '',
        'value' => '',
        'size' => '',
        'height' => 0,
        'rows' => 0,
        'min' => '',
        'max' => '',
        'step' => '',
        'inline' => false,
        'disabled' => false,
        'readonly' => false,
        'sanitize_callback' => null,
        'required' => false,
        'errors' => '',
        'notice' => '',
        'content_name' => '',
        'content_label' => '',
        'content_description' => '',
        'content_value' => '',
        'content_height' => 0,
        'content_editor' => false,
    ];

    /**
     * Outputs form markup whose dynamic values have been escaped while building it.
     * @param string $html Form markup
     */
    protected static function outputHtml(string $html): void {
        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Each field builder escapes attributes and text before constructing the form markup.
    }

    /**
     * Match given attributes with known attributes
     * and fill in default values when necessary.
     * @param array $atts Attributes
     * @return array Matched attributes
     */
    public static function matchAtts($atts = [])
    {
        $atts = (array) $atts;
        $out  = [];
        foreach (self::DEFAULT_ATTS as $name => $default) {
            if (array_key_exists($name, $atts)) {
                $out[$name] = $atts[$name];
            } else {
                $out[$name] = $default;
            }
        }
        return $out;
    }

    /**
     * Function that fills the field with the desired form inputs
     * @param string $type Type of input
     * @return mixed callable|null
     */
    public static function callback($type = '')
    {
        if (method_exists(__CLASS__, $type) && is_callable([__CLASS__, $type])) {
            return [__CLASS__, $type];
        }
        return null;
    }

    /**
     * Returns a description of the settings field.
     * @param array $atts Description attributes
     */
    public static function description(array $atts) {
        if (!empty($atts['description'])) {
            $desc = sprintf(
                '<div class="description">%s</div>',
                wp_kses($atts['description'], self::getDescriptionAllowedHtml())
            );
        } else {
            $desc = '';
        }
        return $desc;
    }

    protected static function getDescriptionAllowedHtml(): array {
        $allowedHtml = wp_kses_allowed_html('post');
        $allowedHtml['details'] = [
            'class' => true,
            'open' => true,
        ];
        $allowedHtml['summary'] = [
            'class' => true,
        ];

        return $allowedHtml;
    }

    /**
     * Displays a text input field.
     * @param array $atts Settings field attributes
     */
    public static function text(array $atts, string $type = 'text') {
        $value = esc_attr($atts['value']);
        $size = $atts['size'] != '' ? $atts['size'] : 'regular';
        $placeholder = $atts['placeholder'] != '' ? ' placeholder="' . esc_attr($atts['placeholder']) . '"' : '';

        $length = '';
        if ((isset($atts['size'])) && (is_numeric($atts['size']))) {
            $length = ' size="' . esc_attr($atts['size']) . '"';
        }
        $pattern = '';
        $title = '';
        switch ($type) {
            case 'tel':
                $pattern = ' pattern="\s*\+?[0-9](?:[0-9\s\(\)\.\/\-]*[0-9]){2,}\s*"';
                $title = ' title="' . esc_attr__(
                    'Enter a telephone or fax number with at least three digits. Spaces, hyphens, parentheses, slashes, and a leading plus sign are permitted, e.g. +49 89 2186 0 or 09131 85-0.',
                    'rrze-legal'
                ) . '"';
                break;
             case 'email':
                $pattern = ' pattern=".+@[a-z0-9\.\-]+\.[a-z]{2,6}"';
                break;
             case 'url':
                $pattern = ' pattern="^https:\/\/[a-z0-9\-\.]+\.[a-z]{2,6}.*"';
                break;
        }
        
        
        
        $html = '';
        if ($atts['disabled']) {
            $html .= sprintf(
                '<input type="hidden" name="%1$s[%2$s_%3$s]" value="%4$s">',
                esc_attr($atts['option_name']),
                esc_attr($atts['section']),
                esc_attr($atts['name']),
                $value,
            );
        }
        
        $html .= sprintf(
            '<input type="%1$s" class="%2$s-text" id="%3$s" name="%4$s[%5$s_%6$s]" value="%7$s"%8$s%9$s%10$s%11$s%12$s%13$s>',
            esc_attr($type),
            esc_attr($size),
            esc_attr($atts['id']),
            esc_attr($atts['option_name']),
            esc_attr($atts['section']),
            esc_attr($atts['name']),
            $value,
            $placeholder,
            $atts['disabled'] ? ' disabled="disabled"' : '',
            $atts['readonly'] ? ' readonly="readonly"' : '',
            $length,
            $pattern,
            $title
        );
        $html .= self::description($atts);

        self::outputHtml($html);
    }

    /**
     * Displays a email input field.
     * @param array $atts Settings field attributes
     */
    public static function email(array $atts)  {
        self::text($atts, 'email');
    }
    
      /**
     * Displays a url input field.
     * @param array $atts Settings field attributes
     */
    public static function url(array $atts)  {
        self::text($atts, 'url');
    }
    
     /**
     * Displays a tel input field.
     * @param array $atts Settings field attributes
     */
    public static function tel(array $atts)  {
        $formatDescription = __(
            'Enter a telephone or fax number with at least three digits. Spaces, hyphens, parentheses, slashes, and a leading plus sign are permitted, e.g. +49 89 2186 0 or 09131 85-0.',
            'rrze-legal'
        );
        $atts['description'] = empty($atts['description'])
            ? $formatDescription
            : $atts['description'] . ' ' . $formatDescription;

        self::text($atts, 'tel');
    }

    /**
     * Displays a textarea field.
     * @param array $atts Settings field attributes
     */
    public static function textarea(array $atts, string $editorType = '') {
        $value = esc_textarea($atts['value']);
        $placeholder = $atts['placeholder'] != '' ? ' placeholder="' . esc_attr($atts['placeholder']) . '"' : '';
        $editorType = $editorType ? 'wpcode-' . $editorType . '-editor ' : '';
        $rows = absint($atts['rows']) > 0 ? absint($atts['rows']) : 4;
        $height = absint($atts['height']) > 0 ? sprintf(' style="min-height: %dpx;"', absint($atts['height'])) : '';
        $format = '<textarea %1$srows="%2$d" cols="50" id="%3$s" name="%4$s[%5$s_%6$s]"%7$s%8$s%9$s%10$s>%11$s</textarea>';
        if ($editorType != '') {
            $format = '<div class="code-editor">' . $format . '</div>';
        }

        $html = '';
        if ($atts['disabled']) {
            $html .= sprintf(
                '<input type="hidden" name="%1$s[%2$s_%3$s]" value="%4$s">',
                esc_attr($atts['option_name']),
                esc_attr($atts['section']),
                esc_attr($atts['name']),
                $value,
            );
        }
     
        
        
        $html .= sprintf(
            $format,
            esc_attr($editorType),
            $rows,
            esc_attr($atts['id']),
            esc_attr($atts['option_name']),
            esc_attr($atts['section']),
            esc_attr($atts['name']),
            $placeholder,
            $atts['disabled'] ? ' disabled="disabled"' : '',
            $atts['readonly'] ? ' readonly="readonly"' : '',
            $height,
            $value
        );
        $html .= self::description($atts);

        self::outputHtml($html);
    }

    /**
     * Displays a html code editor input field.
     * @param array $atts Settings field attributes
     */
    public static function htmleditor(array $atts) {
        self::textarea($atts, 'html');
    }

    /**
     * Displays a js code editor input field.
     * @param array $atts Settings field attributes
     */
    public static function jseditor(array $atts)  {
        self::textarea($atts, 'js');
    }

    /**
     * Displays a css code editor input field.
     * @param array $atts Settings field attributes
     */
    public static function csseditor(array $atts) {
        self::textarea($atts, 'css');
    }

    /**
     * Displays a number input field.
     * @param array $atts Settings field attributes
     */
    public static function number(array $atts) {
        $value = esc_attr($atts['value']);
        $size = $atts['size'] != '' ? $atts['size'] : 'regular';
        $placeholder = $atts['placeholder'] != '' ? ' placeholder="' . esc_attr($atts['placeholder']) . '"' : '';
        $min = ($atts['min'] != '') ? ' min="' . esc_attr($atts['min']) . '"' : '';
        $max = ($atts['max'] != '') ? ' max="' . esc_attr($atts['max']) . '"' : '';
        $step = ($atts['step'] != '') ? ' step="' . esc_attr($atts['step']) . '"' : '';

        $html = sprintf(
            '<input type="number" class="%1$s-number" id="%2$s" name="%3$s[%4$s_%5$s]" value="%6$s"%7$s%8$s%9$s%10$s>',
            esc_attr($size),
            esc_attr($atts['id']),
            esc_attr($atts['option_name']),
            esc_attr($atts['section']),
            esc_attr($atts['name']),
            $value,
            $placeholder,
            $min,
            $max,
            $step
        );
        $html .= self::description($atts);

        self::outputHtml($html);
    }

    /**
     * Displays a date input field.
     * @param array $atts Settings field attributes
     */
    public static function date(array $atts)
    {
        $value = esc_attr($atts['value']);
        $size = $atts['size'] != '' ? $atts['size'] : 'regular';
        $placeholder = $atts['placeholder'] != '' ? ' placeholder="' . esc_attr($atts['placeholder']) . '"' : '';
        $min = ($atts['min'] != '') ? ' min="' . esc_attr($atts['min']) . '"' : '';
        $max = ($atts['max'] != '') ? ' max="' . esc_attr($atts['max']) . '"' : '';
        $step = ($atts['step'] != '') ? ' step="' . esc_attr($atts['step']) . '"' : '';

        $html = sprintf(
            '<input type="date" class="%1$s-text" id="%2$s" name="%3$s[%4$s_%5$s]" value="%6$s"%7$s%8$s%9$s%10$s>',
            esc_attr($size),
            esc_attr($atts['id']),
            esc_attr($atts['option_name']),
            esc_attr($atts['section']),
            esc_attr($atts['name']),
            $value,
            $placeholder,
            $min,
            $max,
            $step
        );
        $html .= self::description($atts);

        self::outputHtml($html);
    }

    /**
     * Displays a checkbox input field.
     * @param array $atts Settings field attributes
     */
    public static function checkbox(array $atts) {
        $value = $atts['value'];

        $html = '';
        if ($atts['disabled']) {
            $html .= sprintf(
                '<input type="hidden" name="%1$s[%2$s_%3$s]" value="%4$s">',
                esc_attr($atts['option_name']),
                esc_attr($atts['section']),
                esc_attr($atts['name']),
                esc_attr($value),
            );
        }
        $html .= '<label>';
        $html .= sprintf(
            '<input type="checkbox" id="%1$s" name="%2$s[%3$s_%4$s]" value="1" %5$s%6$s>',
            esc_attr($atts['id']),
            esc_attr($atts['option_name']),
            esc_attr($atts['section']),
            esc_attr($atts['name']),
            checked($value, '1', false),
            $atts['disabled'] ? ' disabled="disabled"' : ''
        );
        $html .= sprintf(
            '%s</label>',
            wp_kses_post($atts['description'])
        );
        if (!empty($atts['notice'])) {
            $html .= wp_kses_post($atts['notice']);
        }

        self::outputHtml($html);
    }

    /**
     * Displays a checkbox with a directly related text field below it.
     * @param array $atts Settings field attributes
     */
    public static function optionalwpeditor(array $atts) {
        $value = $atts['value'];
        $contentName = sanitize_key($atts['content_name']);
        $contentId = sanitize_key($atts['section'] . '_' . $contentName);
        $height = absint($atts['content_height']) > 150 ? absint($atts['content_height']) : 250;

        $html = '<div class="rrze-legal-optional-textfield">';
        if ($atts['disabled']) {
            $html .= sprintf(
                '<input type="hidden" name="%1$s[%2$s_%3$s]" value="%4$s">',
                esc_attr($atts['option_name']),
                esc_attr($atts['section']),
                esc_attr($atts['name']),
                esc_attr($value)
            );
        }
        $html .= '<label>';
        $html .= sprintf(
            '<input type="checkbox" id="%1$s" name="%2$s[%3$s_%4$s]" value="1" %5$s%6$s>',
            esc_attr($atts['id']),
            esc_attr($atts['option_name']),
            esc_attr($atts['section']),
            esc_attr($atts['name']),
            checked($value, '1', false),
            $atts['disabled'] ? ' disabled="disabled"' : ''
        );
        $html .= sprintf('%s</label>', wp_kses_post($atts['description']));
        $html .= '<div class="rrze-legal-optional-textfield-content">';
        if ($atts['content_label'] !== '') {
            $html .= sprintf(
                '<p><label for="%1$s"><strong>%2$s</strong></label></p>',
                esc_attr($contentId),
                esc_html($atts['content_label'])
            );
        }
        self::outputHtml($html);

        if ($atts['content_editor']) {
            wp_editor($atts['content_value'], $contentId, [
                'teeny' => true,
                'media_buttons' => false,
                'wpautop' => false,
                'editor_height' => $height,
                'textarea_name' => sprintf(
                    '%1$s[%2$s_%3$s]',
                    esc_attr($atts['option_name']),
                    esc_attr($atts['section']),
                    esc_attr($contentName)
                ),
                'textarea_rows' => 10,
            ]);
        } else {
            self::outputHtml(sprintf(
                '<textarea class="large-text rrze-legal-optional-textfield-editor" rows="10" style="min-height: %1$dpx;" id="%2$s" name="%3$s[%4$s_%5$s]">%6$s</textarea>',
                $height,
                esc_attr($contentId),
                esc_attr($atts['option_name']),
                esc_attr($atts['section']),
                esc_attr($contentName),
                esc_textarea($atts['content_value'])
            ));
        }

        self::outputHtml(self::description(['description' => $atts['content_description']]));
        self::outputHtml('</div></div>');
    }

    /**
     * Displays a multi-checkbox input field.
     * @param array $atts Settings field attributes
     */
    public static function multicheckbox(array $atts) {
        $value = (array) $atts['value'];

        $html = '<fieldset>';
        foreach ($atts['options'] as $key => $option) {
            $label = is_array($option) ? ($option['label'] ?? '') : $option;
            $description = is_array($option) ? ($option['description'] ?? '') : '';
            $disabled = is_array($option) ? !empty($option['disabled']) : false;
            $checked = !empty($value[$key]);
            if ($disabled && $checked) {
                $html .= sprintf(
                    '<input type="hidden" name="%1$s[%2$s_%3$s][%4$s]" value="1">',
                    esc_attr($atts['option_name']),
                    esc_attr($atts['section']),
                    esc_attr($atts['name']),
                    esc_attr($key)
                );
            }
            $html .= '<label>';
            $html .= sprintf(
                '<input type="checkbox" id="%1$s-%5$s" name="%2$s[%3$s_%4$s][%5$s]" value="1" %6$s%7$s>',
                esc_attr($atts['id']),
                esc_attr($atts['option_name']),
                esc_attr($atts['section']),
                esc_attr($atts['name']),
                esc_attr($key),
                checked(true, $checked, false),
                $disabled ? ' disabled="disabled"' : ''
            );
            $html .= sprintf('%s</label>', wp_kses_post($label));
            if ($description !== '') {
                $html .= sprintf('<p class="description">%s</p>', wp_kses_post($description));
            }
            $html .= '<br>';
        }

        $html .= self::description($atts);
        $html .= '</fieldset>';

        self::outputHtml($html);
    }

    /**
     * Displays a radio input field.
     * @param array $atts Settings field attributes
     */
    public static function radio(array $atts)  {
        $value = $atts['value'];

        $html  = '<fieldset>';

        foreach ($atts['options'] as $key => $label) {
            $html .= '<label>';
            $html .= sprintf(
                '<input type="radio" name="%1$s[%2$s_%3$s]" value="%4$s" %5$s>',
                esc_attr($atts['option_name']),
                esc_attr($atts['section']),
                esc_attr($atts['name']),
                esc_attr($key),
                checked($value, $key, false)
            );
            $html .= sprintf(
                '%1$s</label>%2$s',
                wp_kses_post($label),
                $atts['inline'] != '' ? ' &nbsp;' : '<br>'
            );
        }

        $html .= self::description($atts);
        $html .= '</fieldset>';

        self::outputHtml($html);
    }

    /**
     * Displays a selectbox field.
     * @param array $atts Settings field attributes
     */
    public static function select(array $atts)
    {
        $value = esc_attr($atts['value']);

        $html  = sprintf(
            '<select id="%1$s" name="%2$s[%3$s_%4$s]">',
            esc_attr($atts['id']),
            esc_attr($atts['option_name']),
            esc_attr($atts['section']),
            esc_attr($atts['name'])
        );

        foreach ($atts['options'] as $key => $label) {
            $html .= sprintf(
                '<option value="%1$s"%2$s>%3$s</option>',
                esc_attr($key),
                selected($value, $key, false),
                esc_html($label)
            );
        }

        $html .= sprintf('</select>');
        $html .= self::description($atts);

        self::outputHtml($html);
    }

    /**
     * Displays a selectbox field with all pages available to select from.
     * @param array $atts Settings field attributes
     */
    public static function selectpage(array $atts)
    {
        $value = esc_attr($atts['value']);

        $name = sprintf(
            '%1$s[%2$s_%3$s]',
            $atts['option_name'],
            $atts['section'],
            $atts['name']
        );

        $html = wp_dropdown_pages(
            [
                'name'              => esc_attr($name),
                'echo'              => 0,
                'show_option_none'  => esc_html(__('&mdash; Select &mdash;', 'rrze-legal')),
                'option_none_value' => esc_html($atts['default']),
                'selected'          => esc_attr($value)
            ]
        );
        $html .= self::description($atts);

        self::outputHtml($html);
    }

    /**
     * Displays a multi-selectbox field.
     * @param array   $atts Settings field attributes
     */
    public static function multiselect(array $atts)  {
        $value = (array) $atts['value'];

        $html  = sprintf(
            '<select id="%1$s" name="%2$s[%3$s_%4$s][]" multiple="multiple">',
            esc_attr($atts['id']),
            esc_attr($atts['option_name']),
            esc_attr($atts['section']),
            esc_attr($atts['name'])
        );

        foreach ($atts['options'] as $key => $label) {
            $html .= sprintf(
                '<option value="%1$s"%2$s>%3$s</option>',
                esc_attr($key),
                selected(true, in_array($key, $value), false),
                esc_html($label)
            );
        }

        $html .= sprintf('</select>');
        $html .= self::description($atts);

        self::outputHtml($html);
    }

    /**
     * Displays a rich text text box (WP editor) for a settings panel.
     * @param array $atts Settings field attributes
     */
    public static function wpeditor($atts) {
        $value = $atts['value'];
        $height = $atts['height'] > 150 ? $atts['height'] : 250;

        $editorSettings = [
            'teeny' => true,
            'media_buttons' => false,
            'wpautop' => false,
            'editor_height' => $height,
            'textarea_name' => sprintf(
                '%1$s[%2$s_%3$s]',
                esc_attr($atts['option_name']),
                esc_attr($atts['section']),
                esc_attr($atts['name'])
            ),
            'textarea_rows' => 10
        ];

        echo '<div class="wpeditor-field-container">';
        wp_editor($value, sanitize_key($atts['section'] . '_' . $atts['name']), $editorSettings);
        echo '</div>';
        self::outputHtml(self::description($atts));
    }
}
