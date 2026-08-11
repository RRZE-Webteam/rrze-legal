<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

use RRZE\Legal\TOS\{Endpoint, NavMenu};
use RRZE\Legal\Consent\Frontend;

/**
 * Class Main
 * @package RRZE\Legal
 */
class Main {
    protected $requiredTOSIssues = [];

    protected $requiredTOSFirstReported = 0;

    protected $requiredTOSNoticeLevel = '';

    protected $overwrittenTOSPostId = 0;

    /**
     * Class constructor.
     */
    public function __construct() {
        // Load network settings
        $isPluginActiveForNetwork = Utils::isPluginActiveForNetwork(plugin()->getBaseName());
        if ($isPluginActiveForNetwork) {
            add_action('init', function () {
                network()->loaded();
                network()->setAdminMenu();
            });
        }

        // Adds a admin menu page
        add_action('admin_menu', [$this, 'adminMenu']);

        // Load consent settings
        add_action('init', function () {
            consent()->loaded();
            consentCategories()->loaded();
            consentCookies()->loaded();
        });

        // Load TOS settings
        add_action('init', function () {
            tos()->loaded();
        });
        new Endpoint();
        NavMenu::addTosMenu();

        // Set admin submenus
        tos()->setAdminMenu();
        consent()->setAdminMenu();
        consentCategories()->setAdminMenu();
        consentCookies()->setAdminMenu();



        // Load banner
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-json/') === false) {
            Frontend::loaded();
        }

        // Update
        Update::loaded();

