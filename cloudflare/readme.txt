=== CloudFlare ===
Contributors: i3149
Tags: cloudflare, comments, spam, cdn
Requires at least: 2.8
Tested up to: 2.8
Stable tag: 1.0.0
License: GPLv2

CloudFlare integrates your blog with the CloudFlare platform.

== Description ==

CloudFlare has developed a plugin for WordPress. By using the CloudFlare WordPress Plugin, you receive: 

* Correct IP Address information for comments posted to your site

* Optimization of your server database 

THINGS YOU NEED TO KNOW:

* The main purpose of this plugin is to ensure you have no change to your originating IPs when using CloudFlare. Since CloudFlare acts a reverse proxy, connecting IPs now come from CloudFlare's range. This plugin will ensure you can continue to see the originating IP. 

* This plugin can also help to ensure your server database is running optimally. If you are going to run the Database Optimizer associated with this plugin, then run it at a low traffic time. While the database optimizer is running, your site will go into Read Only mode, which means that you or your visitors will not be allowed to post. The optimizer should run quickly. Once the optimizer is done running, you will be able to post to your site again. 

* We recommend that any user on WordPress and CloudFlare should use this plugin. 

MORE INFORMATION ON CLOUDFLARE:

CloudFlare is a service that makes websites load faster and protects sites from online spammers and hackers. Any website with a root domain (ie www.mydomain.com) can use CloudFlare. On average, it takes less than 5 minutes to sign up. You can learn more here: [CloudFlare.com](https://www.cloudflare.com/overview.html).

== Installation ==

Upload the CloudFlare plugin to your blog, Activate it, and you're done!

You will also want to sign up your blog with CloudFlare.com

== Changelog ==

= 1.0.0 =

* Initial feature set
* Set RemoteIP header correctly.
* On comment spam, send the offending IP to CloudFlare.
* Clean up DB on load.
