/*!
 * Footer ad rotator — cross-promotes your sites. Drop on every site (add data-position="fixed" to the div on full-screen pages):
 *   <div id="footer-ads"></div>
 *   <script src="/footer-ads/footer-ad-rotator.js" defer></script>
 * Host the JPEGs at BASE (default /footer-ads/) on each site, or set data-base="https://host/footer-ads/" on the <script> tag to load them from another site.
 * The banner for the current site is skipped automatically.
 */
(function () {
  var cs = document.currentScript;
  var BASE = (cs && cs.getAttribute("data-base")) || "/footer-ads/";          // change to e.g. "https://peikoff.net/footer-ads/" to share one copy
  var INTERVAL_MS = 5000;
  var FADE_MS = 600;
  var ADS = [
    { host: "peikoff.net",        img: "peikoff-net.jpg",        alt: "peikoff.net — free browser game" },
    { host: "asonpeikoff.com",    img: "asonpeikoff-com.jpg",    alt: "Ason Peikoff — data science, AI/ML and writing" },
    { host: "barbarapeikoff.com", img: "barbarapeikoff-com.jpg", alt: "Barbara Peikoff — books, course, film" },
    { host: "postsocialist.com",  img: "postsocialist-com.jpg",  alt: "Post-Socialist Capitalism — Postsocialist Press" },
    { host: "posocap.com",        img: "posocap-com.jpg",        alt: "Atlas of Objectivism — interactive map", off: true }, // posocap.com retired: delete ", off: true" to re-enable
    { host: "mycityforum.com",   img: "mycityforum-com.jpg",   alt: "City Forum — chat and encrypted video meetings" },
    { host: "ason.pw",           img: "ason-pw.jpg",            alt: "ason.pw — an isometric room of Ayn Rand posters and Objectivist websites" },
    { host: "ainrand.com",       img: "ainrand-com.jpg",        alt: "AI Ayn Rand — an Objectivist knowledge agent, on the record" },
    { host: "nooiq.co",           img: "nooiq-co.jpg",           alt: "nooiq.co", off: true }, // under maintenance: delete ", off: true" to re-enable
    { host: "the-vault.website",  img: "the-vault-website.jpg",  alt: "The Vault — the-vault.website" },
    { host: "pagepublishing.com", href: "http://www.pagepublishing.com/books/?book=post-socialist-capitalism", img: "book-post-socialist-capitalism.jpg", featured: true, alt: "Post-Socialist Capitalism by Ason Peikoff Rand — new book, out now" },
    { host: "whoneeds.it",        img: "whoneeds-it-com.jpg",    alt: "Who Needs It? — Objectivism quiz at whoneeds.it" }
  ];

  var here = location.hostname.replace(/^www\./, "");
  // An entry with featured: true (the book launch) is shown every other slot: book, site, book, site...
  // Delete "featured: true" to go back to a plain rotation.
  var others = ADS.filter(function (a) { return !a.off && !a.featured && here !== a.host && !here.endsWith("." + a.host); });
  var feat = ADS.filter(function (a) { return a.featured && !a.off; })[0];
  var list = others;
  if (feat) { list = []; others.forEach(function (a) { list.push(feat, a); }); if (!list.length) list = [feat]; }
  if (!list.length) return;
  // random start so every page doesn't open on the same ad
  var i = Math.floor(Math.random() * list.length);
  if (feat && list[i] !== feat) i = (i + list.length - 1) % list.length;   // open on the book

  var box = document.getElementById("footer-ads");
  if (!box) { box = document.createElement("div"); box.id = "footer-ads"; document.body.appendChild(box); }
  box.setAttribute("role", "complementary");
  box.setAttribute("aria-label", "Sister sites");
  // data-position="fixed" pins the ad to the bottom of the viewport (for full-screen pages such as ason.pw)
  var fixed = box.getAttribute("data-position") === "fixed";
  box.style.cssText = fixed
    ? "position:fixed;left:50%;bottom:8px;transform:translateX(-50%);width:min(728px,96vw);aspect-ratio:728/90;overflow:hidden;border-radius:4px;z-index:2147483000;cursor:auto;"
    : "max-width:728px;width:100%;margin:24px auto;aspect-ratio:728/90;position:relative;overflow:hidden;border-radius:4px;";
  box.addEventListener("click", function (e) { e.stopPropagation(); });   // don't trigger page-level click handlers

  var links = list.map(function (a, n) {
    var l = document.createElement("a");
    l.href = a.href || ("https://" + a.host + "/?ref=" + encodeURIComponent(here));
    l.target = "_blank"; l.rel = "noopener";
    l.style.cssText = "position:absolute;inset:0;opacity:0;transition:opacity " + FADE_MS + "ms ease;pointer-events:none;";
    var im = document.createElement("img");
    im.src = BASE + a.img; im.alt = a.alt; im.width = 728; im.height = 90; im.loading = n === i ? "eager" : "lazy";
    im.style.cssText = "display:block;width:100%;height:100%;";
    l.appendChild(im); box.appendChild(l); return l;
  });
  var tag = document.createElement("span");
  tag.textContent = "Ad"; tag.style.cssText = "position:absolute;top:2px;right:4px;font:600 9px/1 sans-serif;color:#fff;background:rgba(0,0,0,.45);padding:2px 4px;border-radius:2px;pointer-events:none;";
  box.appendChild(tag);

  function show(n) {
    links.forEach(function (l, k) { var on = k === n; l.style.opacity = on ? "1" : "0"; l.style.pointerEvents = on ? "auto" : "none"; });
  }
  show(i);
  var timer, reduce = window.matchMedia && matchMedia("(prefers-reduced-motion: reduce)").matches;
  function start() { if (!reduce && links.length > 1) timer = setInterval(function () { i = (i + 1) % links.length; show(i); }, INTERVAL_MS); }
  function stop() { clearInterval(timer); }
  box.addEventListener("mouseenter", stop); box.addEventListener("mouseleave", start);
  box.addEventListener("focusin", stop); box.addEventListener("focusout", start);
  start();
})();
