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
    var $text_domain = 'qa';

    /**
     * Constructor.
     */
    function QA_Core() {
        $this->init();
    }

    /**
     * Intiate plugin.
     *
     * @return void
     */
    function init() {}

    /**
     * Initiate variables.
     *
     * @return void
     */
    function init_vars() {}

    /**
     * Loads "-[xx_XX].mo" language file from the "languages" directory
     * @return void
     */
    function load_plugin_textdomain() {
        $plugin_dir = $this->plugin_dir . 'languages';
        load_plugin_textdomain( $this->text_domain, null, $plugin_dir );
    }

    /**
     * 
     *
     * @return void
     */
    function plugin_activate() { }

    /**
     * Deactivate plugin. 
     *
     * @return void
     */
    function plugin_deactivate() {}


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

/* Initiate Class */
if ( class_exists('QA_Core') )
	$_qa_core = new QA_Core();

?>
