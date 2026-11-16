<img src="theVault_community_edition/public/favicon.ico" alt="The Vault logo" style="width: 24px; height: 24px; vertical-align: middle;"> **theVault**

Welcome to **theVault**! This is a prototype darknet vendor store inspired by:
1. <img src="readmeImages/goldhat.png" alt="Goldhat Free Market" style="width: 25px; height: 25px;"> Goldhat Free Market.
2. The 2024 speculative medical thriller ["Baby X" by Kira Peikoff](https://www.barnesandnoble.com/w/baby-x-kira-peikoff/1143604735), which explores a near-future darknet vendor store selling DNA in the wake of advances in fertilization technologies (available on Amazon, Audible, Barnes & Noble, etc.).

<p align="center">

  <strong>LIVE CLEARNET DEMO:</strong> <a href="https://the-vault.website/" target="_blank">https://the-vault.website/</a>

  <img src="readmeImages/screenshot0.png" alt="The Vault Screenshot - Celebrity DNA" style="max-width: 100%; height: auto;">

  <img src="readmeImages/screenshot.png" alt="The Vault Screenshot - Arists Category" style="max-width: 100%; height: auto;">
</p>

Goldhat was a legal darknet market-like store launched in 2020, forked from Eckmar's Marketplace (v1). The project was only launched as a beta (no sales) to avoid potential legal liability. It included a major PHP upgrade (from v5 to v8) as well as security enhancements with significant hardening of Nginx. Eckmar's project was orphaned following the release of PHP8 due to breaking changes. Goldhat restricted user-to-user communications and implemented other safeguards to prevent abuse during its public beta. The project was open-sourced after the project author decided moving forward presented too many legal concerns. theVault integrates changes from v1 to v2 of Eckmar's with some of the Goldhat improvements (more coming) as well as custom bugfixes and upgrades for the latest Laravel and Scout versions. Once again, the demo URLs are as follows:

- Clearnet: <https://the-vault.website/>
- Onion (open in Tor Browser): `http://vaultb5e64ac3vdgl7qhmvhmyfyfgdcsr3skdehjlqtozmul5i3qrayd.onion`

This is a demo only, thus Sign Up and Sign In are disabled, as is the transaction system (checkout, etc.). Sign up is supported in the free version. Cryptocurrency libraries will need updating. Bitcoin will be easiest to integrate, but any currency can be interfaced. The MoneroRPC project originally used for XMR has been archived, although a fork is still maintained. 

**New to Tor?**
If you haven’t used Tor before, get started by downloading the [Tor Browser Bundle](https://www.torproject.org/download/). It’s free, easy to install, and ideal for safely accessing .onion sites:
1. Download and install the Tor Browser Bundle from the link above.
2. Launch the Tor Browser.
3. Paste the provided .onion address into the browser’s address bar for a secure connection to theVault.

For everyday browsing with enhanced privacy and functionality, consider using the [Brave Browser](https://brave.com/). Brave offers better privacy than Chrome, supports modern web features, and is capable of loading .onion addresses.

## 🚀 Project Overview

**theVault** iterates on the foundations laid by Eckmar's Marketplace and Goldhat Free Market, updating the codebase to utilize **Laravel 12** and **PHP 8.3.x** (use a patched PHP release; see the installer for the minimum versions).

This project has been tested on Debian and Fedora. Fedora is recommended on the basis that Debian is cruddy and basic. It probably runs fine on almost any Linux, but you'll need to update the installer commands (change dnf, apt, or your package manager accordingly).

### ⚙️ Features

- **Modern Framework**: Built on Laravel 12 and PHP 8.3.x for enhanced performance and maintainability.
- **Elasticsearch Vectorstore**: Fast searching with the newest Elasticsearch (currently 8.17).
- **OpenJDK**: Updated Java, bundled with Elasticsearch for compatibility.
- **Redis**: Optional Redis 7.2.7 support for improved app performance.

---

## 📦 Installation

### The Vault Community Edition (theVault CE)
Here you will find a fully functional darknet market/store.

#### What's included?

1. A fully functional, darknet-ready market based on PHP 8.3 and Laravel 12.
2. Simulation-only payments (no cryptocurrency daemons required) for demo use.
3. Admin panel.
4. Multi-seller support.
5. Captchas.
6. Semi-automated installation.
7. Quick-start .sql database backup of the demo site (this can be easier than seeder data for bootstrapping).
8. Sample server configuration (nginx).
9. Support for multiple search backends (Elasticsearch for privacy and Algolia for speed; pure SQL is also supported but not recommended).
10. Optional support for Redis.
11. Featured product module (integrated but unimplemented in the demo configuration).
12. Parent and child product categories.
13. Convenience functions for listing vendors and products (top, rising, newest, etc.) primarily using Eloquent.
14. Troubleshooting notes and common administration tasks.
15. Dispute resolution tools.

This version is essentially Eckmar's Marketplace v1 upgraded to PHP8 with some critical bugfixes from Goldhat and the integration of the Featured Products module from EM v2.

#### What's missing?
For $99 you can access additional features not available in theVault CE, including:
1. Load Balancing.
2. Advanced anti-DDoS.
3. Extra security modules.
4. On-market forum.

The following services are available as freelance work:
- Support for any cryptocurrency.
- Walletless payments.
- Server configuration, setup, and support for any Linux distro.
- Pentesting and vulnerability mitigation.

#### Deployment Steps (CE)

**Requirements:** a Linux VM (Rocky/Alma/RHEL/Fedora or Debian/Ubuntu), root access, 2 GB RAM / 1 vCPU / 10 GB disk, and outbound internet. No inbound port is needed for the onion service; ports 80/443 are only used if you also serve the clearnet.

**1. Install (onion service, one command):**

```bash
curl -fsSL https://raw.githubusercontent.com/posocap/theVault-Darknet-Market/main/deploy/provision.sh -o provision.sh
sudo bash provision.sh
```

The script is idempotent and prints your `.onion` address when it finishes (a new onion service can take a few minutes to become reachable in Tor Browser). It:

- installs PHP 8.3 (refusing unpatched builds), nginx, MariaDB, Tor and Composer;
- imports the demo database (`app/example_files/marketplace_backup.sql`) and copies the demo product images (`app/example_files/demo_images/`, including Trace Thorn's portrait) into `storage/app/public`;
- locks the three demo vendor accounts, creates an admin account when possible, and saves generated secrets to `/root/.thevault-credentials`;
- runs Tor client-only (no relay, exit, SOCKS or DNS port) with default obfs4 bridges (`deploy/bridges.txt`) and onion-service DoS defenses;
- binds nginx and MariaDB to localhost and turns nginx access logging off.

**2. Common options** (environment variables, all optional; the full list is at the top of `deploy/provision.sh`):

| Variable | Purpose |
| --- | --- |
| `CLEARNET_DOMAIN=example.com` | also serve the site on the clearnet (separate nginx vhost; use Cloudflare "Full" SSL mode) |
| `HS_KEY_DIR=/path/to/keys` | restore a backed-up onion key to keep an existing `.onion` address |
| `BRIDGE_LINES` / `USE_BRIDGES=no` | use your own obfs4 bridges, or connect to Tor directly |
| `IMPORT_DEMO=no` | start with an empty schema (`php artisan migrate`) instead of the demo data |
| `REPO_REF=<branch or tag>` | deploy something other than `main` |

Example with the clearnet and an existing onion address: `sudo CLEARNET_DOMAIN=example.com HS_KEY_DIR=/root/onion-keys bash provision.sh`

**3. Update later without downtime:**

```bash
sudo bash /opt/thevault-src/deploy/update.sh
```

This pulls the latest code, keeps your `.env`, database and uploads, and only does graceful *reloads* of php-fpm and nginx. Tor and MariaDB are never restarted, so the onion service stays up.

**4. Back up** `/var/lib/tor/thevault` (your onion key, which *is* your address) and `/root/.thevault-credentials`.

**5. Check your install (no broken pages or images):**

```bash
python3 deploy/check_site.py https://your.domain      # or http://127.0.0.1:8080 on the server
```

It crawls every public page and verifies every image, stylesheet, script and font; it exits non-zero if anything returns an error, a file is missing, or a listing is showing the grey "no picture" placeholder.

**Demo images and data**

- Product pictures are referenced as `/storage/<file>` and live in `storage/app/public`. The demo set ships in `app/example_files/demo_images/` and is installed by `provision.sh`/`update.sh` (existing files are never overwritten, so you can replace any picture with your own). Trace Thorn's portrait is the original artwork; the other pictures are free-to-use photos from [Unsplash](https://unsplash.com/license).
- `app/example_files/demo_fixups.sql` (already appended to the demo dump) gives every product a price and shipping option and enables the simulated `stb` coin, so product pages look complete.
- The "Official Mirrors" box on the home page is filled from `MARKETPLACE_MIRRORS` in `.env` (`provision.sh` sets it to your onion address). The "disable JavaScript" warning is only shown to visitors browsing through the `.onion` address.

---

### The Vault Pro
*Features not available in the Community Edition require the Pro version, available for an additional $99.*
- Includes all the CE features.
- Load Balancing support.
- Advanced anti-DDoS.
- Extra security modules.
- On-market forum integration.
- Further customization and enterprise support options (available as freelance work upon request).

*Deployment steps follow similarly with additional security configuration options.*

For additional help with Redis, check out this article:  
https://linuxcapable.com/install-redis-on-fedora-linux/

The process is analogous on Debian (using apt with package names ending with `-dev` instead of Fedora's `-devel`). The installer attempts to directly install Elasticsearch; however, it is recommended to comment that out and utilize either the provided repo file for a dnf/yum-based installation or deploy the Docker container from https://elastic.co. Other installation strategies are available on their website.

        sudo dnf install --enablerepo=elasticsearch elasticsearch

---

## 💼 Freelance Support

I offer freelance support as a maintainer or developer for **theVault**. This includes server configuration and setup support for any Linux distro, in addition to assistance with development, maintenance, and security enhancements. Please note that any work I undertake must be legally compliant. If you require assistance, feel free to reach out!

## 📅 Future Updates

- The project now runs on **Laravel 12**. Forks on Laravel 11 should upgrade to stay current.
- See **Security & Versions** below for what changed and the exact versions.

## 🔒 Security & Versions

**Current stack (November 2026)**

| Component | Version | Notes |
| --- | --- | --- |
| Laravel | 12.72.0 | upgraded from 11.57.1 |
| PHP | 8.3.36 | patched release; the installers reject vulnerable builds. 8.3 receives security fixes until 31 Dec 2027. PHP 8.4 and 8.5 (latest 8.5.12) are supported by Laravel 12 and are the next step |
| phpseclib | 3.0.58 | was 2.0.56, which has published advisories and no patched 2.x release |
| openpgp-php | 0.7.0 | was 0.5.0 (required phpseclib 2.x) |
| paragonie/sodium_compat | 2.5.2 | new: provides libsodium functions without the PHP extension (needed for sign-up/admin keypairs) |
| paragonie/constant_time_encoding | 3.1.4 | |
| lcobucci/jwt | 5.6.1 | |
| nwidart/laravel-modules | 13.3.0 | |
| graham-campbell/markdown | 16.1.0 | |
| laravel/scout | 10.25.0 | database driver; no Elasticsearch needed |
| laravel/tinker | 2.11.1 | |
| guzzlehttp/guzzle | 7.16.1 | |
| Tor | 0.4.9.15 | current stable; includes the 2026 DoS fixes |
| MariaDB | 10.5.29 | listens on localhost only |
| nginx | 1.20.1 | distro build with backported fixes |

**Security changes**

- Dependencies: `phpseclib` 3.x replaces 2.x, and the unused, abandoned `paragonie/easyrsa` was removed. Composer's insecure-package blocking is on and `composer audit` reports no advisories.
- Application: payments are simulation-only; throttling, one-time captcha and ownership checks added; `info.php` removed; `APP_DEBUG=false`; `.env` is mode 640.
- Web server: `server_tokens off`, `X-Powered-By` removed, `nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: no-referrer`, `Permissions-Policy`, and cross-origin isolation headers. Dotfiles, logs and config files are denied; access logging is off.
- PHP: `expose_php` off; `exec`, `shell_exec`, `system`, `passthru`, `popen`, `proc_open` and `pcntl_exec` disabled for the web pool.
- Tor: client-only (no relay, no exit, no SOCKS or DNS port, so no DNS leaks), obfs4 bridges, onion-service intro-point DoS defense and proof-of-work defense enabled.
- Host: MariaDB bound to localhost (`bind-address = 127.0.0.1`); SSH is key-only; unused firewall rules removed. Application updates use graceful reloads only (`deploy/update.sh`), never restarts of Tor or the database.
- Known limitations: the database is MariaDB 10.5 (community end-of-life was June 2025; the distro keeps backporting fixes); SELinux is disabled on the demo host. Both are on the upgrade list.

## 📞 Contact

For inquiries or support, please reach out to me directly. I can be reached on GitHub and LinkedIn.

---

## ⚖️ Legal Disclaimers

1. **No Illegal Activities**: This project is intended for educational and research purposes only. Any use of this software for illegal activities is strictly prohibited.
2. **Compliance**: Users are responsible for ensuring that their use of this software complies with all applicable laws and regulations in their jurisdiction.
3. **No Warranty**: This software is provided "as is," without warranty of any kind, express or implied, including but not limited to the warranties of merchantability, fitness for a particular purpose, and non-infringement.
4. **Liability**: In no event shall the author or contributors be liable for any damages arising from the use of this software, including but not limited to direct, indirect, incidental, punitive, and consequential damages.
5. **User Responsibility**: Users are solely responsible for any consequences arising from their use of this software, including any legal repercussions.

## 🎩 Friendly Advice

Big Brother leverages the Eye of Sauron and has many dungeons. The darknet is filled with hackers and LEAs. If you're thinking of using this code to run a drug market/store or for other arguably illegal activities, please rethink it. You'll get yourself into trouble. Use this to learn or to provide the so-called 'darknet' some much-needed original, non-illegal functionality. Give the gift of possible anonymity to a legal service. If you would like to invest and want a few ideas as well as a business partner, drop me a line.

---

Thank you for checking out **theVault**! Have fun and be good. 🚀
