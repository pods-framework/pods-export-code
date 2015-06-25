<?php

/**
 * Class Pods_Export_Pages
 */
class Pods_Export_Templates extends Pods_Export_Post_Object {

	const POST_TYPE = '_pods_template';

	/**
	 * @param string $export_directory Directory for the exported files. Will be prefixed with the wp-content path.
	 */
	public function __construct( $export_directory = 'pods-export-templates' ) {

		parent::__construct( self::POST_TYPE, $export_directory );

	}

	/**
	 * @param string $code
	 * @param array  $template
	 * @param Pods   $obj
	 *
	 * @return string
	 */
	public function intercept_pods_template( $code, $template, $obj ) {

		/** @global $wp_filesystem WP_Filesystem_Base */
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		global $wp_filesystem;
		WP_Filesystem();

		$target_file = trailingslashit( $this->export_directory ) . $template[ 'slug' ] . '.php';

		if ( file_exists( $target_file) ) {
			$code = $wp_filesystem->get_contents( $target_file);
		}

		return $code;

	}

}