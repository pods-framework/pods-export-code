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

		$post_title       = $post->post_title;
		$tree             = explode( '/', $post_title );
		$base_filename    = array_pop( $tree );
		$code_filename    = str_replace( '*', 'w', $base_filename ) . '.php';
		$precode_filename = str_replace( '*', 'w', $base_filename ) . '-PRECODE.php';

		$full_path = $export_root;
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
			'code'          => $post->post_content,
			'phpcode'       => $post->post_content, // phpcode is deprecated
			'precode'       => get_post_meta( $post->ID, 'precode', true ),
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

	}

}