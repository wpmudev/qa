<?php
/*
Plugin Name: Q&A Lite
Plugin URI: http://premium.wpmudev.org/project/qa-wordpress-questions-and-answers-plugin
Description: Q&A Lite allows any WordPress site to have a fully featured questions and answers section - just like StackOverflow, Yahoo Answers, Quora and more...
Author: scribu (Incsub)
Version: 1.0.1
Author URI: http://premium.wpmudev.org/
WDP ID: 224
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

// The plugin version
define( 'QA_VERSION', '1.0.1' );

// The full url to the plugin directory
define( 'QA_PLUGIN_URL', WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) ) . '/' );

// The full path to the plugin directory
define( 'QA_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) . '/' );

// The text domain for strings localization
define( 'QA_TEXTDOMAIN', 'questions-and-answers' );

// The key for the options array
define( 'QA_OPTIONS_NAME', 'qa_options' );

// The minimum number of seconds between two user posts
define( 'QA_FLOOD_SECONDS', 10 );

// Reputation multipliers
define( 'QA_ANSWER_ACCEPTED', 15 );
define( 'QA_ANSWER_ACCEPTING', 2 );
define( 'QA_ANSWER_UP_VOTE', 10 );
define( 'QA_QUESTION_UP_VOTE', 5 );
define( 'QA_DOWN_VOTE', -2 );
define( 'QA_DOWN_VOTE_PENALTY', -1 );

// Pagination
define( 'QA_ANSWERS_PER_PAGE', 20 );

// Load plugin files
include_once QA_PLUGIN_DIR . 'core/core.php';
include_once QA_PLUGIN_DIR . 'core/answers.php';
include_once QA_PLUGIN_DIR . 'core/edit.php';
include_once QA_PLUGIN_DIR . 'core/votes.php';
include_once QA_PLUGIN_DIR . 'core/functions.php';
include_once QA_PLUGIN_DIR . 'core/template-tags.php';
include_once QA_PLUGIN_DIR . 'core/widgets.php';
include_once QA_PLUGIN_DIR . 'core/ajax.php';

if ( is_admin() ) {
	include_once QA_PLUGIN_DIR . 'core/admin.php';
}

