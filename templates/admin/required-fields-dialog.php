<?php
/**
 * Dashboard confirmation for a site with overdue legal information.
 *
 * @var array[] $issues Outstanding fields in settings order.
 * @var bool    $canEdit Whether the user may open the legal settings.
 * @var string  $summary Localized description of the outstanding issues.
 * @var string  $escalationText Message configured by the network.
 * @var string  $firstReported Localized date and time for the field list.
 * @var int     $reportedTimestamp Identifier of the incident to acknowledge.
 * @var string  $reminderInterval Localized duration of the reminder pause.
 */

defined('ABSPATH') || exit;
?>
<dialog open id="rrze-legal-required-dialog" class="rrze-legal-required-dialog" aria-labelledby="rrze-legal-dialog-title">
    <div class="rrze-legal-dialog-content">
        <div class="rrze-legal-dialog-header">
            <span class="rrze-legal-notice-symbol dashicons dashicons-warning" aria-hidden="true"></span>
            <h2 id="rrze-legal-dialog-title" tabindex="-1"><?php esc_html_e('Legal information still incomplete', 'rrze-legal'); ?></h2>
        </div>
        <p class="rrze-legal-dialog-summary"><?php echo esc_html($summary); ?></p>
        <?php if (!$canEdit) : ?>
            <p><?php esc_html_e('Please contact the administrator of this website.', 'rrze-legal'); ?></p>
        <?php endif; ?>
        <p class="rrze-legal-dialog-escalation">
            <?php echo esc_html($escalationText); ?>
            <?php esc_html_e('If the legal information remains incomplete, the website may be deactivated.', 'rrze-legal'); ?>
        </p>
        <?php require __DIR__ . '/required-fields-list.php'; ?>
        <div class="rrze-legal-dialog-actions">
            <?php if ($canEdit) : ?>
                <a class="rrze-legal-notice-edit" href="<?php echo esc_url($issues[0]['field_url']); ?>">
                    <?php esc_html_e('Edit details', 'rrze-legal'); ?>
                    <span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
                </a>
                <details class="rrze-legal-dialog-defer">
                    <summary><?php esc_html_e('Edit later', 'rrze-legal'); ?></summary>
                    <?php require __DIR__ . '/required-fields-dialog-form.php'; ?>
                </details>
            <?php else : ?>
                <?php require __DIR__ . '/required-fields-dialog-form.php'; ?>
            <?php endif; ?>
        </div>
    </div>
</dialog>
