function rrzeLegalTosReady($) {
    function getOptionName(option) {
        return legalSettings.optionName + "[" + option + "]";
    }

    function imprintImageRights() {
        var inputName = getOptionName("imprint_optional_image_rights");
        var wpWrap = "wp-imprint_optional_image_rights_content-wrap";
        var input = $(
            "input[name='" + inputName + "']:checked",
            "#rrze-legal-imprint"
        ).val();
        if ("1" === input) {
            $("#" + wpWrap).parents("tr").show();
        } else {
            $("#" + wpWrap).parents("tr").hide();
        }
    }

    function imprintNewSection() {
        var inputName = getOptionName("imprint_optional_new_section");
        var wpWrap = "wp-imprint_optional_new_section_content-wrap";
        var input = $(
            "input[name='" + inputName + "']:checked",
            "#rrze-legal-imprint"
        ).val();
        if ("1" === input) {
            $("#" + wpWrap).parents("tr").show();
        } else {
            $("#" + wpWrap).parents("tr").hide();
        }
    }

    function privacyNewSection(region) {
        if (typeof region === "undefined") {
            region = "";
        }
        if (region !== "") {
            region = "_" + region;
        }

        var inputName = getOptionName("privacy_optional_new_section" + region);
        var wpWrap = "wp-privacy_optional_new_section_content" + region + "-wrap";
        var input = $(
            "input[name='" + inputName + "']:checked",
            "#rrze-legal-privacy"
        ).val();
        if ("1" === input) {
            $("#" + wpWrap).parents("tr").show();
        } else {
            $("#" + wpWrap).parents("tr").hide();
        }
    }

    function accessibilityHelperSection() {
        var inputName = getOptionName("accessibility_statement_non_accessible_content_helper");
        var input = $(
            "input[name='" + inputName + "']:checked",
            "#rrze-legal-accessibility"
        ).val();
        var inputList = "[id^=rrze_legal_accessibility_statement_non_accessible_content_list]";
        if ("1" === input) {
            $(inputList).parents("tr").hide();
        } else {
            $(inputList).parents("tr").show();
        }
    }

    function setStoredAttribute($control, attribute, value) {
        var dataName = "rrzeLegalPrevious" + attribute;

        if (typeof $control.data(dataName) === "undefined") {
            $control.data(dataName, $control.attr(attribute) || "");
        }
        $control.attr(attribute, value);
    }

    function restoreStoredAttribute($control, attribute) {
        var dataName = "rrzeLegalPrevious" + attribute;
        var previousValue = $control.data(dataName);

        if (typeof previousValue === "undefined") {
            return;
        }
        if (previousValue === "") {
            $control.removeAttr(attribute);
        } else {
            $control.attr(attribute, previousValue);
        }
        $control.removeData(dataName);
    }

    function setManualPageControls($subsections, isOverridden) {
        var $controls = $subsections.find("input:not([type='hidden']), select, textarea, button");
        var $readonlyControls = $subsections.find(
            "input[type='date'], input[type='email'], input[type='number'], input[type='password'], " +
            "input[type='search'], input[type='tel'], input[type='text'], input[type='time'], " +
            "input[type='url'], textarea"
        );

        $controls.each(function rrzeLegalManualPageControlState() {
            if (isOverridden) {
                setStoredAttribute($(this), "tabindex", "-1");
            } else {
                restoreStoredAttribute($(this), "tabindex");
            }
        });

        $readonlyControls.each(function rrzeLegalManualPageReadonlyState() {
            if (isOverridden) {
                setStoredAttribute($(this), "readonly", "readonly");
            } else {
                restoreStoredAttribute($(this), "readonly");
            }
        });
    }

    function getManualPageSubmit($manualPageSubsection, endpoint) {
        var submitClass = "rrze-legal-manual-page-submit";
        var $submit = $manualPageSubsection.next("." + submitClass);
        var $form = $manualPageSubsection.closest("form");
        var $bottomSubmit = $form.children(".submit").last().find("input[type='submit']").first();
        var $button;

        if ($submit.length) {
            return $submit;
        }
        if (!$bottomSubmit.length) {
            return $();
        }

        $button = $bottomSubmit.clone();
        $button.removeAttr("id");
        $button.attr("name", "submit");
        $button.attr("data-rrze-legal-endpoint", endpoint);

        $submit = $('<p class="submit ' + submitClass + '"></p>');
        $submit.append($button);
        $manualPageSubsection.after($submit);

        return $submit;
    }

    function setManualPageSubmit(endpoint, $manualPageSubsection, isOverridden) {
        var $submit = getManualPageSubmit($manualPageSubsection, endpoint);

        if (!$submit.length) {
            return;
        }
        if (isOverridden) {
            $submit.show();
        } else {
            $submit.hide();
        }
    }

    function setManualPageState(endpoint) {
        var manualPages = legalSettings.manualPages || {};
        var config = manualPages[endpoint] || {};
        var checkboxId = "#rrze_legal_" + endpoint + "_allow_manual_page";
        var isOverridden = Boolean(config.exists) && $(checkboxId).is(":checked");
        var $manualPageSubsection = $(".subsection-" + endpoint + "_manual_page");
        var $subsections = $manualPageSubsection.nextAll(".subsection");

        $subsections
            .toggleClass("rrze-legal-manual-page-disabled", isOverridden)
            .attr("aria-disabled", isOverridden ? "true" : "false");
        setManualPageControls($subsections, isOverridden);
        setManualPageSubmit(endpoint, $manualPageSubsection, isOverridden);
    }

    function bindManualPageState(endpoint) {
        var checkboxId = "#rrze_legal_" + endpoint + "_allow_manual_page";

        setManualPageState(endpoint);
        $(checkboxId).on("change", function rrzeLegalManualPageChange() {
            setManualPageState(endpoint);
        });
    }

    imprintImageRights();
    imprintNewSection();
    $("#rrze-legal-imprint input[type='radio']").on("change", function rrzeLegalImprintRadioChange() {
        imprintImageRights();
        imprintNewSection();
    });

    privacyNewSection("top");
    privacyNewSection("");
    $("#rrze-legal-privacy input[type='radio']").on("change", function rrzeLegalPrivacyRadioChange() {
        var region = "";
        var name = $(this).attr("name");
        if (name.indexOf("new_section_top") >= 0) {
            region = "top";
        }
        privacyNewSection(region);
    });

    accessibilityHelperSection();
    $("#rrze-legal-accessibility input[type='radio']").on("change", function rrzeLegalAccessibilityRadioChange() {
        accessibilityHelperSection();
    });

    bindManualPageState("imprint");
    bindManualPageState("privacy");
    bindManualPageState("accessibility");
}

jQuery(document).ready(rrzeLegalTosReady);
