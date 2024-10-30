=== LDB WP e-Commerce iDeal ===
Contributors: ldebrouwer
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MYF3W8N6QWXCJ
Tags: wp e-commerce, e-commerce, webshop, ideal, rabobank, abn amro, ing
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 2.0.3

LDB WP e-Commerce iDeal allows you to easily add the iDeal payment gateway to WP e-Commerce for several Dutch banks and iDeal integrations.

== Description ==

LDB WP e-Commerce iDeal allows you to easily add the iDeal payment gateway to WP e-Commerce for several Dutch banks and iDeal integrations.

== Installation ==

1. Upload the folder `ldb-wp-e-commerce-ideal` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Store > Settings > Payment Options and configure the plugin.

== Frequently Asked Questions ==

= It's not working! =

You probably have a **permissions problem** and during the activation the plugin was unable to copy the required file to the WP e-Commerce directory. Manually copy `inc-ldb-wp-e-commerce-ideal.php` from `/plugins/ldb-wp-e-commerce-ideal` to `/plugins/wp-e-commerce/merchants` for versions below WP e-Commerce 3.8 or to `/plugins/wp-e-commerce/wpsc-merchants` for WP e-Commerce 3.8 or higher to solve the problem.

= Description of the main settings =

* Display Name : Name of the payment method as mentioned on your site.
* iDeal type : The type of iDeal integration you're using. Most likely to be 'normal' but if your gateway supplier mentions anything named PSPID just pick 'PSPID'.
* iDeal MerchantID : Your iDeal ID number, also named AcceptantID.
* iDeal SubID : Your iDeal SubID number, in most cases this will be '0'.
* iDeal PSPID : Your iDeal PSPID, also named AcceptantID.
* iDeal MerchantKey : Also named Hash Key or 'Geheime sleutel', generated and defined in 'Configuratie' in your online iDeal Dashboard. (Hash Key for testing has to differ from the key used for production)
* iDeal URL : The address of your account's iDeal site. Most banks require a test sequence before going live which can be done through the test URLs.
* iDeal PSPID URL : The address of your account's iDeal site.
* iDeal urlSuccess : The URL iDeal should return your customer to after a successful payment, it would be wise to include `?wpsc_ajax_action=empty_cart` at the end of the URL to automatically empty the shopping cart.
* iDeal urlCancel : The URL iDeal should return your customer to after a cancelled payment.
* iDeal urlError : The URL iDeal should return your customer to after a failed payment.
* iDeal Currency : The currency used for your iDeal transactions.
* iDeal Language : The language version of iDeal your customers should be directed to.

= List of iDeal URLs =
Below you will find a list of iDeal URLs for several banks. This list is not complete and any additions to it are greatly appreciated!

* Rabobank Test: https://idealtest.rabobank.nl/ideal/mpiPayInitRabo.do
* Rabobank Live: https://ideal.rabobank.nl/ideal/mpiPayInitRabo.do
* ING Bank Test: https://idealtest.secure-ing.com/ideal/mpiPayInitIng.do
* ING Bank Live: https://ideal.secure-ing.com/ideal/mpiPayInitIng.do
* ABN Amro Internetkassa : https://internetkassa.abnamro.nl/ncol/prod/orderstandard.asp

== Screenshots ==

1. The configuration panel for the 'normal' integration.
2. The configuration panel for the 'PSPID' integration.

== Changelog ==

= 2.0.3 =
* Bugs squashed.

= 2.0.2 =
* Fix bug introduced in 2.0.1.

= 2.0.1 =
* Added support for entering an alternate SubID.

= 2.0 =
* Added support for the PSPID iDeal integrations ( ABN Amro Internetkassa, ABN Amro iDeal Easy etc. )
* Some code optimizations.
* Orders are now set to received when the customer is being forwarded to iDeal. The next major version of this plugin will bring payment verification if the iDeal version of choice supports this.

= 1.3 =
* Added WP e-Commerce 3.8 compatibility
* Some code optimizations.
* Removed the listing for ABN Amro iDeal Easy support since they switched to the PSPID system.

= 1.2 =
* Squashed a bug that prevented the settings from being loaded correctly.

= 1.1 =
* Code optimization.
* Added documentation and expanded the FAQ.
* A special thanks goes out to Jochem Ruijgrok for providing some of the documentation!

= 1.0 =
* First version of the plugin.

== Upgrade Notice ==

= 2.0 =
Please backup your website before updating this plugin. A lot of changes were made.

= 1.3 =
An additional de-activation and activition may be required if you update to version 1.3 of this plugin before you upgrade to WP e-Commerce 3.8.
