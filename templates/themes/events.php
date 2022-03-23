<?php

/*
* TOS FAU-Events theme template
*/

namespace RRZE\Legal;

defined('ABSPATH') || exit;

get_header();
if (is_plugin_active('rrze-elements/rrze-elements.php')) {
    wp_enqueue_style('rrze-elements');
    wp_enqueue_script('rrze-accordions');
}
?>
<div class="content-wrap">
    <div id="blog-wrap" class="blog-wrap cf">
        <div id="primary" class="site-content cf rrze-calendar" role="main">
            <article class="page hentry">
                <header class="entry-header">
                    <h1 class="entry-title"><?php echo $title; ?></h1>
                </header><!-- end .entry-header -->
                <div id="rrze-tos">
                    <?php echo $content; ?>
                </div>
            </article>
        </div>
    </div>
</div>
<?php
get_footer();
