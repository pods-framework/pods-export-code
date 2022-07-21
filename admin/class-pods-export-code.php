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
	 * @var array
	 */
	protected $exportable_pods = [];

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
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );

		// Hook into the pods admin menu
		add_filter( 'pods_admin_menu', [ $this, 'add_plugin_admin_menu' ] );

		// Ajax handler
		add_action( 'wp_ajax_pods_export_code', [ $this, 'pods_export_code' ] );
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
			wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), [], Pods_Export_Code::VERSION );
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
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), [ 'jquery' ], Pods_Export_Code::VERSION );
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
		$new_menus = [];

		// New menu item to insert
		$plugin_menu = [
			'label'    => 'Export to Code',
			'function' => [ $this, 'display_plugin_admin_page' ],
			'access'   => $this->plugin_slug,
		];

		// Loop through the Pods menu items looking for the target to insert after
		foreach ( $admin_menus as $key => $this_menu_item ) {
			// Copy all the menu items
			$new_menus[ $key ] = $this_menu_item;

			// Insert our menu item after pods components
			if ( isset( $this_menu_item['access'] ) && 'pods_components' == $this_menu_item['access'] ) {
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
		$this->set_exportable_pods();
		if ( count( $this->exportable_pods() ) > 0 ) {
			include_once( 'views/admin.php' );
		} else {
			include_once( 'views/admin-no-pods.php' );
		}
	}

	/**
	 *
	 */
	private function set_exportable_pods() {
		$this->exportable_pods = [];

		$pods = pods_api()->load_pods( [ 'fields' => false ] );
		foreach ( $pods as $this_pod ) {
			// We do no support table-based Pods
			if ( 'table' == $this_pod['storage'] ) {
				continue;
			}

			$this->exportable_pods[] = $this_pod;
		}
	}

	/**
	 * @return array
	 */
	public function exportable_pods() {
		return $this->exportable_pods;
	}

	/**
	 * AJAX handler
	 */
	public function pods_export_code() {
		if ( ! isset( $_POST['pod_names'] ) ) {
			die();
		}
		$pod_names = $_POST['pod_names'];

		if ( ! is_array( $pod_names ) ) {
			die();
		}

		$export_to_code = new Pods_Export_Code_API();

		// Output function
		$function = 'register_my_pods_config_' . rand( 11, rand( 1212, 452452 ) * 651 );

		echo "function {$function}() {\n\n";

		foreach ( $pod_names as $this_pod ) {
			echo $export_to_code->export_pod( $this_pod );
		}

		echo "}\n";
		echo "add_action( 'init', '{$function}' );";

		die();
	}

}
