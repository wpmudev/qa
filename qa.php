<?php
/*
Plugin Name: Q&A - WordPress Questions and Answers Plugin
Plugin URI: http://premium.wpmudev.org/project/qa-wordpress-questions-and-answers-plugin
Description: Q&A allows any WordPress site to have a fully featured questions and answers section - just like StackOverflow, Yahoo Answers, Quora and more...
Author: S H Mohanjith (Incsub), scribu (Incsub)
Version: 1.0.3
Author URI: http://premium.wpmudev.org/
WDP ID: 217
Text Domain: qa
*/

/*

Copyright 2007-2011 Incsub, (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

if ( !function_exists( 'wdp_un_check' ) ) {
	add_action( 'admin_notices', 'wdp_un_check', 5 );
	add_action( 'network_admin_notices', 'wdp_un_check', 5 );
	function wdp_un_check() {
		if ( !class_exists( 'WPMUDEV_Update_Notifications' ) && current_user_can( 'install_plugins' ) )
			echo '<div class="error fade"><p>' . __('Please install the latest version of <a href="http://premium.wpmudev.org/project/update-notifications/" title="Download Now &raquo;">our free Update Notifications plugin</a> which helps you stay up-to-date with the most stable, secure versions of WPMU DEV themes and plugins. <a href="http://premium.wpmudev.org/wpmu-dev/update-notifications-plugin-information/">More information &raquo;</a>', 'wpmudev') . '</a></p></div>';
	}
}

// The plugin version
define( 'QA_VERSION', '1.0.3' );

// The full url to the plugin directory
define( 'QA_PLUGIN_URL', WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) ) . '/' );

// The full path to the plugin directory
define( 'QA_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) . '/' );

// The text domain for strings localization
define( 'QA_TEXTDOMAIN', 'qa' );

// The key for the options array
define( 'QA_OPTIONS_NAME', 'qa_options' );

// The minimum number of seconds between two user posts
define( 'QA_FLOOD_SECONDS', 10 );

// Rewrite slugs
define( 'QA_SLUG_ROOT','questions' );
define( 'QA_SLUG_ASK', 'ask' );
define( 'QA_SLUG_EDIT', 'edit' );
define( 'QA_SLUG_UNANSWERED', 'unanswered' );
define( 'QA_SLUG_TAGS', 'tags' );
define( 'QA_SLUG_CATEGORIES', 'categories' );
define( 'QA_SLUG_USER', 'user' );

// Reputation multipliers
define( 'QA_ANSWER_ACCEPTED', 15 );
define( 'QA_ANSWER_ACCEPTING', 2 );
define( 'QA_ANSWER_UP_VOTE', 10 );
define( 'QA_QUESTION_UP_VOTE', 5 );
define( 'QA_DOWN_VOTE', -2 );
define( 'QA_DOWN_VOTE_PENALTY', -1 );

// Pagination
define( 'QA_ANSWERS_PER_PAGE', 20 );

global $qa_email_notification_content, $qa_email_notification_subject;

$qa_email_notification_subject = "[SITE_NAME] New Question";  // SITE_NAME
$qa_email_notification_content = "Dear TO_USER,

New question was posted on SITE_NAME.

QUESTION_TITLE

QUESTION_DESCRIPTION

If you wish to answer it please goto QUESTION_LINK.

Thanks,
SITE_NAME";

// Load plugin files
include_once QA_PLUGIN_DIR . 'core/core.php';
include_once QA_PLUGIN_DIR . 'core/answers.php';
include_once QA_PLUGIN_DIR . 'core/edit.php';
include_once QA_PLUGIN_DIR . 'core/votes.php';
include_once QA_PLUGIN_DIR . 'core/subscriptions.php';
include_once QA_PLUGIN_DIR . 'core/functions.php';
include_once QA_PLUGIN_DIR . 'core/template-tags.php';
include_once QA_PLUGIN_DIR . 'core/widgets.php';
include_once QA_PLUGIN_DIR . 'core/ajax.php';

function qa_bp_integration() {
	include_once QA_PLUGIN_DIR . 'core/buddypress.php';
}
add_action( 'bp_loaded', 'qa_bp_integration' );

if ( is_admin() ) {
	include_once QA_PLUGIN_DIR . 'core/admin.php';
}

