<?php

if ( !class_exists('QA_Core_Admin') ):

/**
 * QA_Core 
 * 
 * @package QA
 * @copyright Incsub 2007-2011 {@link http://incsub.com}
 * @author 
 * @author Ivan Shaovchev (Incsub) {@link http://ivan.sh} 
 * @license GNU General Public License (Version 2 - GPLv2) {@link http://www.gnu.org/licenses/gpl-2.0.html}
 */
class QA_Core_Admin extends QA_Core {

	/**
	 * Constructor.
	 */
	function QA_Core_Admin() {
		$this->init();
	}

	/**
	 * Intiate hooks. 
	 *
	 * @return void
	 */
	function init() {
        add_action( 'admin_init', array( &$this, 'admin_head' ) );
        add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
	}

	/**
	 * Initiate variables.
	 *
	 * @return void
	 */
	function init_vars() {}

    /**
     * Register all admin menues.
     *
     * @return void
     **/
    function admin_menu() {
        add_menu_page( __( 'WP Q&A', $this->text_domain ), __( 'WP Q&A', $this->text_domain ), 'edit_users', 'qa_main', array( &$this, 'handle_admin_requests' ) );
    //  add_submenu_page( 'qa_main', __( 'Settings', $this->text_domain ), __( 'Settings', $this->text_domain ), 'edit_users', 'qa_main', array( &$this, 'handle_admin_requests' ) );
    }

    /**
     * Hook styles and scripts.
     *
     * @return void
     */
    function admin_head() {
        $page = ( isset( $_GET['page'] ) ) ? $_GET['page'] : null;
        $hook = get_plugin_page_hook( $page, 'qa_main' );
        add_action( 'admin_print_styles-' .  $hook, array( &$this, 'enqueue_styles' ) );
        add_action( 'admin_print_scripts-' . $hook, array( &$this, 'enqueue_scripts' ) );
    }

    /**
     * Load styles.
     *
     * @return void
     */
    function enqueue_styles() {
        wp_enqueue_style( 'qa-admin-styles',
                           $this->plugin_url . 'ui-admin/css/styles.css');
    }

    /**
     * Load scripts.
     *
     * @return void
     */
    function enqueue_scripts() {
        wp_enqueue_script( 'qa-admin-scripts',
                            $this->plugin_url . 'ui-admin/js/scripts.js',
                            array( 'jquery' ) );
    }

    /**
     * Loads admin page templates.
     *
     * @return void
     */
    function handle_admin_requests() {
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'qa_main' ) {
            if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'general' || empty( $_GET['tab'] ) ) {
                if ( isset( $_GET['sub'] ) && $_GET['sub'] == 'general' || !isset( $_GET['sub'] ) ) {
                    if ( isset( $_POST['save'] ) ) {
                        $this->save_options( $_POST );
                    }
                    $this->render_admin( 'settings-general' );
                }
            }
        }
        do_action('handle_module_admin_requests');
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
		if ( file_exists( "{$this->plugin_dir}ui-admin/{$name}.php" ) )
			include "{$this->plugin_dir}ui-admin/{$name}.php";
		else
			echo "<p>Rendering of admin template {$this->plugin_dir}ui-admin/{$name}.php failed</p>";
	}
}
endif;

?>
