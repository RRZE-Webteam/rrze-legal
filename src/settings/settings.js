jQuery(document).ready(function ($) {
    if ($('[type="date"]').prop("type") != "date") {
        $('[type="date"]').datepicker({
            dateFormat: `${legalSettings.dateFormat}`,
        });
    }
});
