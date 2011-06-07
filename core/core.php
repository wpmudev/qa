<?php

/**
 * Takes care of the 'question' post type, rewrite rules, queries and templates.
 */
class QA_Core {

	function QA_Core() {
		register_activation_hook( QA_PLUGIN_DIR . 'loader.php', array( &$this, 'flush_rules' ) );

		add_action( 'plugins_loaded', array( &$this, 'load_plugin_textdomain' ) );
		add_action( 'init', array( &$this, 'init' ) );

		if ( !is_admin() ) {
			add_action( 'parse_query', array( &$this, 'parse_query' ) );
		}
		add_filter( 'posts_clauses', array( &$this, 'posts_clauses' ), 10, 2 );

		add_action( 'template_redirect', array( &$this, 'load_default_style' ), 11 );
		add_action( 'template_redirect', array( &$this, 'template_redirect' ), 12 );

		add_filter( 'single_template', array( &$this, 'handle_template' ) );
		add_filter( 'archive_template', array( &$this, 'handle_template' ) );

		add_filter( 'wp_title', array( &$this, 'wp_title' ), 10, 3 );
		add_filter( 'body_class', array( &$this, 'body_class' ) );
	}

	/**
	 * Register the 'question' post type and related taxonomies and rewrite rules.
	 */
	function init() {
		// Has to come before the 'question' post type definition
		register_taxonomy( 'question_tag', 'question', array(
			'rewrite' => array( 'slug' => 'questions/tags', 'with_front' => false ),

			'capabilities' => array(
				'manage_terms' => 'edit_others_questions',
				'edit_terms' => 'edit_others_questions',
				'delete_terms' => 'edit_others_questions',
				'assign_terms' => 'edit_published_questions'
			),

			'labels' => array(
				'name'			=> __( 'Question Tags', QA_TEXTDOMAIN ),
				'singular_name'	=> __( 'Question Tag', QA_TEXTDOMAIN ),
				'search_items'	=> __( 'Search Question Tags', QA_TEXTDOMAIN ),
				'popular_items'	=> __( 'Popular Question Tags', QA_TEXTDOMAIN ),
				'all_items'		=> __( 'All Question Tags', QA_TEXTDOMAIN ),
				'edit_item'		=> __( 'Edit Question Tag', QA_TEXTDOMAIN ),
				'update_item'	=> __( 'Update Question Tag', QA_TEXTDOMAIN ),
				'add_new_item'	=> __( 'Add New Question Tag', QA_TEXTDOMAIN ),
				'new_item_name'	=> __( 'New Question Tag Name', QA_TEXTDOMAIN ),
				'separate_items_with_commas'	=> __( 'Separate question tags with commas', QA_TEXTDOMAIN ),
				'add_or_remove_items'			=> __( 'Add or remove question tags', QA_TEXTDOMAIN ),
				'choose_from_most_used'			=> __( 'Choose from the most used question tags', QA_TEXTDOMAIN ),
			)
		) );

		register_post_type( 'question', array(
			'public' => true,
			'rewrite' => array( 'slug' => 'questions', 'with_front' => false ),
			'has_archive' => true,

			'capability_type' => 'question',
			'capabilities' => array(
				'read' => 'read_questions',
				'edit_posts' => 'edit_published_questions',
				'delete_posts' => 'delete_published_questions',
			),
			'map_meta_cap' => true,

			'supports' => array( 'title', 'editor', 'author', 'comments', 'revisions' ),

			'labels' => array(
				'name'			=> __('Questions', QA_TEXTDOMAIN),
				'singular_name'	=> __('Question', QA_TEXTDOMAIN),
				'add_new'		=> __('Add New', QA_TEXTDOMAIN),
				'add_new_item'	=> __('Add New Question', QA_TEXTDOMAIN),
				'edit_item'		=> __('Edit Question', QA_TEXTDOMAIN),
				'new_item'		=> __('New Question', QA_TEXTDOMAIN),
				'view_item'		=> __('View Question', QA_TEXTDOMAIN),
				'search_items'	=> __('Search Questions', QA_TEXTDOMAIN),
				'not_found'		=> __('No questions found.', QA_TEXTDOMAIN),
				'not_found_in_trash'	=> __('No questions found in trash.', QA_TEXTDOMAIN),
			)
		) );

		// Add additional rewrite rules
		global $wp, $wp_rewrite;

		// Ask page
		$wp->add_query_var( 'qa_ask' );
		$this->add_rewrite_rule( 'questions/ask/?$', array(
			'qa_ask' => 1
		) );

		// Edit page
		$wp->add_query_var( 'qa_edit' );
		$this->add_rewrite_rule( 'questions/edit/(\d+)/?$', array(
			'qa_edit' => '$matches[1]'
		) );

		// Unanswered page
		$wp->add_query_var( 'qa_unanswered' );
		$this->add_rewrite_rule( 'questions/unanswered/?$', array(
			'post_type' => 'question',
			'qa_unanswered' => 1
		) );

		$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
		$this->add_rewrite_rule( "questions/unanswered/feed/$feeds/?$", array(
			'post_type' => 'question',
			'qa_unanswered' => 1,
			'feed' => '$matches[1]'
		) );
		$this->add_rewrite_rule( "questions/unanswered/$feeds/?$", array(
			'post_type' => 'question',
			'qa_unanswered' => 1,
			'feed' => '$matches[1]'
		) );

		$this->add_rewrite_rule( "questions/unanswered/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", array(
			'post_type' => 'question',
			'qa_unanswered' => 1,
			'paged' => '$matches[1]'
		) );

		// User page
		$this->add_rewrite_rule( 'questions/user/([^/]+)/?$', array(
			'post_type' => 'question',
			'author_name' => '$matches[1]'
		) );
	}

