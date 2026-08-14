<?php

/*
* TOS rrze-2015 theme template
*/

namespace RRZE\Legal;

defined('ABSPATH') || exit;

get_header();
 ?>
<div id="sidebar" class="sidebar">
    <?php get_sidebar('page'); ?>
</div><!-- .sidebar -->
<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="rrze-tos">
            <header class="entry-header">
                <?php printf('<h1 class="entry-title">%s</h1>', esc_html($title)); ?>
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
