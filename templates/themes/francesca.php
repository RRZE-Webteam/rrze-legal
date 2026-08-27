<?php

/*
 * Francesca theme template for legal endpoints.
 */

namespace RRZE\Legal;

defined('ABSPATH') || exit;

get_header();

?>
<article class="page type-page entry entry-type-page" lang="<?php echo esc_attr($langCode); ?>">
    <header id="page-header" class="entry-header page-header">
        <div class="page-header-content">
            <div class="entry-header-text page-header-text">
                <h1 class="entry-title page-title"><?php echo esc_html($title); ?></h1>
            </div>
        </div>
    </header>

    <div class="entry-content entry-content-singular">
        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Endpoint content is generated from plugin templates and contains required consent data attributes. ?>
        <?php echo $content; ?>
    </div>

    <div class="entry-skip-links"><a class="skip-link screen-reader-text" href="#site-navigation"><?php esc_html_e('Skip back to main navigation', 'rrze-legal'); ?></a></div>
</article>
<?php
get_footer();
