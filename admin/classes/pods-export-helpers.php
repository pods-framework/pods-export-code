<?php

/**
 * Class Pods_Export_Helpers
 */
class Pods_Export_Helpers extends Pods_Export_Post_Object {

	const POST_TYPE = '_pods_helper';

	/**
	 * @param string $export_directory Directory for the exported files. Will be prefixed with the wp-content path.
	 */
	public function __construct( $export_directory = 'pods-export-helpers' ) {

		parent::__construct( self::POST_TYPE, $export_directory );

	}

}