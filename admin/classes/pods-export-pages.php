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
		$precode_filename = $base_filename . self::PRECODE_FILE_SUFFIX . '.php';
		$object_filename  = $base_filename . self:: OBJECT_FILE_SUFFIX . '.php';

		$full_path = $this->export_directory;
		foreach ( $tree as $this_dir ) {
			$this_dir  = str_replace( '*', self::WILDCARD_REPLACEMENT, $this_dir );
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

		$code_file = $this->find_pods_page( $this->export_directory, $uri );
		if ( is_null( $code_file ) ) {
			return $object;
		}
		$code_file    = trailingslashit( $this->export_directory ) . $code_file;
		$base_name    = preg_replace( '/\.php$/', '', $code_file );
		$precode_file = $base_name . self::PRECODE_FILE_SUFFIX . '.php';
		$object_file  = $base_name . self::OBJECT_FILE_SUFFIX . '.php';

		$object  = unserialize( $wp_filesystem->get_contents( $object_file ) );
		$code    = $wp_filesystem->get_contents( $code_file );
		$precode = $wp_filesystem->get_contents( $precode_file );

		$object[ 'code' ]    = $code;
		$object[ 'phpcode' ] = $code; // phpcode is deprecated
		$object[ 'precode' ] = $precode;

		return $object;
	}

	/**
	 * @param string $starting_dir
	 * @param string $uri
	 *
	 * @return string|null Matching subdirectory name or NULL
	 */
	protected function find_pods_page( $starting_dir, $uri ) {

		$uri_segments   = explode( '/', $uri );
		$target         = array_shift( $uri_segments );
		$wildcard_match = null;

		// The final segment will be a physical file
		if ( 0 == count( $uri_segments ) ) {
			$target .= '.php';

			$files = scandir( $starting_dir );
			foreach ( $files as $this_file ) {
				if ( is_dir( $this_file ) ) {
					continue;
				}

				// Return exact matches right away
				if ( $this_file == $target ) {
					return $this_file;
				}

				// Check for wildcards as a fallback
				$this_file_pcre = str_replace( self::WILDCARD_REPLACEMENT, '(.*)', $this_file ); // Convert wildcards to PCRE
				if ( preg_match( '/^' . $this_file_pcre . '$/', $target ) ) {
					$wildcard_match = $this_file;
				}
			}

			// No exact match found but maybe there was a wildcard match
			if ( ! isnull( $wildcard_match ) ) {
				return $wildcard_match;
			} else {
				return null;
			}

			// This isn't the last segment so it has to match a directory
		} else {

			$dir_list = glob( trailingslashit( $starting_dir ) . '*', GLOB_ONLYDIR ); // Full path of all subdirectories
			if ( ! is_array( $dir_list ) || 0 >= count( $dir_list ) ) {
				return null;
			}

			$dir_list = array_map( 'basename', $dir_list ); // Just the subdirectory names
			foreach ( $dir_list as $subdirectory ) {

				// Favor exact matches
				if ( $subdirectory == $target ) {
					$check_dir = trailingslashit( $starting_dir ) . $subdirectory;
					$path      = $this->find_pods_page( $check_dir, implode( '/', $uri_segments ) );

					if ( ! is_null( $path ) ) {
						return trailingslashit( $subdirectory ) . $path;
					}
				} else {
					// Check for wildcards matches but stash them as a last resort
					$subdirectory_pcre = str_replace( self::WILDCARD_REPLACEMENT, '(.*)', $subdirectory ); // Convert wildcards to PCRE
					if ( preg_match( '/^' . $subdirectory_pcre . '$/', $target ) ) {
						$check_dir = trailingslashit( $starting_dir ) . $subdirectory;
						$path      = $this->find_pods_page( $check_dir, implode( '/', $uri_segments ) );

						if ( ! is_null( $path ) ) {
							$wildcard_match = trailingslashit( $subdirectory ) . $path;
						}
					}
				}
			}

			// Check for wildcard fall back
			if ( ! is_null( $wildcard_match ) ) {
				return $wildcard_match;
			} else {
				return null;
			}

		}

	}

}