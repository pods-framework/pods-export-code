<?php

/**
 * Class Pods_Export_Pages
 */
class Pods_Export_Post_Object extends Pods_Export_Code_Object {

	/**
	 * @var string
	 */
	public $post_type;

	/**
	 * @param string $post_type
	 */
	public function __construct( $post_type ) {

		$this->post_type = $post_type;

	}

	/**
	 * @inheritdoc
	 */
	public function get_item_names() {

		if ( is_null( $this->items ) ) {

			$this->items = array();

			$args  = array(
				'post_type' => $this->post_type,
				'nopaging'  => true
			);
			$query = new WP_Query( $args );

			if ( is_array( $query->posts ) ) {

				/** @var WP_Post $this_post */
				foreach ( $query->posts as $this_post ) {
					$this->items[ ] = $this_post->post_name;
				}
			}
		}

		return $this->items;

	}

	/**
	 * @inheritdoc
	 */
	public function export( $items, $output_directory = null ) {

		if ( ! is_array( $items ) || empty( $items ) ) {
			return;
		}

		foreach ( $items as $this_item ) {

			// Lookup this template in the posts table
			$args  = array(
				'post_type' => $this->post_type,
				'name'      => $this_item
			);
			$query = new WP_Query( $args );
			$post  = $query->post;

			// Found it?
			if ( is_a( $post, 'WP_Post' ) ) {

				/** @global $wp_filesystem WP_Filesystem_Base */
				global $wp_filesystem;

				WP_Filesystem();

				if ( ! $wp_filesystem ) {
					return;
				}

				$template_export_dir = $wp_filesystem->wp_content_dir() . $output_directory;

				if ( ! $wp_filesystem->is_dir( $template_export_dir ) ) {
					if ( ! $wp_filesystem->mkdir( $template_export_dir, FS_CHMOD_DIR ) ) {
						return;
					}
				}

				// Todo: do something besides blindly ignore errors from put_contents?
				$filename = trailingslashit( $template_export_dir ) . $this_item . '.php';
				$wp_filesystem->put_contents( $filename, $post->post_content, FS_CHMOD_FILE );
			}
		}

	}

}