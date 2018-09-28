# Report'n'Block App
App with interface for reporting (and blocking) of possible bot followers.

Demo: [MicroApp by mkbodanu4](https://apps.manko.pp.ua/report-n-block/)

Installation:
1. Upload code to your hosting / VPS
2. Install composer dependencies: via ssh `$ composer install ` or directly upload *vendor* folder with `abraham/twitteroauth` library.
3. Add missing information scripts (Privacy Policy, Terms of Service)
4. Rename `config.sample.php` to `config.php` and update all required configuration in it.
5. Done

Make sure you added Privacy Policy (*policy.php*) and Terms of Service (*terms.php*) files before public use.
Or you app will be restricted by platform (like me once).
