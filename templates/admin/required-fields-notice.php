<?php
/**
 * Dashboard notice for missing or invalid legal information.
 *
 * @var array[] $issues Fields supplied by the required-field check.
 * @var bool    $canEdit Whether the current user can edit the legal settings.
 * @var string  $summary Localized description of the outstanding issues.
 * @var string  $firstReported Localized date and time of the first report.
 * @var bool    $isError Whether the error period has been reached.
 * @var string  $escalationText Network message for the current escalation level.
 */

defined('ABSPATH') || exit;
?>
<div class="notice <?php echo $isError ? 'notice-error' : 'notice-warning'; ?> rrze-legal-required-notice">
    <div class="rrze-legal-notice-overview">
        <span class="rrze-legal-notice-symbol dashicons dashicons-warning" aria-hidden="true"></span>
        <div class="rrze-legal-notice-intro">
            <h2><?php esc_html_e('Legal information incomplete', 'rrze-legal'); ?></h2>
            <p><?php echo esc_html($summary); ?></p>
            <?php if (!$canEdit) : ?>
                <p><?php esc_html_e('Please contact the administrator of this website.', 'rrze-legal'); ?></p>
            <?php endif; ?>
        </div>
        <?php if ($canEdit) : ?>
            <div class="rrze-legal-notice-actions">
                <a class="rrze-legal-notice-edit" href="<?php echo esc_url($issues[0]['field_url']); ?>">
                    <?php esc_html_e('Edit details', 'rrze-legal'); ?>
                    <span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
                </a>
            </div>
        <?php endif; ?>
        <?php if ($escalationText !== '' || $isError) : ?>
            <p class="rrze-legal-notice-escalation">
                <?php echo esc_html($escalationText); ?>
                <?php if ($isError) : ?>
                    <?php esc_html_e('If the legal information remains incomplete, the website may be deactivated.', 'rrze-legal'); ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
    <?php require __DIR__ . '/required-fields-list.php'; ?>
</div>
