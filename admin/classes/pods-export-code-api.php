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
	public function __construct() {
		$this->api = pods_api();
	}

	/**
	 * @param string $pod_name
	 *
	 * @return string
	 */
	public function export_pod( $pod_name ) {
		$output = '';

		// Attempt to load the pod, don't throw an exception on error
		$params = [
			'name'   => $pod_name,
			'fields' => true,
		];

		$pod = $this->api->load_pod( $params, false );

		// Exit if the pod wasn't found or is table based (not supported)
		if ( false === $pod ) {
			return 'ERROR: Pod not found.';
		} elseif ( ! isset( $pod['storage'] ) || 'table' == $pod['storage'] ) {
			return 'ERROR: Pod storage type not supported.';
		}

		$options_to_remove = [
			'id',
			'object_type',
			'object_storage_type',
			'parent',
			'group',
		];

		$groups = $pod->get_groups();

		$pod = $pod->export( [
			'include_groups'       => false,
			'include_group_fields' => false,
			'include_fields'       => false,
		] );

		foreach ( $options_to_remove as $option_to_remove ) {
			if ( isset( $pod[ $option_to_remove ] ) ) {
				unset( $pod[ $option_to_remove ] );
			}
		}

		foreach ( $pod as $option => $value ) {
			if ( 0 === (int) $value && 0 === strpos( $option, 'built_in_' ) ) {
				unset( $pod[ $option ] );
			}
		}

		// Output the pods_register_type() call
		$output .= sprintf( "\t\$pod = %s;\n\n", $this->var_export_format( $pod, 1 ) );
		$output .= "\tpods_register_type( \$pod['type'], \$pod['name'], \$pod );\n\n";

		// Output a pods_register_field() call for each field
		foreach ( $groups as $group ) {
			$group_fields = $group->get_fields();

			$group = $group->export( [
				'include_groups'       => false,
				'include_group_fields' => false,
				'include_fields'       => false,
			] );

			foreach ( $options_to_remove as $option_to_remove ) {
				if ( isset( $group[ $option_to_remove ] ) ) {
					unset( $group[ $option_to_remove ] );
				}
			}

			$group_fields_to_export = [];

			// Output a pods_register_field() call for each field
			foreach ( $group_fields as $group_field ) {
				$group_field = $group_field->export( [
					'include_groups'       => false,
					'include_group_fields' => false,
					'include_fields'       => false,
				] );

				foreach ( $options_to_remove as $option_to_remove ) {
					if ( isset( $group_field[ $option_to_remove ] ) ) {
						unset( $group_field[ $option_to_remove ] );
					}
				}

				$group_fields_to_export[ $group_field['name'] ] = $group_field;
			}

			$output .= sprintf( "\t\$group = %s;\n\n", $this->var_export_format( $group, 1 ) );
			$output .= sprintf( "\t\$group_fields = %s;\n\n", $this->var_export_format( $group_fields_to_export, 1 ) );
			$output .= "\tpods_register_group( \$group, \$pod['name'], \$group_fields );\n\n";
		}

		return $output;
	}

	/**
	 * @param mixed $var
	 * @param int   $leading_tabs
	 *
	 * @return string
	 */
	private function var_export_format( $var, $leading_tabs = 0 ) {
		$var          = var_export( $var, true );
		$leading_tabs = str_repeat( "\t", $leading_tabs );

		// Convert params like 0 => 'Option 1' to just 'Option 1'
		$var = preg_replace( '/\d+ => /', '', $var );

		$output = '';

		foreach ( preg_split( '~[\r\n]+~', $var ) as $line ) {
			// Skip blank lines
			if ( empty( $line ) || ctype_space( $line ) ) {
				continue;
			}

			// Leading tabs plus replace double spaces with tabs
			$output .= sprintf( "%s%s\n", $leading_tabs, preg_replace( "/ {2}/", "\t", $line ) );
		}

		$output = ltrim( rtrim( $output, "\n" ), "\t" );

		// Trim the leading tab and the final newline.
		$output = preg_replace( '/\d+ => /', '', $output );
		$output = preg_replace( '/array \(\n/', "array(\n", $output );
		$output = preg_replace( '/\n\s*array\(\n/', "array(\n", $output );
		$output = preg_replace( '/\n\n/', "\n", $output );

		return $output;
	}

}