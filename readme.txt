=== NowThen Photo Display===
Contributors: Thaya Kareeson
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=madeinthayaland@gmail.com&currency_code=USD&amount=&return=&item_name=Donate+a+cup+of+coffee+or+two+for+NowThen+Photo+Display+WordPress+Widget
Tags: nowthen, mobile, pictures, photo, rss
Requires at least: 2.3
Tested up to: 2.6
Stable tag: 0.4

Display your NowThen pictures on your widget sidebar or in a gallery page.

== Description ==

NowThen Photo Display parses picture RSS feeds from the image service
nowthen.com and displays the pictures on your sidebar.  Show your readers
where you are and what you're doing by displaying your latest NowThen
mobile pics in a groovy widget sidebar on your page or in a designated
gallery page.


== Installation ==

1. Download the plugin archive and expand it into the directory
wp-content/plugins/nowthen-photo-display (you've likely already done this).

2. The archive comes with an empty folder nowthen-photo-display/cache.  Make
sure this directory is writable by the web server by using chown/chmod.

3. Go to the Plugins page in your WordPress Administration area and click
'Activate' for NowThen Photo Display.

4. Go to your Plugin settings &gt; NowThen and specify your NowThen
username. (If this step is skipped, the default NowThen public picture feed
will be displayed)

5. Go into your Widgets management screen and add the NowThen Photo Display
widget to your sidebar.

6. To add a NowThen picture gallery, create a new page, add "[[nowthen]]" to
the page's content, and publish the page.

Congratulations, you've just installed NowThen Photo Display.


== NowThen Gallery Options ==

= NowThen Username =
This field specifies your NowThen username (Login name).  If your login is
a mobile phone number then you may find your username by logging into
nowthen.com and viewing your account information.  When this field is left
blank, the widget will display pictures from NowThen's public feed.  Both
the gallery and widget will use this username when displaying pictures.

= XML Cache Expiration =
Fetching the RSS feed each time a page gets loaded drastically reduces your
site's performance.  This widget fetches the feed and caches it locally (on
the web server) for quick access.  This field specifies how many seconds
until RSS feed gets re-fetched (Effectively how often to update the RSS

fetch the RSS feed every time, but this is not recommended.  Both the
gallery and widget will use this cache setting.

= Disable NowThen Gallery =
Disables searching for [[nowthen]] in posts/pages and replacing with the
NowThen gallery.  If you are not using the gallery, it is recommended that
you set this as disabled for slightly improved site performance.

= Number of Photos to Display in Gallery =
This field specifies how many pictures you want to display in your gallery.
The minimum number of pictures is 1 and the maximum is 30 (NowThen feed
limitation).

= Number of Columns per Row of Photos =
This field specifies how many columns of pictures you want to display per
row in your gallery.

= Display "See more of my mobile photos at NowThen"? =
At the end of the gallery, you have the option of displaying a link to your
NowThen profile, so your users can see more of your NowThen pictures.


== Widget Options ==

= Title =
This field specifies the text that shows up as the title this section of the
sidebar.

= Number of Pictures to Show =
This field specifies how many pictures you want to display in your widget.
The minimum number of pictures is 1 and the maximum is 10.

= Enable/Disable NowThen Logo and Tagline =
This field lets you insert or remove the NowThen logo and tagline in your
widget.

== Frequently Asked Questions ==

= What is NowThen? =
nowthen.com is a mobile photo sharing and storing service.  NowThen can be
used to:

- Share cellphone pictures back and forth with your family and friends
- Automatically broadcast your pictures straight to your site, blog or
  Myspace
- Show family and friends what you're up to when you travel for business or
  vacation
- Follow your friends updates - receive them directly to your phone
- Save time and money by blasting a text or pic message to a bunch of
  friends at once

Visit http://www.nowthen.com/help for more NowThen FAQs.


== Changelog ==

0.4
- Upgraded plugin to be compatible with the new movable wp-content
  and wp-config.php changes on WordPress 2.6.
0.3.2
- Fixed a fread() bug that messes up the gallery display when displaying
  large numbers of pictures.
0.3.1
- Made gallery and sidebar widget display the thumbnail version of the
  photos to improve page load time.
0.3
- Added gallery feature.
- Moved picture caption to bottom of image.
0.2.5
- WordPress 2.5 compatibility fix.
0.2
- Cleaned up some hard-coded values.
- Added NowThen tagline at the bottom of the widget.
- Also added option to enable/disable this new tagline.
0.1
- Initial release


== Screenshots ==

1. Widget
2. Widget Options
3. Gallery
4. Gallery Options