        // Notices for the administrator
        add_action('admin_init', [$this, 'adminInit']);
        add_action('admin_post_rrze_legal_ack_tos_notice', [$this, 'acknowledgeRequiredTOSNotice']);
    }

    /**
     * Adds a admin menu page.
     * @return void
     */
    public function adminMenu() {
        add_menu_page(
            __('Legal', 'rrze-legal'),
            __('Legal', 'rrze-legal'),
            'manage_options',
            'legal',
            '',
            'dashicons-privacy',
            null
        );
    }

    public function adminInit() {
        if (!current_user_can('read') || wp_doing_ajax()) {
            return;
        }

        $hadRequiredTOSFirstReported = tos()->hasRequiredDataNoticeTimestamp();
        $this->requiredTOSIssues = tos()->getRequiredDataIssues();
        $this->requiredTOSFirstReported = tos()->syncRequiredDataNoticeTimestamp($this->requiredTOSIssues);
        $this->requiredTOSNoticeLevel = $this->getRequiredTOSNoticeLevel();
        if ($hadRequiredTOSFirstReported) {
            $this->logRequiredTOSNoticeEscalation();
        }

        $slugs = Endpoint::getSlugs();
        $published = [];
        if (tos()->overwriteEndpoints()) {
            foreach ($slugs as $endpoint => $slug) {
                if (!tos()->isManualPageAllowed($endpoint)) {
                    continue;
                }

                $page = get_page_by_path($slug);
                if (!is_null($page) && $page->post_status == 'publish') {
                    $published[$endpoint] = $page->ID;
                }
            }
        }
        $pagePrefix = tos()->getPagePrefix();
        $currentPage = array_key_exists('page', $_GET) ? sanitize_key((string) $_GET['page']) : '';
        $current = array_key_exists('current-tab', $_GET) ? sanitize_key((string) $_GET['current-tab']) : '';
        $current = $current && strpos($current, $pagePrefix) === 0 ? substr($current, strlen($pagePrefix)) : '';
        $current = $current == '' ? array_key_first($slugs) : $current;

        if (!empty($this->requiredTOSIssues)) {
            if (current_user_can('manage_options')) {
                $notice_menu_inline_css = " #adminmenu li#toplevel_page_legal {background-color: red;color: white;} ";
                wp_add_inline_style('wp-admin', $notice_menu_inline_css);
            }
            add_action('admin_notices', [$this, 'requiredTOSFieldNotice']);
            if ($this->mustAcknowledgeRequiredTOSNotice()) {
                add_action('admin_footer', [$this, 'requiredTOSAcknowledgementDialog']);
            }
        }
        if ($currentPage == 'legal' && isset($published[$current])) {
            $this->overwrittenTOSPostId = (int) $published[$current];
            add_action('admin_notices', [$this, 'currentTOSEndpointOverwrittenNotice']);
        }
    }


    public function requiredTOSFieldNotice() {
        wp_enqueue_style('rrze-legal-settings');
        $canEdit = current_user_can('manage_options');
        $firstReported = tos()->formatRequiredDataNoticeTimestamp($this->requiredTOSFirstReported);
        $settingsLink = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url(add_query_arg(['page' => 'legal'], admin_url('admin.php'))),
            esc_html__('Legal Mandatory Information', 'rrze-legal')
        );

        echo '<div class="notice notice-warning rrze-legal-dashboardalert">';
        echo '<h2>' . esc_html__('Please note', 'rrze-legal') . '</h2>';

        if ($canEdit) {
            printf(
                '<p class="details">%s</p>',
                wp_kses_post(
                    sprintf(
                        /* translators: %s: Link of the settings page. */
                        __('One or more mandatory fields of the legal settings have not been filled or still contain default values. Please correct these fields as soon as possible here: %s.', 'rrze-legal'),
                        $settingsLink
                    )
                )
            );
        } else {
            echo '<p class="details">' . esc_html__('One or more mandatory fields of the legal settings have not been filled or still contain default values. Please inform a site administrator.', 'rrze-legal') . '</p>';
        }

        echo '<p>' . esc_html__('In order to operate this website, it is mandatory that all necessary legal texts are available. Websites that do not provide this information or do not provide it completely may be deactivated.', 'rrze-legal') . '</p>';
        if ($firstReported !== '') {
            printf(
                '<p>%1$s %2$s</p>',
                esc_html__('This message was first displayed on:', 'rrze-legal'),
                esc_html($firstReported)
            );
        }
        $this->requiredTOSNoticeEscalationText();

        $this->requiredTOSIssuesList($canEdit);
        echo '</div>';
    }

    protected function requiredTOSNoticeEscalationText(): void {
        if ($this->requiredTOSNoticeLevel === 'error') {
            printf(
                '<p class="rrze-legal-required-escalation">%s</p>',
                esc_html(network()->getTosNoticeErrorText())
            );
            return;
        }

        if ($this->requiredTOSNoticeLevel === 'warning') {
            printf(
                '<p class="rrze-legal-required-escalation">%s</p>',
                esc_html(network()->getTosNoticeWarningText())
            );
        }
    }

    protected function getRequiredTOSNoticeLevel(): string {
        if (empty($this->requiredTOSIssues) || $this->requiredTOSFirstReported <= 0) {
            return '';
        }

        $age = current_time('timestamp') - $this->requiredTOSFirstReported;
        if ($age >= network()->getTosNoticeErrorDays() * DAY_IN_SECONDS) {
            return 'error';
        }
        if ($age >= network()->getTosNoticeWarningDays() * DAY_IN_SECONDS) {
            return 'warning';
        }

        return '';
    }

    protected function logRequiredTOSNoticeEscalation(): void {
        if ($this->requiredTOSNoticeLevel === '') {
            return;
        }

        if ($this->requiredTOSNoticeLevel === 'error') {
            if (tos()->hasRequiredDataNoticeLog('error')) {
                return;
            }
            do_action('rrze.log.error', $this->getRequiredTOSNoticeLogMessage());
            tos()->markRequiredDataNoticeLog('error');
            return;
        }

        if ($this->requiredTOSNoticeLevel === 'warning') {
            if (tos()->hasRequiredDataNoticeLog('warning')) {
                return;
            }
            do_action('rrze.log.warning', $this->getRequiredTOSNoticeLogMessage());
            tos()->markRequiredDataNoticeLog('warning');
        }
    }

    protected function getRequiredTOSNoticeLogMessage(): string {
        $firstReported = tos()->formatRequiredDataNoticeTimestamp($this->requiredTOSFirstReported);
        $siteUrl = get_site_url();
        $siteName = get_bloginfo('name');

        return sprintf(
            'RRZE Legal: TOS-Daten wurden seit %1$s nicht vollständig oder korrekt gesetzt. Site: %2$s (ID %3$d, URL %4$s).',
            $firstReported,
            $siteName,
            get_current_blog_id(),
            $siteUrl
        );
    }

    protected function mustAcknowledgeRequiredTOSNotice(): bool {
        if ($this->requiredTOSNoticeLevel !== 'error') {
            return false;
        }
        if (!network()->isTosNoticeAcknowledgementRequired()) {
            return false;
        }
        if (!is_user_logged_in() || $this->requiredTOSFirstReported <= 0) {
            return false;
        }

        return !$this->hasAcknowledgedRequiredTOSNotice();
    }

    protected function hasAcknowledgedRequiredTOSNotice(): bool {
        $acknowledgedTimestamp = (int) get_user_meta(
            get_current_user_id(),
            $this->getRequiredTOSNoticeAcknowledgementMetaKey(),
            true
        );

        return $acknowledgedTimestamp === (int) $this->requiredTOSFirstReported;
    }

    protected function getRequiredTOSNoticeAcknowledgementMetaKey(): string {
        return 'rrze_legal_required_tos_notice_ack_' . get_current_blog_id();
    }

    public function acknowledgeRequiredTOSNotice(): void {
        if (!is_user_logged_in()) {
            wp_die(esc_html__('You are not allowed to confirm this notice.', 'rrze-legal'));
        }

        check_admin_referer('rrze_legal_ack_tos_notice', 'rrze_legal_ack_tos_notice_nonce');
        $timestamp = absint($_POST['rrze_legal_tos_notice_timestamp'] ?? 0);
        $confirmed = !empty($_POST['rrze_legal_tos_notice_confirm']);
        if (!$confirmed || $timestamp <= 0 || $timestamp !== tos()->getRequiredDataNoticeTimestamp()) {
            wp_die(esc_html__('Please confirm that you have read the notice.', 'rrze-legal'));
        }

        update_user_meta(
            get_current_user_id(),
            'rrze_legal_required_tos_notice_ack_' . get_current_blog_id(),
            $timestamp
        );

        $redirectTo = esc_url_raw((string) ($_POST['redirect_to'] ?? admin_url()));
        wp_safe_redirect($redirectTo !== '' ? $redirectTo : admin_url());
        exit;
    }

    public function requiredTOSAcknowledgementDialog(): void {
        $firstReported = tos()->formatRequiredDataNoticeTimestamp($this->requiredTOSFirstReported);
        $redirectTo = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : admin_url();

        echo '<div class="rrze-legal-required-ack-backdrop" role="presentation"></div>';
        echo '<div class="rrze-legal-required-ack-dialog" role="dialog" aria-modal="true" aria-labelledby="rrze-legal-required-ack-title">';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="rrze_legal_ack_tos_notice">';
        echo '<input type="hidden" name="rrze_legal_tos_notice_timestamp" value="' . esc_attr((string) $this->requiredTOSFirstReported) . '">';
        echo '<input type="hidden" name="redirect_to" value="' . esc_url($redirectTo) . '">';
        wp_nonce_field('rrze_legal_ack_tos_notice', 'rrze_legal_ack_tos_notice_nonce');
        echo '<h2 id="rrze-legal-required-ack-title">' . esc_html__('Legal mandatory information is missing', 'rrze-legal') . '</h2>';
        echo '<p>' . esc_html__('The legal mandatory information for this website has been missing or incomplete for too long. Please read and confirm this notice before continuing in the backend.', 'rrze-legal') . '</p>';
        if ($firstReported !== '') {
            printf(
                '<p>%1$s %2$s</p>',
                esc_html__('This message was first displayed on:', 'rrze-legal'),
                esc_html($firstReported)
            );
        }
        $this->requiredTOSNoticeEscalationText();
        echo '<label class="rrze-legal-required-ack-confirm">';
        echo '<input type="checkbox" name="rrze_legal_tos_notice_confirm" value="1" required>';
        echo ' ' . esc_html__('I have read this notice.', 'rrze-legal');
        echo '</label>';
        submit_button(__('Continue', 'rrze-legal'), 'primary', 'submit', false);
        echo '</form>';
        echo '</div>';
    }

    protected function requiredTOSIssuesList(bool $canEdit): void {
        if (empty($this->requiredTOSIssues)) {
            return;
        }

        printf(
            '<details class="rrze-legal-required-issues"><summary>%s</summary><ul>',
            esc_html__('Mängelliste anzeigen', 'rrze-legal')
        );

        foreach ($this->requiredTOSIssues as $issue) {
            $field = sprintf(
                '%1$s: %2$s',
                $issue['section_label'] ?? '',
                $issue['field_label'] ?? ''
            );
            if ($canEdit && !empty($issue['edit_url'])) {
                $field = sprintf(
                    '<a href="%1$s">%2$s</a>',
                    esc_url($issue['edit_url']),
                    esc_html($field)
                );
            } else {
                $field = esc_html($field);
            }

            printf(
                '<li>%1$s <span class="description">%2$s</span></li>',
                wp_kses_post($field),
                esc_html($issue['reason'] ?? '')
            );
        }

        echo '</ul></details>';
    }


    protected function currentTOSEndpointOverwritten(int $postId = 0) {
        $link = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url(get_permalink($postId)),
            esc_html(get_the_title($postId))
        );
        $message = sprintf(
            /* translators: %s: Permalink of the page that overrides the endpoint. */
            __('The output of this settings page is overwritten by the content of the following page: %s.', 'rrze-legal'),
            $link
        );


        echo '<div class="notice notice-warning is-dismissible"><p>' . wp_kses_post($message) . '</p></div>';
    }

    public function currentTOSEndpointOverwrittenNotice(): void {
        $this->currentTOSEndpointOverwritten($this->overwrittenTOSPostId);
    }
}
