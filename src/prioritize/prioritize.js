rrzelegalCookiePrioritized = (function () {
    "use strict";
    var e = rrzelegalCookiePrioritized,
        o = {},
        t = !1,
        i = document.createDocumentFragment(),
        n = { prioritizedCodeUnblocked: null };
    (n.prioritizedCodeUnblocked = document.createEvent("Event")),
        n.prioritizedCodeUnblocked.initEvent(
            "rrzelegal-cookie-prioritized-code-unblocked",
            !0,
            !0
        );
    var r,
        d = function (e) {
            var o = e.split("<script");
            for (var t in o)
                if (-1 !== o[t].indexOf("script>")) {
                    o[t] = "<script" + o[t];
                    var n = document.createElement("div"),
                        r = document.createRange();
                    r.selectNodeContents(n);
                    var d = r.createContextualFragment(o[t]);
                    n.appendChild(d), i.appendChild(n.firstElementChild);
                }
        };
    if (document.cookie.length) {
        for (var a = document.cookie.split(";"), c = 0; c < a.length; c++)
            try {
                a[c] = decodeURIComponent(a[c]);
                var l = a[c].split("="),
                    s = void 0 !== l[0] ? l[0].trim() : "",
                    p = void 0 !== l[1] ? l[1].trim() : "";
                if ("rrzelegal-cookie" === s) {
                    var v = JSON.parse(decodeURIComponent(p));
                    void 0 !== v.domainPath &&
                        v.domainPath === e.domain + e.path &&
                        (o = v);
                }
            } catch (e) {
                console.log("The cookie is spoiled:"),
                    console.dir(a[c]),
                    console.dir(e);
            }
        if (
            (e.bots &&
                /bot|googlebot|crawler|spider|robot|crawling/i.test(
                    navigator.userAgent.toLowerCase()
                ) &&
                (t = !0),
            Object.keys(o).length > 0 && o.version === e.version && (t = !0),
            t)
        ) {
            for (var m in o.consents)
                for (var b in o.consents[m]) {
                    var u = o.consents[m][b];
                    void 0 !== e.optInJS[m] &&
                        void 0 !== e.optInJS[m][u] &&
                        (d(
                            ((r = e.optInJS[m][u]),
                            decodeURIComponent(
                                Array.prototype.map
                                    .call(window.atob(r), function (e) {
                                        return (
                                            "%" +
                                            (
                                                "00" +
                                                e.charCodeAt(0).toString(16)
                                            ).slice(-2)
                                        );
                                    })
                                    .join("")
                            ))
                        ),
                        delete e.optInJS[m][u]);
                }
            document.getElementsByTagName("head")[0].appendChild(i),
                document.dispatchEvent(n.prioritizedCodeUnblocked);
        }
    }
    return e;
})(rrzelegalCookiePrioritized);
