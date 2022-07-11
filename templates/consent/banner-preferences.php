<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;
?>
<div
    class="cookie-preference"
    aria-hidden="true"
    role="dialog"
    aria-describedby="CookiePrefDescription"
    aria-modal="true"
>
    <div class="container not-visible">
        <div class="row no-gutters">
            <div class="col-12">
                <div class="row no-gutters align-items-top">
                    <div class="col-12">
                        <div class="_rrzelegal-flex-center">
                            <span role="heading" aria-level="3" class="_rrzelegal-h3">
                                <?php echo $bannerPreferenceTextHeadline; ?>
                            </span>
                        </div>

                        <p id="CookiePrefDescription">
                            <?php echo do_shortcode($bannerPreferenceTextDescription); ?>
                        </p>

                        <div class="row no-gutters align-items-center">
                            <div class="col-12 col-sm-10">
                                <p class="_rrzelegal-accept">
                                    <a
                                        href="#"
                                        class="_rrzelegal-btn _rrzelegal-btn-accept-all _rrzelegal-cursor"
                                        tabindex="0"
                                        role="button"
                                        data-cookie-accept-all
                                    >
                                        <?php echo $bannerPreferenceTextAcceptAllButton; ?>
                                    </a>

                                    <a
                                        href="#"
                                        id="CookiePrefSave"
                                        tabindex="0"
                                        role="button"
                                        class="_rrzelegal-btn _rrzelegal-cursor"
                                        data-cookie-accept
                                    >
                                        <?php echo $bannerPreferenceTextSaveButton; ?>
                                    </a>

                                    <a
                                        href="#"
                                        class="_rrzelegal-btn _rrzelegal-refuse-btn _rrzelegal-cursor"
                                        tabindex="0"
                                        role="button"
                                        data-cookie-refuse
                                    >
                                        <?php echo $bannerPreferenceTextRefuseLink; ?>
                                    </a>
                                </p>
                            </div>

                            <div class="col-12 col-sm-2">
                                <p class="_rrzelegal-refuse">
                                    <a
                                        href="#"
                                        class="_rrzelegal-cursor"
                                        tabindex="0"
                                        data-cookie-back
                                    >
                                        <?php echo $bannerPreferenceTextBackLink; ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div data-cookie-accordion>
                    <?php
                    if (! empty($categories)) { ?>
                        <fieldset>
                            <legend class="sr-only"><?php echo esc_attr($bannerPreferenceTextHeadline); ?></legend>

                            <?php
                            foreach ($categories as $category) { ?>
                                <?php
                                if ($category['has_cookies']) { ?>
                                    <div class="bcac-item">
                                        <div class="d-flex flex-row">
                                            <label class="w-75">
                                                <span role="heading" aria-level="4" class="_rrzelegal-h4">
                                                    <?php echo esc_html($category['name']); ?> (<?php echo count($category['cookies']); ?>)
                                                </span>
                                            </label>

                                            <div class="w-25 text-right">
                                                <?php
                                                if ($category['id'] !== 'essential') { ?>
                                                    <label class="_rrzelegal-btn-switch">
                                                        <span class="sr-only">
                                                            <?php echo esc_html($category['name']); ?>
                                                        </span>
                                                        <input
                                                            tabindex="0"
                                                            id="rrzelegal-cookie-group-<?php echo $category['id']; ?>"
                                                            type="checkbox"
                                                            name="cookieGroup[]"
                                                            value="<?php echo $category['id']; ?>"
                                                            <?php echo !empty($category['preselected']) ? ' checked' : ''; ?>
                                                            data-rrzelegal-cookie-switch
                                                        />
                                                        <span class="_rrzelegal-slider"></span>
                                                        <span
                                                            class="_rrzelegal-btn-switch-status"
                                                            data-active="<?php echo $bannerPreferenceTextSwitchStatusActive; ?>"
                                                            data-inactive="<?php echo $bannerPreferenceTextSwitchStatusInactive; ?>">
                                                        </span>
                                                    </label>
                                                    <?php
                                                } ?>
                                            </div>
                                        </div>

                                        <div class="d-block">
                                            <p><?php
                                                echo $category['description']; ?></p>

                                            <p class="text-center">
                                                <a
                                                    href="#"
                                                    class="_rrzelegal-cursor d-block"
                                                    tabindex="0"
                                                    data-cookie-accordion-target="<?php echo $category['id']; ?>"
                                                >
                                                    <span data-cookie-accordion-status="show">
                                                        <?php echo $bannerPreferenceTextShowCookieLink; ?>
                                                    </span>

                                                    <span data-cookie-accordion-status="hide" class="rrzelegal-hide">
                                                        <?php echo $bannerPreferenceTextHideCookieLink; ?>
                                                    </span>
                                                </a>
                                            </p>
                                        </div>

                                        <div
                                            class="rrzelegal-hide"
                                            data-cookie-accordion-parent="<?php echo $category['id']; ?>"
                                        >
                                            <?php
                                            foreach ($category['cookies'] as $cookieData) { ?>
                                                <table>
                                                    <?php
                                                    if ($category['id'] !== 'essential') { ?>
                                                        <tr>
                                                            <th><?php echo $bannerCookieDetailsTableAccept; ?></th>
                                                            <td>
                                                                <label class="_rrzelegal-btn-switch _rrzelegal-btn-switch--textRight">
                                                                    <span class="sr-only"><?php
                                                                        echo esc_html($cookieData['name']); ?></span>
                                                                    <input
                                                                        id="rrzelegal-cookie-<?php echo $cookieData['id']; ?>"
                                                                        tabindex="0"
                                                                        type="checkbox" data-cookie-group="<?php echo $category['id']; ?>"
                                                                        name="cookies[<?php echo $category['id']; ?>][]"
                                                                        value="<?php echo $cookieData['id']; ?>"
                                                                        <?php echo !empty($category['preselected']) ? ' checked' : ''; ?>
                                                                        data-rrzelegal-cookie-switch
                                                                    />

                                                                    <span class="_rrzelegal-slider"></span>

                                                                    <span
                                                                        class="_rrzelegal-btn-switch-status"
                                                                        data-active="<?php echo $bannerPreferenceTextSwitchStatusActive; ?>"
                                                                        data-inactive="<?php echo $bannerPreferenceTextSwitchStatusInactive; ?>"
                                                                        aria-hidden="true">
                                                                    </span>
                                                                </label>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    } ?>

                                                    <tr>
                                                        <th><?php echo $bannerCookieDetailsTableName; ?></th>
                                                        <td>
                                                            <label>
                                                                <?php echo esc_html($cookieData['name']); ?>
                                                            </label>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th><?php echo $bannerCookieDetailsTableProvider; ?></th>
                                                        <td><?php echo $cookieData['provider']; ?></td>
                                                    </tr>

                                                    <?php
                                                    if (! empty($cookieData['purpose'])) { ?>
                                                        <tr>
                                                            <th><?php echo $bannerCookieDetailsTablePurpose; ?></th>
                                                            <td><?php echo $cookieData['purpose']; ?></td>
                                                        </tr>
                                                        <?php
                                                    } ?>

                                                    <?php
                                                    if (! empty($cookieData['privacy_policy_url'])) { ?>
                                                        <tr>
                                                            <th><?php echo $bannerCookieDetailsTablePrivacyPolicy; ?></th>
                                                            <td class="_rrzelegal-pp-url">
                                                                <a
                                                                    href="<?php echo esc_url($cookieData['privacy_policy_url']); ?>"
                                                                    target="_blank"
                                                                    rel="nofollow noopener noreferrer"
                                                                >
                                                                    <?php echo esc_url($cookieData['privacy_policy_url']); ?>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    } ?>

                                                    <?php
                                                    if (! empty($cookieData['hosts'])) { ?>
                                                        <tr>
                                                            <th><?php
                                                                echo $bannerCookieDetailsTableHosts; ?></th>
                                                            <td><?php
                                                                echo implode(', ', explode(PHP_EOL, $cookieData['hosts'])); ?></td>
                                                        </tr>
                                                        <?php
                                                    } ?>

                                                    <?php
                                                    if (! empty($cookieData['cookie_name'])) { ?>
                                                        <tr>
                                                            <th><?php
                                                                echo $bannerCookieDetailsTableCookieName; ?></th>
                                                            <td><?php
                                                                echo esc_html($cookieData['cookie_name']); ?></td>
                                                        </tr>
                                                        <?php
                                                    } ?>

                                                    <?php
                                                    if (! empty($cookieData['cookie_expiry'])) { ?>
                                                        <tr>
                                                            <th><?php
                                                                echo $bannerCookieDetailsTableCookieExpiry; ?></th>
                                                            <td><?php
                                                                echo esc_html($cookieData['cookie_expiry']); ?></td>
                                                        </tr>
                                                        <?php
                                                    } ?>
                                                </table>
                                                <?php
                                            } ?>
                                        </div>
                                    </div>
                                    <?php
                                } ?>
                                <?php
                            } ?>
                            </fieldset>
                        <?php
                    } ?>
                </div>

                <div class="d-flex justify-content-between">
                    <p class="_rrzelegal-legal flex-fill">
                        <a href="<?php echo $imprintUrl; ?>">
                            <?php echo $imprintLinkText; ?>
                        </a>           
                        <span class="_rrzelegal-separator"></span>             
                        <a href="<?php echo $privacyPolicyUrl; ?>">
                            <?php echo $privacyPolicyLinkText; ?>
                        </a>
                        <span class="_rrzelegal-separator"></span>
                        <a href="<?php echo $accessibilityUrl; ?>">
                            <?php echo $accessibilityLinkText; ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
