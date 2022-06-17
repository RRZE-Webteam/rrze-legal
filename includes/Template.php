<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

class Template
{
    const TOS_PATH = 'templates/tos';

    const THEMES_PATH = 'templates/themes';

    protected static $themesFilenames = [
        'fau.php' => [
            'FAU-Einrichtungen',
            'FAU-Einrichtungen-BETA',
            'FAU-Medfak',
            'FAU-RWFak',
            'FAU-Philfak',
            'FAU-Techfak',
            'FAU-Natfak',
            'FAU-Jobportal'
        ],
        'rrze.php' => [
            'rrze-2015',
            'rrze-2019',
        ],
        'events.php' => [
            'FAU-Events',
        ],
        'jobs.php' => [
            'FAU-Jobportal-Theme',
        ]
    ];

    /**
     * Get the template content.
     * @param string $template
     * @param array $data
     */
    public static function getContent($template = '', $data = [])
    {
        return self::parseContent($template, $data);
    }

    /**
     * Parses the content of the template with the data provided.
     * @param  string $template
     * @param  array  $data
     * @return string
     */
    protected static function parseContent($template, $data)
    {
        $templateFile = self::getTemplateLocale($template);
        if ($templateFile) {
            $parser = new Parser();
            return $parser->parse($templateFile, $data);
        }
        return '';
    }

    /**
     * Load the locale template file
     * @param  string $templateFile
     * @return string
     */
    protected static function getTemplateLocale($templateFile)
    {
        return is_readable($templateFile) ? $templateFile : '';
    }

    /**
     * Get the Theme template filename.
     * @var string
     */
    public static function getThemeFilename(): string
    {
        $currentTheme = wp_get_theme();
        foreach (self::$themesFilenames as $filename => $theme) {
            if (in_array(strtolower($currentTheme->stylesheet), array_map('strtolower', $theme))) {
                return $filename;
            }
        }
        return 'default.php';
    }
}
