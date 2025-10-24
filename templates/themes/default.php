<?php

/*
* TOS default theme template
*/

namespace RRZE\Legal;

defined('ABSPATH') || exit;

get_header();
if (is_plugin_active('rrze-elements/rrze-elements.php')) {
    wp_enqueue_style('rrze-elements');
    wp_enqueue_script('rrze-accordions');
}
?>
    <main id="rrze-tos"  lang="<?php echo esc_attr($langCode); ?>">
        <h1><?php echo esc_html( $title ); ?></h1>
         <?php echo $content; ?>
    </main>
<?php
get_footer();
