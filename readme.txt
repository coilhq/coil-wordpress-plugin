=== Coil Web Monetization ===
Author URI: https://coil.com
Plugin URI: https://coil.com
Contributors: coil, pragmaticagency
Tags: coil, content, monetization, payment, interledger, ilp
Requires at least: 5.0+
Requires PHP: 7.1
Tested up to: 5.3
Stable tag: 1.9.0
License: Apache-2.0
License URI: http://www.apache.org/licenses/LICENSE-2.0.txt

Coil makes it easy for you to get paid for your online content.

== Description ==

Coil's official WordPress plugin makes it easy for you to get paid instantly while Coil Members browse your content.

### How it works

1. Coil Members pay a monthly fee.
2. You create an account with an ILP-enabled digital wallet.
3. You install the WordPress plugin.
4. Coil streams payments to your wallet while Coil Members enjoy your content.

To learn more about Coil, visit us at [coil.com](https://coil.com/).

For user documentation, visit our [help center](https://help.coil.com/for-creators/wordpress-plugin).

== Installation ==

1. Install the official Coil Web Monetization plugin.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Select 'Coil' in the Admin menu.
4. Select the 'Global Settings' tab and enter your payment pointer.
5. Click 'Save Changes'.

== Features ==

Our plugin lets you add monetization to all or some of your pages and posts.

* Enable Earning with Public Access (default) - Allow all visitors to see the content, get paid when your visitor is a Coil Member
* Enable Earning with Coil Only Access - Only allow Coil Members to see the content
* Disable Earning - Allow all visitors to see the content, don't get paid when your visitor is a Coil Member

The plugin is supported with the Classic and Block/Gutenberg editors. Monetization can be assigned globally to pages and posts, to individual pages and posts, and to tags and categories. If you're using the Gutenberg editor, you can choose to monetize at the block level.

== Frequently Asked Questions ==

= How do I start using the Coil Web Monetization plugin? =

Simply install the plugin. Once activated, you must enter your payment pointer in the plugin's 'Global Settings' tab.

= What's an ILP-enabled digital wallet? =

The Interledger Protocol (ILP) is an open protocol that Coil uses to stream payments. A digital wallet provider must support this protocol to receive payments from us. For more information about supported digital wallets, see [Digital Wallets](https://webmonetization.org/docs/ilp-wallets).

= What's a payment pointer? =

A payment pointer is like an email address for your digital wallet. It's how we know where to stream your payments. Your payment pointer is assigned to you by your wallet provider.

= Do I have to sign up for a Coil Membership? =

Nope. You'll receive payments from Coil Members regardless of whether you have an account with us.

= Where can I report bugs or contribute to the project? =

Reach out to us on the [Coil Web Monetization support forum](https://wordpress.org/support/plugin/coil-web-monetization/).

= Is Coil Web Monetization translatable? =

Yes! Translations can be contributed at [translate.wordpress.org](https://translate.wordpress.org/).

= Where can I ask for help? =

If you get stuck, check out our [help center](https://help.coil.com/for-creators/wordpress-plugin) or post a question to the [Coil Web Monetization support forum](https://wordpress.org/support/plugin/coil-web-monetization/).

== Screenshots ==

1. Simple setup
2. Great support
3. Exclusive content

== Changelog ==

= v1.7.0 =

First release.

= v1.8.0 =

= 08 Mar 2021 =

* Fix - Resolved issue that prevented users from adding content to custom pages.
* Fix - Solved the incompatibility issue with the Co-Authors Plus plugin and the CoverNews Pro theme.
* Fix - Fixed compatibility error with LearnDash when browser did not load.

* Enhancement - Updated handling of excerpts for monetized posts in the post archive. The default excerpt is now blank unless a custom excerpt has been written.
* Enhancement - A warning pop up has been added when a user saves their Global Settings and the payment pointer field is empty.
* Enhancement - The padlock icon has been completely removed from split content page / post titles and will only appear when entire pages / posts are for Coil Members only.

* Tweak - Minimum supported PHP version is now version 7.2.
* Tweak - The gradient effect on the CTA boxes that block exclusive content for non-monetized users has been replaced with a solid border instead so that the excerpt above it is now clearly legible.
* Tweak - Updated Coil header on the settings panel.
* Tweak - Removed the “Configure the plugin” button in the settings panel (simply took users to the Global Settings tab).
* Tweak - All buttons that point to external links now open in a new tab.

* Security - Added extra checks on the Theme Options partial loader logic.

= v1.9.0 =

= 20 May 2021 =

* Fix - Fixed embedded videos that were set to Paying Viewers Only so that they now display correctly once monetization has begun.
* Fix - The support creator footer bar message no longer displays for paying viewers. 

* Enhancement - The Coil settings panel's first tab is now the Global Settings tab where the payment pointers and CSS selectors are saved.
* Enhancement - A sidebar has been added throughout the settings panel with help, FAQ and documentation links.
* Enhancement - All customization features have been consolidated into the Coil settings panel (including message, button text and link, and padlock and support creator footer bar display customization).
* Enhancement - The wording explaining message customizations has been simplified for easier reading.
* Enhancement - The wording describing monetization settings has been adjusted to Paying Viewers instead of referring to Coil Members and Monetized Users.
* Enhancement - The new default monetization setting for pages and posts is now Monetized and Public instead of No Monetization.

* Tweak - Code has been linted.
* Tweak - Eslint is the new linting tool being used instead of JSHint.
* Tweak - The Grunt watch task now also includes minifying the relevant CSS files as well to reflect SCSS changes made in real time during development.
