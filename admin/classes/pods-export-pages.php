<?php

/**
 * Class Pods_Export_Pages
 */
class Pods_Export_Pages extends Pods_Export_Post_Object {

	const POST_TYPE = '_pods_page';

	/**
	 *
	 */
	public function __construct() {

		parent::__construct( self::POST_TYPE );

	}

	/**
	 * @inheritdoc
	 */
	protected function export_to_file( $export_root, $content, $post ) {

		/** @global $wp_filesystem WP_Filesystem_Base */
		global $wp_filesystem;

		$post_title = $post->post_title;
		$tree = explode( '/', $post_title );
		$filename = array_pop( $tree );
		$filename = str_replace( '*', 'w', $filename ) . '.php';

		$full_path = $export_root;
		foreach( $tree as $this_dir ) {
			$full_path = trailingslashit( $full_path ) . $this_dir;

			if ( ! $wp_filesystem->is_dir( $full_path ) ) {
				if ( ! $wp_filesystem->mkdir( $full_path, FS_CHMOD_DIR ) ) {
					return; // Todo: Error/Exception?
				}
			}
		}

		// Todo: do something besides blindly ignore errors from put_contents?
		$filename = trailingslashit( $full_path ) . $filename;
		$wp_filesystem->put_contents( $filename, $content, FS_CHMOD_FILE );

	}

}