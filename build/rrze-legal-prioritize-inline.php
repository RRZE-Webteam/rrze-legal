<?php

return 'rrzelegalCookiePrioritized = (function() {
  "use strict";
  var e = rrzelegalCookiePrioritized, o = {}, t = false, i = document.createDocumentFragment(), n = { prioritizedCodeUnblocked: null };
  n.prioritizedCodeUnblocked = document.createEvent("Event"), n.prioritizedCodeUnblocked.initEvent(
    "rrzelegal-cookie-prioritized-code-unblocked",
    true,
    true
  );
  var r, d = function(e2) {
    var o2 = e2.split("<script");
    for (var t2 in o2)
      if (-1 !== o2[t2].indexOf("script>")) {
        o2[t2] = "<script" + o2[t2];
        var n2 = document.createElement("div"), r2 = document.createRange();
        r2.selectNodeContents(n2);
        var d2 = r2.createContextualFragment(o2[t2]);
        n2.appendChild(d2), i.appendChild(n2.firstElementChild);
      }
  };
  if (document.cookie.length) {
    for (var a = document.cookie.split(";"), c = 0; c < a.length; c++)
      try {
        a[c] = decodeURIComponent(a[c]);
        var l = a[c].split("="), s = void 0 !== l[0] ? l[0].trim() : "", p = void 0 !== l[1] ? l[1].trim() : "";
        if ((e.cookieName || "rrze-legal-consent") === s) {
          var v = JSON.parse(decodeURIComponent(p));
          void 0 !== v.domainPath && v.domainPath === e.domain + e.path && (o = v);
        }
      } catch (e2) {
        console.log("The cookie is spoiled:"), console.dir(a[c]), console.dir(e2);
      }
    if (e.bots && /bot|googlebot|crawler|spider|robot|crawling/i.test(
      navigator.userAgent.toLowerCase()
    ) && (t = true), Object.keys(o).length > 0 && o.version === e.version && (t = true), t) {
      for (var m in o.consents)
        for (var b in o.consents[m]) {
          var u = o.consents[m][b];
          void 0 !== e.optInJS[m] && void 0 !== e.optInJS[m][u] && (d(
            (r = e.optInJS[m][u], decodeURIComponent(
              Array.prototype.map.call(window.atob(r), function(e2) {
                return "%" + ("00" + e2.charCodeAt(0).toString(16)).slice(-2);
              }).join("")
            ))
          ), delete e.optInJS[m][u]);
        }
      document.getElementsByTagName("head")[0].appendChild(i), document.dispatchEvent(n.prioritizedCodeUnblocked);
    }
  }
  return e;
})(rrzelegalCookiePrioritized);
';
