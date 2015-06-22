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
					$this->items[] = $this_post->post_name;
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
			return '';
		}

		/** @global $wp_filesystem WP_Filesystem_Base */
		global $wp_filesystem;

		WP_Filesystem();

		if ( ! $wp_filesystem ) {
			return ''; // Todo: do we want to provide any feedback?
		}

		$template_export_dir = $wp_filesystem->wp_content_dir() . $output_directory;

		if ( ! $wp_filesystem->is_dir( $template_export_dir ) ) {
			if ( ! $wp_filesystem->mkdir( $template_export_dir, FS_CHMOD_DIR ) ) {
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

				// Todo: do something besides blindly ignore errors from put_contents?
				$filename = trailingslashit( $template_export_dir ) . $this_item . '.php';
				$wp_filesystem->put_contents( $filename, $content, FS_CHMOD_FILE );
			}
		}

		return ''; // Todo: do we want to provide any feedback?

	}

}