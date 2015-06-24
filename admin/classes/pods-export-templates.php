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

}