**theVault CE v0.2.0: Laravel 12**

- Upgraded to Laravel 12.72 on PHP 8.3 (`nwidart/laravel-modules` 13, `graham-campbell/markdown` 16).
- Crypto dependencies updated: `phpseclib` 3.x, `openpgp-php` 0.7, and `paragonie/sodium_compat` for libsodium functions without the PHP extension. `composer audit` is clean; the unused `paragonie/easyrsa` was removed.
- Runs on a patched PHP 8.3 (the installers reject vulnerable versions); simulation-only payments, no cryptocurrency daemons required.
- Deployment script for clearnet + Tor onion service, with default obfs4 bridges.
- Fixed the landing page crash (`Vendor::user()`); search uses the database Scout driver.
- Root README added; Ahrefs/IndexNow key file served from the app.

Demo: https://the-vault.website
Onion: `http://vaultb5e64ac3vdgl7qhmvhmyfyfgdcsr3skdehjlqtozmul5i3qrayd.onion`
