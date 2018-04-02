=== QuickieBar  ===
Contributors: philbaylog
Tags: bar, conversion, drop-down, notification, opt-in bar, opt in bar, notification bar, top bar, hello bar, header bar, conversion bar, drop down bar
Requires at least: 3.8
Donate Link: https://quickiebar.com/pricing
Tested up to: 4.6.1
Stable tag: 1.8.4
License: GPLv2

QuickieBar makes it easy for you to convert visitors by adding an attractive and easily customizable conversion bar to the top or bottom of your site.

== Description ==

https://www.youtube.com/watch?v=lzIV6IYWaaQ

Quickly add a powerful drop down conversion bar to your site and start converting visitors. Grab your visitor’s attention with an attractive opt-in bar displayed either at the top or bottom of your site, and direct them to product page, signup page, or newsletter subscribe page.

QuickieBar is completely responsive and designed to look great on mobile, tablet, and desktop devices. Customize options of your bar to adhere to your brand standards, or pop out from the page. Choose from a multitude of colors (preset or custom), bar styles, and button styles.

QuickieBar comes with over 500 icons that you can use anywhere in your bar (provided by FontAwesome).

Instant previewing of your bar while you create it means that you can see your changes in real time. You can also click a button to preview the bar directly on your site.

Advanced conversion reporting allows you to dive into metrics on your bars, and see how successful they are over time. Each bar you create is tracked separately, so you can test different messages and bar colors to optimize conversion.

Conversion bars like the ones you can make with QuickieBar are starting to appear all over the web to help site owners drive traffic to conversion pages. QuickieBar enables you to outfit your site with a similar (and in many cases, more sophisticated and attractive) bar in seconds.

= Full Feature List =

QuickieBar features that enable you to create & convert as quickly & effortlessly as possible:

* One-Minute Install: Install and launch your first bar in under 60 seconds
* Beautiful Color Schemes: Choose from ten attractive color schemes, or create your own!
* Mobile Friendly: Responsive from the ground up so your bars look great on your phone and browser
* Awesome Icon Selection: Add over 500 distinctive icons to your calls to action
* Top or Bottom Placement: Easily toggle whether users see the bar at the top or bottom of your site
* Conversion Reports: Ongoing reporting to help you uncover customer trends and behavior
* Conditional Triggering: Choose to show your bar everywhere or on specific pages, posts, or just your homepage
* Advanced Customization: Customize just about anything about the way your bar looks and behaves.
* Fixed Header Compatibility: QuickieBar is even compatible with most (not all) fixed header themes: simply enable fixed header compatibility mode.


= Bar Options List =

Customize any of the following options for your bars:

* Bar Style
* Conversion Button Style
* Opens in New Tab
* Top or Bottom Placement
* Text & Button Alignment
* Sticks to Top of Page
* Animation
* Close Button Visibility
* Bar Text
* Button Text
* Bar Background Color
* Button Background Color


= Browser Compatibility List =

While ideally QuickieBar would work perfectly in every browser, outdated browsers can cause bars to look unattractive or display inconsistently across different devices. As such, I have opted to not display any bar to users using older browsers, instead of showing them one of lower quality.

QuickieBar **should** display perfectly in the following browsers. Users viewing your site on browsers not on this list will simply not be shown your conversion bar (they will also not be tracked for Views or Conversions).

* Google Chrome 4+
* Firefox 3.5+
* Safari 3.1+
* Internet Explorer 10+

