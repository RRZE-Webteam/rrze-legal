<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

use RRZE\Legal\TOS\{Endpoint, NavMenu};
use RRZE\Legal\Blocks\Legal;
use RRZE\Legal\Consent\Frontend;

/**
 * Class Main
 * @package RRZE\Legal
 */
class Main {
    protected $requiredTOSIssues = [];

    protected $requiredTOSFirstReported = 0;

    protected $requiredTOSNoticeLevel = '';

    /** @var bool Whether this dashboard request requires the acknowledgement view. */
    private bool $requiredTOSDialogPending = false;

    protected $overwrittenTOSPostId = 0;

    protected $accessibilityReviewIsOverdue = false;

    protected $accessibilityLastReviewDate = '';

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
        new Legal();
        NavMenu::addTosMenu();

        // Set admin submenus
        tos()->setAdminMenu();
        consent()->setAdminMenu();
        consentCookies()->setAdminMenu();



        // Load banner after consent settings are available.
        add_action('init', [$this, 'loadFrontendConsent']);

        // Update
        Update::loaded();

        // Notices for the administrator
        add_action('admin_init', [$this, 'adminInit']);
        add_action('load-index.php', [$this, 'registerRequiredTOSDashboardNotice']);
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

    public function loadFrontendConsent() {
        if (!isset($_SERVER['REQUEST_URI']) || strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) {
            return;
        }

        Frontend::loaded();
    }

    /**
     * Evaluate the site's legal notices and register them on the relevant admin screen.
     *
     * @return void
     */
    public function adminInit() {
        if (!current_user_can('read') || wp_doing_ajax() || is_network_admin()) {
            return;
        }

        $hadRequiredTOSFirstReported = tos()->hasRequiredDataNoticeTimestamp();
        $this->requiredTOSIssues = tos()->getRequiredDataIssues();
        $this->requiredTOSFirstReported = tos()->syncRequiredDataNoticeTimestamp($this->requiredTOSIssues);
        $this->requiredTOSNoticeLevel = $this->getRequiredTOSNoticeLevel();
        $this->accessibilityReviewIsOverdue = $this->isAccessibilityReviewOverdue();
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

        if ($this->accessibilityReviewIsOverdue && $this->isDashboardPage() && current_user_can('manage_options')) {
            add_action('admin_notices', [$this, 'accessibilityReviewNotice']);
        }
        if ($currentPage == 'legal' && isset($published[$current])) {
            $this->overwrittenTOSPostId = (int) $published[$current];
            add_action('admin_notices', [$this, 'currentTOSEndpointOverwrittenNotice']);
        }
    }

    protected function isDashboardPage(): bool {
        global $pagenow;
        return $pagenow === 'index.php';
    }

    /**
     * Register the inline notice or the required dialog on the site's Dashboard home screen.
     *
     * @return void
     */
    public function registerRequiredTOSDashboardNotice(): void {
        if (empty($this->requiredTOSIssues) || get_current_screen()?->id !== 'dashboard') {
            return;
        }

        $this->requiredTOSDialogPending = $this->mustAcknowledgeRequiredTOSNotice();
        if ($this->requiredTOSDialogPending) {
            add_filter('admin_body_class', [$this, 'addRequiredTOSDialogBodyClass']);
            add_action('admin_footer', [$this, 'requiredTOSFieldNotice']);
        } else {
            add_action('admin_notices', [$this, 'requiredTOSFieldNotice']);
        }
        add_action('admin_enqueue_scripts', [$this, 'enqueueRequiredTOSNoticeAssets']);
    }

    /**
     * Identify the dashboard that provides a standalone confirmation form until JS opens the modal.
     *
     * @param string $classes Existing admin body classes.
     * @return string Body classes for this request.
     */
    public function addRequiredTOSDialogBodyClass(string $classes): string {
        return $classes . ' rrze-legal-ack-pending ';
    }

    protected function isAccessibilityReviewOverdue(): bool {
        $lastReviewDate = (string) tos()->getOption('accessibility', 'compliance_status_last_review_date');
        if ($lastReviewDate === '') {
            return false;
        }

        $timezone = wp_timezone();
        $lastReview = \DateTimeImmutable::createFromFormat('!Y-m-d', $lastReviewDate, $timezone);
        $errors = \DateTimeImmutable::getLastErrors();
        if (
            !$lastReview instanceof \DateTimeImmutable
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $lastReview->format('Y-m-d') !== $lastReviewDate
        ) {
            return false;
        }

        $reviewMonths = max(1, (int) config()->get('accessibility_review_months', 18));
        $reviewDeadline = current_datetime()
            ->setTime(0, 0)
            ->modify(sprintf('-%d months', $reviewMonths));
        if ($lastReview >= $reviewDeadline) {
            return false;
        }

        $this->accessibilityLastReviewDate = wp_date(get_option('date_format'), $lastReview->getTimestamp(), $timezone);

        return true;
    }

