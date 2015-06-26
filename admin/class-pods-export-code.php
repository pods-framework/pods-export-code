<?php

/**
 * Class Pods_Export_Code_Admin
 */
class Pods_Export_Code_Admin {

	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin            = Pods_Export_Code::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Hook into the pods admin menu
		add_filter( 'pods_admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Ajax handler
		add_action( 'wp_ajax_pods_export_code', array( $this, 'pods_export_code' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Pods_Export_Code::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			$export_pods      = new Pods_Export_Pods();
			$export_templates = new Pods_Export_Templates();
			$export_pages     = new Pods_Export_Pages();
			$export_helpers   = new Pods_Export_Helpers();

			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Pods_Export_Code::VERSION );
			wp_localize_script( $this->plugin_slug . '-admin-script', 'pods_export_pods', $export_pods->get_item_names() );
			wp_localize_script( $this->plugin_slug . '-admin-script', 'pods_export_templates', $export_templates->get_item_names() );
			wp_localize_script( $this->plugin_slug . '-admin-script', 'pods_export_pages', $export_pages->get_item_names() );
			wp_localize_script( $this->plugin_slug . '-admin-script', 'pods_export_helpers', $export_helpers->get_item_names() );
		}

	}

	/**
	 * Hooks the 'pods_admin_menu' filter
	 *
	 * @param $admin_menus
	 *
	 * @return array
	 */
	public function add_plugin_admin_menu( $admin_menus ) {

		// Fresh array to insert our new menu item
		$new_menus = array();

		// New menu item to insert
		$plugin_menu = array(
			'label'    => 'Export to Code',
			'function' => array( $this, 'display_plugin_admin_page' ),
			'access'   => $this->plugin_slug
		);

		// Loop through the Pods menu items looking for the target to insert after
		foreach ( $admin_menus as $key => $this_menu_item ) {

			// Copy all the menu items
			$new_menus[ $key ] = $this_menu_item;

			// Insert our menu item after pods components
			if ( isset( $this_menu_item[ 'access' ] ) && 'pods_components' == $this_menu_item[ 'access' ] ) {
				$new_menus[ $this->plugin_slug ] = $plugin_menu;

				// ToDo: Proper way to do this?
				$this->plugin_screen_hook_suffix = 'pods-admin_page_' . $this->plugin_slug;
			}
		}

		return $new_menus;
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {

		include_once( 'views/admin.php' );
	}

	/**
	 * AJAX handler
	 */
	public function pods_export_code() {

		if ( isset( $_POST[ 'pods-export-pods' ] ) ) {
			$export_object = new Pods_Export_Pods();
			$items = $_POST[ 'pods-export-pods' ];
		} elseif ( isset( $_POST[ 'pods-export-templates' ] ) ) {
			$export_object = new Pods_Export_Templates();
			$items = $_POST[ 'pods-export-templates' ];
		} elseif ( isset( $_POST[ 'pods-export-pages' ] ) ) {
			$export_object = new Pods_Export_Pages();
			$items = $_POST[ 'pods-export-pages' ];
		} elseif ( isset( $_POST[ 'pods-export-helpers' ] ) ) {
			$export_object = new Pods_Export_Helpers();
			$items = $_POST[ 'pods-export-helpers' ];
			// No items or an unknown POST key
		} else {
			die();
		}

		echo $export_object->export( $items );
		die();

	}

}