	/**
	 * Simple wrapper for adding straight rewrite rules,
	 * but with the matched rule as an associative array.
	 *
	 * @see http://core.trac.wordpress.org/ticket/16840
	 *
	 * @param string $regex The rewrite regex
	 * @param array $args The mapped args
	 * @param string $position Where to stick this rule in the rules array. Can be 'top' or 'bottom'
	 */
	function add_rewrite_rule( $regex, $args, $position = 'top' ) {
		global $wp, $wp_rewrite;

		$result = add_query_arg( $args, 'index.php' );
		add_rewrite_rule( $regex, $result, $position );
	}

	/**
	 * Flush rewrite rules when the plugin is activated.
	 */
	function flush_rules() {
		$this->init();
		flush_rewrite_rules();
	}

	/**
	 * Various WP_Query manipulations.
	 */
	function parse_query( $wp_query ) {
		if ( $GLOBALS['wp_query'] !== $wp_query )
			return;

		if ( $wp_query->get( 'qa_ask' ) || $wp_query->get( 'qa_edit' ) ) {
			$wp_query->is_home = false;
		}

		if ( $wp_query->get( 'qa_edit' ) ) {
			$wp_query->set( 'post_type', array( 'question', 'answer' ) );
			$wp_query->set( 'post__in', array( $wp_query->get( 'qa_edit' ) ) );
		}

		if ( 'question' == $wp_query->get( 'post_type' ) && is_archive() ) {
			$wp_query->set( 'orderby', 'modified' );
		}
	}

	/**
	 * Redirect templates using $wp_query.
	 */
	function template_redirect() {
		global $wp_query;

		if ( is_qa_page( 'ask' ) ) {
			$this->load_template( 'ask-question.php' );
		}

		if ( is_qa_page( 'edit' ) ) {
			$post_type = $wp_query->posts[0]->post_type;
			$this->load_template( "edit-{$post_type}.php" );
		}

		if ( is_qa_page( 'user' ) ) {
			$wp_query->queried_object_id = (int) $wp_query->get('author');
			$wp_query->queried_object = get_userdata( $wp_query->queried_object_id );
			$wp_query->is_post_type_archive = false;

			$this->load_template( 'user-question.php' );
		}

		if ( ( is_qa_page( 'archive' ) && is_search() ) || is_qa_page( 'unanswered' ) ) {
			$this->load_template( 'archive-question.php' );
		}

		// Redirect template loading to archive-question.php rather than to archive.php
		if ( is_qa_page( 'tag' ) ) {
			$wp_query->set( 'post_type', 'question' );
		}
	}

	/**
	 * Loads default templates if the current theme doesn't have them.
	 */
	function handle_template( $path ) {
		global $wp_query;

		if ( 'question' != get_query_var( 'post_type' ) )
			return $path;

		$type = reset( explode( '_', current_filter() ) );

		$file = basename( $path );

		if ( empty( $path ) || "$type.php" == $file ) {
			// A more specific template was not found, so load the default one
			$path = QA_PLUGIN_DIR . "default-templates/$type-question.php";
		}

		return $path;
	}

	/**
	 * Load a template, with fallback to default-templates.
	 */
	function load_template( $name ) {
		$path = locate_template( $name );

		if ( !$path ) {
			$path = QA_PLUGIN_DIR . "default-templates/$name";
		}

		load_template( $path );
		die;
	}

