(() => {
  // src/javascript/frontend/banner.js
  (() => {
    var o;
    o = jQuery, window.RRZELegal = (function() {
      "use strict";
      var e, t, n = "#RRZELegalBanner input[type='checkbox']", i = "#RRZELegalBanner", a = "#RRZELegalBannerWrap", s = "#RRZELegalBanner input[type='checkbox'][name='cookieGroup[]']", c = "._rrzelegal-btn-switch-status", r = "data-rrzelegal-cookie-uid", d = ".RRZELegal", u = {}, b = {}, h = {}, k = {}, p = { scriptBlockerId: {}, jsHandle: {} }, f = false, v = {
        consents: {},
        expires: null,
        uid: "anonymous",
        version: null
      }, consentCookieName = "rrze-legal-consent", m = {
        consentSaved: null,
        codeUnblocked: null,
        codeUnblockedAfterConsent: null
      }, x = null, C = false, y = null, bannerVisibilityTimer = null, focusTrapElement = null;
      function B(o2, e2) {
        if (e2) {
          var t2 = e2.querySelectorAll(
            'a[href]:not([disabled]), button:not([disabled]), textarea:not([disabled]), input[type="text"]:not([disabled]), input[type="radio"]:not([disabled]), input[type="checkbox"]:not([disabled]), select:not([disabled])'
          ), n2 = Array.from(t2).filter(function(o3) {
            return 0 !== o3.offsetHeight;
          }), i2 = n2[0], a2 = n2[n2.length - 1];
          if (!n2.length) {
            return;
          }
          ("Tab" === o2.key || 9 === o2.keyCode) && (o2.shiftKey ? document.activeElement === i2 && (o2.preventDefault(), a2.focus()) : document.activeElement === a2 && (o2.preventDefault(), i2.focus()));
        }
      }
      function handleFocusTrap(event) {
        B(event, event.currentTarget);
      }
      function w(element) {
        L(focusTrapElement);
        if (element) {
          element.addEventListener("keydown", handleFocusTrap, true);
          focusTrapElement = element;
        }
      }
      function L(element) {
        if (element) {
          element.removeEventListener("keydown", handleFocusTrap, true);
        }
        if (focusTrapElement === element) {
          focusTrapElement = null;
        }
      }
      function O() {
        o("body").removeClass("rrzelegal-position-fix");
        window.clearInterval(bannerVisibilityTimer);
        bannerVisibilityTimer = null;
        L(focusTrapElement);
        o(i).attr("aria-modal", false);
        document.removeEventListener("keydown", handleBannerEscape, true);
      }
      function isBannerElementVisible(element) {
        if (!element || !element.isConnected) {
          return false;
        }
        var rect = element.getBoundingClientRect();
        if (rect.width <= 0 || rect.height <= 0) {
          return false;
        }
        for (var parent = element; parent; parent = parent.parentElement) {
          var style = window.getComputedStyle(parent);
          if (style.display === "none" || style.visibility === "hidden" || style.visibility === "collapse" || style.opacity === "0" || style.contentVisibility === "hidden") {
            return false;
          }
        }
        return true;
      }
      function getVisibleBannerAction() {
        var panels = document.querySelectorAll(
          i + " .cookie-box, " + i + " .cookie-preference"
        );
        for (var panel of panels) {
          if (panel.getAttribute("aria-hidden") === "true" || !isBannerElementVisible(panel.querySelector(".container"))) {
            continue;
          }
          var saveAction = panel.querySelector("[data-cookie-accept]");
          if (isBannerElementVisible(saveAction)) {
            return saveAction;
          }
          var actions = panel.querySelectorAll(
            "[data-cookie-accept-all], [data-cookie-refuse]"
          );
          for (var action of actions) {
            if (isBannerElementVisible(action)) {
              return action;
            }
          }
        }
        return null;
      }
      function checkBannerVisibility() {
        if (!getVisibleBannerAction()) {
          j();
        }
      }
      function focusBannerControl(selector) {
        var control = document.querySelector(i + " " + selector);
        if (!isBannerElementVisible(control)) {
          control = getVisibleBannerAction();
        }
        if (control) {
          control.focus({ preventScroll: true });
        }
      }
      function saveConsentAndClose() {
        try {
          H();
        } finally {
          j();
        }
      }
      function refuseConsent() {
        try {
          var groups = [];
          o(s + ":checked").each(function() {
            -1 === groups.indexOf(this.value) && (o(this).trigger("click"), groups.push(this.value));
          });
          H();
        } finally {
          j();
        }
      }
      function handleBannerEscape(event) {
        if ("Escape" !== event.key && "Esc" !== event.key && 27 !== event.keyCode) {
          return;
        }
        event.preventDefault();
        event.stopPropagation();
        refuseConsent();
      }
      function isNarrowBannerViewport() {
        return window.matchMedia("(max-width: 479px)").matches;
      }
      var _, S = function(t2) {
        O();
        var banner = document.querySelector(i), overlay = document.querySelector(i + " > div"), box = banner && banner.querySelector(".cookie-box"), wrap = banner && banner.querySelector("._rrzelegal-" + e.boxLayout + "-wrap");
        if (!overlay || !box || !wrap) {
          j();
          return false;
        }
        try {
          var a2 = o(s), c2 = null;
          Object.keys(v.consents).length ? (o("[data-rrzelegal-cookie-group]").each(function() {
            -1 === Object.keys(v.consents).indexOf(
              this.dataset.rrzelegalCookieGroup
            ) && o(this).addClass("rrzelegal-hide");
          }), a2.length && "1" === e.boxLayoutAdvanced && a2.each(function() {
            c2 = this.value, "string" == typeof this.dataset.rrzelegalCookieCheckbox && ("object" == typeof v.consents[c2] ? o(this).prop("checked", true) : o(this).prop("checked", false));
          })) : a2.length && a2.each(function() {
            c2 = this.value, "1" === e.boxLayoutAdvanced && "1" === e.ignorePreSelectStatus && false === f && "essential" !== c2 && (o(this).prop("checked", false), o(n + "[name='cookies[" + c2 + "][]']").prop("checked", false).trigger("change"));
          }), e.blockContent ? (o("#RRZELegalBanner > div").addClass(
            "_rrzelegal-block-content"
          ), e.animation ? (o("#RRZELegalBanner > div").addClass(
            "_rrzelegal-bg-animation"
          ), setTimeout(function() {
            o("#RRZELegalBanner > div").addClass(
              "_rrzelegal-bg-dark"
            );
          }, 25)) : o("#RRZELegalBanner > div").addClass(
            "_rrzelegal-bg-dark"
          )) : o(
            "._rrzelegal-" + e.boxLayout + "-wrap"
          ).addClass("_rrzelegal-position-fixed"), o("#RRZELegalBanner > div").css("display", ""), o("#RRZELegalBanner > div").removeClass("rrzelegal-hide").addClass("show-cookie-box");
          var action = getVisibleBannerAction();
          if (!action) {
            j();
            return false;
          }
          action.focus({ preventScroll: true });
          y = wrap.offsetWidth + "px";
          w(action.closest(".cookie-box, .cookie-preference"));
          o(i).attr("aria-modal", true);
          document.addEventListener("keydown", handleBannerEscape, true);
          o("body").addClass("rrzelegal-position-fix");
          bannerVisibilityTimer = window.setInterval(checkBannerVisibility, 250);
          return true;
        } catch (error) {
          j();
          throw error;
        }
      }, j = function() {
        return O(), e.animation && (o(
          "#RRZELegalBanner ._rrzelegal-" + e.boxLayout
        ).removeClass("delay-1s "), o(
          "#RRZELegalBanner ._rrzelegal-" + e.boxLayout
        ).removeClass(e.animationIn), o(
          "#RRZELegalBanner ._rrzelegal-" + e.boxLayout
        ).addClass(e.animationOut)), o("#RRZELegalBanner > div").hide().removeClass("show-cookie-box").addClass("rrzelegal-hide"), e.blockContent ? o("#RRZELegalBanner > div").removeClass(
          "_rrzelegal-bg-dark"
        ) : o(
          "._rrzelegal-" + e.boxLayout + "-wrap"
        ).addClass("_rrzelegal-position-fixed"), setTimeout(
          function() {
            if ("true" === o(i).attr("aria-modal")) {
              return;
            }
            o(
              "._rrzelegal-" + e.boxLayout + "-wrap"
            ).removeAttr("style"), o(
              "._rrzelegal-" + e.boxLayout + " .cookie-box .container"
            ).removeAttr("style"), o(
              "._rrzelegal-" + e.boxLayout + " .cookie-preference .container"
            ).removeAttr("style"), e.animation && (o(
              "._rrzelegal-" + e.boxLayout
            ).removeClass("_rrzelegal-animated"), o(
              "._rrzelegal-" + e.boxLayout
            ).removeClass("delay-1s"), o(
              "._rrzelegal-" + e.boxLayout
            ).removeClass(e.animationIn), o(
              "._rrzelegal-" + e.boxLayout
            ).removeClass(e.animationOut)), o("#RRZELegalBanner > div").removeClass(
              "show-cookie-box"
            ), e.blockContent && o("#RRZELegalBanner > div").removeClass(
              "_rrzelegal-block-content"
            );
          },
          e.animation ? 1e3 : 0
        ), true;
      }, I = function() {
        L(document.querySelector("#RRZELegalBanner .cookie-box")), w(document.querySelector("#RRZELegalBanner .cookie-preference"));
        var t2 = o(s), i2 = null;
        t2.length && t2.each(function() {
          i2 = this.value, Object.keys(v.consents).length ? false === f && (void 0 !== v.consents[i2] ? (o(this).prop("checked", true), o(this).trigger("change"), o(
            n + "[name='cookies[" + i2 + "][]']"
          ).each(function() {
            -1 !== v.consents[i2].indexOf(
              this.value
            ) ? o(this).prop(
              "checked",
              true
            ) : o(this).prop(
              "checked",
              false
            ), o(this).trigger("change");
          })) : (o(this).prop("checked", false), o(this).trigger("change"), o(
            n + "[name='cookies[" + i2 + "][]']"
          ).prop("checked", false).trigger("change"))) : ("1" === e.ignorePreSelectStatus && false === f && (o(this).prop("checked", false), o(
            "#RRZELegalBanner [data-rrzelegal-cookie-group='" + this.value + "']"
          ).addClass("rrzelegal-hide")), o(this).trigger("change"), o(
            n + "[name='cookies[" + i2 + "][]']"
          ).each(function() {
            "1" === e.ignorePreSelectStatus && false === f && o(this).prop("checked", false), o(this).trigger("change");
          }));
        });
        if (isNarrowBannerViewport()) {
          o(
            "._rrzelegal-" + e.boxLayout + " .cookie-box"
          ).attr("aria-hidden", true), o(
            "._rrzelegal-" + e.boxLayout + " .cookie-preference"
          ).attr("aria-hidden", false), o(
            "._rrzelegal-" + e.boxLayout + " .cookie-box .container"
          ).removeAttr("style").addClass("not-visible"), o(
            "._rrzelegal-" + e.boxLayout + " .cookie-preference .container"
          ).removeAttr("style").removeClass("not-visible"), focusBannerControl("#CookiePrefSave");
          return;
        }
        o(
          "._rrzelegal-" + e.boxLayout + " .cookie-box .container"
        ).animate({ height: 0, opacity: 0 }, function() {
          o(
            "._rrzelegal-" + e.boxLayout + " .cookie-box"
          ).attr("aria-hidden", true), o(
            "._rrzelegal-" + e.boxLayout + " .cookie-preference"
          ).attr("aria-hidden", false), showPreferenceContainer(), focusBannerControl("#CookiePrefSave");
        });
      }, D = function() {
        L(document.querySelector("#RRZELegalBanner .cookie-preference"));
        w(document.querySelector("#RRZELegalBanner .cookie-box"));
        if (isNarrowBannerViewport()) {
          o(
            "._rrzelegal-" + e.boxLayout + " .cookie-box"
          ).attr("aria-hidden", false), o(
            "._rrzelegal-" + e.boxLayout + " .cookie-preference"
          ).attr("aria-hidden", true), o(
            "._rrzelegal-" + e.boxLayout + " .cookie-preference .container"
          ).removeAttr("style").addClass("not-visible"), o(
            "._rrzelegal-" + e.boxLayout + " .cookie-box .container"
          ).removeAttr("style").removeClass("not-visible"), focusBannerControl("#BannerSaveButton");
          return;
        }
        o(
          "._rrzelegal-" + e.boxLayout + " .cookie-preference .container"
        ).animate({ height: 0, opacity: 0 }, function() {
          o(
            "._rrzelegal-" + e.boxLayout + " .cookie-box"
          ).attr("aria-hidden", false), o(
            "._rrzelegal-" + e.boxLayout + " .cookie-preference"
          ).attr("aria-hidden", true), showCookieBoxContainer(), focusBannerControl("#BannerSaveButton");
        });
      }, showPreferenceContainer = function() {
        if (isNarrowBannerViewport()) {
          o(
            "._rrzelegal-" + e.boxLayout + " .cookie-preference .container"
          ).removeAttr("style").removeClass("not-visible");
          return;
        }
        o(
          "._rrzelegal-" + e.boxLayout + " .cookie-preference .container"
        ).removeAttr("style").removeClass("not-visible");
      }, showCookieBoxContainer = function() {
        if (isNarrowBannerViewport()) {
          o(
            "._rrzelegal-" + e.boxLayout + " .cookie-box .container"
          ).removeAttr("style").removeClass("not-visible");
          return;
        }
        o(
          "._rrzelegal-" + e.boxLayout + " .cookie-box .container"
        ).removeAttr("style").removeClass("not-visible");
      }, T = function(event) {
        if (event) {
          event.preventDefault();
        }
        if (!S(false)) {
          return false;
        }
        o(i + " [data-cookie-back]").hide();
        o(i + " [data-cookie-back] + span._rrzelegal-separator").hide();
        I();
        return true;
      }, A = function() {
        o("[data-cookie-accordion]").on(
          "click",
          "[data-cookie-accordion-target]",
          function() {
            var e2 = o(this).closest("[data-cookie-accordion]");
            e2.find("[data-cookie-accordion-parent]:visible").length && (e2.find(
              "[data-cookie-accordion-status='hide']"
            ).addClass("rrzelegal-hide"), e2.find(
              "[data-cookie-accordion-status='show']"
            ).removeClass("rrzelegal-hide"), e2.find(
              "[data-cookie-accordion-parent]:visible"
            ).slideUp()), e2.find(
              "[data-cookie-accordion-parent='" + this.dataset.cookieAccordionTarget + "']:hidden"
            ).length && (o(this).children(
              "[data-cookie-accordion-status='show']"
            ).addClass("rrzelegal-hide"), o(this).children(
              "[data-cookie-accordion-status='hide']"
            ).removeClass("rrzelegal-hide"), e2.find(
              "[data-cookie-accordion-parent='" + this.dataset.cookieAccordionTarget + "']"
            ).slideDown());
          }
        );
      }, E = function() {
        try {
          var e2 = o(s), t2 = null;
          e2.length && e2.each(function() {
            t2 = this.value, o(this).prop("checked", true), o(this).trigger("change"), o(n + "[name='cookies[" + t2 + "][]']").each(
              function() {
                o(this).prop("checked", true), o(this).trigger("change");
              }
            );
          }), H();
        } finally {
          j();
        }
      }, U = function() {
        o(document).on("click", s, function() {
          f = true, this.checked ? (o(
            n + "[name='cookies[" + this.value + "][]']"
          ).prop("checked", true).trigger("change"), o(s + "[value='" + this.value + "']").prop(
            "checked",
            true
          ), o(
            "#RRZELegalBanner [data-rrzelegal-cookie-group='" + this.value + "']"
          ).length && o(
            "#RRZELegalBanner [data-rrzelegal-cookie-group='" + this.value + "']"
          ).removeClass("rrzelegal-hide")) : (o(
            n + "[name='cookies[" + this.value + "][]']"
          ).prop("checked", false).trigger("change"), o(s + "[value='" + this.value + "']").prop(
            "checked",
            false
          ), o(
            "#RRZELegalBanner [data-rrzelegal-cookie-group='" + this.value + "']"
          ).length && o(
            "#RRZELegalBanner [data-rrzelegal-cookie-group='" + this.value + "']"
          ).addClass("rrzelegal-hide"));
        });
      }, P = function() {
        o(document).on(
          "click",
          n + "[name^='cookies']",
          function() {
            f = true, this.checked && (o(
              s + "[value='" + this.dataset.cookieGroup + "']"
            ).prop("checked", true).trigger("change"), o(
              "#RRZELegalBanner [data-rrzelegal-cookie-group='" + this.dataset.cookieGroup + "']"
            ).length && o(
              "#RRZELegalBanner [data-rrzelegal-cookie-group='" + this.dataset.cookieGroup + "']"
            ).removeClass("rrzelegal-hide"));
          }
        );
      }, N = function() {
        true === this.checked ? (o(this).parent().parent().children(c).children().last().css("display", "none"), o(this).parent().parent().children(c).children().first().css("display", "inline-block")) : (o(this).parent().parent().children(c).children().first().css("display", "none"), o(this).parent().parent().children(c).children().last().css("display", "inline-block"));
      }, H = function() {
        var t2 = { essential: e.cookies.essential }, i2 = o(s + ":checked"), a2 = o(n + "[name^='cookies']:checked");
        if (i2.length && (i2.each(function() {
          this.value.length && new RegExp(/^[a-z-_]{3,}$/).test(
            this.value
          ) && "essential" !== this.value && (t2[this.value] = []);
        }), a2.length && a2.each(function() {
          this.value.length && "string" == typeof this.dataset.cookieGroup && new RegExp(/^[a-z-_]{3,}$/).test(
            this.value
          ) && new RegExp(/^[a-z-_]{3,}$/).test(
            this.dataset.cookieGroup
          ) && t2[this.dataset.cookieGroup].push(
            this.value
          );
        })), Object.keys(v.consents).length) {
          for (var c2 in v.consents)
            if (void 0 !== t2[c2])
              for (var r2 in v.consents[c2])
                -1 === t2[c2].indexOf(v.consents[c2][r2]) && K(l);
            else if (void 0 !== u[c2]) for (var l in u[c2]) K(l);
        }
        if (Object.keys(v.consents).length) {
          for (var c2 in v.consents)
            if (void 0 !== t2[c2])
              for (var r2 in v.consents[c2])
                -1 === t2[c2].indexOf(v.consents[c2][r2]) && Q(c2, v.consents[c2][r2]);
            else if (void 0 !== u[c2])
              for (var l in u[c2]) Q(c2, l);
        }
        J(t2, false), "1" !== e.reloadAfterConsent && (V(), F(), Y(), document.dispatchEvent(m.codeUnblockedAfterConsent), document.dispatchEvent(m.codeUnblocked));
      }, R = function() {
        var o2 = false;
        return "string" == typeof v.version && (v.version === e.cookieVersion ? o2 = true : v.consents = {}), o2;
      }, G = function() {
        if (document.cookie.length)
          for (var o2 = document.cookie.split(";"), t2 = 0; t2 < o2.length; t2++)
            try {
              o2[t2] = decodeURIComponent(o2[t2]);
              var n2 = o2[t2].split("="), i2 = void 0 !== n2[0] ? n2[0].trim() : "", a2 = void 0 !== n2[1] ? n2[1].trim() : "";
              if (i2 === consentCookieName) {
                var s2 = JSON.parse(decodeURIComponent(a2));
                void 0 !== s2.domainPath ? s2.domainPath === e.cookieDomain + e.cookiePath && (v = s2) : v = s2;
              }
            } catch (e2) {
              console.log("The cookie is spoiled:"), console.dir(o2[t2]), console.dir(e2);
            }
        return v;
      }, J = function(o2, t2) {
        var n2 = {}, i2 = "", a2 = [], s2 = e.cookieLifetime;
        if (1 === Object.keys(o2).length && void 0 !== e.cookieLifetimeEssentialOnly && (s2 = e.cookieLifetimeEssentialOnly), false === t2 || false === R()) {
          var c2 = /* @__PURE__ */ new Date();
          c2.setTime(
            c2.getTime() + 24 * parseInt(s2) * 60 * 60 * 1e3
          ), i2 = c2.toUTCString();
        } else i2 = v.expires;
        n2.consents = o2, n2.domainPath = e.cookieDomain + e.cookiePath, n2.expires = i2, n2.uid = v.uid, n2.version = e.cookieVersion, void 0 === n2.consents.essential && (n2.consents.essential = e.cookies.essential);
        var r2 = true;
        1 === Object.keys(o2).length ? (n2.uid = "anonymous", ("anonymous" !== v.uid || 1 === Object.keys(v.consents).length) && (r2 = false)) : "anonymous" === v.uid ? (n2.uid = $(), 1 === Object.keys(v.consents).length && (r2 = false)) : "anonymous" !== v.uid && (r2 = false), a2.push(
          consentCookieName + "=" + encodeURIComponent(JSON.stringify(n2))
        ), "" !== e.cookieDomain && "" === e.automaticCookieDomainAndPath && a2.push("domain=" + e.cookieDomain), a2.push("path=" + e.cookiePath), a2.push("expires=" + i2), a2.push("SameSite=Lax"), e.cookieSecure && a2.push("secure"), document.cookie = a2.join(";"), G(), no(), ao(r2), document.dispatchEvent(m.consentSaved);
      }, M = function(o2, t2, n2) {
        var i2, a2 = "", s2 = [], c2 = "", r2 = /* @__PURE__ */ new Date();
        return null == t2 || false === t2 ? "" !== e.cookieDomain && "" === e.automaticCookieDomainAndPath && (a2 = e.cookieDomain) : a2 = t2, void 0 === n2 && (n2 = false), i2 = (function(o3, e2) {
          var t3 = { name: "", value: "" };
          if (void 0 === e2 && (e2 = false), e2 && (o3 = o3.replace("*", "")), document.cookie.length)
            for (var n3 = document.cookie.split(";"), i3 = 0; i3 < n3.length; i3++)
              try {
                n3[i3] = decodeURIComponent(n3[i3]);
                var a3 = n3[i3].split("="), s3 = void 0 !== a3[0] ? a3[0].trim() : "", c3 = void 0 !== a3[1] ? a3[1].trim() : "";
                e2 ? -1 !== s3.indexOf(o3) && (t3.name = s3, t3.value = c3) : s3 === o3 && (t3.name = s3, t3.value = c3);
              } catch (o4) {
                console.log("The cookie is spoiled:"), console.dir(n3[i3]), console.dir(o4);
              }
          return t3;
        })(o2, n2), i2.name.length && (s2.push(i2.name + "="), "" !== a2 && s2.push("domain=" + a2), s2.push("path=" + e.cookiePath), r2.setTime(r2.getTime() - 864e5), c2 = r2.toUTCString(), s2.push("expires=" + c2), document.cookie = s2.join(";")), true;
      }, z = function(o2, e2) {
        return (function(o3, e3) {
          "string" == typeof o3 && o3.length && new RegExp(/^[a-z-_]{3,}$/).test(o3) && (void 0 === v.consents[o3] && (v.consents[o3] = []), "string" == typeof e3 && new RegExp(/^[a-z-_]{3,}$/).test(e3) && -1 === v.consents[o3].indexOf(e3) && v.consents[o3].push(e3)), J(v.consents, true);
        })(o2, e2), true;
      }, W = function(o2, e2) {
        return (function(o3, e3) {
          if ("string" == typeof o3 && o3.length && new RegExp(/^[a-z-_]{3,}$/).test(o3)) {
            var t2 = true;
            "string" == typeof e3 && new RegExp(/^[a-z-_]{3,}$/).test(e3) && void 0 !== v.consents[o3] && -1 !== v.consents[o3].indexOf(e3) && (v.consents[o3].splice(
              v.consents[o3].indexOf(e3),
              1
            ), t2 = false), true === t2 && void 0 !== v.consents[o3] && delete v.consents[o3];
          }
          Object.keys(v.consents).length && J(v.consents, true);
        })(o2, e2), K(e2), Q(o2, e2), true;
      }, q = function(o2) {
        var e2 = false;
        for (var t2 in v.consents)
          -1 !== v.consents[t2].indexOf(o2) && (e2 = true);
        return e2;
      }, $ = function() {
        function o2() {
          var o3 = "";
          if ("object" == typeof window.crypto) {
            var e2 = 0, t2 = new Uint32Array(4);
            for (window.crypto.getRandomValues(t2); e2 < 4; e2++)
              o3 += "abcdefhgihjklmnopqrstuvwxyz0123456789"[t2[e2] % 37];
          } else
            o3 = Math.floor(65536 * (1 + Math.random())).toString(16).substring(1);
          return o3;
        }
        return o2() + o2() + "-" + o2() + o2() + "-" + o2() + o2() + "-" + o2() + o2();
      }, V = function() {
        for (var e2 in v.consents)
          for (var n2 in v.consents[e2]) {
            var i2 = v.consents[e2][n2];
            void 0 !== u[e2] && void 0 !== u[e2][i2] && void 0 !== u[e2][i2].optInJS && (o("body").append(to(u[e2][i2].optInJS)), u[e2][i2].optInJS = "");
          }
      }, F = function() {
        o("[data-rrzelegal-cookie-type='cookie-group']").each(
          function() {
            if (void 0 !== v.consents[this.dataset.rrzelegalCookieId]) {
              var t2;
              t2 = "javascript" === e.bannerIntegration ? to(this.firstChild.innerHTML) : to(this.innerHTML), o(this).prev().length ? o(this).prev().after(t2) : o(this).parent().prepend(t2), this.parentNode.removeChild(this);
            }
          }
        );
      }, Y = function() {
        o("[data-rrzelegal-cookie-type='cookie']").each(
          function() {
            for (var t2 in v.consents)
              if (-1 !== v.consents[t2].indexOf(
                this.dataset.rrzelegalCookieId
              )) {
                var n2;
                n2 = "javascript" === e.bannerIntegration ? to(this.firstChild.innerHTML) : to(this.innerHTML), o(this).prev().length ? o(this).prev().after(n2) : o(this).parent().prepend(n2), this.parentNode.removeChild(this);
              }
          }
        );
      }, K = function(o2) {
        null === x && (x = [], C = true), x.push(o2);
      }, Q = function(e2, t2) {
        if (void 0 !== u[e2] && void 0 !== u[e2][t2] && void 0 !== u[e2][t2].optOutJS) {
          var n2 = to(u[e2][t2].optOutJS);
          void 0 !== u[e2][t2].settings.asyncOptOutCode && "1" === u[e2][t2].settings.asyncOptOutCode || (n2 += "<script>window.RRZELegal.optOutDone('" + t2 + "')<\/script>"), o("body").append(n2), u[e2][t2].optOutJS = "";
        }
      }, Z = function(t2) {
        t2.preventDefault();
        var n2, i2, a2 = o(this).parents(".RRZELegal"), s2 = false;
        if (n2 = a2.find(
          "[data-rrzelegal-cookie-type='content-blocker']"
        )[0].dataset.rrzelegalCookieId)
          for (var c2 in o(
            "[data-rrzelegal-cookie-type='content-blocker'][data-rrzelegal-cookie-id='" + n2 + "']"
          ).each(function() {
            X(o(this).parents(".RRZELegal"));
          }), e.cookies)
            -1 !== e.cookies[c2].indexOf(n2) && z(c2, n2);
        else X(a2);
      }, X = function(o2) {
        var t2 = o2.find(
          "[data-rrzelegal-cookie-type='content-blocker']"
        ), n2 = "";
        if (t2.length) {
          var i2;
          n2 = t2[0].dataset.rrzelegalCookieId, void 0 !== b[n2].settings.executeGlobalCodeBeforeUnblocking && "1" === b[n2].settings.executeGlobalCodeBeforeUnblocking && void 0 === h[n2] && (b[n2].global(b[n2]), h[n2] = true), i2 = "javascript" === e.bannerIntegration ? to(t2[0].firstChild.innerHTML) : to(t2[0].innerHTML);
          var a2 = setInterval(function() {
            var e2 = true;
            if (void 0 !== k[n2]) {
              var t3;
              if (void 0 !== k[n2].scriptBlockerId)
                for (t3 in k[n2].scriptBlockerId)
                  true !== eo(
                    k[n2].scriptBlockerId[t3],
                    "scriptBlockerId"
                  ) && (e2 = false);
              if (void 0 !== k[n2].scriptBlockerId)
                for (t3 in k[n2].jsHandle)
                  true !== eo(k[n2].jsHandle[t3], "jsHandle") && (e2 = false);
            }
            true === e2 && (clearInterval(a2), o2.prev().length ? o2.prev().after(i2) : o2.parent().prepend(i2), void 0 !== b[n2].settings.executeGlobalCodeBeforeUnblocking && "0" !== b[n2].settings.executeGlobalCodeBeforeUnblocking || void 0 === h[n2] && (b[n2].global(b[n2]), h[n2] = true), b[n2].init(o2.prev()[0], b[n2]), o2[0].parentNode.removeChild(o2[0]));
          }, 50);
        }
      }, oo = function(e2, t2, n2) {
        var i2 = o(e2)[0];
        if (void 0 !== i2) {
          var a2 = document.createElement("script");
          if ("" !== i2.id && (a2.id = i2.id), "" !== i2.className && (a2.className = i2.className), "" !== i2.dataset) {
            for (var s2 in i2.dataset)
              if (-1 === s2.indexOf("rrzelegal")) {
                var c2 = s2.split(/(?=[A-Z])/);
                for (var r2 in c2)
                  c2[r2] = "-" + c2[r2].toLowerCase();
                a2.setAttribute(
                  "data" + c2.join(""),
                  i2.dataset.hasOwnProperty(s2)
                );
              }
          }
          "string" == typeof i2.dataset.rrzelegalScriptBlockerSrc ? (a2.src = i2.dataset.rrzelegalScriptBlockerSrc, a2.onload = function() {
            p[n2][t2]--, oo(e2, t2, n2);
          }, i2.parentNode.insertBefore(a2, i2), i2.parentNode.removeChild(i2)) : (a2.type = "text/javascript", a2.innerHTML = i2.innerHTML, i2.parentNode.insertBefore(a2, i2), i2.parentNode.removeChild(i2), p[n2][t2]--, oo(e2, t2, n2));
        }
        return true;
      }, eo = function(o2, e2) {
        var t2 = false;
        return void 0 !== p[e2][o2] && 0 === p[e2][o2] && (t2 = true), t2;
      }, to = function(o2) {
        return decodeURIComponent(
          Array.prototype.map.call(window.atob(o2), function(o3) {
            return "%" + ("00" + o3.charCodeAt(0).toString(16)).slice(
              -2
            );
          }).join("")
        );
      }, no = function() {
        e.reloadAfterConsent && Object.keys(v.consents).length > 0 && location.reload(true), C && bo();
      }, continueConsentChecks = function() {
        e.hasOnlyEssentialCookies || e.respectDoNotTrack && void 0 !== navigator.doNotTrack && "1" === navigator.doNotTrack ? (J({ essential: e.cookies.essential }, false), V(), F(), Y(), O(), document.dispatchEvent(m.codeUnblocked)) : S(true);
      }, ao = function(t2) {
        if (e.crossDomainCookie.length)
          for (var n2 in e.crossDomainCookie) {
            var i2 = e.crossDomainCookie[n2];
            o("body").append(
              '<iframe class="rrzelegal-hide" src="' + i2 + "?cookieData=" + encodeURIComponent(JSON.stringify(v)) + "&essentialStatistic=" + (t2 ? 1 : 0) + '"></iframe>'
            );
          }
      }, so = function() {
        o(".RRZELegal [name^='rrzelegalCookie']").each(function() {
          q(this.value) ? this.checked = true : this.checked = false, o(this).trigger("change");
        }), o(document).on(
          "change",
          ".RRZELegal [name^='rrzelegalCookie']",
          function() {
            this.checked ? z(this.dataset.cookieGroup, this.value) : W(this.dataset.cookieGroup, this.value);
          }
        );
      }, bo = function() {
        C = false, 0 === x.length && (x = null, e.reloadAfterOptOut && window.location.reload());
      };
      return {
        addConsent: z,
        allocateScriptBlockerToContentBlocker: function(o2, e2, t2) {
          "scriptBlockerId" !== t2 && "jsHandle" !== t2 || (void 0 === k[o2] && (k[o2] = { scriptBlockerId: [], jsHandle: [] }), -1 === k[o2][t2].indexOf(e2) && k[o2][t2].push(e2));
        },
        callWhenLoaded: function(o2, e2) {
          var t2 = function() {
            true === window.hasOwnProperty(o2) ? e2(e2) : window.setTimeout(t2, 1e3);
          };
          t2();
        },
        checkCookieConsent: q,
        checkCookieGroupConsent: function(o2) {
          var e2 = false;
          return void 0 !== v.consents[o2] && (e2 = true), e2;
        },
        deleteCookie: M,
        getCookie: G,
        hideBanner: j,
        init: function(n2, i2, c2, h2) {
          return "about:blank" !== window.location.href && (e = o.extend(
            {
              language: "en",
              animation: "1",
              animationDelay: "",
              animationIn: "fadeIn",
              animationOut: "fadeOut",
              blockContent: "",
              boxLayout: "box",
              boxLayoutAdvanced: "0",
              automaticCookieDomainAndPath: "",
              cookieDomain: "",
              cookiePath: "",
              cookieSecure: true,
              cookieLifetime: "365",
              crossDomainCookie: [],
              cookieBeforeConsent: "",
              cookieVersion: "1",
              hideBannerOnUrls: [],
              respectDoNotTrack: "",
              hasOnlyEssentialCookies: "",
              reloadAfterConsent: "",
              reloadAfterOptOut: "",
              showBanner: "1",
              bannerIntegration: "javascript",
              ignorePreSelectStatus: "1",
              cookies: []
            },
            n2
          ), consentCookieName = e.consentCookieName || consentCookieName, t = h2, u = i2, b = c2, m.consentSaved = document.createEvent("Event"), m.consentSaved.initEvent(
            "rrzelegal-cookie-consent-saved",
            true,
            true
          ), m.codeUnblocked = document.createEvent("Event"), m.codeUnblocked.initEvent(
            "rrzelegal-cookie-code-unblocked",
            true,
            true
          ), m.codeUnblockedAfterConsent = document.createEvent("Event"), m.codeUnblockedAfterConsent.initEvent(
            "rrzelegal-cookie-code-unblocked-after-consent",
            true,
            true
          ), G(), (function() {
            if (Object.keys(u).length) {
              for (var o2 in u)
                if (Object.keys(u[o2]).length) {
                  for (var e2 in u[o2])
                    if (void 0 !== u[o2][e2].settings && void 0 !== u[o2][e2].settings.blockCookiesBeforeConsent && "1" === u[o2][e2].settings.blockCookiesBeforeConsent && void 0 !== u[o2][e2].cookieNameList && false === q(e2))
                      for (var t2 in u[o2][e2].cookieNameList)
                        M(
                          t2,
                          null,
                          -1 !== t2.indexOf("*")
                        ), M(
                          t2,
                          "",
                          -1 !== t2.indexOf("*")
                        );
                }
            }
          })(), e.cookieBeforeConsent && (null !== v.uid && "anonymous" !== v.uid || (v.uid = $()), "function" == typeof Object && (void 0 === Object.entries && (Object.entries = function(o2) {
            for (var e2 = Object.keys(o2), t2 = e2.length, n3 = new Array(t2); t2--; )
              n3[t2] = [e2[t2], o2[e2[t2]]];
            return n3;
          }), 0 === Object.entries(v.consents).length && null === v.expires && ((function() {
            var o2 = {}, t2 = [], n3 = e.cookieLifetimeEssentialOnly, i3 = /* @__PURE__ */ new Date();
            i3.setTime(
              i3.getTime() + 24 * parseInt(n3) * 60 * 60 * 1e3
            );
            var a2 = i3.toUTCString();
            o2.consents = {}, o2.domainPath = e.cookieDomain + e.cookiePath, o2.expires = a2, o2.uid = v.uid, o2.version = null, t2.push(
              consentCookieName + "=" + encodeURIComponent(
                JSON.stringify(o2)
              )
            ), "" !== e.cookieDomain && "" === e.automaticCookieDomainAndPath && t2.push(
              "domain=" + e.cookieDomain
            ), t2.push("path=" + e.cookiePath), t2.push("expires=" + a2), t2.push("SameSite=Lax"), e.cookieSecure && t2.push("secure"), document.cookie = t2.join(";"), G();
          })(), no()))), o(a).length && "SCRIPT" === o(a)[0].tagName && o(a).after(o(a).html()), o(document).on(
            "click",
            "[data-cookie-accept]",
            function(o2) {
              o2.preventDefault(), saveConsentAndClose();
            }
          ), o(document).on(
            "click",
            "[data-cookie-accept-all]",
            function(o2) {
              o2.preventDefault(), E();
            }
          ), o(document).on(
            "click",
            "[data-cookie-back]",
            function(o2) {
              o2.preventDefault(), D();
            }
          ), o(document).on(
            "click",
            "[data-cookie-individual]",
            function(o2) {
              o2.preventDefault(), I();
            }
          ), o(document).on(
            "click",
            "[data-cookie-refuse]",
            function(e2) {
              e2.preventDefault();
              refuseConsent();
            }
          ), A(), U(), P(), true === R() ? (V(), F(), Y(), document.dispatchEvent(m.codeUnblocked), O()) : e.showBanner ? 0 === e.hideBannerOnUrls.length || -1 === e.hideBannerOnUrls.indexOf(
            window.location.protocol + "//" + window.location.host + window.location.pathname
          ) ? continueConsentChecks() : (V(), F(), Y(), O(), document.dispatchEvent(m.codeUnblocked)) : O(), o(document).on(
            "click",
            "[data-rrzelegal-cookie-unblock]",
            Z
          ), o(document).on(
            "click",
            "[data-rrzelegal-cookie-preference]",
            T
          ), o(document).on(
            "click",
            ".rrzelegal-cookie-preference",
            T
          ), o(document).on(
            "change",
            "[data-rrzelegal-cookie-switch]",
            N
          ), o(document).on("keydown", function(e2) {
            9 === e2.keyCode && o(d).addClass("_rrzelegal-keyboard");
          }), o(document).on("mousedown", function(e2) {
            o(d).is(":visible") && o(d).removeClass("_rrzelegal-keyboard");
          }), o("body").on(
            "focus",
            ".RRZELegal._rrzelegal-keyboard input[type='checkbox']",
            function(e2) {
              var t2 = o(e2.currentTarget).closest("label");
              t2 && o(t2).addClass("_rrzelegal-focused");
            }
          ), o(d).on("blur", "input[type='checkbox']", function(e2) {
            var t2 = o(e2.currentTarget).closest("label");
            t2 && o(t2).removeClass("_rrzelegal-focused");
          }), so(), o("[" + r + "]").length && o("[" + r + "]").each(function() {
            o(this).html(v.uid);
          }), true);
        },
        initSwitchConsentButtonStatus: so,
        openCookiePreference: T,
        removeConsent: W,
        showBanner: S,
        unblockContentId: function(e2) {
          o(
            "[data-rrzelegal-cookie-type='content-blocker'][data-rrzelegal-cookie-id='" + e2 + "']"
          ).each(function() {
            X(o(this).parents(".RRZELegal"));
          });
        },
        unblockScriptBlockerId: function(e2) {
          var t2 = "[data-rrzelegal-script-blocker-id='" + e2 + "']";
          return o(t2).length && (p.scriptBlockerId[e2] = o(t2).length, oo(t2, e2, "scriptBlockerId")), true;
        },
        unblockScriptBlockerJSHandle: function(e2) {
          var t2 = "[data-rrzelegal-script-blocker-js-handle='" + e2 + "']";
          return o(t2).length && (p.jsHandle[e2] = o(t2).length, oo(t2, e2, "jsHandle")), true;
        },
        optOutDone: function(o2) {
          null !== x ? x.length !== (x = x.filter(function(e2, t2, n2) {
            return e2 !== o2;
          })).length ? 0 !== x.length || C || (x = null, e.reloadAfterOptOut && window.location.reload()) : console.log(
            'No opt out found for cookie "' + o2 + '"'
          ) : console.log("No opt out has been initialized");
        }
      };
    })();
  })();
})();
//# sourceMappingURL=rrze-legal.js.map
