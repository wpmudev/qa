=== Q&A ===
Contributors: WPMUDEV
Tags: questions, answers, community, Q&A, stackoverflow, wordpress-plugins
Requires at least: 3.1
Tested up to: 3.2
Stable tag: trunk

Q&A Lite allows any WordPress site to have a fully featured questions and answers section - just like StackOverflow, Yahoo Answers, Quora and more...

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

This powerful plugin covers all the question and answer bases right out of the box. It’s easy to install and fully operational in moments, and highly customizable too!

= Pro Version =

Q&A Lite is an entirely functional but limited version of our <a href='http://premium.wpmudev.org/project/qa-wordpress-questions-and-answers-plugin'>full Q&A plugin</a>.

Here are some features that come *only* with the PRO version:

**Categories**, in addition to tags, can be assigned to questions (separate from the normal post categories). Very handy when you have a few broad areas that you would like to distinguish between.

**Anonymous visitors** can also post question and answers, which will be published after they have successfully logged in (which is easier to do than usual). This lowers the barrier to entry, making your community grow faster.

Users can **subscribe** to questions, receiving emails when new answers are posted. This is a great way to engage users and leads to better answers overall.

<a href='http://premium.wpmudev.org/project/qa-wordpress-questions-and-answers-plugin'>**Upgrade to the full version now &raquo;**</a>

== Screenshots ==

1. The question form
2. Voting system
3. User profile
4. Single question view
5. The admin area

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

When you feel that the Q&A section is ready for primetime, if your theme supports [custom menus](http://en.support.wordpress.com/menus/), you could add direct links to http://yoursite.com/questions/ and even to http://yoursite.com/questions/ask/ to your main menu.

== Changelog ==

= 1.0.1 =
* show message when non-logged-in user tries to vote
* fix reputation points bug
* load archive-question.php template even when there are no unanswered questions

= 1.0 =
* initial release


