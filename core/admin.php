<?php

/**
 * QA_Core_Admin
 *
 * @package QA
 * @copyright Incsub 2007-2011 {@link http://incsub.com}
 * @license GNU General Public License (Version 2 - GPLv2) {@link http://www.gnu.org/licenses/gpl-2.0.html}
 */
class QA_Core_Admin extends QA_Core {
	/** @var array Holds all capability names, along with descriptions. */
	var $capability_map;

	/** @var string Holds the settings' page hook name. */
	var $hook_suffix;

	/**
	 * Constructor.
	 */
	function QA_Core_Admin() {
		$this->capability_map = array(
			'read_questions'             => __( 'View questions.', QA_TEXTDOMAIN ),
			'publish_questions'          => __( 'Ask questions.', QA_TEXTDOMAIN ),
			'edit_published_questions'   => __( 'Edit questions.', QA_TEXTDOMAIN ),
			'delete_published_questions' => __( 'Delete questions.', QA_TEXTDOMAIN ),
			'edit_others_questions'      => __( 'Edit others\' questions.', QA_TEXTDOMAIN ),
			'delete_others_questions'    => __( 'Delete others\' questions.', QA_TEXTDOMAIN ),
			'subscribe_to_new_questions' => __( 'Subscribe to new questions.', QA_TEXTDOMAIN ),

			'read_answers'               => __( 'View answers.', QA_TEXTDOMAIN ),
			'publish_answers'            => __( 'Add answers.', QA_TEXTDOMAIN ),
			'edit_published_answers'     => __( 'Edit answers.', QA_TEXTDOMAIN ),
			'delete_published_answers'   => __( 'Delete answers.', QA_TEXTDOMAIN ),
			'edit_others_answers'        => __( 'Edit others\' answers.', QA_TEXTDOMAIN ),
			'delete_others_answers'      => __( 'Delete others\' answers.', QA_TEXTDOMAIN ),
		);

		$this->init();
	}

