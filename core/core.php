<?php

/**
 * Takes care of the 'question' post type, rewrite rules, queries and templates.
 * V1.4.2.1
 */
class QA_Core {

	function __construct() {
		$this->g_settings = $this->get_options( 'general_settings' );

		// Pagination
		$nop = 20;
		if ( isset( $this->g_settings["answers_per_page"] ) && $this->g_settings["answers_per_page"] ) {
			$nop = $this->g_settings["maskanswers_per_page"];
		}
		if ( ! defined( 'QA_ANSWERS_PER_PAGE' ) ) {
			define( 'QA_ANSWERS_PER_PAGE', $nop );
		}

		load_plugin_textdomain( QA_TEXTDOMAIN, '', plugin_basename( QA_PLUGIN_DIR . 'languages' ) );

		register_activation_hook( QA_PLUGIN_DIR . 'loader.php', array( &$this, 'install' ) );

		add_action( 'init', array( &$this, 'init' ) );

		if ( ! is_admin() ) {
			add_action( 'parse_query', array( &$this, 'parse_query' ) );
		}

		add_action( 'parse_request', array( &$this, 'parse_request' ), 1 );

		add_filter( 'posts_clauses', array( &$this, 'posts_clauses' ), 10, 2 );

		add_action( 'template_redirect', array( &$this, 'load_default_style' ), 11 );
		add_action( 'template_redirect', array( &$this, 'template_redirect' ), 12 );
		add_action( 'option_rewrite_rules', array( &$this, 'check_rewrite_rules' ) );

		//add_filter( 'single_template', array( &$this, 'handle_template' ) );
		//add_filter( 'archive_template', array( &$this, 'handle_template' ) );
		add_filter( 'the_title', array( &$this, 'the_title' ) );
		add_action( 'loop_start', array( &$this, 'loop_start' ) );

		add_action( 'loop_start', array( &$this, 'add_custom_content_before_loop' ) );

		add_filter( 'the_content', array( &$this, 'add_custom_content' ), 10, 1 );

		add_filter( 'wp_title', array( &$this, 'wp_title' ), 10, 3 );
		add_filter( 'body_class', array( &$this, 'body_class' ) );

		add_action( 'pre_get_posts', array( &$this, 'questions_per_page' ) );

		// Since V 1.3.1
		add_action( 'wp_ajax_nopriv_qa_flag', array( &$this, 'qa_flag' ) );
		add_action( 'wp_ajax_qa_flag', array( &$this, 'qa_flag' ) );
	}

	function add_custom_content_before_loop() {
		global $post, $wp;

		if ( ( ( in_the_loop() && get_post_type( $post ) == 'question' ) || in_the_loop() && isset( $wp->query_vars['qa_ask'] ) ) && ! isset( $wp->query_vars['s'] ) ) {
			echo $this->get_template_details( QA_PLUGIN_DIR . '/default-templates/qa-menu.php' );
		}
	}

	function add_custom_content( $content ) {
		if ( is_admin() ) {
			return $content;
		}

		global $post;
		if ( get_post_type( $post ) == 'question' && ! is_single() ) {
			$prepend_content = $this->get_template_details( QA_PLUGIN_DIR . '/default-templates/archive-question-single.php', array(), false, false );
			$content         = $content . $prepend_content;
		}

		if ( is_singular( 'question' ) ) {
			$append_content = $this->get_template_details( QA_PLUGIN_DIR . '/default-templates/single-answers.php' );
			$content        = $content . $append_content;
		}

		return $content;
	}

