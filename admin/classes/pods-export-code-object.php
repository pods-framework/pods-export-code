<?php

/**
 * Class Pods_Export_Code_Object
 */
abstract class Pods_Export_Code_Object {

	/** @var array|null */
	var $items = null;

	/**
	 * @return array Array of names of all the exportable objects of this type
	 */
	abstract public function get_item_names();

	/**
	 * This function is called via ajax and any output will be part of the result
	 *
	 * @param array $items Array of object item names that are to be exported
	 * @param string|null $output_directory Directory for the exported files. Will be prefixed with the wp-content path.
	 */
	abstract public function export( $items, $output_directory = null );

}