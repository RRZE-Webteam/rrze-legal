<?php

/*
* TOS FAU-Einrichtungen theme template
*/

namespace RRZE\Legal;

defined('ABSPATH') || exit;


get_header();
if (is_plugin_active('rrze-elements/rrze-elements.php')) {
    wp_enqueue_style('rrze-elements');
    wp_enqueue_script('rrze-accordions');
}
$currentTheme = wp_get_theme();
$vers = $currentTheme->get('Version');

?>
<div id="content">
    <div class="content-container">
        <div class="content-row">
            <main lang="<?php echo esc_attr($langCode); ?>">
                <h1 id="maintop" class="screen-reader-text"><?php echo esc_html($title); ?></h1>
                <div class="inline-box">
                    <div class="content-inline">
                        <?php echo $content; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>
<?php
get_footer();