    public function accessibilityReviewNotice(): void {
        $settingsLink = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url(
                add_query_arg(
                    [
                        'page' => 'legal',
                        'current-tab' => tos()->getPagePrefix() . 'accessibility',
                    ],
                    admin_url('admin.php')
                )
            ),
            esc_html__('Accessibility settings', 'rrze-legal')
        );

        printf(
            '<div class="notice notice-warning"><p>%s</p></div>',
            wp_kses_post(
                sprintf(
                    /* translators: 1: Date of the last review, 2: Link to the accessibility settings. */
                    __('The accessibility statement was last reviewed on %1$s, more than 18 months ago. Please check whether the information provided is still correct. If it is still correct or you make changes, update the date of the last review in the %2$s.', 'rrze-legal'),
                    esc_html($this->accessibilityLastReviewDate),
                    $settingsLink
                )
            )
        );
    }


    /**
     * Display the outstanding legal fields or the acknowledgement required for this visit.
     *
     * @return void
     */
    public function requiredTOSFieldNotice() {
        if (empty($this->requiredTOSIssues)) {
            return;
        }

        $canEdit = current_user_can('manage_options');
        $issues = $this->requiredTOSIssues;
        $summary = $this->getRequiredTOSNoticeSummary($issues);
        $firstReported = tos()->formatRequiredDataNoticeTimestamp($this->requiredTOSFirstReported);
        $isError = $this->requiredTOSNoticeLevel === 'error';
        $escalationText = $this->getRequiredTOSNoticeEscalationText();

        if ($this->requiredTOSDialogPending) {
            $reportedTimestamp = $this->requiredTOSFirstReported;
            $reminderInterval = $this->formatRequiredTOSReminderInterval();
            require plugin()->getPath() . 'templates/admin/required-fields-dialog.php';
        } else {
            require plugin()->getPath() . 'templates/admin/required-fields-notice.php';
        }
    }

    /**
     * Summarize the affected sections and the corrections needed in the dashboard notice.
     *
     * @param array[] $issues Non-empty list of issues from the required-field check.
     * @return string Localized summary in plain text.
     */
    protected function getRequiredTOSNoticeSummary(array $issues): string {
        $sections = array_column($issues, 'section_label', 'section');
        $sectionId = count($sections) === 1 ? array_key_first($sections) : '';
        $location = match ($sectionId) {
            'imprint' => __('In the legal notice', 'rrze-legal'),
            'privacy' => __('In the privacy policy', 'rrze-legal'),
            'accessibility' => __('In the accessibility statement', 'rrze-legal'),
            default => sprintf(
                /* translators: %s: List of affected settings sections. */
                _n('In the section %s', 'In the sections %s', count($sections), 'rrze-legal'),
                wp_sprintf_l('%l', array_values($sections))
            ),
        };

        $count = count($issues);
        $reasons = array_count_values(array_column($issues, 'reason_code'));
        if (count($reasons) > 1) {
            /* translators: 1: Location of the affected fields, 2: Total number of fields. */
            $format = __('%1$s, %2$s entries are missing or contain invalid email addresses.', 'rrze-legal');
        } elseif (isset($reasons['invalid_email'])) {
            /* translators: 1: Location of the affected fields, 2: Number of invalid email addresses. */
            $format = _n('%1$s, %2$s email address is invalid.', '%1$s, %2$s email addresses are invalid.', $count, 'rrze-legal');
        } else {
            /* translators: 1: Location of the affected fields, 2: Number of missing entries. */
            $format = _n('%1$s, %2$s required field is empty.', '%1$s, %2$s required fields are empty.', $count, 'rrze-legal');
        }

        return sprintf($format, $location, number_format_i18n($count));
    }

    /**
     * Load the dashboard styles and upgrade the standalone form to a modal when required.
     *
     * @return void
     */
    public function enqueueRequiredTOSNoticeAssets(): void {
        wp_enqueue_style('rrze-legal-settings');
        if (!$this->requiredTOSDialogPending) {
            return;
        }

        $script = 'build/rrze-legal-required-fields-dialog.js';
        $path = plugin()->getPath() . $script;
        if (is_file($path)) {
            wp_enqueue_script(
                'rrze-legal-required-fields-dialog',
                plugins_url($script, plugin()->getBasename()),
                [],
                (string) filemtime($path),
                true
            );
        }
    }

    /**
     * Get the network's message for the current escalation level.
     *
     * @return string Plain text, or an empty string before the warning period.
     */
    protected function getRequiredTOSNoticeEscalationText(): string {
        return match ($this->requiredTOSNoticeLevel) {
            'error' => network()->getTosNoticeErrorText(),
            'warning' => network()->getTosNoticeWarningText(),
            default => '',
        };
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
            'RRZE Legal: TOS data has not been fully or correctly set since %1$s. Site: %2$s (ID %3$d, URL %4$s).',
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
        $cookieName = $this->getRequiredTOSNoticeAcknowledgementCookieName();
        $acknowledgedTimestamp = isset($_COOKIE[$cookieName]) ? absint($_COOKIE[$cookieName]) : 0;

        return $acknowledgedTimestamp === (int) $this->requiredTOSFirstReported;
    }

    protected function getRequiredTOSNoticeAcknowledgementCookieName(): string {
        return 'rrze_legal_required_tos_notice_ack_' . get_current_blog_id();
    }

    /**
     * Acknowledge the current incident after an explicit confirmation and return to the dashboard.
     *
     * A form from a resolved or superseded incident may return to the dashboard but cannot
     * postpone a newer reminder.
     *
     * @return void
     */
    public function acknowledgeRequiredTOSNotice(): void {
        if (!is_user_logged_in()) {
            wp_die(esc_html__('You are not allowed to confirm this notice.', 'rrze-legal'));
        }

        check_admin_referer('rrze_legal_ack_tos_notice', 'rrze_legal_ack_tos_notice_nonce');
        $timestamp = absint($_POST['rrze_legal_tos_notice_timestamp'] ?? 0);
        $confirmed = ($_POST['rrze_legal_tos_notice_confirm'] ?? '') === '1';
        if (!$confirmed || $timestamp <= 0) {
            wp_die(esc_html__('Please confirm that you have read the notice.', 'rrze-legal'));
        }

        if ($timestamp === tos()->getRequiredDataNoticeTimestamp() && $this->mustAcknowledgeRequiredTOSNotice()) {
            $this->setRequiredTOSNoticeAcknowledgementCookie($timestamp);
        }

        wp_safe_redirect(admin_url(), 303);
        exit;
    }

    /**
     * Pause the dashboard reminder for this incident in the current browser.
     *
     * @param int $timestamp First report timestamp of the acknowledged incident.
     * @return void
     */
    protected function setRequiredTOSNoticeAcknowledgementCookie(int $timestamp): void {
        $path = wp_parse_url(admin_url(), PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }

        setcookie(
            $this->getRequiredTOSNoticeAcknowledgementCookieName(),
            (string) $timestamp,
            [
                'expires' => time() + $this->getRequiredTOSAcknowledgementLifetime(),
                'path' => $path,
                'domain' => defined('COOKIE_DOMAIN') && COOKIE_DOMAIN ? (string) COOKIE_DOMAIN : '',
                'secure' => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    /**
     * Get the lifetime shared by the reminder text and the acknowledgement cookie.
     *
     * @return int Number of seconds before the acknowledgement expires.
     */
    private function getRequiredTOSAcknowledgementLifetime(): int {
        return max(0, (int) config()->get('tos_notice_acknowledgement_lifetime', DAY_IN_SECONDS));
    }

    /**
     * Express the reminder interval in the largest unit that represents its exact duration.
     *
     * @return string Localized duration for the acknowledgement form.
     */
    private function formatRequiredTOSReminderInterval(): string {
        $seconds = $this->getRequiredTOSAcknowledgementLifetime();
        if ($seconds > 0 && $seconds % HOUR_IN_SECONDS === 0) {
            $count = intdiv($seconds, HOUR_IN_SECONDS);
            /* translators: %s: Number of hours until the reminder returns. */
            $format = _n('%s hour', '%s hours', $count, 'rrze-legal');
        } elseif ($seconds > 0 && $seconds % MINUTE_IN_SECONDS === 0) {
            $count = intdiv($seconds, MINUTE_IN_SECONDS);
            /* translators: %s: Number of minutes until the reminder returns. */
            $format = _n('%s minute', '%s minutes', $count, 'rrze-legal');
        } else {
            $count = $seconds;
            /* translators: %s: Number of seconds until the reminder returns. */
            $format = _n('%s second', '%s seconds', $count, 'rrze-legal');
        }
        return sprintf($format, number_format_i18n($count));
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
