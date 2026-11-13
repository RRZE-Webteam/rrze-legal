<?php

namespace RRZE\Legal\Blocks;

defined('ABSPATH') || exit;

use RRZE\Legal\TOS\Endpoint;
use function RRZE\Legal\plugin;

class Legal
{
    public function __construct()
    {
        add_action('init', [$this, 'register']);
        add_filter('block_categories_all', [$this, 'registerCategory'], 10, 2);
    }

    public function registerCategory(array $categories, $editorContext): array
    {
        foreach ($categories as $category) {
            if (($category['slug'] ?? '') === 'rrze') {
                return $categories;
            }
        }

        array_unshift(
            $categories,
            [
                'slug' => 'rrze',
                'title' => __('RRZE', 'rrze-legal'),
                'icon' => null,
            ]
        );

        return $categories;
    }

    public function register(): void
    {
        $blockPath = plugin()->getPath('build/blocks/legal');
        if (!is_readable($blockPath . 'block.json')) {
            return;
        }

        wp_register_style(
            'rrze-legal-frontend',
            plugin()->getUrl('build') . 'rrze-legal.css',
            [],
            plugin()->getVersion()
        );

        $blockType = register_block_type(
            $blockPath,
            [
                'render_callback' => [$this, 'render'],
                'style' => 'rrze-legal-frontend',
            ]
        );

        if ($blockType instanceof \WP_Block_Type && $blockType->editor_script) {
            wp_set_script_translations(
                $blockType->editor_script,
                'rrze-legal',
                plugin()->getPath('languages')
            );
        }

        if (wp_is_block_theme()) {
            register_block_template(
                'rrze-legal//legal',
                [
                    'title' => __('Legal', 'rrze-legal'),
                    'description' => __('Template for RRZE Legal endpoint pages.', 'rrze-legal'),
                    'content' => $this->getTemplateContent(),
                    'post_types' => ['page'],
                ]
            );
        }
    }

    public function render(array $attributes = []): string
    {
        $endpoint = isset($attributes['endpoint']) && is_string($attributes['endpoint'])
            ? $attributes['endpoint']
            : '';

        if ($endpoint === 'current') {
            $endpoint = Endpoint::getCurrentEndpoint();
        }

        if (!Endpoint::isEndpoint($endpoint)) {
            return '';
        }

        $title = Endpoint::endpointTitle($endpoint);
        $content = Endpoint::getEndpointContent($endpoint);
        if ($content === '') {
            return '';
        }

        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => 'rrze-legal',
        ]);

        return sprintf(
            '<div %1$s><h1>%2$s</h1>%3$s</div>',
            $wrapperAttributes,
            esc_html($title),
            $content
        );
    }

    public static function getTemplateContent(): string
    {
        return '<!-- wp:template-part {"slug":"header","area":"header","tagName":"header"} /-->' . "\n\n"
            . '<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} -->' . "\n"
            . '<main class="wp-block-group">' . "\n"
            . '<!-- wp:rrze-legal/legal {"endpoint":"current"} /-->' . "\n"
            . '</main>' . "\n"
            . '<!-- /wp:group -->' . "\n\n"
            . '<!-- wp:template-part {"slug":"footer","area":"footer","tagName":"footer"} /-->';
    }
}
