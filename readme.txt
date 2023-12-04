=== Temza addon for Contact Form 7 ===
Contributors: neuropassenger
Donate link: https://neuropasssenger.ru
Tags: Contact Form 7, UTM Tracking, webhook, cf7, Contact, dnd
Requires at least: 6.3.2
Tested up to: 6.3.2
Stable tag: 1.2.2
Requires PHP: 7.4.33
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Implements adding UTM tags to form data, adds the ability to send data via webhook, and improves security for uploaded files.

== Description ==

Temza addon for Contact Form 7 is a plugin that extends the functionality of Contact Form 7 forms. Created specifically for Temza sites.

The plugin implements several features:

Collects and saves the transmitted utm-data in COOKIE to be used when submitting Contact Form 7 forms. 
The following tags are currently supported:
* utm_source
* utm_medium
* utm_term
* utm_content,
* utm_campaign

In addition to the tags passed, the plugin will store the address of the page that was the first page the user visited. If it is possible to determine the source of transition, it will be done.

Improves file upload security in Contact Form 7 with the use of Drag and Drop Multiple File Upload - Contact Form 7 plugin.
* Changes the names of uploaded files so they cannot be matched
* Changes the file retention time to 1 month

Adds the ability to submit form data when the form is submitted via webhook. 
To do this, you need to specify <code>webhook_url</code> in Additional Settings for the form. For example, <code>webhook_url: "https://webhook.site/19bc49b3-4982-45d3-8fe7-84860e4c0c10"</code>. 
If everything is correct, the plugin will send all form fields to webhook and add utm-data to them. The names for the form fields remain the same, but the <code>bs_</code> prefix is added to the utm tag names. For example, if utm_source utm tag is sent, it will look like bs_utm_source.
In addition, the plugin tries to replace the data from utm_source with human-readable names when submitting via webhhook. Here's a list of matches:
* organic to Organic
* googleads to Google Ads
* facebook to Facebook
* facebookads to Facebook Ads 
* instagram to Instagram
* instagramads to Instagram Ads
* bing to Bing
* linkedin to LinkedIn
* referral to Referral
* repeat to Repeat

== Changelog ==

= 1.2.1 =
* Added verification of Drag and Drop Multiple File Upload - Contact Form 7 plugin usage.

= 1.2.0 =
* Not using tokens for updates. 

= 1.1.0 =
* Added the ability to enter the Github token required to update the plugin.

= 1.0.0 =
* Release.

== Upgrade Notice ==

= 1.0.0 =
Update because this is the first release and stable version of the plugin.