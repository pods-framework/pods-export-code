<?php

/**
 * Class Pods_Export_Code_Object
 */
abstract class Pods_Export_Code_Object {

	/**
	 * @var array|null Array of exportable items in name => label format
	 */
	protected $items = null;

	/**
	 * @return array Array of names of all the exportable objects of this type
	 */
	abstract public function get_item_names();

	/**
	 * This function is called via ajax
	 *
	 * @param array $items Array of object item names that are to be exported
	 *
	 * @return string Output to be returned as the XHR response
	 */
	abstract public function export( $items );

}