If you notice any inconsistencies in the way your bars display to visitors using these browsers, please let me know [here](https://quickiebar.com/support) and I will do what I can to fix the issue.


== Installation ==

This section describes how to install the plugin and create your first bar.

1. Upload all files in `quickiebar.zip` to `/wp-content/plugins/quickiebar` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click on the QuickieBar menu item on the Admin Menu (it will show up between the Comments and Appearance sections)
4. If you want to receive product updates, Enter your email and click **Get Product Updates**. If not, just click **Skip this step**.
5. Click **Create a new bar** and fill out the options to create your first bar (all Basic Settings are required)!


== Frequently Asked Questions ==

###My bar is not showing up in the correct place. What should I do?
or…
###QuickieBar is messing up my header. What should I do?

Due to the nature of displaying a bar at a fixed position at the top of a site, some page layouts will inherently not “play nice” with a fixed bar at the top of the page. Either they themselves used fixed positioning to place components on the page (such as a sidebar or header), or they use javascript to lay out certain items vertically on the page. While I have done everything possible to make this plugin as compatible with as many themes as possible, it is impossible to work perfectly with every theme (Twenty Fourteen is a great example, since it uses a fixed positioned header).

The first thing you’ll want to try is enabling “Fixed Header Compatibility Mode” from the Settings page. This will attempt to adjust the position of your fixed header when QuickieBar bars are displayed.

You can try using the “Bottom” placement for your bar or disabling the “Sticky” option, as bars that are configured to stick to the top of the page are usually the ones causing the layout issues.

If none of these options fix the layout problems, you should open a ticket [here](https://quickiebar.com/support) and make sure to describe the issue as well as include the name of the theme that your site uses.


###X Feature is not working. What should I do?

Open a ticket [here](https://quickiebar.com/support). Describe what isn’t working, what should be happening, and provide the name of the theme you are using.


###How do the cookies work behind the scenes?

When a new visitor accesses your site, QuickieBar saves a cookie that persists a non-personally-identifiable ID for that visitor across all page views. If a bar is shown, that ID is logged as a “view” for whatever bar was shown. If that same user clicks on the bar, then that ID is logged as a “conversion” for whatever bar was shown.

If a visitor closes the bar, they will not be shown that bar again (for up to 7 days). All cookies persist for 7 days, so if that same person comes back after 7 days, they will be treated as a new visitor and be shown the bar and have views and conversions logged.


###When I deactivate or uninstall the plugin, is all my data deleted?

No. If you wish to delete all data when uninstalling the plugin, click “Delete all plugin settings & data” at the bottom of the QuickieBar Settings page. This will deactivate the plugin and erase all settings & data associated with the plugin. You can then “Delete” the plugin files just like any other plugin.


###What information (if any) is sent to third-party sites (such as quickiebar.com)?

By default, no information is sent to quickiebar.com. You can opt in to receive infrequent product updates, in which case only your email address and website URL are saved to our mailchimp newsletter list.

== Screenshots ==

1. QuickieBar on an example website
2. QuickieBar bar creation page (in admin menu) with live preview
3. Viewing all bars - you can see quick stats about your bars and pause / resume bars that are running
4. The only required fields for creating a new bar: Bar Text, Button Text, and Button Destination
5. Select any preset color options or specify your own
6. Viewing conversion history by Week, Month, or All-Time
7. All additional options that can be set on a bar by bar basis
8. Plugin-wide options such as enabling attribution (disabled by default) and setting bar visibility
9. Custom icon chooser to select or search through 500+ icons (provided by FontAwesome)

== Changelog ==

= 1.8.4 | Sep 20, 2016 =
* Fix: Fixed permissions error of readfile() function caused by a small amount of hosting providers
* Fix: Fixed issue with multiple icons in bar text not always being replaced correctly on mobile devices

= 1.8.3 | Jul 26, 2016 =
* Fix: Fixed sql error in prior commit

= 1.8.2 | Jul 26, 2016 =
* Fix: Fixed issue w/ duplicated db records being created in some WP instances on db update
* Fix: Removed deprecated get_currentuserinfo call

= 1.8.1 | Jan 15, 2016 =
* Fix: Links to "call", "sms", and "skype" prefixes can now be added as QuickieBar actions

= 1.8.0 | Sep 15, 2015 =
* Feature: Visibility for custom post types can now be togged on or off
* Fix: Fixed bug which caused QuickieBar welcome message to be displayed by mistake

= 1.7.2 | Aug 26, 2015 =
* Tweak: Added background styling to "wrap" class in QuickieBar to prevent some theme styles from affecting bar styles

= 1.7.1 | Aug 3, 2015 =
* Fix: Increased character limits of bar text, button text, and destination fields to improve support for html in bars and non-english characters

= 1.7.0 | July 16, 2015 =
* Feature: Bars can now be configured to open click actions in the current tab, in addition to the default action (opening in new tab)

= 1.6.6 | July 15, 2015 =
* Tweak: Added support for a fixed header class (.qb-fixed-header) which allows users to specify which page element should be adjusted in fixed header compatibility mode

= 1.6.5 | June 30, 2015 =
* Tweak: Added support for mailto, tel, on page, and relative links for bar destination

= 1.6.4 | June 28, 2015 =
* Tweak: Removed attribution html element when not enabled (previously was just hidden)

= 1.6.3 | June 10, 2015 =
* Fix: Updated call to get pages on settings page to improve theme/plugin compatibility
* Tweak: Added better notes about fixed compatibility setting

= 1.6.2 | May 29, 2015 =
* Fix: Updated links for "Get Started With.." and "Settings" plugin links to detect location of settings page

= 1.6.1 | May 20, 2015 =
* Fix: Live Preview will now correctly preview bars
* Fix: Updated admin menu icon size

= 1.6.0 | May 19, 2015 =
* Feature: Bars can now be toggled to show on archive pages using custom visibility settings
* Feature: Getting Started Videos are now accessible from the plugin's new Help page
* Feature: Z-Index can now be set from the settings page to improve theme compatibility

= 1.5.0 | May 11, 2015 =
* Feature: Added support for editing visibility by categories
* Tweak: Added 10% OFF Discount for newsletter subscription
* Tweak: Added "Upgrade to PRO" content to sidebar & when users try to publish two bars at once

= 1.4.2 | Apr 29, 2015 =
* Tweak: Bars can now be created without a link or button text, allowing for them to act as notification messages or CTAs w/o buttons
* Fix: Special characters will now save correctly in bar text and button text fields
* Fix: Converted add_menu_page position parameter to string to reduce menu conflicts based on position

= 1.4.1 | Apr 20, 2015 =
* Fix: Removed code that preventing new bars from being created/edited.

= 1.4.0 | Apr 20, 2015 =
* Feature: Introduced a "minimized" state for bars - users can now expand dismissed bars (desktop only)
* Tweak: Added "Settings" link to plugins page
* Tweak: Added welcome message CTA when plugin is installed for the first time
* Fix: Fixed bug preventing bar dismissals sometimes not persisting after page refresh
* Fix: Fixed bug causing the bar deletion process to "hang" for some bars
* Fix: Fixed UI glitch after deleting a bar

= 1.3.2 | Apr 15, 2015 =
* Fix: Fixed issues with page visibility settings not applying to admin page views

= 1.3.1 | Apr 3, 2015 =
* Fix: QuickieBar will now not be shown on all admin pages. You know, because you probably already know about the stuff you're promoting...

= 1.3.0 | Apr 3, 2015 =
* Feature: Skinny Bar Height is now available, for those who prefer their bars even skinnier!
* Tweak: Bars will now properly displace the default WordPress admin bar (#wpadminbar) for logged in users
* Tweak: Admins are no longer able to permanently dismiss the bars they create
* Tweak: Destination URLS can now be on-page links (#example)
* Tweak: The Most Used Fontawesome icons now show up at the top of the icon list
* Fix: Bars will now show up more consistently (and in some cases, altogether) for logged in users & admins
* Fix: Fixed Header Compatibility Mode now supports more fixed header themes

= 1.2.0 | Mar 3, 2015 =
* Feature: You can now toggle bars to only be shown to visitors on specific devices (all devices, desktops only, or mobile & tablets only)
* Tweak: Turning Debug Mode on will now make the QuickieBar public script available on all pages (makes for easier support debugging)
* Tweak: Adjusted script loading logic on admin pages to improve admin page performance for multi-admin sites
* Fix: The QuickieBar preview on admin pages now closes correctly after editing is finished
* Fix: Knockout library now binds specific to QuickieBar settings pages (reduces likelihood of compatibility issues with other WordPress plugins using knockout bindings)
* Fix: Specific custom visibility settings will no longer cause plugin errors when installed on < php 5.4

= 1.1.3 | Feb 26, 2015 =
* Fix: Dismissing the bar now works correctly in desktop & mobile browsers
* Fix: Fixed issue causing some bar dismissals to be counted as conversions

= 1.1.2 | Feb 24, 2015 =
* Fix: Prevent default bar from showing up to some visitors (based on cookies & debugging settings) after all bars are paused

= 1.1.1 | Feb 20, 2015 =
* Fix: Prevent existing bars from showing when users attempted to "Live Preview" a different bar on quickiebar.com

= 1.1.0 | Feb 19, 2015 =
* Feature: Enhanced the capability of Conditional Triggering, allowing users to toggle individual pages or posts to be shown or hidden
* Tweak: Made admin sidebar "sticky" and removed link to Forums for support from sidebar

= 1.0.1 | Feb 10, 2015 =
* Fix: Users with old versions of fontawesome installed on their site could not select icons properly
* Fix: Fixed naming issue with Roboto Slab font causing it to not load for some Users

= 1.0.0 | Feb 9, 2015 =
* Initial public release of QuickieBar

== Translations ==

* English - default

Please contact me if you wish to help with translation!

== Feedback ==

I always appreciate any feedback or ideas you might have for the plugin. Please open a ticket [here](https://quickiebar.com/support) and let me know what’s on your mind!
