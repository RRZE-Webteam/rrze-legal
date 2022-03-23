<?php

/*
* TOS rrze-2015 theme template
*/

namespace RRZE\Legal;

defined('ABSPATH') || exit;

get_header();
if (is_plugin_active('rrze-elements/rrze-elements.php')) {
    wp_enqueue_style('rrze-elements');
    wp_enqueue_script('rrze-accordions');
} ?>
<div id="sidebar" class="sidebar">
    <?php get_sidebar('page'); ?>
</div><!-- .sidebar -->
<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="rrze-tos">
            <header class="entry-header">
                <?php printf('<h1 class="entry-title">%s</h1>', $title); ?>
            </header><!-- .entry-header -->
            <div class="entry-content">
                <div class="rrze-tos">
                    <?php echo $content; ?>
                </div>
            </div><!-- .entry-content -->
        </article><!-- #rrze-tos -->
    </main><!-- #main -->
</div><!-- #primary -->
<?php
get_footer();
