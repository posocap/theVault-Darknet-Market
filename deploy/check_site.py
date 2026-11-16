#!/usr/bin/env python3
"""Smoke test for a running theVault site: crawls the public pages (GET only) and
reports every HTTP error and every broken image/CSS/JS/font reference.

Usage: python3 deploy/check_site.py https://your.domain   (exit code 1 on any problem)"""
import re, sys, urllib.request, urllib.parse, urllib.error, collections

BASE = sys.argv[1].rstrip("/")
HOST = urllib.parse.urlparse(BASE).netloc
SAFE = re.compile(r"^/(?:$|category/|product/[0-9a-f-]+(?:/(?:delivery|feedback|rules|vendor))?$|vendor/|search|login$|register|terms|rules|faq|support|about|forgotpassword)")
BAD = re.compile(r"logout|delete|remove|clear|buy|purchase|checkout|wishlist|cart|profile|admin|confirm|become", re.I)

def fetch(url):
    req = urllib.request.Request(url, headers={"User-Agent": "vault-crawler"})
    try:
        with urllib.request.urlopen(req, timeout=60) as r:
            return r.status, r.headers.get("content-type", ""), r.read()
    except urllib.error.HTTPError as e:
        return e.code, e.headers.get("content-type", ""), e.read()
    except Exception as e:
        return 0, str(e), b""

def norm(u, page):
    u = urllib.parse.urljoin(page, u.split("#")[0])
    p = urllib.parse.urlparse(u)
    return u if p.scheme in ("http", "https") else None

pages, assets = collections.OrderedDict(), {}
placeholders = []
queue = [BASE + "/"]
seen = set(queue)
while queue:
    url = queue.pop(0)
    status, ctype, body = fetch(url)
    pages[url] = status
    if status != 200 or "html" not in ctype:
        continue
    html = body.decode("utf-8", "replace")
    if "img/product.png" in html:
        placeholders.append(url)   # a listing shows the grey "no picture" fallback
    for m in re.finditer(r'(?:src|href|data-src|poster)\s*=\s*["\']([^"\']+)["\']', html, re.I):
        raw = m.group(1)
        if raw.startswith(("javascript:", "mailto:", "data:", "#")):
            continue
        u = norm(raw, url)
        if not u:
            continue
        p = urllib.parse.urlparse(u)
        is_asset = re.search(r"\.(png|jpe?g|gif|webp|svg|ico|css|js|woff2?|ttf)(\?|$)", p.path, re.I) or "/storage/" in p.path
        if is_asset:
            assets.setdefault(u, url)
        elif p.netloc == HOST and SAFE.match(p.path) and not BAD.search(p.path) and u not in seen:
            seen.add(u); queue.append(u)
    for m in re.finditer(r'url\(["\']?([^)"\']+)["\']?\)', html):
        u = norm(m.group(1), url)
        if u and not m.group(1).startswith("data:"):
            assets.setdefault(u, url)

# also follow url() inside same-host CSS files
for u in [a for a in list(assets) if re.search(r"\.css(\?|$)", a)]:
    st, ct, b = fetch(u)
    for m in re.finditer(r'url\(["\']?([^)"\']+)["\']?\)', b.decode("utf-8", "replace")):
        if m.group(1).startswith("data:"):
            continue
        v = norm(m.group(1), u)
        if v:
            assets.setdefault(v, u)

bad_pages = {u: s for u, s in pages.items() if s != 200}
bad_assets = []
for u, src in assets.items():
    if urllib.parse.urlparse(u).netloc != HOST:
        continue
    st, ct, b = fetch(u)
    wants_image = re.search(r"\.(png|jpe?g|gif|webp)(\?|$)", u, re.I) or "/storage/" in u
    if st != 200 or (wants_image and not ct.startswith("image/")) or len(b) == 0:
        bad_assets.append((st, ct, u, src))

print(f"pages crawled: {len(pages)}   assets checked: {len(assets)}")
print(f"BAD PAGES: {len(bad_pages)}")
for u, s in bad_pages.items():
    print("  ", s, u)
print(f"BAD ASSETS: {len(bad_assets)}")
for st, ct, u, src in bad_assets:
    print("  ", st, u, "<- on", src)
print(f"PAGES SHOWING THE 'NO PICTURE' PLACEHOLDER: {len(placeholders)}")
for u in placeholders:
    print("  ", u)
sys.exit(1 if bad_pages or bad_assets or placeholders else 0)
