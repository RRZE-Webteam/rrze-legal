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
        'min' => '',
        'max' => '',
        'step' => '',
        'inline' => false,
        'disabled' => false,
        'sanitize_callback' => null,
        'required' => false,
        'errors' => '',
    ];

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
                '<p class="description">%s</p>',
                $atts['description']
            );
        } else {
            $desc = '';
        }
        return $desc;
    }

    /**
     * Displays a text input field.
     * @param array $atts Settings field attributes
     */
    public static function text(array $atts, string $type = 'text') {
        $value = esc_attr($atts['value']);
        $size = $atts['size'] != '' ? $atts['size'] : 'regular';
        $placeholder = $atts['placeholder'] != '' ? ' placeholder="' . $atts['placeholder'] . '"' : '';

        $length = '';
        if ((isset($atts['size'])) && (is_numeric($atts['size']))) {
            $length = ' size="'.$atts['size'].'"';
        }
        $pattern = '';
        switch ($type) {
            case 'tel':
                $pattern = ' pattern="[0-9\+]{3} [0-9]{3,5} [0-9\-\s]+"';
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
                $atts['option_name'],
                $atts['section'],
                $atts['name'],
                $value,
            );
        }
        
        $html .= sprintf(
            '<input type="%1$s" class="%2$s-text" id="%3$s" name="%4$s[%5$s_%6$s]" value="%7$s"%8$s%9$s%10$s%11$s>',
            $type,
            $size,
            $atts['id'],
            $atts['option_name'],
            $atts['section'],
            $atts['name'],
            $value,
            $placeholder,
            $atts['disabled'] ? ' disabled="disabled"' : '',
            $length,
            $pattern    
        );
        $html .= self::description($atts);

        echo $html;
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
        self::text($atts, 'tel');
    }

    /**
     * Displays a textarea field.
     * @param array $atts Settings field attributes
     */
    public static function textarea(array $atts, string $editorType = '') {
        $value = esc_textarea($atts['value']);
        $placeholder = $atts['placeholder'] != '' ? ' placeholder="' . $atts['placeholder'] . '"' : '';
        $editorType = $editorType ? 'wpcode-' . $editorType . '-editor ' : '';
        $format = '<textarea %1$srows="4" cols="50" id="%2$s" name="%3$s[%4$s_%5$s]"%6$s%7$s>%8$s</textarea>';
        if ($editorType != '') {
            $format = '<div class="code-editor">' . $format . '</div>';
        }

        $html = '';
        if ($atts['disabled']) {
            $html .= sprintf(
                '<input type="hidden" name="%1$s[%2$s_%3$s]" value="%4$s">',
                $atts['option_name'],
                $atts['section'],
                $atts['name'],
                $value,
            );
        }
     
        
        
        $html .= sprintf(
            $format,
            $editorType,
            $atts['id'],
            $atts['option_name'],
            $atts['section'],
            $atts['name'],
            $placeholder,
            $atts['disabled'] ? ' disabled="disabled"' : '',
            $value
        );
        $html .= self::description($atts);

        echo $html;
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
        $placeholder = $atts['placeholder'] != '' ? ' placeholder="' . $atts['placeholder'] . '"' : '';
        $min = ($atts['min'] != '') ? ' min="' . $atts['min'] . '"' : '';
        $max = ($atts['max'] != '') ? ' max="' . $atts['max'] . '"' : '';
        $step = ($atts['step'] != '') ? ' step="' . $atts['step'] . '"' : '';

        $html = sprintf(
            '<input type="number" class="%1$s-number" id="%2$s" name="%3$s[%4$s_%5$s]" value="%6$s"%7$s%8$s%9$s%10$s>',
            $size,
            $atts['id'],
            $atts['option_name'],
            $atts['section'],
            $atts['name'],
            $value,
            $placeholder,
            $min,
            $max,
            $step
        );
        $html .= self::description($atts);

        echo $html;
    }

    /**
     * Displays a date input field.
     * @param array $atts Settings field attributes
     */
    public static function date(array $atts)
    {
        $value = esc_attr($atts['value']);
        $size = $atts['size'] != '' ? $atts['size'] : 'regular';
        $placeholder = $atts['placeholder'] != '' ? ' placeholder="' . $atts['placeholder'] . '"' : '';
        $min = ($atts['min'] != '') ? ' min="' . $atts['min'] . '"' : '';
        $max = ($atts['max'] != '') ? ' max="' . $atts['max'] . '"' : '';
        $step = ($atts['step'] != '') ? ' step="' . $atts['step'] . '"' : '';

        $html = sprintf(
            '<input type="date" class="%1$s-text" id="%2$s" name="%3$s[%4$s_%5$s]" value="%6$s"%7$s%8$s%9$s%10$s>',
            $size,
            $atts['id'],
            $atts['option_name'],
            $atts['section'],
            $atts['name'],
            $value,
            $placeholder,
            $min,
            $max,
            $step
        );
        $html .= self::description($atts);

        echo $html;
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
                $atts['option_name'],
                $atts['section'],
                $atts['name'],
                checked($value, '1', false),
            );
        }
        $html .= '<label>';
        $html .= sprintf(
            '<input type="checkbox" id="%1$s" name="%2$s[%3$s_%4$s]" value="1" %5$s%6$s>',
            $atts['id'],
            $atts['option_name'],
            $atts['section'],
            $atts['name'],
            checked($value, '1', false),
            $atts['disabled'] ? ' disabled="disabled"' : ''
        );
        $html .= sprintf(
            '%s</label>',
            $atts['description']
        );

        echo $html;
    }

    /**
     * Displays a multi-checkbox input field.
     * @param array $atts Settings field attributes
     */
    public static function multicheckbox(array $atts) {
        $value = (array) $atts['value'];

        $html = '<fieldset>';
        foreach ($atts['options'] as $key => $label) {
            $html .= '<label>';
            $html .= sprintf(
                '<input type="checkbox" id="%1$s-%5$s" name="%2$s[%3$s_%4$s][%5$s]" value="1" %6$s>',
                $atts['id'],
                $atts['option_name'],
                $atts['section'],
                $atts['name'],
                $key,
                checked(true, !empty($value[$key]), false)
            );
            $html .= sprintf('%s</label><br>', $label);
        }

        $html .= self::description($atts);
        $html .= '</fieldset>';

        echo $html;
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
                $atts['option_name'],
                $atts['section'],
                $atts['name'],
                $key,
                checked($value, $key, false)
            );
            $html .= sprintf(
                '%1$s</label>%2$s',
                $label,
                $atts['inline'] != '' ? ' &nbsp;' : '<br>'
            );
        }

        $html .= self::description($atts);
        $html .= '</fieldset>';

        echo $html;
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
            $atts['id'],
            $atts['option_name'],
            $atts['section'],
            $atts['name']
        );

        foreach ($atts['options'] as $key => $label) {
            $html .= sprintf(
                '<option value="%1$s"%2$s>%3$s</option>',
                $key,
                selected($value, $key, false),
                $label
            );
        }

        $html .= sprintf('</select>');
        $html .= self::description($atts);

        echo $html;
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

        echo $html;
    }

    /**
     * Displays a multi-selectbox field.
     * @param array   $atts Settings field attributes
     */
    public static function multiselect(array $atts)  {
        $value = (array) $atts['value'];

        $html  = sprintf(
            '<select id="%1$s" name="%2$s[%3$s_%4$s][]" multiple="multiple">',
            $atts['id'],
            $atts['option_name'],
            $atts['section'],
            $atts['name']
        );

        foreach ($atts['options'] as $key => $label) {
            $html .= sprintf(
                '<option value="%1$s"%2$s>%3$s</option>',
                $key,
                selected(true, in_array($key, $value), false),
                $label
            );
        }

        $html .= sprintf('</select>');
        $html .= self::description($atts);

        echo $html;
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
                $atts['option_name'],
                $atts['section'],
                $atts['name']
            ),
            'textarea_rows' => 10
        ];

        echo '<div class="wpeditor-field-container">';
        wp_editor($value, $atts['section'] . '_' . $atts['name'], $editorSettings);
        echo '</div>';
        echo self::description($atts);
    }
}
