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
	}

	/**
	 * Initiate variables.
	 *
	 * @return void
	 */
	function init_vars() {}

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
