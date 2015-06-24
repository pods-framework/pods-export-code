<?php

/**
 * Class Pods_Export_Pages
 */
class Pods_Export_Pages extends Pods_Export_Post_Object {

	const POST_TYPE = '_pods_page';

	const WILDCARD_REPLACEMENT = '_w_';

	const PRECODE_FILE_SUFFIX = '-PRECODE';

	const OBJECT_FILE_SUFFIX = '-OBJECT';

	/**
	 * @param string $export_directory Directory for the exported files. Will be prefixed with the wp-content path.
	 */
	public function __construct( $export_directory = 'pods-export-pages' ) {

		parent::__construct( self::POST_TYPE, $export_directory );

	}

	/**
	 * @inheritdoc
	 */
	protected function export_to_file( $content, $post ) {

		/** @global $wp_filesystem WP_Filesystem_Base */
		global $wp_filesystem;

		$post_title       = $post->post_title;
		$tree             = explode( '/', $post_title );
		$base_filename    = array_pop( $tree );
		$base_filename    = str_replace( '*', self::WILDCARD_REPLACEMENT, $base_filename );
		$code_filename    = $base_filename . '.php';
		$precode_filename = $base_filename .  self::PRECODE_FILE_SUFFIX . '.php';
		$object_filename  = $base_filename . self:: OBJECT_FILE_SUFFIX . '.php';

		$full_path = $this->export_directory;
		foreach ( $tree as $this_dir ) {
			$full_path = trailingslashit( $full_path ) . $this_dir;

			if ( ! $wp_filesystem->is_dir( $full_path ) ) {
				if ( ! $wp_filesystem->mkdir( $full_path, FS_CHMOD_DIR ) ) {
					return; // Todo: Error/Exception?
				}
			}
		}
		$full_path = trailingslashit( $full_path );

		$object = array(
			'id'            => null,
			'uri'           => $post->post_title,
			'page_template' => get_post_meta( $post->ID, 'page_template', true ),
			'title'         => get_post_meta( $post->ID, 'page_title', true ),
			'options'       => array(
				'admin_only'              => (boolean) get_post_meta( $post->ID, 'admin_only', true ),
				'restrict_role'           => (boolean) get_post_meta( $post->ID, 'restrict_role', true ),
				'restrict_capability'     => (boolean) get_post_meta( $post->ID, 'restrict_capability', true ),
				'roles_allowed'           => get_post_meta( $post->ID, 'roles_allowed', true ),
				'capability_allowed'      => get_post_meta( $post->ID, 'capability_allowed', true ),
				'restrict_redirect'       => (boolean) get_post_meta( $post->ID, 'restrict_redirect', true ),
				'restrict_redirect_login' => (boolean) get_post_meta( $post->ID, 'restrict_redirect_login', true ),
				'restrict_redirect_url'   => get_post_meta( $post->ID, 'restrict_redirect_url', true ),
				'pod'                     => get_post_meta( $post->ID, 'pod', true ),
				'pod_slug'                => get_post_meta( $post->ID, 'pod_slug', true ),
			)
		);

		// Todo: do something besides blindly ignore errors from put_contents?
		$wp_filesystem->put_contents( $full_path . $code_filename, $content, FS_CHMOD_FILE );
		$wp_filesystem->put_contents( $full_path . $precode_filename, $object[ 'precode' ], FS_CHMOD_FILE );
		$wp_filesystem->put_contents( $full_path . $object_filename, serialize( $object ), FS_CHMOD_FILE );

	}

	/**
	 * Hook the 'pods_page_exists' filter and point it here
	 *
	 * @param $object
	 * @param $uri
	 *
	 * @return mixed
	 */
	public function intercept_pods_page( $object, $uri ) {

		/** @global $wp_filesystem WP_Filesystem_Base */
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		global $wp_filesystem;
		WP_Filesystem();

		$tree = explode( '/', $uri );
		//$last_segment = array_pop( $tree );

		$current_dir = $this->export_directory;
		foreach ( $tree as $this_target ) {

			$last_dir    = $current_dir;
			$current_dir = trailingslashit( $current_dir ) . $this_target;
			if ( ! $wp_filesystem->is_dir( $current_dir ) ) {

				// Not a directory and not the last part of the path, then it's no match
				$last_index = count( $tree ) - 1;
				if ( ! $tree[ $last_index ] == $this_target ) {
					return $object;
				}

				// Last part of the pods page path, could be a page name or a wildcard
				$explicit_filename = trailingslashit( $last_dir ) . $this_target . '.php';
				$wildcard_filename = trailingslashit( $last_dir ) . self::WILDCARD_REPLACEMENT . '.php';
				$found             = false;
				if ( $wp_filesystem->is_readable( $explicit_filename ) ) {
					$found   = true;
					$object  = unserialize( $wp_filesystem->get_contents( trailingslashit( $last_dir ) . $this_target . self::OBJECT_FILE_SUFFIX . '.php' ) );
					$code    = $wp_filesystem->get_contents( $explicit_filename );
					$precode = $wp_filesystem->get_contents( trailingslashit( $last_dir ) . $this_target . self:: PRECODE_FILE_SUFFIX . '.php' );
				} elseif ( $wp_filesystem->is_readable( $wildcard_filename ) ) {
					$found   = true;
					$object  = unserialize( $wp_filesystem->get_contents( trailingslashit( $last_dir ) . self::WILDCARD_REPLACEMENT . self::OBJECT_FILE_SUFFIX . '.php' ) );
					$code    = $wp_filesystem->get_contents( $wildcard_filename );
					$precode = $wp_filesystem->get_contents( trailingslashit( $last_dir ) . self::WILDCARD_REPLACEMENT . self::PRECODE_FILE_SUFFIX . '.php' );
				}

				if ( $found ) {
					$object[ 'code' ]    = $code;
					$object[ 'phpcode' ] = $code; // phpcode is deprecated
					$object[ 'precode' ] = $precode;
				}
			}

		}

		return $object;
	}

}