	/**
	 * Intiate hooks.
	 *
	 * @return void
	 */
	function init() {
		register_activation_hook( QA_PLUGIN_DIR . 'loader.php', array( &$this, 'init_defaults' ) );

		add_action( 'admin_init', array( &$this, 'admin_head' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'wp_ajax_qa-get-caps', array( &$this, 'ajax_get_caps' ) );
		add_action( 'wp_ajax_qa-save', array( &$this, 'ajax_save' ) );
		
		add_action( 'wp_ajax_nopriv_ajax-tag-search', array( &$this, 'ajax_tag_search' ) );
		
		add_action( 'show_user_profile', array( &$this, 'show_user_profile' ) ); 
		add_action( 'edit_user_profile', array( &$this, 'show_user_profile' ) );
		add_action( 'profile_update', array( &$this, 'profile_update' ) );
		
		add_filter( 'user_has_cap', array(&$this, 'user_has_cap'), 10, 3);
	}
	
	function ajax_tag_search() {
		global $wpdb;
		
		if ( isset( $_GET['tax'] ) ) {
			$taxonomy = sanitize_key( $_GET['tax'] );
			$tax = get_taxonomy( $taxonomy );
			if ( ! $tax )
				die( '0' );
		} else {
			die('0');
		}
	
		$s = stripslashes( $_GET['q'] );
	
		if ( false !== strpos( $s, ',' ) ) {
			$s = explode( ',', $s );
			$s = $s[count( $s ) - 1];
		}
		$s = trim( $s );
		if ( strlen( $s ) < 2 )
			die; // require 2 chars for matching
	
		$results = $wpdb->get_col( $wpdb->prepare( "SELECT t.name FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.name LIKE (%s)", $taxonomy, '%' . like_escape( $s ) . '%' ) );
		echo join( $results, "\n" );
		die;
		break;
	}

	/**
	 * Initiate variables.
	 *
	 * @return void
	 */
	function init_vars() {}

	/**
	 * Initiate admin default settings.
	 *
	 * @return void
	 */
	function init_defaults() {
		global $wp_roles;

		foreach ( array_keys( $this->capability_map ) as $capability )
			$wp_roles->add_cap( 'administrator', $capability );
		
		$wp_roles->add_cap( 'subscriber', 'read_questions' );
		$wp_roles->add_cap( 'subscriber', 'read_answers' );
		// add option to the autoload list
		add_option( QA_OPTIONS_NAME, array() );
	}

	/**
	 * Register all admin menus.
	 *
	 * @return void
	 */
	function admin_menu() {
		$this->hook_suffix = add_submenu_page( 'edit.php?post_type=question', __( 'Settings', QA_TEXTDOMAIN ), __( 'Settings', QA_TEXTDOMAIN ), 'edit_users', 'settings', array( &$this, 'handle_admin_requests' ) );
	}

	/**
	 * Hook styles and scripts.
	 *
	 * @return void
	 */
	function admin_head() {
		add_action( 'admin_print_styles-' . $this->hook_suffix, array( &$this, 'enqueue_styles' ) );
		add_action( 'admin_print_scripts-' . $this->hook_suffix, array( &$this, 'enqueue_scripts' ) );
	}

	/**
	 * Load styles.
	 *
	 * @return void
	 */
	function enqueue_styles() {
		wp_enqueue_style( 'qa-admin-styles',
						   QA_PLUGIN_URL . 'ui-admin/css/styles.css');
	}

	/**
	 * Load scripts.
	 *
	 * @return void
	 */
	function enqueue_scripts() {
		wp_enqueue_script( 'qa-admin-scripts',
							QA_PLUGIN_URL . 'ui-admin/js/scripts.js',
							array( 'jquery' ) );
	}

	/**
	 * Loads admin page templates.
	 *
	 * @return void
	 */
	function handle_admin_requests() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'settings' ) {
			if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'general' || !isset( $_GET['tab'] ) ) {
				if ( isset( $_GET['sub'] ) && $_GET['sub'] == 'general' || !isset( $_GET['sub'] ) ) {
					$this->render_admin('settings-general');
				}
			}
		}
		do_action('handle_module_admin_requests');
	}
	
	/**
	 * Display notification settings in user profile
	 */
	function show_user_profile() {
		if (!current_user_can('subscribe_to_new_questions'))
			return;
		
		if ( file_exists( QA_PLUGIN_DIR . "ui-admin/user_profile.php" ) )
			include QA_PLUGIN_DIR . "ui-admin/user_profile.php";
		else
			echo "<p>Rendering of admin template " . QA_PLUGIN_DIR . "ui-admin/user_profile.php failed</p>";
	}
	
	/**
	 * Save notification settings when the user profile is updated
	 */
	function profile_update() {
		if (!current_user_can('subscribe_to_new_questions'))
			return;
		
		global $wpdb;
		$user_id =  $_REQUEST['user_id'];
		
		if (isset($_POST['qa_notification'])) {
			update_usermeta($user_id, 'qa_notification', $_POST['qa_notification']);
		} else {
			update_usermeta($user_id, 'qa_notification', 0);
		}
	}

	/**
	 * Ajax callback which gets the post types associated with each page.
	 *
	 * @return JSON Encoded string
	 */
	function ajax_get_caps() {
		if ( !current_user_can( 'manage_options' ) )
			die(-1);

		global $wp_roles;

		$role = $_POST['role'];

		if ( !$wp_roles->is_role( $role ) )
			die(-1);

		$role_obj = $wp_roles->get_role( $role );

		$response = array_intersect( array_keys( $role_obj->capabilities ), array_keys( $this->capability_map ) );
		$response = array_flip( $response );

		// response output
		header( "Content-Type: application/json" );
		echo json_encode( $response );
		die();
	}

	/**
	 * Save admin options.
	 *
	 * @return void die() if _wpnonce is not verified
	 */
	function ajax_save() {
		check_admin_referer( 'qa-verify' );

		if ( !current_user_can( 'manage_options' ) )
			die(-1);

		// add/remove capabilities
		global $wp_roles;
		
		$qa_capabilities_set = get_option('qa_capabilties_set', array());
		
		$role = $_POST['roles'];

		$all_caps = array_keys( $this->capability_map );
		if (isset($_POST['capabilities'])) {
			$to_add = array_keys( $_POST['capabilities'] );
		} else {
			$to_add = array();
		}
		$to_remove = array_diff( $all_caps, $to_add );
		
		foreach ( $to_remove as $capability ) {
			$wp_roles->remove_cap( $role, $capability );
		}

		foreach ( $to_add as $capability ) {
			$wp_roles->add_cap( $role, $capability );
		}

		$options = array(
			'general_settings' => array(
				'moderation' => isset( $_POST['moderation'] )
			)
		);
		
		$qa_capabilities_set[$role] = true;
		
		update_option( 'qa_capabilties_set', array_unique( $qa_capabilities_set ));
		update_option( QA_OPTIONS_NAME, $options );
		
		update_option( 'qa_email_notification_subject', $_POST['qa_email_notification_subject'] );
		update_option( 'qa_email_notification_content', $_POST['qa_email_notification_content'] );

		die(1);
	}

	/**
	 * Renders an admin section of display code.
	 *
	 * @param  string $name Name of the admin file(without extension)
	 * @param  string $vars Array of variable name=>value that is available to the display code(optional)
	 * @return void
	 */
	function render_admin( $name, $vars = array() ) {
		extract( $vars );

		if ( file_exists( QA_PLUGIN_DIR . "ui-admin/{$name}.php" ) )
			include QA_PLUGIN_DIR . "ui-admin/{$name}.php";
		else
			echo "<p>Rendering of admin template " . QA_PLUGIN_DIR . "ui-admin/{$name}.php failed</p>";
	}
	
	function user_has_cap($allcaps, $caps = null, $args = null) {
		global $current_user, $blog_id, $post;
		
		$qa_capabilities_set = get_option('qa_capabilties_set', array());
		
		$capable = false;
		
		$qa_cap_set = false;
		foreach ($current_user->roles as $role) {
			if (isset($qa_capabilities_set[$role])) {
				$qa_cap_set = true;
			}
		}
		
		if (!$qa_cap_set && preg_match('/(_question|_questions|_answer|_answers)/i', join($caps, ',')) > 0) {
			if (in_array('administrator', $current_user->roles)) {
				foreach ($caps as $cap) {
					$allcaps[$cap] = 1;
				}
				return $allcaps;
			}
			
			foreach ($caps as $cap) {
				$capable = false;
				
				switch ($cap) {
					case 'read_questions' or 'read_answers':
						$capable = true;
						break;
					default:
						if (isset($args[1]) && isset($args[2])) {
							if (current_user_can(preg_replace('/_question|_answer/i', '_post', $cap), $args[1], $args[2])) {
								$capable = true;
							}
						} else if (isset($args[1])) {
							if (current_user_can(preg_replace('/_question|_answer/i', '_post', $cap), $args[1])) {
								$capable = true;
							}
						} else if (current_user_can(preg_replace('/_question|_answer/i', '_post', $cap))) {
							$capable = true;
						}
						break;
				}
				
				if ($capable) {
					$allcaps[$cap] = 1;
				}
			}
		}
		return $allcaps;
	}
}

$GLOBALS['_qa_core_admin'] = new QA_Core_Admin();
