<?php

/*
* TOS default theme template
*/

namespace RRZE\Legal;

defined('ABSPATH') || exit;

get_header();

?>
    <div id="default-theme"  lang="<?php echo esc_attr($langCode); ?>">
        <h1><?php echo esc_html( $title ); ?></h1>
         <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Endpoint content is generated from plugin templates and contains required consent data attributes. ?>
         <?php echo $content; ?>
    </div>
<?php
get_footer();