	/**
	 * Helper method for retriving a COUNT(*) using WP_Query
	 *
	 * @access protected
	 *
	 * @param array $args Additional args to be passed to WP_Query
	 */
	function get_count( $args ) {
		$args = array_merge( $args, array(
			'nopaging' => true,
			'orderby' => 'none',
			'fields' => 'ids',
			'qa_count' => true,
		) );

		$r = new WP_Query( $args );

		return $r->posts[0];
	}

	/**
	 * Various SQL manipulations.
	 */
	function posts_clauses( $clauses, $wp_query ) {
		global $wpdb;

		if ( $wp_query->get( 'qa_count' ) ) {
			$clauses['fields'] = 'COUNT(*)';
			$clauses['groupby'] = '';
		}
		
		// TODO: use meta_query ?
		if ( $wp_query->get( 'qa_unanswered' ) ) {
			$clauses['where'] .= " AND $wpdb->posts.ID NOT IN(
				SELECT post_id FROM $wpdb->postmeta
				WHERE meta_key = '_answer_count'
				AND meta_value > '0'
			)";
		}

		return $clauses;
	}

	/**
	 * Enqueue default CSS and JS.
	 */
	function load_default_style() {
		if ( !is_qa_page() )
			return;

		if ( !current_theme_supports( 'qa_style' ) ) {
			wp_enqueue_style( 'qa-section', QA_PLUGIN_URL . 'default-templates/css/general.css', array(), QA_VERSION );
		}

		if ( !current_theme_supports( 'qa_script' ) ) {
			if ( is_qa_page( 'ask' ) || is_qa_page( 'edit' ) || is_qa_page( 'single' ) ) {
				wp_enqueue_style( 'cleditor', QA_PLUGIN_URL . 'default-templates/js/cleditor/jquery.cleditor.css', array(), '1.3.0-l10n' );

				wp_enqueue_script( 'cleditor', QA_PLUGIN_URL . 'default-templates/js/cleditor/jquery.cleditor.js', array( 'jquery' ), '1.3.0-l10n' );

				wp_enqueue_script( 'suggest' );
			}

			wp_enqueue_script( 'qa-init', QA_PLUGIN_URL . 'default-templates/js/init.js', array('jquery'), QA_VERSION );
			wp_localize_script( 'qa-init', 'QA_L10N', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'msg_login' => __( 'Please login or register to vote.', QA_TEXTDOMAIN ),
				'msg_own' => __( 'You can\'t vote on your own post.', QA_TEXTDOMAIN )
			) );
		}
	}

	/**
	 * Various wp_title manipulations.
	 */
	function wp_title( $title, $sep, $seplocation ) {
		global $wp_query;

		if ( is_qa_page( 'ask' ) ) {
			$new_title = __( 'Ask a question', QA_TEXTDOMAIN );
		}
		elseif ( is_qa_page( 'edit' ) ) {
			$post_type_obj = get_post_type_object( $wp_query->posts[0]->post_type );
			$new_title = $post_type_obj->labels->edit_item;
		}
		elseif ( is_qa_page( 'user' ) ) {
			$user = get_queried_object();
			$new_title = sprintf( __( 'User: %s', QA_TEXTDOMAIN ), $user->display_name );
		}

		if ( isset( $new_title ) ) {
			$title = array( $new_title );

			if ( 'right' == $seplocation )
				array_push( $title, " $sep " );
			else
				array_unshift( $title, " $sep " );

			$title = implode( '', $title );
		}

		return $title;
	}

	function body_class( $classes ) {
		if ( is_qa_page( 'ask' ) )
			$classes[] = 'ask-question';

		if ( is_qa_page( 'edit' ) )
			$classes[] = 'edit-question';

		if ( is_qa_page( 'unanswered' ) )
			$classes[] = 'unanswered';

		return $classes;
	}

	/**
	 * Loads "-[xx_XX].mo" language file from the "languages" directory
	 */
	function load_plugin_textdomain() {
		load_plugin_textdomain( QA_TEXTDOMAIN, '', plugin_basename( QA_PLUGIN_DIR . 'languages' ) );
	}

	/**
	 * Get plugin options.
	 *
	 * @param  string|NULL $key The key for that plugin option.
	 * @return array $options Plugin options or empty array if no options are found
	 */
	function get_options( $key = null ) {
		$options = get_option( QA_OPTIONS_NAME );
		$options = is_array( $options ) ? $options : array();
		// Check if specific plugin option is requested and return it
		if ( isset( $key ) && array_key_exists( $key, $options ) )
			return $options[$key];
		else
			return $options;
	}
}

$_qa_core = new QA_Core();

