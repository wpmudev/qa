=== Q&A ===
Contributors: scribu
Tags: questions, answers, community, Q&A, stackoverflow, wordpress-plugins
Requires at least: 3.1
Tested up to: 3.1
Stable tag: trunk

Q&A allows any WordPress site to have a fully featured questions and answers section - just like StackOverflow, Yahoo Answers, Quora and more...

== Description ==

Q&A allows any WordPress site to have a fully featured questions and answers section - just like StackOverflow, Yahoo Answers, Quora and more...except better :)

You've seen how engaging, informative, and just plain fun Q&A sites such as Quora can be.

With this plugin, you can bring full Questions and Answers functionality to any WordPress or BuddyPress site in mere minutes!

Chock-full of features such as:

* Full **front-end** capability - users don't ever have to see your site's admin back-end
* **WYSIWYG** editing of both questions and answers
* Snazzy **voting** for both questions and answers
* Integrated **reputation points** system
* Dedicated **user profile** pages
* Easy theme integration using **widgets**
* Fully **customizable** using dedicated template files

This extensive and powerful plugin covers all the question and answer bases right out of the box while easily installed and fully operational in moments -- and highly customizable too!

== Notes ==

Newly registered users are automatically logged in. To prevent spam bots from running amok, we strongly recommend installing this free plugin:

http://wordpress.org/extend/plugins/stop-spammer-registrations-plugin/

== Installation ==

1. Activate plugin.
2. Go to Questions -> Settings to assign capabilities to the roles of your choosing.
3. Go to http://yoursite.com/questions/ask/ to create your first question.

= Styling =

Copy the php files from default-templates into your theme folder and start customizing.

To disable the default CSS, add the following line to your theme's functions.php file:

`add_theme_support( 'qa_style' );`

To disable the default JavaScript, add the following line to your theme's functions.php file:

`add_theme_support( 'qa_script' );`

When you feel the Q&A section is ready for prime time, if your theme supports [custom menus](http://en.support.wordpress.com/menus/), you could add direct links to http://yoursite.com/questions/ and even to http://yoursite.com/questions/ask/ to your main menu.

== Changelog ==

= 1.0.3 =
* Fixed: BP Default theme issues
* Fixed: BP Default child theme issues
* Fixed: BP 1.5 compatibility
* Fixed: Tag search when not logged in

= 1.0.2 =
* BuddyPress integration
* prevent extra large font on single question page
* don't penalize users for downvoting questions, only answers
* New question e-mail notification

= 1.0.1 =
* show message when non-logged-in user tries to vote
* fix reputation points bug
* load archive-question.php template even when there are no unanswered questions

= 1.0 =
* ajaxified voting and answer accepting
* allow users to accept their own answers (without gaining reputation)
* fixed compatibility with Theme My Login plugin
* more descriptive error messages
* sturdier CSS

= 1.0-beta2 =
* changed default CSS
* added widgets: question list, question tags, question categories
* added sidebar to default templates
* added <body> class to qa templates
* fixed issue with WP-Polls plugin
* fixed issue with form not working in IE
* other minor bugfixes

= 1.0-beta1 =
* initial release

