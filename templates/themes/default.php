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
<section id="primary" class="content-area">
    <main id="main" class="site-main">
        <div id="rrze-tos">
            <?php echo $content; ?>
        </div>
    </main>
</section>
<?php
get_footer();
