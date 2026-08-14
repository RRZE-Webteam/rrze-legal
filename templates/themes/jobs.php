<?php

/*
* TOS FAU-Jobportal theme template
*/

namespace RRZE\Legal;

defined('ABSPATH') || exit;

get_header();

?>
<div id="content">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <main id="droppoint">
                    <h1 class="page-title"><?php echo esc_html($title); ?></h1>
                    <div id="rrze-tos">
                        <?php echo $content; ?>
                    </div>
                </main>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();
