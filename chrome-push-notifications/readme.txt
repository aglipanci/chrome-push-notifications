=== Plugin Name ===
Contributors: aglipanci
Donate link: http://aglipanci.com/
Tags: chrome, push, notifications
Requires at least: 3.0.1
Tested up to: 4.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enable Chrome push notifications for your wordpress site. Send notifications, check user registration statistics, etc.

== Description ==

Enable Chrome push notifications for your wordpress site. Send notifications, check user registration statistics, etc.

You need HTTPS to use this plugin, after installing a valid SSL certificate on your website or using one from Cloudlfare you will be able to use this plugin. User will be asked to accept to receive push notification from your website.


== Installation ==

Requirements:

- A valid SSL certificate so your website can be accessed through HTTPS (even a free one from cloudflare.com will do the work)

- Chrome 42+ (Desktop & Android)

Installation:

1. Install the Chrome Push Notifications Plug-in 

2. Go to Google Developers Console, create a new project (activate the Cloud Messaging API)

3. Open the new project, go to “Overview” and copy the “Project Number”

4. Go to APIs & auth > Credentials and click “Create a new key”

5. Select “Server Key”, you may enter your server IP if you want or leave it blank

6. Copy the “API Key” generated.

7. Go to your wordpress dashboard, Chrome Push > Settings and paste the Project number and API 

key into the plugin settings.

8. Upload an icon or logo, and click save.

9. That's it!


== Frequently Asked Questions ==

Go to: https://github.com/aglipanci/chrome-push-notifications

== Screenshots ==

No screenshots available.

== Changelog ==

= 1.0 =
* Post types configurations

== Upgrade Notice ==

No upgrades yet.