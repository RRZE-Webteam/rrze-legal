(() => {
  // src/javascript/admin/settings.js
  function initDatepicker($) {
    if ($('[type="date"]').prop("type") != "date") {
      $('[type="date"]').datepicker({
        dateFormat: `${legalSettings.dateFormat}`
      });
    }
  }
  function setConsentCookieTechnicalSectionState($button, $table, isOpen) {
    $button.attr("aria-expanded", isOpen ? "true" : "false");
    $button.text(isOpen ? "Technische Angaben ausblenden" : "Technische Angaben anzeigen");
    $table.prop("hidden", !isOpen);
  }
  function bindConsentCookieTechnicalToggle() {
    var $ = jQuery;
    var $buttons = $(".rrze-legal-consent-technical-toggle");
    $buttons.each(function initConsentCookieTechnicalToggle() {
      var $button = $(this);
      var $table = $button.closest("p").nextAll("table.form-table").first();
      if (!$table.length) {
        return;
      }
      $table.addClass("rrze-legal-consent-technical-fields");
      setConsentCookieTechnicalSectionState($button, $table, false);
      $button.on("click", function toggleConsentCookieTechnicalSection() {
        var isOpen = $button.attr("aria-expanded") === "true";
        setConsentCookieTechnicalSectionState($button, $table, !isOpen);
      });
    });
  }
  function setConsentCookieStatusVisibility($pluginSlug) {
    var $ = jQuery;
    var hasPluginSlug = $.trim($pluginSlug.val()) !== "";
    var $status = $("#rrze_legal_consent_cookies_status");
    $status.closest("tr").toggle(!hasPluginSlug);
  }
  function bindConsentCookieStatusVisibility() {
    var $ = jQuery;
    var $pluginSlug = $("#rrze_legal_consent_cookies_plugin_slug");
    if (!$pluginSlug.length) {
      return;
    }
    setConsentCookieStatusVisibility($pluginSlug);
    $pluginSlug.on("input", function updateConsentCookieStatusVisibility() {
      setConsentCookieStatusVisibility($pluginSlug);
    });
  }
  function setCookiesForUserAgentsReadonly($checkbox, $textarea) {
    $textarea.prop("readonly", !$checkbox.is(":checked"));
  }
  function bindCookiesForUserAgentsReadonly() {
    var $ = jQuery;
    var $checkbox = $("[id$='_banner_cookies_for_bots'], [id$='_network_banner_cookies_for_bots']").first();
    var $textarea = $("[id$='_banner_cookies_for_user_agents'], [id$='_network_banner_cookies_for_user_agents']").first();
    if (!$checkbox.length || !$textarea.length) {
      return;
    }
    setCookiesForUserAgentsReadonly($checkbox, $textarea);
    $checkbox.on("change", function updateCookiesForUserAgentsReadonly() {
      setCookiesForUserAgentsReadonly($checkbox, $textarea);
    });
  }
  function initSettings() {
    var $ = jQuery;
    initDatepicker($);
    bindConsentCookieTechnicalToggle();
    bindConsentCookieStatusVisibility();
    bindCookiesForUserAgentsReadonly();
  }
  jQuery(document).ready(initSettings);

  // src/javascript/admin/tos.js
  function rrzeLegalTosReady($) {
    function getOptionName(option) {
      return legalSettings.optionName + "[" + option + "]";
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
        "input[type='date'], input[type='email'], input[type='number'], input[type='password'], input[type='search'], input[type='tel'], input[type='text'], input[type='time'], input[type='url'], textarea"
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
      $subsections.toggleClass("rrze-legal-manual-page-disabled", isOverridden).attr("aria-disabled", isOverridden ? "true" : "false");
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
    accessibilityHelperSection();
    $("#rrze-legal-accessibility input[type='radio']").on("change", function rrzeLegalAccessibilityRadioChange() {
      accessibilityHelperSection();
    });
    bindManualPageState("imprint");
    bindManualPageState("privacy");
    bindManualPageState("accessibility");
  }
  jQuery(document).ready(rrzeLegalTosReady);
})();
//# sourceMappingURL=rrze-legal-admin.js.map
