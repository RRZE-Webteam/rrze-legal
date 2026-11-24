<?php
/**
 * Explicit acknowledgement that postpones the dashboard reminder.
 *
 * @var int    $reportedTimestamp Identifier of the incident shown to the user.
 * @var string $reminderInterval Localized duration of the reminder pause.
 */

defined('ABSPATH') || exit;
?>
<form class="rrze-legal-dialog-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="rrze_legal_ack_tos_notice">
    <input type="hidden" name="rrze_legal_tos_notice_timestamp" value="<?php echo esc_attr((string) $reportedTimestamp); ?>">
    <?php wp_nonce_field('rrze_legal_ack_tos_notice', 'rrze_legal_ack_tos_notice_nonce', false); ?>
    <label class="rrze-legal-dialog-confirm">
        <input type="checkbox" name="rrze_legal_tos_notice_confirm" value="1" required>
        <?php esc_html_e('I have read this notice.', 'rrze-legal'); ?>
    </label>
    <p class="rrze-legal-dialog-reminder" id="rrze-legal-dialog-reminder"><?php
        /* translators: %s: Time until the dashboard reminder returns, e.g. 24 hours. */
        printf(esc_html__('If any fields still need attention, this dialog will reappear after %s.', 'rrze-legal'), esc_html($reminderInterval));
    ?></p>
    <button type="submit" class="rrze-legal-dialog-submit" aria-describedby="rrze-legal-dialog-reminder">
        <?php esc_html_e('Confirm and return to the dashboard', 'rrze-legal'); ?>
    </button>
</form>
