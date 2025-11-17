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

?>

<main lang="<?php echo esc_attr($langCode); ?>">
  <header class="page-header">
    <h1 class="wp-block-post-title">
      <?php echo esc_html( $title ); ?>
    </h1>
  </header>

  <?php echo $content; ?>
</main>

<?php
get_footer();


