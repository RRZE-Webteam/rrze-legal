(() => {
    var o;
    (o = jQuery),
        (window.RRZELegal = (function () {
            "use strict";
            var e,
                t,
                n = "#RRZELegalBanner input[type='checkbox']",
                i = "#RRZELegalBanner",
                a = "#RRZELegalBannerWrap",
                s =
                    "#RRZELegalBanner input[type='checkbox'][name='cookieGroup[]']",
                c = "._rrzelegal-btn-switch-status",
                r = "data-rrzelegal-cookie-uid",
                l = "data-rrzelegal-cookie-consent-history",
                d = ".RRZELegal",
                u = {},
                b = {},
                h = {},
                k = {},
                p = { scriptBlockerId: {}, jsHandle: {} },
                f = !1,
                v = {
                    consents: {},
                    expires: null,
                    uid: "anonymous",
                    version: null,
                },
                consentCookieName = "rrze-legal-consent",
                m = {
                    consentSaved: null,
                    codeUnblocked: null,
                    codeUnblockedAfterConsent: null,
                },
                x = null,
                C = !1,
                y = null;
            function B(o, e) {
                if (e) {
                    var t = e.querySelectorAll(
                            'a[href]:not([disabled]), button:not([disabled]), textarea:not([disabled]), input[type="text"]:not([disabled]), input[type="radio"]:not([disabled]), input[type="checkbox"]:not([disabled]), select:not([disabled])'
                        ),
                        n = Array.from(t).filter(function (o) {
                            return 0 !== o.offsetHeight;
                        }),
                        i = n[0],
                        a = n[n.length - 1];
                    ("Tab" === o.key || 9 === o.keyCode) &&
                        (o.shiftKey
                            ? document.activeElement === i &&
                              (o.preventDefault(), a.focus())
                            : document.activeElement === a &&
                              (o.preventDefault(), i.focus()));
                }
            }
            function w(o) {
                o.addEventListener(
                    "keydown",
                    function (e) {
                        return B(e, o);
                    },
                    !0
                );
            }
            function L(o) {
                o.removeEventListener(
                    "keydown",
                    function (e) {
                        return B(e, o);
                    },
                    !0
                );
            }
            function O() {
                o(i).attr("aria-modal", !1);
            }
            var _,
                S = function (t) {
                    o(i).attr("aria-modal", !0),
                        o("body").addClass("rrzelegal-position-fix"),
                        w(document.querySelector(".cookie-box")),
                        void 0 === t && (t = !1);
                    var a = o(s),
                        c = null;
                    Object.keys(v.consents).length
                        ? (o("[data-rrzelegal-cookie-group]").each(function () {
                              -1 ===
                                  Object.keys(v.consents).indexOf(
                                      this.dataset.rrzelegalCookieGroup
                                  ) && o(this).addClass("rrzelegal-hide");
                          }),
                          a.length &&
                              "1" === e.boxLayoutAdvanced &&
                              a.each(function () {
                                  (c = this.value),
                                      "string" ==
                                          typeof this.dataset
                                              .rrzelegalCookieCheckbox &&
                                          ("object" == typeof v.consents[c]
                                              ? o(this).prop("checked", !0)
                                              : o(this).prop("checked", !1));
                              }))
                        : a.length &&
                          a.each(function () {
                              (c = this.value),
                                  "1" === e.boxLayoutAdvanced &&
                                      "1" === e.ignorePreSelectStatus &&
                                      !1 === f &&
                                      "essential" !== c &&
                                      (o(this).prop("checked", !1),
                                      o(n + "[name='cookies[" + c + "][]']")
                                          .prop("checked", !1)
                                          .trigger("change"));
                          }),
                        e.blockContent
                            ? (o("#RRZELegalBanner > div").addClass(
                                  "_rrzelegal-block-content"
                              ),
                              e.animation
                                  ? (o("#RRZELegalBanner > div").addClass(
                                        "_rrzelegal-bg-animation"
                                    ),
                                    setTimeout(function () {
                                        o("#RRZELegalBanner > div").addClass(
                                            "_rrzelegal-bg-dark"
                                        );
                                    }, 25))
                                  : o("#RRZELegalBanner > div").addClass(
                                        "_rrzelegal-bg-dark"
                                    ))
                            : o(
                                  "._rrzelegal-" + e.boxLayout + "-wrap"
                              ).addClass("_rrzelegal-position-fixed"),
                        o("#RRZELegalBanner > div").css("display", ""),
                        o("#RRZELegalBanner > div").addClass("show-cookie-box");
                    const r = o("#RRZELegalBanner > div")[0];
                    return (
                        r.offsetWidth,
                        r.offsetHeight,
                        o("#BannerSaveButton")[0].focus({
                            preventScroll: !0,
                        }),
                        (y =
                            o("._rrzelegal-" + e.boxLayout + "-wrap")[0]
                                .offsetWidth + "px"),
                        !0
                    );
                },
                j = function () {
                    return (
                        O(),
                        L(document.querySelector(".cookie-box")),
                        e.animation &&
                            (o(
                                "#RRZELegalBanner ._rrzelegal-" + e.boxLayout
                            ).removeClass("delay-1s "),
                            o(
                                "#RRZELegalBanner ._rrzelegal-" + e.boxLayout
                            ).removeClass(e.animationIn),
                            o(
                                "#RRZELegalBanner ._rrzelegal-" + e.boxLayout
                            ).addClass(e.animationOut)),
                        o("#RRZELegalBanner > div").addClass("rrzelegal-hide"),
                        e.blockContent
                            ? o("#RRZELegalBanner > div").removeClass(
                                  "_rrzelegal-bg-dark"
                              )
                            : o(
                                  "._rrzelegal-" + e.boxLayout + "-wrap"
                              ).addClass("_rrzelegal-position-fixed"),
                              setTimeout(
                                function () {
                                    o(
                                        "._rrzelegal-" + e.boxLayout + "-wrap"
                                    ).removeAttr("style"),
                                        o(
                                            "._rrzelegal-" +
                                                e.boxLayout +
                                                " .cookie-box .container"
                                        ).removeAttr("style"),
                                        o(
                                            "._rrzelegal-" +
                                                e.boxLayout +
                                                " .cookie-preference .container"
                                        ).removeAttr("style"),
                                        e.animation &&
                                            (o(
                                                "._rrzelegal-" + e.boxLayout
                                            ).removeClass("_rrzelegal-animated"),
                                            o("._rrzelegal-" + e.boxLayout).removeClass(
                                                "delay-1s"
                                            ),
                                            o("._rrzelegal-" + e.boxLayout).removeClass(
                                                e.animationIn
                                            ),
                                            o("._rrzelegal-" + e.boxLayout).removeClass(
                                                e.animationOut
                                            )),
                                        o("#RRZELegalBanner > div").removeClass(
                                            "show-cookie-box"
                                        ),
                                        e.blockContent &&
                                            o(
                                                "#RRZELegalBanner > div"
                                            ).removeClass("_rrzelegal-block-content");
                                },
                                e.animation ? 1e3 : 0
                            ),                              
                        o("body").removeClass("rrzelegal-position-fix"),
                        !0
                    );
                },
                I = function () {
                    L(document.querySelector(".cookie-box")),
                        w(document.querySelector(".cookie-preference"));
                    var t = o(s),
                        i = null;
                    t.length &&
                        t.each(function () {
                            (i = this.value),
                                Object.keys(v.consents).length
                                    ? !1 === f &&
                                      (void 0 !== v.consents[i]
                                          ? (o(this).prop("checked", !0),
                                            o(this).trigger("change"),
                                            o(
                                                n +
                                                    "[name='cookies[" +
                                                    i +
                                                    "][]']"
                                            ).each(function () {
                                                -1 !==
                                                v.consents[i].indexOf(
                                                    this.value
                                                )
                                                    ? o(this).prop(
                                                          "checked",
                                                          !0
                                                      )
                                                    : o(this).prop(
                                                          "checked",
                                                          !1
                                                      ),
                                                    o(this).trigger("change");
                                            }))
                                          : (o(this).prop("checked", !1),
                                            o(this).trigger("change"),
                                            o(
                                                n +
                                                    "[name='cookies[" +
                                                    i +
                                                    "][]']"
                                            )
                                                .prop("checked", !1)
                                                .trigger("change")))
                                    : ("1" === e.ignorePreSelectStatus &&
                                          !1 === f &&
                                          (o(this).prop("checked", !1),
                                          o(
                                              "#RRZELegalBanner [data-rrzelegal-cookie-group='" +
                                                  this.value +
                                                  "']"
                                          ).addClass("rrzelegal-hide")),
                                      o(this).trigger("change"),
                                      o(
                                          n + "[name='cookies[" + i + "][]']"
                                      ).each(function () {
                                          "1" === e.ignorePreSelectStatus &&
                                              !1 === f &&
                                              o(this).prop("checked", !1),
                                              o(this).trigger("change");
                                      }));
                        }),
                        o("._rrzelegal-" + e.boxLayout + " .cookie-box .container")
                        .animate(
                            {height: 0, opacity: 0}, 
                            function () {
                                o("._rrzelegal-" + e.boxLayout + " .cookie-box").attr("aria-hidden", !0),
                                o("._rrzelegal-" + e.boxLayout + " .cookie-preference").attr("aria-hidden", !1),
                                o("#CookiePrefSave")[0].focus({preventScroll: !0}),
                                o("._rrzelegal-" + e.boxLayout + "-wrap")
                                .animate(
                                    {width: "100vw", maxWidth: "box" === e.boxLayout ? "768px" : "100%"}, "box" === e.boxLayout ? 400 : 0,
                                    function () {
                                        var t = o("._rrzelegal-" + e.boxLayout + " .cookie-preference .container")[0].scrollHeight;
                                        o("._rrzelegal-" + e.boxLayout + " .cookie-preference .container")
                                        .animate(
                                            {height: "80vh", maxHeight: t, opacity: 1}
                                        );
                                    }
                                );
                            }
                        );
                },
                D = function () {
                    L(document.querySelector(".cookie-preference")),
                        o(
                            "._rrzelegal-" +
                                e.boxLayout +
                                " .cookie-preference .container"
                        ).animate({ height: 0, opacity: 0 }, function () {
                            o(
                                "._rrzelegal-" + e.boxLayout + " .cookie-box"
                            ).attr("aria-hidden", !1),
                                o(
                                    "._rrzelegal-" +
                                        e.boxLayout +
                                        " .cookie-preference"
                                ).attr("aria-hidden", !0),
                                o(
                                    "._rrzelegal-" + e.boxLayout + "-wrap"
                                ).animate(
                                    {
                                        maxWidth:
                                            "box" === e.boxLayout ? y : "100%",
                                    },
                                    "box" === e.boxLayout ? 400 : 0,
                                    function () {
                                        var t =
                                            o(
                                                "._rrzelegal-" +
                                                    e.boxLayout +
                                                    " .cookie-box .container"
                                            )[0].scrollHeight + "px";
                                        o(
                                            "._rrzelegal-" +
                                                e.boxLayout +
                                                " .cookie-box .container"
                                        ).animate({ height: t, opacity: 1 });
                                    }
                                ),
                                o("#BannerSaveButton")[0].focus();
                        });
                },
                T = function (t) {
                    return (
                        void 0 !== t && t.preventDefault(),
                        o(
                            "._rrzelegal-" +
                                e.boxLayout +
                                " .cookie-preference .container a[" +
                                "data-cookie-back]"
                        ).css("display", "none"),
                        o(
                            "._rrzelegal-" +
                                e.boxLayout +
                                " .cookie-preference .container a[" +
                                "data-cookie-back] + span._rrzelegal-separator"
                        ).css("display", "none"),
                        o(
                            "._rrzelegal-" +
                                e.boxLayout +
                                " .cookie-box .container"
                        ).css("height", 0),
                        o(
                            "._rrzelegal-" +
                                e.boxLayout +
                                " .cookie-box .container"
                        ).css("opacity", 0),
                        o("._rrzelegal-" + e.boxLayout + "-wrap").css({
                            width: "100vw",
                            maxWidth: "box" === e.boxLayout ? "768px" : "100%",
                        }),
                        S(!1),
                        setTimeout(function () {
                            I();
                        }, 500),
                        !0
                    );
                },
                A = function () {
                    o("[data-cookie-accordion]").on(
                        "click",
                        "[data-cookie-accordion-target]",
                        function () {
                            var e = o(this).closest("[data-cookie-accordion]");
                            e.find("[data-cookie-accordion-parent]:visible")
                                .length &&
                                (e
                                    .find(
                                        "[data-cookie-accordion-status='hide']"
                                    )
                                    .addClass("rrzelegal-hide"),
                                e
                                    .find(
                                        "[data-cookie-accordion-status='show']"
                                    )
                                    .removeClass("rrzelegal-hide"),
                                e
                                    .find(
                                        "[data-cookie-accordion-parent]:visible"
                                    )
                                    .slideUp()),
                                e.find(
                                    "[data-cookie-accordion-parent='" +
                                        this.dataset.cookieAccordionTarget +
                                        "']:hidden"
                                ).length &&
                                    (o(this)
                                        .children(
                                            "[data-cookie-accordion-status='show']"
                                        )
                                        .addClass("rrzelegal-hide"),
                                    o(this)
                                        .children(
                                            "[data-cookie-accordion-status='hide']"
                                        )
                                        .removeClass("rrzelegal-hide"),
                                    e
                                        .find(
                                            "[data-cookie-accordion-parent='" +
                                                this.dataset
                                                    .cookieAccordionTarget +
                                                "']"
                                        )
                                        .slideDown());
                        }
                    );
                },
                E = function () {
                    var e = o(s),
                        t = null;
                    e.length &&
                        e.each(function () {
                            (t = this.value),
                                o(this).prop("checked", !0),
                                o(this).trigger("change"),
                                o(n + "[name='cookies[" + t + "][]']").each(
                                    function () {
                                        o(this).prop("checked", !0),
                                            o(this).trigger("change");
                                    }
                                );
                        }),
                        H(),
                        j();
                },
                U = function () {
                    o(document).on("click", s, function () {
                        (f = !0),
                            this.checked
                                ? (o(
                                      n +
                                          "[name='cookies[" +
                                          this.value +
                                          "][]']"
                                  )
                                      .prop("checked", !0)
                                      .trigger("change"),
                                  o(s + "[value='" + this.value + "']").prop(
                                      "checked",
                                      !0
                                  ),
                                  o(
                                      "#RRZELegalBanner [data-rrzelegal-cookie-group='" +
                                          this.value +
                                          "']"
                                  ).length &&
                                      o(
                                          "#RRZELegalBanner [data-rrzelegal-cookie-group='" +
                                              this.value +
                                              "']"
                                      ).removeClass("rrzelegal-hide"))
                                : (o(
                                      n +
                                          "[name='cookies[" +
                                          this.value +
                                          "][]']"
                                  )
                                      .prop("checked", !1)
                                      .trigger("change"),
                                  o(s + "[value='" + this.value + "']").prop(
                                      "checked",
                                      !1
                                  ),
                                  o(
                                      "#RRZELegalBanner [data-rrzelegal-cookie-group='" +
                                          this.value +
                                          "']"
                                  ).length &&
                                      o(
                                          "#RRZELegalBanner [data-rrzelegal-cookie-group='" +
                                              this.value +
                                              "']"
                                      ).addClass("rrzelegal-hide"));
                    });
                },
                P = function () {
                    o(document).on(
                        "click",
                        n + "[name^='cookies']",
                        function () {
                            (f = !0),
                                this.checked &&
                                    (o(
                                        s +
                                            "[value='" +
                                            this.dataset.cookieGroup +
                                            "']"
                                    )
                                        .prop("checked", !0)
                                        .trigger("change"),
                                    o(
                                        "#RRZELegalBanner [data-rrzelegal-cookie-group='" +
                                            this.dataset.cookieGroup +
                                            "']"
                                    ).length &&
                                        o(
                                            "#RRZELegalBanner [data-rrzelegal-cookie-group='" +
                                                this.dataset.cookieGroup +
                                                "']"
                                        ).removeClass("rrzelegal-hide"));
                        }
                    );
                },
                N = function () {
                    !0 === this.checked
                        ? (o(this)
                              .parent()
                              .parent()
                              .children(c)
                              .children()
                              .last()
                              .css("display", "none"),
                          o(this)
                              .parent()
                              .parent()
                              .children(c)
                              .children()
                              .first()
                              .css("display", "inline-block"))
                        : (o(this)
                              .parent()
                              .parent()
                              .children(c)
                              .children()
                              .first()
                              .css("display", "none"),
                          o(this)
                              .parent()
                              .parent()
                              .children(c)
                              .children()
                              .last()
                              .css("display", "inline-block"));
                },
                H = function () {
                    var t = { essential: e.cookies.essential },
                        i = o(s + ":checked"),
                        a = o(n + "[name^='cookies']:checked");
                    if (
                        (i.length &&
                            (i.each(function () {
                                this.value.length &&
                                    new RegExp(/^[a-z-_]{3,}$/).test(
                                        this.value
                                    ) &&
                                    "essential" !== this.value &&
                                    (t[this.value] = []);
                            }),
                            a.length &&
                                a.each(function () {
                                    this.value.length &&
                                        "string" ==
                                            typeof this.dataset.cookieGroup &&
                                        new RegExp(/^[a-z-_]{3,}$/).test(
                                            this.value
                                        ) &&
                                        new RegExp(/^[a-z-_]{3,}$/).test(
                                            this.dataset.cookieGroup
                                        ) &&
                                        t[this.dataset.cookieGroup].push(
                                            this.value
                                        );
                                })),
                        Object.keys(v.consents).length)
                    )
                        for (var c in v.consents)
                            if (void 0 !== t[c])
                                for (var r in v.consents[c])
                                    -1 === t[c].indexOf(v.consents[c][r]) &&
                                        K(l);
                            else if (void 0 !== u[c]) for (var l in u[c]) K(l);
                    if (Object.keys(v.consents).length)
                        for (var c in v.consents)
                            if (void 0 !== t[c])
                                for (var r in v.consents[c])
                                    -1 === t[c].indexOf(v.consents[c][r]) &&
                                        Q(c, v.consents[c][r]);
                            else if (void 0 !== u[c])
                                for (var l in u[c]) Q(c, l);
                    J(t, !1),
                        "1" !== e.reloadAfterConsent &&
                            (V(),
                            F(),
                            Y(),
                            document.dispatchEvent(m.codeUnblockedAfterConsent),
                            document.dispatchEvent(m.codeUnblocked));
                },
                R = function () {
                    var o = !1;
                    return (
                        "string" == typeof v.version &&
                            (v.version === e.cookieVersion
                                ? (o = !0)
                                : (v.consents = {})),
                        o
                    );
                },
                G = function () {
                    if (document.cookie.length)
                        for (
                            var o = document.cookie.split(";"), t = 0;
                            t < o.length;
                            t++
                        )
                            try {
                                o[t] = decodeURIComponent(o[t]);
                                var n = o[t].split("="),
                                    i = void 0 !== n[0] ? n[0].trim() : "",
                                    a = void 0 !== n[1] ? n[1].trim() : "";
                                if (i === consentCookieName) {
                                    var s = JSON.parse(decodeURIComponent(a));
                                    void 0 !== s.domainPath
                                        ? s.domainPath === e.cookieDomain + e.cookiePath && (v = s)
                                        : (v = s);
                                }
                            } catch (e) {
                                console.log("The cookie is spoiled:"),
                                    console.dir(o[t]),
                                    console.dir(e);
                            }
                    return v;
                },
                J = function (o, t) {
                    var n = {},
                        i = "",
                        a = [],
                        s = e.cookieLifetime;
                    if (
                        (1 === Object.keys(o).length &&
                            void 0 !== e.cookieLifetimeEssentialOnly &&
                            (s = e.cookieLifetimeEssentialOnly),
                        !1 === t || !1 === R())
                    ) {
                        var c = new Date();
                        c.setTime(
                            c.getTime() + 24 * parseInt(s) * 60 * 60 * 1e3
                        ),
                            (i = c.toUTCString());
                    } else i = v.expires;
                    (n.consents = o),
                        (n.domainPath = e.cookieDomain + e.cookiePath),
                        (n.expires = i),
                        (n.uid = v.uid),
                        (n.version = e.cookieVersion),
                        void 0 === n.consents.essential && (n.consents.essential = e.cookies.essential);
                    var r = !0;
                    1 === Object.keys(o).length
                        ? ((n.uid = "anonymous"),
                          ("anonymous" !== v.uid ||
                              1 === Object.keys(v.consents).length) &&
                              (r = !1))
                        : "anonymous" === v.uid
                        ? ((n.uid = $()),
                          1 === Object.keys(v.consents).length && (r = !1))
                        : "anonymous" !== v.uid && (r = !1),
                        a.push(consentCookieName + "=" + encodeURIComponent(JSON.stringify(n))),
                        "" !== e.cookieDomain &&
                            "" === e.automaticCookieDomainAndPath &&
                            a.push("domain=" + e.cookieDomain),
                        a.push("path=" + e.cookiePath),
                        a.push("expires=" + i),
                        a.push("SameSite=Lax"),
                        e.cookieSecure && a.push("secure"),
                        (document.cookie = a.join(";")),
                        G(),
                        no(r),
                        ao(r),
                        document.dispatchEvent(m.consentSaved);
                },
                M = function (o, t, n) {
                    var i,
                        a = "",
                        s = [],
                        c = "",
                        r = new Date();
                    return (
                        null == t || !1 === t
                            ? "" !== e.cookieDomain &&
                              "" === e.automaticCookieDomainAndPath &&
                              (a = e.cookieDomain)
                            : (a = t),
                        void 0 === n && (n = !1),
                        (i = (function (o, e) {
                            var t = { name: "", value: "" };
                            if (
                                (void 0 === e && (e = !1),
                                e && (o = o.replace("*", "")),
                                document.cookie.length)
                            )
                                for (
                                    var n = document.cookie.split(";"), i = 0;
                                    i < n.length;
                                    i++
                                )
                                    try {
                                        n[i] = decodeURIComponent(n[i]);
                                        var a = n[i].split("="),
                                            s =
                                                void 0 !== a[0]
                                                    ? a[0].trim()
                                                    : "",
                                            c =
                                                void 0 !== a[1]
                                                    ? a[1].trim()
                                                    : "";
                                        e
                                            ? -1 !== s.indexOf(o) &&
                                              ((t.name = s), (t.value = c))
                                            : s === o &&
                                              ((t.name = s), (t.value = c));
                                    } catch (o) {
                                        console.log("The cookie is spoiled:"),
                                            console.dir(n[i]),
                                            console.dir(o);
                                    }
                            return t;
                        })(o, n)),
                        i.name.length &&
                            (s.push(i.name + "="),
                            "" !== a && s.push("domain=" + a),
                            s.push("path=" + e.cookiePath),
                            r.setTime(r.getTime() - 864e5),
                            (c = r.toUTCString()),
                            s.push("expires=" + c),
                            (document.cookie = s.join(";"))),
                        !0
                    );
                },
                z = function (o, e) {
                    return (
                        (function (o, e) {
                            "string" == typeof o &&
                                o.length &&
                                new RegExp(/^[a-z-_]{3,}$/).test(o) &&
                                (void 0 === v.consents[o] &&
                                    (v.consents[o] = []),
                                "string" == typeof e &&
                                    new RegExp(/^[a-z-_]{3,}$/).test(e) &&
                                    -1 === v.consents[o].indexOf(e) &&
                                    v.consents[o].push(e)),
                                J(v.consents, !0);
                        })(o, e),
                        !0
                    );
                },
                W = function (o, e) {
                    return (
                        (function (o, e) {
                            if (
                                "string" == typeof o &&
                                o.length &&
                                new RegExp(/^[a-z-_]{3,}$/).test(o)
                            ) {
                                var t = !0;
                                "string" == typeof e &&
                                    new RegExp(/^[a-z-_]{3,}$/).test(e) &&
                                    void 0 !== v.consents[o] &&
                                    -1 !== v.consents[o].indexOf(e) &&
                                    (v.consents[o].splice(
                                        v.consents[o].indexOf(e),
                                        1
                                    ),
                                    (t = !1)),
                                    !0 === t &&
                                        void 0 !== v.consents[o] &&
                                        delete v.consents[o];
                            }
                            Object.keys(v.consents).length && J(v.consents, !0);
                        })(o, e),
                        K(e),
                        Q(o, e),
                        !0
                    );
                },
                q = function (o) {
                    var e = !1;
                    for (var t in v.consents)
                        -1 !== v.consents[t].indexOf(o) && (e = !0);
                    return e;
                },
                $ = function () {
                    function o() {
                        var o = "";
                        if ("object" == typeof window.crypto) {
                            var e = 0,
                                t = new Uint32Array(4);
                            for (window.crypto.getRandomValues(t); e < 4; e++)
                                o += "abcdefhgihjklmnopqrstuvwxyz0123456789"[
                                    t[e] % 37
                                ];
                        } else
                            o = Math.floor(65536 * (1 + Math.random()))
                                .toString(16)
                                .substring(1);
                        return o;
                    }
                    return (
                        o() +
                        o() +
                        "-" +
                        o() +
                        o() +
                        "-" +
                        o() +
                        o() +
                        "-" +
                        o() +
                        o()
                    );
                },
                V = function () {
                    for (var e in v.consents)
                        for (var n in v.consents[e]) {
                            var i = v.consents[e][n];
                            void 0 !== u[e] &&
                                void 0 !== u[e][i] &&
                                void 0 !== u[e][i].optInJS &&
                                (o("body").append(to(u[e][i].optInJS)),
                                (u[e][i].optInJS = ""));
                        }
                },
                F = function () {
                    o("[data-rrzelegal-cookie-type='cookie-group']").each(
                        function () {
                            if (
                                void 0 !==
                                v.consents[this.dataset.rrzelegalCookieId]
                            ) {
                                var t;
                                (t =
                                    "javascript" === e.bannerIntegration
                                        ? to(this.firstChild.innerHTML)
                                        : to(this.innerHTML)),
                                    o(this).prev().length
                                        ? o(this).prev().after(t)
                                        : o(this).parent().prepend(t),
                                    this.parentNode.removeChild(this);
                            }
                        }
                    );
                },
                Y = function () {
                    o("[data-rrzelegal-cookie-type='cookie']").each(
                        function () {
                            for (var t in v.consents)
                                if (
                                    -1 !==
                                    v.consents[t].indexOf(
                                        this.dataset.rrzelegalCookieId
                                    )
                                ) {
                                    var n;
                                    (n =
                                        "javascript" === e.bannerIntegration
                                            ? to(this.firstChild.innerHTML)
                                            : to(this.innerHTML)),
                                        o(this).prev().length
                                            ? o(this).prev().after(n)
                                            : o(this).parent().prepend(n),
                                        this.parentNode.removeChild(this);
                                }
                        }
                    );
                },
                K = function (o) {
                    null === x && ((x = []), (C = !0)), x.push(o);
                },
                Q = function (e, t) {
                    if (
                        void 0 !== u[e] &&
                        void 0 !== u[e][t] &&
                        void 0 !== u[e][t].optOutJS
                    ) {
                        var n = to(u[e][t].optOutJS);
                        (void 0 !== u[e][t].settings.asyncOptOutCode &&
                            "1" === u[e][t].settings.asyncOptOutCode) ||
                            (n +=
                                "<script>window.RRZELegal.optOutDone('" +
                                t +
                                "')</script>"),
                            o("body").append(n),
                            (u[e][t].optOutJS = "");
                    }
                },
                Z = function (t) {
                    t.preventDefault();
                    var n,
                        i,
                        a = o(this).parents(".RRZELegal"),
                        s = !1;
                    if (
                        ((n = a.find(
                            "[data-rrzelegal-cookie-type='content-blocker']"
                        )[0].dataset.rrzelegalCookieId))
                    )
                        for (var c in (o(
                            "[data-rrzelegal-cookie-type='content-blocker'][data-rrzelegal-cookie-id='" +
                                n +
                                "']"
                        ).each(function () {
                            X(o(this).parents(".RRZELegal"));
                        }),
                        e.cookies))
                            -1 !== e.cookies[c].indexOf(n) && z(c, n);
                    else X(a);
                },
                X = function (o) {
                    var t = o.find(
                            "[data-rrzelegal-cookie-type='content-blocker']"
                        ),
                        n = "";
                    if (t.length) {
                        var i;
                        (n = t[0].dataset.rrzelegalCookieId),
                            void 0 !==
                                b[n].settings
                                    .executeGlobalCodeBeforeUnblocking &&
                                "1" ===
                                    b[n].settings
                                        .executeGlobalCodeBeforeUnblocking &&
                                void 0 === h[n] &&
                                (b[n].global(b[n]), (h[n] = !0)),
                            (i =
                                "javascript" === e.bannerIntegration
                                    ? to(t[0].firstChild.innerHTML)
                                    : to(t[0].innerHTML));
                        var a = setInterval(function () {
                            var e = !0;
                            if (void 0 !== k[n]) {
                                var t;
                                if (void 0 !== k[n].scriptBlockerId)
                                    for (t in k[n].scriptBlockerId)
                                        !0 !==
                                            eo(
                                                k[n].scriptBlockerId[t],
                                                "scriptBlockerId"
                                            ) && (e = !1);
                                if (void 0 !== k[n].scriptBlockerId)
                                    for (t in k[n].jsHandle)
                                        !0 !==
                                            eo(k[n].jsHandle[t], "jsHandle") &&
                                            (e = !1);
                            }
                            !0 === e &&
                                (clearInterval(a),
                                o.prev().length
                                    ? o.prev().after(i)
                                    : o.parent().prepend(i),
                                (void 0 !==
                                    b[n].settings
                                        .executeGlobalCodeBeforeUnblocking &&
                                    "0" !==
                                        b[n].settings
                                            .executeGlobalCodeBeforeUnblocking) ||
                                    (void 0 === h[n] &&
                                        (b[n].global(b[n]), (h[n] = !0))),
                                b[n].init(o.prev()[0], b[n]),
                                o[0].parentNode.removeChild(o[0]));
                        }, 50);
                    }
                },
                oo = function (e, t, n) {
                    var i = o(e)[0];
                    if (void 0 !== i) {
                        var a = document.createElement("script");
                        if (
                            ("" !== i.id && (a.id = i.id),
                            "" !== i.className && (a.className = i.className),
                            "" !== i.dataset)
                        )
                            for (var s in i.dataset)
                                if (-1 === s.indexOf("rrzelegal")) {
                                    var c = s.split(/(?=[A-Z])/);
                                    for (var r in c)
                                        c[r] = "-" + c[r].toLowerCase();
                                    a.setAttribute(
                                        "data" + c.join(""),
                                        i.dataset.hasOwnProperty(s)
                                    );
                                }
                        "string" == typeof i.dataset.rrzelegalScriptBlockerSrc
                            ? ((a.src = i.dataset.rrzelegalScriptBlockerSrc),
                              (a.onload = function () {
                                  p[n][t]--, oo(e, t, n);
                              }),
                              i.parentNode.insertBefore(a, i),
                              i.parentNode.removeChild(i))
                            : ((a.type = "text/javascript"),
                              (a.innerHTML = i.innerHTML),
                              i.parentNode.insertBefore(a, i),
                              i.parentNode.removeChild(i),
                              p[n][t]--,
                              oo(e, t, n));
                    }
                    return !0;
                },
                eo = function (o, e) {
                    var t = !1;
                    return void 0 !== p[e][o] && 0 === p[e][o] && (t = !0), t;
                },
                to = function (o) {
                    return decodeURIComponent(
                        Array.prototype.map
                            .call(window.atob(o), function (o) {
                                return (
                                    "%" +
                                    ("00" + o.charCodeAt(0).toString(16)).slice(
                                        -2
                                    )
                                );
                            })
                            .join("")
                    );
                },
                no = function (t) {
                    !1 ===
                        /bot|googlebot|crawler|spider|robot|crawling|lighthouse/i.test(
                            navigator.userAgent.toLowerCase()
                        ) &&
                        o
                            .ajax(e.ajaxURL, {
                                type: "POST",
                                data: {
                                    action: "banner_log_handler",
                                    type: "log",
                                    cookieData: v,
                                    essentialStatistic: t,
                                },
                            })
                            .done(function () {
                                e.reloadAfterConsent &&
                                    Object.keys(v.consents).length > 0 &&
                                    location.reload(!0),
                                    C && bo();
                            });
                },
                io = function () {
                    o.ajax(e.ajaxURL, {
                        type: "POST",
                        data: {
                            action: "banner_log_handler",
                            type: "consent_history",
                            uid: v.uid,
                        },
                    }).done(function (e) {
                        (e = o.parseJSON(e)).length &&
                            o.each(e, function (e, t) {
                                o("[" + l + "] table").append(
                                    "<tr><td>" +
                                        t.stamp +
                                        "</td><td>" +
                                        t.version +
                                        "</td><td>" +
                                        t.consent +
                                        "</td></tr>"
                                );
                            });
                    });
                },
                ao = function (t) {
                    if (e.crossDomainCookie.length)
                        for (var n in e.crossDomainCookie) {
                            var i = e.crossDomainCookie[n];
                            o("body").append(
                                '<iframe class="rrzelegal-hide" src="' +
                                    i +
                                    "?cookieData=" +
                                    encodeURIComponent(JSON.stringify(v)) +
                                    "&essentialStatistic=" +
                                    (t ? 1 : 0) +
                                    '"></iframe>'
                            );
                        }
                },
                so = function () {
                    o(".RRZELegal [name^='rrzelegalCookie']").each(function () {
                        q(this.value)
                            ? (this.checked = !0)
                            : (this.checked = !1),
                            o(this).trigger("change");
                    }),
                        o(document).on(
                            "change",
                            ".RRZELegal [name^='rrzelegalCookie']",
                            function () {
                                this.checked
                                    ? z(this.dataset.cookieGroup, this.value)
                                    : W(this.dataset.cookieGroup, this.value);
                            }
                        );
                },
                bo = function () {
                    (C = !1),
                        0 === x.length &&
                            ((x = null),
                            e.reloadAfterOptOut && window.location.reload());
                };
            return {
                addConsent: z,
                allocateScriptBlockerToContentBlocker: function (o, e, t) {
                    ("scriptBlockerId" !== t && "jsHandle" !== t) ||
                        (void 0 === k[o] &&
                            (k[o] = { scriptBlockerId: [], jsHandle: [] }),
                        -1 === k[o][t].indexOf(e) && k[o][t].push(e));
                },
                callWhenLoaded: function (o, e) {
                    var t = function () {
                        !0 === window.hasOwnProperty(o)
                            ? e(e)
                            : window.setTimeout(t, 1e3);
                    };
                    t();
                },
                checkCookieConsent: q,
                checkCookieGroupConsent: function (o) {
                    var e = !1;
                    return void 0 !== v.consents[o] && (e = !0), e;
                },
                deleteCookie: M,
                getCookie: G,
                hideBanner: j,
                init: function (n, i, c, h) {
                    return (
                        "about:blank" !== window.location.href &&
                        ((e = o.extend(
                            {
                                ajaxURL: "",
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
                                cookieSecure: !0,
                                cookieLifetime: "365",
                                crossDomainCookie: [],
                                cookieBeforeConsent: "",
                                cookiesForBots: "1",
                                cookieVersion: "1",
                                hideBannerOnPages: [],
                                respectDoNotTrack: "",
                                reloadAfterConsent: "",
                                reloadAfterOptOut: "",
                                showBanner: "1",
                                bannerIntegration: "javascript",
                                ignorePreSelectStatus: "1",
                                cookies: [],
                            },
                            n
                        )),
                        (t = h),
                        (u = i),
                        (b = c),
                        (m.consentSaved = document.createEvent("Event")),
                        m.consentSaved.initEvent(
                            "rrzelegal-cookie-consent-saved",
                            !0,
                            !0
                        ),
                        (m.codeUnblocked = document.createEvent("Event")),
                        m.codeUnblocked.initEvent(
                            "rrzelegal-cookie-code-unblocked",
                            !0,
                            !0
                        ),
                        (m.codeUnblockedAfterConsent =
                            document.createEvent("Event")),
                        m.codeUnblockedAfterConsent.initEvent(
                            "rrzelegal-cookie-code-unblocked-after-consent",
                            !0,
                            !0
                        ),
                        G(),
                        (function () {
                            if (Object.keys(u).length)
                                for (var o in u)
                                    if (Object.keys(u[o]).length)
                                        for (var e in u[o])
                                            if (
                                                void 0 !== u[o][e].settings &&
                                                void 0 !== u[o][e].settings.blockCookiesBeforeConsent &&
                                                "1" === u[o][e].settings.blockCookiesBeforeConsent &&
                                                void 0 !== u[o][e].cookieNameList &&
                                                !1 === q(e)
                                            )
                                                for (var t in u[o][e].cookieNameList)
                                                    M(
                                                        t,
                                                        null,
                                                        -1 !== t.indexOf("*")
                                                    ),
                                                        M(
                                                            t,
                                                            "",
                                                            -1 !==
                                                                t.indexOf("*")
                                                        );
                        })(),
                        e.cookieBeforeConsent &&
                            ((null !== v.uid && "anonymous" !== v.uid) ||
                                (v.uid = $()),
                            "function" == typeof Object &&
                                (void 0 === Object.entries &&
                                    (Object.entries = function (o) {
                                        for (
                                            var e = Object.keys(o),
                                                t = e.length,
                                                n = new Array(t);
                                            t--;

                                        )
                                            n[t] = [e[t], o[e[t]]];
                                        return n;
                                    }),
                                0 === Object.entries(v.consents).length &&
                                    null === v.expires &&
                                    ((function () {
                                        var o = {},
                                            t = [],
                                            n = e.cookieLifetimeEssentialOnly,
                                            i = new Date();
                                        i.setTime(i.getTime() + 24 * parseInt(n) * 60 * 60 * 1e3);
                                        var a = i.toUTCString();
                                        (o.consents = {}),
                                            (o.domainPath = e.cookieDomain + e.cookiePath),
                                            (o.expires = a),
                                            (o.uid = v.uid),
                                            (o.version = null),
                                            t.push(consentCookieName + "=" + encodeURIComponent(JSON.stringify(o))),
                                            "" !== e.cookieDomain &&
                                                "" === e.automaticCookieDomainAndPath &&
                                                t.push("domain=" + e.cookieDomain),
                                            t.push("path=" + e.cookiePath),
                                            t.push("expires=" + a),
                                            t.push("SameSite=Lax"),
                                            e.cookieSecure && t.push("secure"),
                                            (document.cookie = t.join(";")),
                                            G();
                                    })(),
                                    no(!0)))),
                        o(a).length &&
                            "SCRIPT" === o(a)[0].tagName &&
                            o(a).after(o(a).html()),
                        o(document).on(
                            "click",
                            "[data-cookie-accept]",
                            function (o) {
                                o.preventDefault(), H(), j();
                            }
                        ),
                        o(document).on(
                            "click",
                            "[data-cookie-accept-all]",
                            function (o) {
                                o.preventDefault(), E();
                            }
                        ),
                        o(document).on(
                            "click",
                            "[data-cookie-back]",
                            function (o) {
                                o.preventDefault(), D();
                            }
                        ),
                        o(document).on(
                            "click",
                            "[data-cookie-individual]",
                            function (o) {
                                o.preventDefault(), I();
                            }
                        ),
                        o(document).on(
                            "click",
                            "[data-cookie-refuse]",
                            function (e) {
                                e.preventDefault();
                                var t = [];
                                o(s + ":checked").each(function () {
                                    -1 === t.indexOf(this.value) &&
                                        (o(this).trigger("click"),
                                        t.push(this.value));
                                }),
                                    H(),
                                    j();
                            }
                        ),
                        A(),
                        U(),
                        P(),
                        !0 === R()
                            ? (V(),
                              F(),
                              Y(),
                              document.dispatchEvent(m.codeUnblocked),
                              O())
                            : e.showBanner
                            ? 0 === e.hideBannerOnPages.length ||
                              -1 ===
                                  e.hideBannerOnPages.indexOf(
                                      window.location.protocol +
                                          "//" +
                                          window.location.host +
                                          window.location.pathname
                                  )
                                ? e.cookiesForBots &&
                                  /bot|googlebot|crawler|spider|robot|crawling|lighthouse/i.test(
                                      navigator.userAgent.toLowerCase()
                                  )
                                    ? (J(e.cookies, !1),
                                      V(),
                                      F(),
                                      Y(),
                                      O(),
                                      document.dispatchEvent(m.codeUnblocked))
                                    : e.respectDoNotTrack &&
                                      void 0 !== navigator.doNotTrack &&
                                      "1" === navigator.doNotTrack
                                    ? (J(
                                          { essential: e.cookies.essential },
                                          !1
                                      ),
                                      V(),
                                      F(),
                                      Y(),
                                      O(),
                                      document.dispatchEvent(m.codeUnblocked))
                                    : S(!0)
                                : (V(),
                                  F(),
                                  Y(),
                                  O(),
                                  document.dispatchEvent(m.codeUnblocked))
                            : O(),
                        o(document).on(
                            "click",
                            "[data-rrzelegal-cookie-unblock]",
                            Z
                        ),
                        o(document).on(
                            "click",
                            "[data-rrzelegal-cookie-preference]",
                            T
                        ),
                        o(document).on(
                            "click",
                            ".rrzelegal-cookie-preference",
                            T
                        ),
                        o(document).on(
                            "change",
                            "[data-rrzelegal-cookie-switch]",
                            N
                        ),
                        o(document).on("keydown", function (e) {
                            9 === e.keyCode &&
                                o(d).addClass("_rrzelegal-keyboard");
                        }),
                        o(document).on("mousedown", function (e) {
                            o(d).is(":visible") &&
                                o(d).removeClass("_rrzelegal-keyboard");
                        }),
                        o("body").on(
                            "focus",
                            ".RRZELegal._rrzelegal-keyboard input[type='checkbox']",
                            function (e) {
                                var t = o(e.currentTarget).closest("label");
                                t && o(t).addClass("_rrzelegal-focused");
                            }
                        ),
                        o(d).on("blur", "input[type='checkbox']", function (e) {
                            var t = o(e.currentTarget).closest("label");
                            t && o(t).removeClass("_rrzelegal-focused");
                        }),
                        o("[" + l + "]").length && io(),
                        so(),
                        o("[" + r + "]").length &&
                            o("[" + r + "]").each(function () {
                                o(this).html(v.uid);
                            }),
                        !0)
                    );
                },
                initConsentHistoryTable: io,
                initSwitchConsentButtonStatus: so,
                openCookiePreference: T,
                removeConsent: W,
                showBanner: S,
                unblockContentId: function (e) {
                    o(
                        "[data-rrzelegal-cookie-type='content-blocker'][data-rrzelegal-cookie-id='" +
                            e +
                            "']"
                    ).each(function () {
                        X(o(this).parents(".RRZELegal"));
                    });
                },
                unblockScriptBlockerId: function (e) {
                    var t = "[data-rrzelegal-script-blocker-id='" + e + "']";
                    return (
                        o(t).length &&
                            ((p.scriptBlockerId[e] = o(t).length),
                            oo(t, e, "scriptBlockerId")),
                        !0
                    );
                },
                unblockScriptBlockerJSHandle: function (e) {
                    var t =
                        "[data-rrzelegal-script-blocker-js-handle='" + e + "']";
                    return (
                        o(t).length &&
                            ((p.jsHandle[e] = o(t).length),
                            oo(t, e, "jsHandle")),
                        !0
                    );
                },
                optOutDone: function (o) {
                    null !== x
                        ? x.length !==
                          (x = x.filter(function (e, t, n) {
                              return e !== o;
                          })).length
                            ? 0 !== x.length ||
                              C ||
                              ((x = null),
                              e.reloadAfterOptOut && window.location.reload())
                            : console.log(
                                  'No opt out found for cookie "' + o + '"'
                              )
                        : console.log("No opt out has been initialized");
                },
            };
        })());
})();
