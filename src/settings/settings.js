function initDatepicker($) {
    if ($('[type="date"]').prop("type") != "date") {
        $('[type="date"]').datepicker({
            dateFormat: `${legalSettings.dateFormat}`,
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

function initSettings() {
    var $ = jQuery;

    initDatepicker($);
    bindConsentCookieTechnicalToggle();
    bindConsentCookieStatusVisibility();
}

jQuery(document).ready(initSettings);
