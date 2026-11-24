<?php
/**
 * Expandable details shared by the dashboard notice and the acknowledgement dialog.
 *
 * @var array[] $issues Outstanding fields in settings order.
 * @var bool    $canEdit Whether the user may follow the field links.
 * @var string  $firstReported Localized date and time of the first report.
 */

defined('ABSPATH') || exit;
?>
<details class="rrze-legal-notice-fields">
    <summary><?php esc_html_e('Affected fields', 'rrze-legal'); ?></summary>
    <ul role="list">
        <?php foreach ($issues as $issue) : ?>
            <?php $label = implode(' › ', array_filter([$issue['section_label'], $issue['subsection_label'], $issue['field_label']])); ?>
            <li>
                <?php if ($canEdit) : ?>
                    <a href="<?php echo esc_url($issue['field_url']); ?>"><?php echo esc_html($label); ?></a>
                <?php else : ?>
                    <span><?php echo esc_html($label); ?></span>
                <?php endif; ?>
                <span class="description"><?php
                    echo $issue['reason_code'] === 'invalid_email'
                        ? esc_html__('Enter a valid email address.', 'rrze-legal')
                        : esc_html__('Add the missing information.', 'rrze-legal');
                ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php if ($firstReported !== '') : ?>
        <p><?php
            /* translators: %s: Date and time the issues were first detected. */
            printf(esc_html__('First detected on %s.', 'rrze-legal'), esc_html($firstReported));
        ?></p>
    <?php endif; ?>
</details>