	function parse_request( $wp ) {

		global $wp_rewrite, $wp;

		if ( isset( $wp->query_vars['qa_edit'] ) ) {
			$theme_file = locate_template( array( 'page-edit-answer.php' ) );

			if ( $theme_file != '' ) {
				require_once( $theme_file );
				exit;
			} else {
				$args = array(
					'slug'        => $wp->request,
					'title'       => __( 'Edit Answer', QA_TEXTDOMAIN ),
					'content'     => $this->get_template_details( QA_PLUGIN_DIR . '/default-templates/edit-answer.php', array(), true ),
					'type'        => 'post',
					'post_type'   => 'post_type',
					'is_page'     => false,
					'is_singular' => true,
					'is_archive'  => false,
					'ID'          => $wp->query_vars['qa_edit']
				);
				$pg   = new QA_Virtual_Page( $args );
			}
		}

		if ( isset( $wp->query_vars['qa_ask'] ) ) {
			$theme_file = locate_template( array( 'page-ask-question.php' ) );

			if ( $theme_file != '' ) {
				require_once( $theme_file );
				exit;
			} else {
				$args = array(
					'slug'        => $wp->request,
					'title'       => __( 'Ask Question', QA_TEXTDOMAIN ),
					'content'     => $this->get_template_details( QA_PLUGIN_DIR . '/default-templates/ask-question.php', array(), true ),
					'type'        => 'page',
					'is_page'     => true,
					'is_singular' => false,
					'is_archive'  => false
				);
				$pg   = new QA_Virtual_Page( $args );
			}
		}

		if ( isset( $wp->query_vars['author_name'] ) && isset( $wp->query_vars['post_type'] ) && $wp->query_vars['post_type'] == 'question' ) {
			$args = array(
				'slug'          => $wp->request,
				'title'         => __( 'User Profile', QA_TEXTDOMAIN ),
				'content'       => $this->get_template_details( QA_PLUGIN_DIR . '/default-templates/user-question.php', array(), true ),
				'type'          => 'page',
				'is_page'       => true,
				'is_singular'   => false,
				'is_archive'    => false,
				'max_num_pages' => 1
			);
			$pg   = new QA_Virtual_Page( $args );
		}
	}

	/**
	 * Handles report request
	 * Since V1.3.1
	 */
	function qa_flag() {

		$id   = $_POST['ID'];
		$post = get_post( $id );
		// Don't add anchor for answers, as they already have
		if ( 'answer' != $post->post_type ) {
			$anchor = '#"question-body';
		} else {
			$anchor = '';
		}

		// Check report reason
		if ( isset( $this->g_settings["report_reasons"] ) && '' != trim( $this->g_settings["report_reasons"] ) && ! isset( $_POST["report_reason"] ) ) {

			$url = add_query_arg( array( 'no_reason' => '1' . $anchor ), get_permalink( $id ) );
			wp_redirect( $url );
			die;
		}
		// Check Captcha
		if ( isset( $this->g_settings["captcha"] ) && $this->g_settings["captcha"] && qa_is_captcha_usable() ) {

			if ( ! session_id() ) {
				@session_start();
			}

			$random = strtoupper( $_POST['random'] );

			//			include_once WP_PLUGIN_DIR . '/qa/securimage/securimage.php';
			//			$securimage = new Securimage();
			//
			//			if ($securimage->check($_POST['captcha_code']) == false) {

			if ( $_SESSION['captcha_random_value'] != md5( $random ) ) {
				$url = add_query_arg( array( 'flag_error' => '1' . $anchor ), get_permalink( $id ) );
				wp_redirect( $url );
				die;
			}
		}

		$meta              = get_post_meta( $id, '_qa_report', true );
		$new_meta          = array();
		$new_meta["count"] = 1;
		if ( $meta && isset( $meta["count"] ) ) {
			$new_meta["count"] = $meta["count"] + 1;
		}

		if ( is_user_logged_in() ) {
			global $current_user;
			$user_info        = get_userdata( $current_user->ID );
			$new_meta["user"] = $user_info->user_login;
		} else if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$new_meta["user"] = $_SERVER['REMOTE_ADDR'];
		} else {
			$new_meta["user"] = __( 'Unknown', QA_TEXTDOMAIN );
		}

