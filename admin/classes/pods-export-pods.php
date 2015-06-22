<?php

/**
 * Class Pods_Export_Pods
 */
class Pods_Export_Pods extends Pods_Export_Code_Object {

	/**
	 * @inheritdoc
	 */
	public function get_item_names() {

		if ( is_null( $this->items ) ) {

			$this->items = array();

			$pods = pods_api()->load_pods( array( 'fields' => false ) );
			foreach ( $pods as $this_pod ) {

				// We do no support table-based Pods
				if ( 'table' == $this_pod[ 'storage' ] ) {
					continue;
				}

				$this->items[ ] = $this_pod[ 'name' ];
			}
		}

		return $this->items;

	}

	/**
	 * @inheritdoc
	 */
	public function export( $items, $output_directory = null ) {

		if ( ! is_array( $items ) ) {
			return;
		}

		$export_to_code = new Pods_Export_Code_API();

		// Output function
		$function = 'register_my_pods_config_' . rand( 11, rand( 1212, 452452 ) * 651 );

		echo "function {$function}() {\n\n";

		foreach ( $items as $this_item ) {
			echo $export_to_code->export_pod( $this_item );
		}

		echo "}\n";
		echo "add_action( 'init', '{$function}' );";

	}
}