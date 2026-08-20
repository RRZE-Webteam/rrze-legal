<?php

/*
* TOS FAU-Einrichtungen theme template
*/

namespace RRZE\Legal;

defined('ABSPATH') || exit;


get_header();


?>
<div id="content">
    <div class="content-container">
        <div class="content-row">
            <main lang="<?php echo esc_attr($langCode); ?>">
                <h1 id="maintop" class="screen-reader-text"><?php echo esc_html($title); ?></h1>
                <div class="inline-box">
                    <div class="content-inline">
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Endpoint content is generated from plugin templates and contains required consent data attributes. ?>
                        <?php echo $content; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>
<?php
get_footer();
