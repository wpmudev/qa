<?php

if ( !class_exists('QA_Core') ):

/**
 * QA_Core
 *
 * @package QA
 * @copyright Incsub 2007-2011 {@link http://incsub.com}
 * @author
 * @author Ivan Shaovchev (Incsub) {@link http://ivan.sh}
 * @license GNU General Public License (Version 2 - GPLv2) {@link http://www.gnu.org/licenses/gpl-2.0.html}
 */
class QA_Core {

	/** @var string $plugin_version plugin version */
	var $plugin_version = QA_VERSION;
	/** @var string $plugin_url Plugin URL */
	var $plugin_url = QA_PLUGIN_URL;
	/** @var string $plugin_dir Path to plugin directory */
	var $plugin_dir = QA_PLUGIN_DIR;
	/** @var string $text_domain The text domain for strings localization */
	var $text_domain = 'qa_textdomain';
	/** @var string $options_name The key for the options array */
	var $options_name = 'qa_options';

	/**
	 * Constructor.
	 */
	function QA_Core() {
		$this->init_modules();

		register_activation_hook( $this->plugin_dir . 'loader.php', array( $this, 'plugin_activate' ) );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'handle_forms' ), 11 );

		add_action( 'parse_query', array( $this, 'parse_query' ) );

		add_filter( 'index_template', array( $this, 'handle_template' ) );
		add_filter( 'single_template', array( $this, 'handle_template' ) );
		add_filter( 'archive_template', array( $this, 'handle_template' ) );

		add_filter( 'wp_title', array( $this, 'wp_title' ), 10, 3 );
	}

	/**
	 * Register post types and taxonomies and sets rewrite rules.
	 *
	 * For rewriting to work, taxonomies have to be registered before the post type.
	 */
	function init() {
		register_taxonomy( 'question_category', 'question', array(
			'hierarchical' => true,
			'rewrite' => array( 'slug' => 'questions/category', 'hierarchical' => true ),
			'labels' => array(
				'name'			=> __( 'Question Categories', $this->text_domain ),
				'singular_name'	=> __( 'Question Category', $this->text_domain ),
				'search_items'	=> __( 'Search Question Categories', $this->text_domain ),
				'all_items'		=> __( 'All Question Categories', $this->text_domain ),
				'parent_item'	=> __( 'Parent Question Category', $this->text_domain ),
				'parent_item_colon'	=> __( 'Parent Question Category:', $this->text_domain ),
				'edit_item'		=> __( 'Edit Question Category', $this->text_domain ),
				'update_item'	=> __( 'Update Question Category', $this->text_domain ),
				'add_new_item'	=> __( 'Add New Question Category', $this->text_domain ),
				'new_item_name'	=> __( 'New Question Category Name', $this->text_domain ),
			)
		) );

		register_taxonomy( 'question_tag', 'question', array(
			'rewrite' => array( 'slug' => 'questions/tag' ),
			'labels' => array(
				'name'			=> __( 'Question Tags', $this->text_domain ),
				'singular_name'	=> __( 'Question Tag', $this->text_domain ),
				'search_items'	=> __( 'Search Question Tags', $this->text_domain ),
				'popular_items'	=> __( 'Popular Question Tags', $this->text_domain ),
				'all_items'		=> __( 'All Question Tags', $this->text_domain ),
				'edit_item'		=> __( 'Edit Question Tag', $this->text_domain ),
				'update_item'	=> __( 'Update Question Tag', $this->text_domain ),
				'add_new_item'	=> __( 'Add New Question Tag', $this->text_domain ),
				'new_item_name'	=> __( 'New Question Tag Name', $this->text_domain ),
				'separate_items_with_commas'	=> __( 'Separate question tags with commas', $this->text_domain ),
				'add_or_remove_items'			=> __( 'Add or remove question tags', $this->text_domain ),
				'choose_from_most_used'			=> __( 'Choose from the most used question tags', $this->text_domain ),
			)
		) );

		register_post_type( 'question', array(
			'public' => true,
			'rewrite' => array( 'slug' => 'questions' ),
			'has_archive' => true,

			'capability_type' => 'post',

			'supports' => array( 'title', 'editor', 'author', 'comments', 'revisions' ),

			'labels' => array(
				'name'			=> __('Questions', $this->text_domain),
				'singular_name'	=> __('Question', $this->text_domain),
				'add_new'		=> __('Add New', $this->text_domain),
				'add_new_item'	=> __('Add New Question', $this->text_domain),
				'edit_item'		=> __('Edit Question', $this->text_domain),
				'new_item'		=> __('New Question', $this->text_domain),
				'view_item'		=> __('View Question', $this->text_domain),
				'search_items'	=> __('Search Questions', $this->text_domain),
				'not_found'		=> __('No questions found', $this->text_domain),
				'not_found_in_trash'	=> __('No questions found in trash', $this->text_domain),
			)
		) );

		global $wp;
		$wp->add_query_var( 'ask_question' );
		add_rewrite_rule( 'questions/ask/?$', 'index.php?ask_question=1', 'top' );

		$wp->add_query_var( 'edit_question' );
		add_rewrite_rule( 'questions/([^/]+)/edit/?$', 'index.php?question=$matches[1]&edit_question=1', 'top' );
	}

	/**
	 * Handles questions create/edit form submissions
	 */
	function handle_forms() {
		if ( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'qa_edit' ) )
			return;

		$question = array(
			'ID' => $_POST['question_id'],
			'post_title' => $_POST['question_title'],
			'post_content' => $_POST['question_content'],
			'post_type' => 'question',
			'post_status' => 'publish'
		);

		// TODO: check for duplicate submissions and flooding

		$question_id = wp_insert_post( $question );

		if ( !$question_id || is_wp_error( $question_id ) ) {
			debug( $question_id );
			return;
		}

		wp_set_post_terms( $question_id, $_POST['question_tags'], 'question_tag' );

		wp_redirect( get_permalink( $question_id ) );
		die;
	}

	/**
	 * Various WP_Query manipulations
	 */
	function parse_query( $wp_query ) {
		// Redirect template loading to archive-question.php rather than to archive.php
		if ( $wp_query->get( 'question_category' ) || $wp_query->get( 'question_tag' ) ) {
			$wp_query->set( 'post_type', 'question' );
		}

		// Force 'index' template type
		if ( $wp_query->get( 'ask_question' ) || $wp_query->get( 'edit_question' ) ) {
			$wp_query->init_query_flags();
		}
	}

	/**
	 * Load a template, with fallback to default-templates
	 */
	function load_template( $name ) {
		$path = locate_template( $name );

		if ( !$path ) {
			$path = QA_PLUGIN_DIR . "default-templates/$name";
		}

		load_template( $path );
	}

	/**
	 * Loads default templates if the current theme doesn't have them.
	 */
	function handle_template( $path ) {
		$type = reset( explode( '_', current_filter() ) );

		$file = basename( $path );

		if ( is_question_page( 'ask' ) ) {
			$this->load_template( 'ask-question.php' );
		}
		elseif ( get_query_var( 'edit_question' ) ) {
			$this->load_template( 'edit-question.php' );
		}
		elseif ( 'question' == get_query_var( 'post_type' ) && "$type.php" == $file ) {
			// A more specific template was not found, so load the default one
			$path = $this->plugin_dir . "default-templates/$type-question.php";
		}

		return $path;
	}

	/**
	 * Various wp_title manipulations
	 */
	function wp_title( $title, $sep, $seplocation ) {
		if ( is_question_page( 'ask' ) ) {
			$title = array( __( 'Ask a question', $this->text_domain ) );
		}
		elseif ( is_question_page( 'edit' ) ) {
			$title = array( __( 'Edit question', $this->text_domain ) );
		}

		if ( is_array( $title ) ) {
			if ( 'right' == $seplocation )
				array_push( $title, " $sep " );
			else
				array_unshift( $title, " $sep " );

			$title = implode( '', $title );
		}

		return $title;
	}

	/**
	 * Initiate modules.
	 */
	function init_modules() {
		include_once $this->plugin_dir . 'core/template-tags.php';

		if ( is_admin() ) {
			include_once $this->plugin_dir . 'core/admin.php';
			new QA_Core_Admin();
		}
	}

	/**
	 * Loads "-[xx_XX].mo" language file from the "languages" directory
	 */
	function load_plugin_textdomain() {
		load_plugin_textdomain( $this->text_domain, null, $this->plugin_dir . 'languages' );
	}

	/**
	 * Activate plugin.
	 */
	function plugin_activate() {
		$this->init();
		flush_rewrite_rules();
	}

	/**
	 * Save plugin options.
	 *
	 * @param  array $params The $_POST array
	 * @return die() if _wpnonce is not verified
	 */
	function save_options( $params ) {
		if ( wp_verify_nonce( $params['_wpnonce'], 'verify' ) ) {
			// Remove unwanted parameters
			unset( $params['_wpnonce'], $params['_wp_http_referer'], $params['save'] );
			// Update options by merging the old ones
			$options = $this->get_options();
			$options = array_merge( $options, array( $params['key'] => $params ) );
			update_option( $this->options_name, $options );
		} else {
			die( __( 'Security check failed!', $this->text_domain ) );
		}
	}

	/**
	 * Get plugin options.
	 *
	 * @param  string|NULL $key The key for that plugin option.
	 * @return array $options Plugin options or empty array if no options are found
	 */
	function get_options( $key = null ) {
		$options = get_option( $this->options_name );
		$options = is_array( $options ) ? $options : array();
		// Check if specific plugin option is requested and return it
		if ( isset( $key ) && array_key_exists( $key, $options ) )
			return $options[$key];
		else
			return $options;
	}
}
endif;

/* Initiate Class */
if ( class_exists('QA_Core') )
	$_qa_core = new QA_Core();

?>
