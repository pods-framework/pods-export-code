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
		$params = array(
			'name' => $pod_name,
			'fields' => true,
		);

		$pod = $this->api->load_pod( $params, false );

		// Exit if the pod wasn't found or is table based (not supported)
		if ( false === $pod || !isset( $pod[ 'storage' ] ) || 'table' == $pod[ 'storage' ] ) {
			return '';
		}

		// Pull out the field list
		$fields = $pod[ 'fields' ];

		$options_ignore = array(
			'id',
			'pod_id',
			'old_name',
			'object_type',
			'object_name',
			'object_hierarchical',
			'table',
			'meta_table',
			'pod_table',
			'field_id',
			'field_index',
			'field_slug',
			'field_type',
			'field_parent',
			'field_parent_select',
			'meta_field_id',
			'meta_field_index',
			'meta_field_value',
			'pod_field_id',
			'pod_field_index',
			'fields',
			'object_fields',
			'join',
			'where',
			'where_default',
			'orderby',
			'pod',
			'recurse',
			'table_info',
			'attributes',
			'group',
			'grouped',
			'developer_mode',
			'dependency',
			'depends-on',
			'excludes-on'
		);

		$empties = array(
			'description',
			'alias',
			'help',
			'class',
			'pick_object',
			'pick_val',
			'sister_id',
			'required',
			'unique',
			'admin_only',
			'restrict_role',
			'restrict_capability',
			'hidden',
			'read_only',
			'object',
			'label_singular'
		);

		$field_types = PodsForm::field_types();

		$field_type_options = array();

		foreach ( $field_types as $type => $field_type_data ) {
			$field_type_options[ $type ] = PodsForm::ui_options( $type );
		}

		if ( isset( $pod[ 'options' ] ) ) {
			$pod = array_merge( $pod, $pod[ 'options' ] );

			unset( $pod[ 'options' ] );
		}

		foreach ( $pod as $option => $option_value ) {
			if ( in_array( $option, $options_ignore ) || null === $option_value ) {
				unset( $pod[ $option ] );
			}
			elseif ( in_array( $option, $empties ) && ( empty( $option_value ) || '0' == $option_value ) ) {
				if ( 'restrict_role' == $option && isset( $pod[ 'roles_allowed' ] ) ) {
					unset( $pod[ 'roles_allowed' ] );
				}
				elseif ( 'restrict_capability' == $option && isset( $pod[ 'capabilities_allowed' ] ) ) {
					unset( $pod[ 'capabilities_allowed' ] );
				}

				unset( $pod[ $option ] );
			}
		}

		if ( !empty( $fields ) ) {
			foreach ( $fields as &$field ) {
				if ( isset( $field[ 'options' ] ) ) {
					$field = array_merge( $field, $field[ 'options' ] );

					unset( $field[ 'options' ] );
				}

				foreach ( $field as $option => $option_value ) {
					if ( in_array( $option, $options_ignore ) || null === $option_value ) {
						unset( $field[ $option ] );
					}
					elseif ( in_array( $option, $empties ) && ( empty( $option_value ) || '0' == $option_value ) ) {
						if ( 'restrict_role' == $option && isset( $field[ 'roles_allowed' ] ) ) {
							unset( $field[ 'roles_allowed' ] );
						}
						elseif ( 'restrict_capability' == $option && isset( $field[ 'capabilities_allowed' ] ) ) {
							unset( $field[ 'capabilities_allowed' ] );
						}

						unset( $field[ $option ] );
					}
				}

				foreach ( $field_type_options as $type => $options ) {
					if ( $type == pods_var( 'type', $field ) ) {
						continue;
					}

					foreach ( $options as $option_data ) {
						if ( isset( $option_data[ 'group' ] ) && is_array( $option_data[ 'group' ] ) && !empty( $option_data[ 'group' ] ) ) {
							if ( isset( $field[ $option_data[ 'name' ] ] ) ) {
								unset( $field[ $option_data[ 'name' ] ] );
							}

							foreach ( $option_data[ 'group' ] as $group_option_data ) {
								if ( isset( $field[ $group_option_data[ 'name' ] ] ) ) {
									unset( $field[ $group_option_data[ 'name' ] ] );
								}
							}
						}
						elseif ( isset( $field[ $option_data[ 'name' ] ] ) ) {
							unset( $field[ $option_data[ 'name' ] ] );
						}
					}
				}
			}
		}

		// Output the pods_register_type() call
		$output .= sprintf( "\t\$pod = %s;\n\n", preg_replace( '/\d+ => /', '', var_export( $pod, true ) ) );
		$output .= "\tpods_register_type( \$pod[ 'type' ], \$pod[ 'name' ], \$pod );\n\n";

		// Output a pods_register_field() call for each field
		foreach ( $fields as $this_field ) {
			$output .= sprintf( "\t\$field = %s;\n\n", preg_replace( '/\d+ => /', '', var_export( $this_field, true ) ) );
			$output .= "\tpods_register_field( \$pod[ 'name' ], \$field[ 'name' ], \$field );\n\n";
		}

		return $output;

	}

}