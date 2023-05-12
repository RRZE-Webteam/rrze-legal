<?php

/*
* TOS FAU-Einrichtungen theme template
*/

namespace RRZE\Legal;

defined('ABSPATH') || exit;

global $post;
$post->post_title = $title;
get_header();
if (is_plugin_active('rrze-elements/rrze-elements.php')) {
    wp_enqueue_style('rrze-elements');
    wp_enqueue_script('rrze-accordions');
}

$currentTheme = wp_get_theme();		
$vers = $currentTheme->get( 'Version' );
  if (version_compare($vers, "2.3", '<')) {  
    // alte Anweisung für den Hero hier....
      get_template_part('template-parts/hero', 'small');
  }
?>

<div id="content">
    <div class="content-container">
        <div class="content-row">
            <main<?php echo fau_get_page_langcode($post->ID);?>>
                <h1 id="maintop" class="screen-reader-text"><?php echo $title; ?></h1>

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
