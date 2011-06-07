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
		add_action( 'admin_notices', array( &$this, 'upgrade' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'wp_ajax_qa-get-caps', array( &$this, 'ajax_get_caps' ) );
		add_action( 'wp_ajax_qa-save', array( &$this, 'ajax_save' ) );
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

	function upgrade() {
		if ( $GLOBALS['hook_suffix'] != $this->hook_suffix )
			return;
?>
<div class="updated"><p>Upgrade to the <a href="http://premium.wpmudev.org/project/qa-wordpress-questions-and-answers-plugin">full version</a> to get more features and dedicated support.</p></div>
<?php
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

		$role = $_POST['roles'];

		$all_caps = array_keys( $this->capability_map );
		$to_add = array_keys( $_POST['capabilities'] );
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

		update_option( QA_OPTIONS_NAME, $options );

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
		foreach ( $vars as $key => $val )
			$$key = $val;
		if ( file_exists( QA_PLUGIN_DIR . "ui-admin/{$name}.php" ) )
			include QA_PLUGIN_DIR . "ui-admin/{$name}.php";
		else
			echo "<p>Rendering of admin template " . QA_PLUGIN_DIR . "ui-admin/{$name}.php failed</p>";
	}
}

$GLOBALS['_qa_core_admin'] = new QA_Core_Admin();

?>
