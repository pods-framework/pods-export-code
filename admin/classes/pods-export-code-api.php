<?php

/**
 * Class Pods_Export_Code_API
 */
class Pods_Export_Code_API {

	/**
	 * @var PodsAPI $api
	 */
	private $api = null;

	/**
	 *
	 */
	public function __construct () {
		$this->api = pods_api();
	}

	/**
	 * @param string $pod_name
	 *
	 * @return string
	 */
	public function export_pod ( $pod_name ) {

		$output = '';

		// Attempt to load the pod, don't throw an exception on error
		$params = array(
			'name'   => $pod_name,
			'fields' => true,
		);
		$pod = $this->api->load_pod( $params, false );

		// Exit if the pod wasn't found or is table based (not supported)
		if ( false === $pod || !isset( $pod[ 'storage' ] ) || 'table' == $pod[ 'storage' ] ) {
			return '';
		}

		// Pull out the field list
		$fields = $pod[ 'fields' ];
		unset( $pod[ 'fields' ] );
		unset( $pod[ 'object_fields' ] );
		unset( $pod[ 'id' ] );

		// Output the pods_register_type() call
		$output .= sprintf( "\$pod = %s;\n\n", var_export( $pod, true ) );
		$output .= sprintf( "pods_register_type( '%s', '%s', \$pod );\n\n", $pod[ 'type' ], $pod_name );

		// Output a pods_register_field() call for each field
		foreach ( $fields as $this_field ) {
			$output .= sprintf( "\$field = %s;\n\n", var_export( $this_field, true ) );
			$output .= sprintf( "pods_register_field( '%s', '%s', \$field );\n\n", $pod_name, $this_field[ 'name' ] );
		}

		return $output;
	}

}