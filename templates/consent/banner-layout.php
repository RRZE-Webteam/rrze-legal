<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;
?>
<div
    id="RRZELegalBanner"
    class="RRZELegal"
    role="dialog"
    aria-labelledby="BannerTextHeadline"
    aria-describedby="BannerTextDescription"
    aria-modal="true"
>
    <div class="middle-center" style="display: none;">
        <div class="_rrzelegal-box-wrap">
            <div class="_rrzelegal-box _rrzelegal-box-advanced">
                <div class="cookie-box">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="_rrzelegal-flex-center">
                                    <span role="heading" aria-level="3" class="_rrzelegal-h3" id="BannerTextHeadline">
                                        <?php echo esc_html($bannerTextHeadline); ?>
                                    </span>
                                </div>

                                <p id="BannerTextDescription">
                                    <?php echo $bannerTextDescription; ?>
                                </p>

                                <?php
                                if (! empty($categories)) { ?>
                                    <fieldset>
                                        <legend class="sr-only"><?php echo esc_html($bannerTextHeadline); ?></legend>
                                        <ul>
                                            <?php
                                            foreach ($categories as $category) {
                                                if ($category['has_cookies']) { ?>
                                                    <li>
                                                        <label class="_rrzelegal-checkbox">
                                                            <?php echo $category['name']; ?>
                                                            <input
                                                                id="checkbox-<?php echo esc_attr($category['id']); ?>"
                                                                tabindex="0"
                                                                type="checkbox"
                                                                name="cookieGroup[]"
                                                                value="<?php echo esc_attr($category['id']); ?>"
                                                                <?php echo !empty($category['preselected']) ? ' checked' : ''; ?>
                                                                <?php echo $category['id'] === 'essential' ? ' disabled' : ''; ?>
                                                                data-rrzelegal-cookie-checkbox
                                                            >
                                                            <span class="_rrzelegal-checkbox-indicator"></span>
                                                        </label>
                                                    </li>
                                                <?php
                                                }
                                            } ?>
                                        </ul>
                                    </fieldset>

                                    <?php
                                } ?>

                                <p class="_rrzelegal-accept">
                                    <a
                                        href="#"
                                        tabindex="0"
                                        role="button"
                                        class="_rrzelegal-btn _rrzelegal-btn-accept-all _rrzelegal-cursor"
                                        data-cookie-accept-all
                                    >
                                        <?php echo esc_html($bannerPreferenceTextAcceptAllButton); ?>
                                    </a>
                                </p>

                                <p class="_rrzelegal-accept">
                                    <a
                                        href="#"
                                        tabindex="0"
                                        role="button"
                                        id="BannerSaveButton"
                                        class="_rrzelegal-btn _rrzelegal-cursor"
                                        data-cookie-accept
                                    >
                                        <?php echo esc_html($bannerPreferenceTextSaveButton); ?>
                                    </a>
                                </p>

                                <p class="_rrzelegal-refuse-btn">
                                    <a
                                        class="_rrzelegal-btn"
                                        href="#"
                                        tabindex="0"
                                        role="button"
                                        data-cookie-refuse
                                    >
                                        <?php echo esc_html($bannerTextRefuseLink); ?>
                                    </a>
                                </p>

                                <p class="_rrzelegal-manage-btn">
                                    <a href="#" tabindex="0" data-cookie-individual>
                                        <?php echo esc_html($bannerTextManageLink); ?>
                                    </a>
                                </p>

                                <p class="_rrzelegal-legal">
                                    <a href="<?php echo esc_html($imprintUrl); ?>" tabindex="0">
                                        <?php echo esc_html($imprintLinkText); ?>
                                    </a>
                                                                        
                                    <span class="_rrzelegal-separator"></span>
                                    <a href="<?php echo esc_url($privacyPolicyUrl); ?>" tabindex="0">
                                        <?php echo esc_html($privacyPolicyLinkText); ?>
                                    </a>

                                    <span class="_rrzelegal-separator"></span>
                                    <a href="<?php echo esc_url($accessibilityUrl); ?>" tabindex="0">
                                        <?php echo esc_html($accessibilityLinkText); ?>
                                    </a>                                    

                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                if (! empty($cookiePreferenceTemplateFile)) {
                    include $cookiePreferenceTemplateFile;
                }
                ?>
            </div>
        </div>
    </div>
</div>