		if ( isset( $_POST["report_reason"] ) ) {
			$new_meta["reason"] = $_POST["report_reason"];
		} else {
			$new_meta["reason"] = __( 'None', QA_TEXTDOMAIN );
		}

		update_post_meta( $id, '_qa_report', $new_meta );
		do_action( 'qa_reported', $id, $new_meta );

		// Only send email for the first report
		if ( isset( $this->g_settings["report_email"] ) && is_email( $this->g_settings["report_email"] ) && $new_meta["count"] == 1 ) {
			$to      = $this->g_settings["report_email"];
			$subject = __( 'A question or answer has been reported', QA_TEXTDOMAIN );
			$message = __( 'Reported by:', QA_TEXTDOMAIN );
			$message .= $new_meta["user"];
			$message .= "\n";
			$message .= __( 'Report reason:', QA_TEXTDOMAIN );
			$message .= stripslashes( $new_meta["reason"] );
			$message .= "\n\n";
			$message .= __( 'You can edit it by clicking this link:', QA_TEXTDOMAIN );
			$message .= "\n\n";
			$message .= admin_url( "post.php?post=" . $id . "&action=edit" );
			wp_mail( $to, $subject, $message );
		}

		$url = add_query_arg( array( 'flag_received' => '1' . $anchor ), get_permalink( $id ) );
		wp_redirect( $url );
		die;
	}

	/**
	 * Sets question per page.
	 * Since v1.2.1
	 */
	function questions_per_page( $query ) {

		if ( 'question' != $query->get( 'post_type' ) || ! isset( $this->g_settings["questions_per_page"] ) || $this->g_settings["questions_per_page"] < get_option( 'posts_per_page' ) ) {
			return;
		}

		$query->set( 'posts_per_page', $this->g_settings["questions_per_page"] );
	}

	/**
	 * Register the 'question' post type and related taxonomies and rewrite rules.
	 */
	function init() {

		global $wp, $wp_rewrite;

		// Ask page
		$wp->add_query_var( 'qa_ask' );
		$this->add_rewrite_rule( QA_SLUG_ROOT . '/' . QA_SLUG_ASK . '/?$', array(
			'qa_ask' => 1
		) );

		// Edit page
		$wp->add_query_var( 'qa_edit' );
		$this->add_rewrite_rule( QA_SLUG_ROOT . '/' . QA_SLUG_EDIT . '/(\d+)/?$', array(
			'qa_edit' => '$matches[1]'
		) );

		// User page
		$this->add_rewrite_rule( QA_SLUG_ROOT . '/' . QA_SLUG_USER . '/([^/]+)/?$', array(
			'post_type'   => 'question',
			'author_name' => '$matches[1]'
		) );

		// Unanswered page
		$wp->add_query_var( 'qa_unanswered' );

		$wp_rewrite->add_rewrite_tag( '%qa_unanswered%', '(' . QA_SLUG_UNANSWERED . ')', 'post_type=question&qa_unanswered=' );
		$wp_rewrite->add_permastruct( 'questions-unanswered', QA_SLUG_ROOT . '/%qa_unanswered%', false );

		// Has to come before the 'question' post type definition
		register_taxonomy( 'question_category', 'question', array(
			'hierarchical' => true,
			'rewrite'      => array( 'slug' => QA_SLUG_ROOT . '/' . QA_SLUG_CATEGORIES, 'with_front' => false ),
			'capabilities' => array(
				'manage_terms' => 'edit_others_questions',
				'edit_terms'   => 'edit_others_questions',
				'delete_terms' => 'edit_others_questions',
				'assign_terms' => 'edit_published_questions'
			),
			'labels'       => array(
				'name'              => __( 'Question Categories', QA_TEXTDOMAIN ),
				'singular_name'     => __( 'Question Category', QA_TEXTDOMAIN ),
				'search_items'      => __( 'Search Question Categories', QA_TEXTDOMAIN ),
				'all_items'         => __( 'All Question Categories', QA_TEXTDOMAIN ),
				'parent_item'       => __( 'Parent Question Category', QA_TEXTDOMAIN ),
				'parent_item_colon' => __( 'Parent Question Category:', QA_TEXTDOMAIN ),
				'edit_item'         => __( 'Edit Question Category', QA_TEXTDOMAIN ),
				'update_item'       => __( 'Update Question Category', QA_TEXTDOMAIN ),
				'add_new_item'      => __( 'Add New Question Category', QA_TEXTDOMAIN ),
				'new_item_name'     => __( 'New Question Category Name', QA_TEXTDOMAIN ),
			)
		) );

		// Has to come before the 'question' post type definition
		register_taxonomy( 'question_tag', 'question', array(
			'rewrite'      => array( 'slug' => QA_SLUG_ROOT . '/' . QA_SLUG_TAGS, 'with_front' => false ),
			'capabilities' => array(
				'manage_terms' => 'edit_others_questions',
				'edit_terms'   => 'edit_others_questions',
				'delete_terms' => 'edit_others_questions',
				'assign_terms' => 'edit_published_questions'
			),
			'labels'       => array(
				'name'                       => __( 'Question Tags', QA_TEXTDOMAIN ),
				'singular_name'              => __( 'Question Tag', QA_TEXTDOMAIN ),
				'search_items'               => __( 'Search Question Tags', QA_TEXTDOMAIN ),
				'popular_items'              => __( 'Popular Question Tags', QA_TEXTDOMAIN ),
				'all_items'                  => __( 'All Question Tags', QA_TEXTDOMAIN ),
				'edit_item'                  => __( 'Edit Question Tag', QA_TEXTDOMAIN ),
				'update_item'                => __( 'Update Question Tag', QA_TEXTDOMAIN ),
				'add_new_item'               => __( 'Add New Question Tag', QA_TEXTDOMAIN ),
				'new_item_name'              => __( 'New Question Tag Name', QA_TEXTDOMAIN ),
				'separate_items_with_commas' => __( 'Separate question tags with commas', QA_TEXTDOMAIN ),
				'add_or_remove_items'        => __( 'Add or remove question tags', QA_TEXTDOMAIN ),
				'choose_from_most_used'      => __( 'Choose from the most used question tags', QA_TEXTDOMAIN ),
			)
		) );

		$args = array(
			'public'          => true,
			'rewrite'         => array( 'slug' => QA_SLUG_ROOT, 'with_front' => false ),
			'has_archive'     => true,
			'capability_type' => 'question',
			'capabilities'    => array(
				'read'         => 'read_questions',
				'edit_posts'   => 'edit_published_questions',
				'delete_posts' => 'delete_published_questions',
			),
			'map_meta_cap'    => true,
			'supports'        => array( 'title', 'editor', 'author', 'comments', 'revisions' ),
			'labels'          => array(
				'name'               => __( 'Questions', QA_TEXTDOMAIN ),
				'singular_name'      => __( 'Question', QA_TEXTDOMAIN ),
				'add_new'            => __( 'Add New', QA_TEXTDOMAIN ),
				'add_new_item'       => __( 'Add New Question', QA_TEXTDOMAIN ),
				'edit_item'          => __( 'Edit Question', QA_TEXTDOMAIN ),
				'new_item'           => __( 'New Question', QA_TEXTDOMAIN ),
				'view_item'          => __( 'View Question', QA_TEXTDOMAIN ),
				'search_items'       => __( 'Search Questions', QA_TEXTDOMAIN ),
				'not_found'          => __( 'No questions found.', QA_TEXTDOMAIN ),
				'not_found_in_trash' => __( 'No questions found in trash.', QA_TEXTDOMAIN ),
			)
		);

		$args = apply_filters( 'qa_register_post_type_args', $args );

		register_post_type( 'question', $args );
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

	function install() {
		// Nothing to do
	}

	function check_rewrite_rules( $value ) {
		//prevent an infinite loop
		if ( ! post_type_exists( 'question' ) ) {
			return $value;
		}

		if ( ! is_array( $value ) ) {
			$value = array();
		}

		$array_key = QA_SLUG_ROOT . '/' . QA_SLUG_ASK . '/?$';
		if ( ! array_key_exists( $array_key, $value ) ) {
			remove_action( 'option_rewrite_rules', array( &$this, 'check_rewrite_rules' ) );
			$this->flush_rules();
		}

		return $value;
	}

	/**
	 * Flush rewrite rules when the plugin is activated.
	 */
	function flush_rules() {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	/**
	 * Various WP_Query manipulations.
	 */
	function parse_query( $wp_query ) {

		if ( $GLOBALS['wp_query'] !== $wp_query ) {
			return;
		}

		if ( $wp_query->get( 'qa_ask' ) || $wp_query->get( 'qa_edit' ) ) {
			$wp_query->is_home = false;
			// Fix for incorrect 404 assignment when there are no posts
			$count_posts = wp_count_posts();
			if ( ! is_object( $count_posts ) || ! isset( $count_posts->publish ) || ! $count_posts->publish ) {
				//$wp_query->is_robots = true;
			}
		}

		if ( $wp_query->get( 'qa_edit' ) ) {
			$wp_query->set( 'post_type', array( 'question', 'answer' ) );
			$wp_query->set( 'post__in', array( $wp_query->get( 'qa_edit' ) ) );
			//$wp_query->set( 'post_status', array( 'publish', 'pending' ) );
		}

		if ( 'question' == $wp_query->get( 'post_type' ) && is_archive() ) {
			$wp_query->set( 'orderby', 'modified' );
		}
	}

	/**
	 * Check visitor's capability for a given cap
	 */
	function visitor_has_cap( $cap ) {
		$v = get_role( 'visitor' );
		if ( ! $v || ! is_object( $v ) ) {
			return false;
		}

		return $v->has_cap( $cap );
	}

	/**
	 * Check if current page is allowed to the visitor or logged in user
	 */
	function is_page_allowed() {
		// First find the cap requirement for this page
		if ( is_qa_page( 'archive' ) ) {
			$cap = 'read_questions';
		} else if ( is_qa_page( 'ask' ) ) {
			$cap = 'publish_questions';
		} else {
			return true;
		} // Always allow for unlisted pages

		if ( ! is_user_logged_in() ) {
			return $this->visitor_has_cap( $cap );
		} else {
			return current_user_can( $cap );
		}
	}

	/**
	 * Redirect templates using $wp_query.
	 */
	function template_redirect() {
		global $wp_query;

		//print_r($wp_query);
		// Dont display these pages to unauthorized people
		if ( ! $this->is_page_allowed() ) {
			$redirect_url = site_url();
			if ( isset( $this->g_settings["unauthorized"] ) ) {
				$redirect_url = get_permalink( $this->g_settings["unauthorized"] );
			}

			wp_redirect( $redirect_url );
			die;
		}

		if ( is_qa_page( 'edit' ) ) {
			if ( $wp_query->found_posts == 0 ) {
				$wp_query->is_404 = true;
			} else {
				$post_type = $wp_query->posts[0]->post_type;
				$this->load_template( "edit-{$post_type}.php" );
			}
		}

		if ( is_qa_page( 'user' ) ) {
			//global $wp;
			$wp_query->queried_object_id    = (int) $wp_query->get( 'author' );
			$wp_query->queried_object       = get_userdata( $wp_query->queried_object_id );
			$wp_query->is_post_type_archive = false;

			//$this->load_template( 'user-question-old.php' );
			/* $args	 = array(
			  'slug'			 => $wp->request,
			  'title'			 => __( 'User Profile', QA_TEXTDOMAIN ),
			  'content'		 => $this->get_template_details( QA_PLUGIN_DIR . '/default-templates/user-question.php', array(), true ),
			  'type'			 => 'page',
			  'is_page'		 => TRUE,
			  'is_singular'	 => FALSE,
			  'is_archive'	 => FALSE
			  );
			  $pg		 = new QA_Virtual_Page( $args ); */
		}

		/* if ( ( is_qa_page( 'archive' ) && is_search() ) || is_qa_page( 'unanswered' ) ) {
		  $this->load_template( 'archive-question.php' );
		  } */

		// Redirect template loading to archive-question.php rather than to archive.php
		if ( is_qa_page( 'tag' ) || is_qa_page( 'category' ) ) {
			$wp_query->set( 'post_type', array( 'question' ) );
		}
	}

	function get_template_details( $template, $args = array(), $remove_wpautop = false, $include_once = true ) {
		ob_start();
		if ( $remove_wpautop ) {
			remove_filter( 'the_content', 'wpautop' );
		}
		extract( $args );
		if ( $include_once ) {
			include_once( $template );
		} else {
			include( $template );
		}
		$content = ob_get_clean();

		return $content;
	}

	function the_title( $the_title ) {
		global $wp_query;
		//var_dump($wp_query);
		if ( in_the_loop() && is_archive( 'question' ) ) {
			$qa_class  = ( is_question_answered() ) ? "qa-answered-icon" : "qa-unanswered-icon";
			$qa_status = '<div class="qa-status-icon ' . $qa_class . '"></div>';

			return $qa_status . $the_title;
		} else {
			return $the_title;
		}
	}

	function single_title( $the_title ) {
		global $do_not_duplicate;
		if ( $do_not_duplicate == 0 ) {
			$do_not_duplicate = 1;

			return get_the_question_voting() . $the_title . '<div class="clearvf"></div>';
		} else {
			return $the_title;
		}
	}

	function loop_start() {
		global $wp_query;
		if ( is_single() && in_the_loop() && $wp_query->post->post_type == 'question' ) {
			add_filter( 'the_title', array( &$this, 'single_title' ) );
		}
	}

	/**
	 * Loads default templates if the current theme doesn't have them.
	 */
	function handle_template( $path ) {
		global $wp_query;

		if ( 'question' != get_query_var( 'post_type' ) ) {
			return $path;
		}

		$cf   = explode( '_', current_filter() );
		$type = reset( $cf );

		$file = basename( $path );

		if ( empty( $path ) || "$type.php" == $file ) {
			// A more specific template was not found, so load the default one
			$path = QA_PLUGIN_DIR . QA_DEFAULT_TEMPLATE_DIR . "/$type-question.php";
		}

		return $path;
	}

	/**
	 * Load a template, with fallback to default-templates.
	 */
	function load_template( $name ) {
		$path = locate_template( $name );
		if ( ! $path ) {
			$path = QA_PLUGIN_DIR . QA_DEFAULT_TEMPLATE_DIR . "/$name";
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
			'orderby'  => 'none',
			'fields'   => 'ids',
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
			$clauses['fields']  = 'COUNT(*)';
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
		global $wp_version, $wp_query;

		if ( ! is_qa_page() ) {
			return;
		}

		if ( ! current_theme_supports( 'qa_style' ) ) {
			wp_enqueue_style( 'qa-section', QA_PLUGIN_URL . QA_DEFAULT_TEMPLATE_DIR . '/css/general.css', array(), QA_VERSION );

			$qa_current_theme = get_template();

			/* if (file_exists( QA_PLUGIN_DIR . 'theme-mods/css/custom-'.$qa_current_theme.'.css' )) {
			  wp_enqueue_style( 'qa-section-custom', QA_PLUGIN_URL . 'theme-mods/css/custom-'.$qa_current_theme.'.css', array('qa-section'), QA_VERSION );
			  } */
			add_action( 'wp_head', array( &$this, 'wp_head' ) );
		}

		if ( ! current_theme_supports( 'qa_script' ) ) {
			if ( is_qa_page( 'ask' ) || is_qa_page( 'edit' ) || is_qa_page( 'single' ) ) {
				if ( version_compare( $wp_version, "3.3", "<" ) ) {
					wp_enqueue_style( 'cleditor', QA_PLUGIN_URL . QA_DEFAULT_TEMPLATE_DIR . '/js/cleditor/jquery.cleditor.css', array(), '1.3.0-l10n' );
					wp_enqueue_script( 'cleditor', QA_PLUGIN_URL . QA_DEFAULT_TEMPLATE_DIR . '/js/cleditor/jquery.cleditor.js', array( 'jquery' ), '1.3.0-l10n' );
				}
				wp_enqueue_script( 'suggest' );
			}

			wp_enqueue_script( 'qa-init', QA_PLUGIN_URL . QA_DEFAULT_TEMPLATE_DIR . '/js/init.js', array( 'jquery' ), QA_VERSION );
			wp_localize_script( 'qa-init', 'QA_L10N', array(
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'msg_login'     => __( 'Please login or register to vote.', QA_TEXTDOMAIN ),
				'msg_own'       => __( 'You can\'t vote on your own post.', QA_TEXTDOMAIN ),
				'content_width' => $this->_get_content_width()
			) );
		}
	}

	/**
	 * Attempt to integrate better with the theme
	 */
	function wp_head() {

	}

	function _get_content_width() {
		if ( isset( $GLOBALS['content_width'] ) ) {
			$cw = $GLOBALS['content_width'];
		} else if ( isset( $this->g_settings['content_width'] ) && $this->g_settings['content_width'] ) {
			$cw = (int) $this->g_settings['content_width'];
		} else {
			$cw = 620;
		}

		return $cw;
	}

	/**
	 * Various wp_title manipulations.
	 */
	function wp_title( $title, $sep, $seplocation ) {
		global $wp_query, $bp;

		if ( is_qa_page( 'ask' ) ) {
			$new_title = __( 'Ask a question', QA_TEXTDOMAIN );
		} elseif ( is_qa_page( 'edit' ) ) {
			if ( $wp_query->found_posts != 0 ) {
				$post_type_obj = get_post_type_object( $wp_query->posts[0]->post_type );
				$new_title     = $post_type_obj->labels->edit_item;
			}
		} elseif ( is_qa_page( 'user' ) ) {
			$user = get_queried_object();
			// Don't modify title in Buddypress
			if ( ! is_object( $bp ) ) {
				$new_title = sprintf( __( 'User: %s', QA_TEXTDOMAIN ), $user->display_name );
			}
		}

		if ( isset( $new_title ) ) {
			$title = array( $new_title );

			if ( 'right' == $seplocation ) {
				array_push( $title, " $sep " );
			} else {
				array_unshift( $title, " $sep " );
			}

			$title = implode( '', $title );
		}

		return $title;
	}

	function body_class( $classes ) {
		if ( is_qa_page( 'ask' ) ) {
			$classes[] = 'ask-question';
		}

		if ( is_qa_page( 'edit' ) ) {
			$classes[] = 'edit-question';
		}

		if ( is_qa_page( 'unanswered' ) ) {
			$classes[] = 'unanswered';
		}

		return $classes;
	}

	/**
	 * Get plugin options.
	 *
	 * @param  string|NULL $key The key for that plugin option.
	 *
	 * @return array $options Plugin options or empty array if no options are found
	 */
	function get_options( $key = null ) {
		$options = get_option( QA_OPTIONS_NAME );
		$options = is_array( $options ) ? $options : array();
		// Check if specific plugin option is requested and return it
		if ( isset( $key ) && array_key_exists( $key, $options ) ) {
			return $options[ $key ];
		} else {
			return $options;
		}
	}

}

$_qa_core            = new QA_Core();
$qa_general_settings = $_qa_core->get_options( 'general_settings' );
global $_qa_core, $qa_general_settings;
