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
	/** @var string $text_domain The text domain for strings localization */
	var $options_name = 'qa_options';

	/**
	 * Constructor.
	 */
	function QA_Core() {
		$this->init_modules();

		add_action( 'init', array( $this, 'init_data_structure' ) );
	}

	/**
	 * Register post types and taxonomies.
	 *
	 * @return void
	 */
	function init_data_structure() {
		register_taxonomy( 'question_category', 'question', array(
			'hierarchical' => true,
			'rewrite' => array( 'slug' => 'questions/category' ),
			'labels' => array(
				'name' => __( 'Question Categories', 'qa-textdomain' ),
				'singular_name' => __( 'Question Category', 'qa-textdomain' ),
				'search_items' => __( 'Search Question Categories', 'qa-textdomain' ),
				'all_items' => __( 'All Question Categories', 'qa-textdomain' ),
				'parent_item' => __( 'Parent Question Category', 'qa-textdomain' ),
				'parent_item_colon' => __( 'Parent Question Category:', 'qa-textdomain' ),
				'edit_item' => __( 'Edit Question Category', 'qa-textdomain' ),
				'update_item' => __( 'Update Question Category', 'qa-textdomain' ),
				'add_new_item' => __( 'Add New Question Category', 'qa-textdomain' ),
				'new_item_name' => __( 'New Question Category Name', 'qa-textdomain' ),
			)
		) );

		register_taxonomy( 'question_tag', 'question', array(
			'rewrite' => array( 'slug' => 'questions/tag' ),
			'labels' => array(
				'name' => __( 'Question Tags', 'qa-textdomain' ),
				'singular_name' => __( 'Question Tag', 'qa-textdomain' ),
				'search_items' => __( 'Search Question Tags', 'qa-textdomain' ),
				'popular_items' => __( 'Popular Question Tags', 'qa-textdomain' ),
				'all_items' => __( 'All Question Tags', 'qa-textdomain' ),
				'edit_item' => __( 'Edit Question Tag', 'qa-textdomain' ),
				'update_item' => __( 'Update Question Tag', 'qa-textdomain' ),
				'add_new_item' => __( 'Add New Question Tag', 'qa-textdomain' ),
				'new_item_name' => __( 'New Question Tag Name', 'qa-textdomain' ),
				'separate_items_with_commas' => __( 'Separate question tags with commas', 'qa-textdomain' ),
				'add_or_remove_items' => __( 'Add or remove question tags', 'qa-textdomain' ),
				'choose_from_most_used' => __( 'Choose from the most used question tags', 'qa-textdomain' ),
			)
		) );

		register_post_type( 'question', array(
			'public' => true,
			'rewrite' => array( 'slug' => 'questions' ),
			'has_archive' => true,

			'capability_type' => 'post',

			'supports' => array( 'title', 'editor', 'author', 'comments', 'revisions' ),

			'labels' => array(
				'name'			=> __('Questions', 'qa-textdomain'),
				'singular_name' => __('Question', 'qa-textdomain'),
				'add_new'		=> __('Add New', 'qa-textdomain'),
				'add_new_item'	=> __('Add New Question', 'qa-textdomain'),
				'edit_item'		=> __('Edit Question', 'qa-textdomain'),
				'new_item'		=> __('New Question', 'qa-textdomain'),
				'view_item'		=> __('View Question', 'qa-textdomain'),
				'search_items'	=> __('Search Questions', 'qa-textdomain'),
				'not_found'		=> __('No questions found', 'qa-textdomain'),
				'not_found_in_trash' => __('No questions found in trash', 'qa-textdomain'),
			)
		) );
	}

	/**
	 * Initiate variables.
	 *
	 * @return void
	 */
	function init_vars() {}

	/**
	 * Initiate variables.
	 *
	 * @return void
	 */
	function init_modules() {
		include_once $this->plugin_dir . 'core/admin.php';
		new QA_Core_Admin();
	}

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
