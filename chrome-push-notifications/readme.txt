=== Plugin Name ===
Contributors: aglipanci
Donate link: http://aglipanci.com/
Tags: chrome, push, push notifications, notifications, subscribe
Requires at least: 3.0.1
Tested up to: 4.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Unlimited Push Notifications for your WordPress (Chrome Desktop + Android). Everything you need is inside this plugin, no extra service or servers.

== Description ==

Enable Chrome push notifications for your WordPress (Chrome Desktop + Android). You the latest Chrome feature to reach your users directly on Desktop and Android. More info here: http://blog.chromium.org/2015/04/reaching-and-re-engaging-users-on.html

By installing and configuring the Chrome Push Notifications plugin on your WordPress site, you will not need any other extra service to enable the service for your users.

You need HTTPS to use this plugin (even the free Clouflare SSL certificate will do the job), after installing a valid SSL certificate on your website you will be able to use this plugin. User will be asked to accept to receive push notification from your website.

Plugin Features:

- Chrome Notifications (Chrome Desktop + Android)
- Select Post Types for which you want the Push Notifications to be send
- Automatic Push Notifications for newly created posts
- Custom Logo for your notifications
- Custom Notifications

Comming soon:

- User statistics
- Notifications statistics

Demo URL: https://www.aglipanci.com


== Installation ==

Requirements:

- A valid SSL certificate so your website can be accessed through HTTPS (even a free one from cloudflare.com will do the work). Check this guide to configure the Flexible SSL by Cloudflare: https://wordpresscheat.com/wordpress-cloudflare-flexible-ssl-guide/

- Chrome 42+ (Desktop & Android)

Installation:

How to Install and Configure the Plugin

[youtube https://www.youtube.com/watch?v=yHzz3Ui2yS4]


Follow this steps:

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

Not working? drop me an email to agli [dot] panci [at] GMAIL [dot] com


== Frequently Asked Questions ==

= Is the Push Notification is send even when the user is not navigating in the page? =

Yes! If the user accepts to receive Push Notifications for your website, the push notications will apear even when he is not in the page.

= Does this plugin uses some extra service backend? =

Nope! Everything is inside the plugin, using the CGM service by Google.

= Where can i report issues with the plugin? =

Please report issues to the GIT repository

Go to: https://github.com/aglipanci/chrome-push-notifications

== Screenshots ==

No screenshots available.

== Changelog ==

= 1.1.1 =
* Notifications Hits fix

= 1.1 =
* MAJOR UPDATE (CHROME API CHANGES)

= 1.0 =
* Post types configurations

== Upgrade Notice ==

No upgrades yet.