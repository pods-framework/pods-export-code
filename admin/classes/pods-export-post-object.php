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
				'orderby'   => 'name',
				'order'     => 'ASC',
				'nopaging'  => true
			);
			$query = new WP_Query( $args );

			if ( is_array( $query->posts ) ) {

				/** @var WP_Post $this_post */
				foreach ( $query->posts as $this_post ) {
					$this->items[ $this_post->post_name ] = $this_post->post_title;
				}
			}
		}

		return $this->items;

	}

	/**
	 * @inheritdoc
	 */
	public function export( $items, $export_directory = null ) {

		/** @global $wp_filesystem WP_Filesystem_Base */
		global $wp_filesystem;

		if ( ! is_array( $items ) || empty( $items ) ) {
			return '';
		}

		WP_Filesystem();
		if ( ! $wp_filesystem ) {
			return ''; // Todo: do we want to provide any feedback?
		}

		$export_root = $wp_filesystem->wp_content_dir() . $export_directory;

		if ( ! $wp_filesystem->is_dir( $export_root ) ) {
			if ( ! $wp_filesystem->mkdir( $export_root, FS_CHMOD_DIR ) ) {
				return ''; // Todo: do we want to provide any feedback?
			}
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
				/**
				 * Filter the post content before writing out to the file
				 *
				 * @since 0.9.1
				 *
				 * @param string $value
				 */
				$content = apply_filters( "pods_export_code_post_content{$this->post_type}", $post->post_content );

				$this->export_to_file( $export_root, $content, $post );
			}
		}

		return ''; // Todo: do we want to provide any feedback?

	}

	/**
	 * @param string  $export_root Full server path to the root export directory
	 * @param string  $content     The content to be saved (already filtered)
	 * @param WP_Post $post
	 *
	 */
	protected function export_to_file( $export_root, $content, $post ) {

		/** @global $wp_filesystem WP_Filesystem_Base */
		global $wp_filesystem;

		// Todo: do something besides blindly ignore errors from put_contents?
		$filename = trailingslashit( $export_root ) . $post->post_name . '.php';
		$wp_filesystem->put_contents( $filename, $content, FS_CHMOD_FILE );

	}

}