<?php

/*
* TOS FAU-Einrichtungen theme template
*/

namespace RRZE\Legal;

defined('ABSPATH') || exit;


get_header();


?>

<main lang="<?php echo esc_attr($langCode); ?>">
  <header class="page-header">
    <h1 class="wp-block-post-title">
      <?php echo esc_html( $title ); ?>
    </h1>
  </header>

  <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Endpoint content is generated from plugin templates and contains required consent data attributes. ?>
  <?php echo $content; ?>
</main>

<?php
get_footer();
