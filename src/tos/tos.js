jQuery(document).ready(function ($) {
    function imprintImageRights() {
        let inputName = `${legalSettings.optionName}[imprint_optional_image_rights]`;
        let wpWrap = "wp-imprint_optional_image_rights_content-wrap";
        let input = $(
            `input[name='${inputName}']:checked`,
            "#rrze-legal-imprint"
        ).val();
        if ("1" === input) {
            $(`#${wpWrap}`).parents("tr").show();
        } else {
            $(`#${wpWrap}`).parents("tr").hide();
        }
    }

    function imprintNewSection() {
        let inputName = `${legalSettings.optionName}[imprint_optional_new_section]`;
        let wpWrap = "wp-imprint_optional_new_section_content-wrap";
        let input = $(
            `input[name='${inputName}']:checked`,
            "#rrze-legal-imprint"
        ).val();
        if ("1" === input) {
            $(`#${wpWrap}`).parents("tr").show();
        } else {
            $(`#${wpWrap}`).parents("tr").hide();
        }
    }

    function privacyNewSection(region = '') {
        if (region != '')
            region = `_${region}`;
        let inputName = `${legalSettings.optionName}[privacy_optional_new_section${region}]`;
        let wpWrap = `wp-privacy_optional_new_section_content${region}-wrap`;
        let input = $(
            `input[name='${inputName}']:checked`,
            "#rrze-legal-privacy"
        ).val();
        if ("1" === input) {
            $(`#${wpWrap}`).parents("tr").show();
        } else {
            $(`#${wpWrap}`).parents("tr").hide();
        }
    }

    function accessibilityHelperSection() {
        let inputName = `${legalSettings.optionName}[accessibility_statement_non_accessible_content_helper]`;
        let input = $(
            `input[name='${inputName}']:checked`,
            "#rrze-legal-accessibility"
        ).val();
        let inputList =
            "[id^=rrze_legal_accessibility_statement_non_accessible_content_list]";
        if ("1" === input) {
            $(`${inputList}`).parents("tr").hide();
        } else {
            $(`${inputList}`).parents("tr").show();
        }
    }

    imprintImageRights();
    imprintNewSection();
    $("#rrze-legal-imprint input[type='radio']").on("change", function () {
        imprintImageRights();
        imprintNewSection();
    });

    privacyNewSection('top');
    privacyNewSection('');
    $("#rrze-legal-privacy input[type='radio']").on("change", function () {
        let region = '';
        let name = $(this).attr('name');
        if (name.indexOf("new_section_top") >= 0) {
            region = 'top';
        }
        privacyNewSection(region);
    });

    accessibilityHelperSection();
    $("#rrze-legal-accessibility input[type='radio']").on(
        "change",
        function () {
            accessibilityHelperSection();
        }
    );
});
