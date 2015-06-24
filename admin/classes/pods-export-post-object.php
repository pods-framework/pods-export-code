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
	 * @var string Full directory path for the exported files
	 */
	public $export_directory;

	/**
	 * @param string $post_type
	 * @param string $export_directory Directory for the exported files. Will be prefixed with the wp-content path.
	 */
	public function __construct( $post_type, $export_directory ) {

		/** @global $wp_filesystem WP_Filesystem_Base */
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		global $wp_filesystem;
		WP_Filesystem();

		$this->post_type = $post_type;

		$this->export_directory = $wp_filesystem->wp_content_dir() . $export_directory;
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
	public function export( $items ) {

		/** @global $wp_filesystem WP_Filesystem_Base */
		global $wp_filesystem;

		if ( ! is_array( $items ) || empty( $items ) ) {
			return '';
		}

		WP_Filesystem();
		if ( ! $wp_filesystem ) {
			return ''; // Todo: do we want to provide any feedback?
		}

		if ( ! $wp_filesystem->is_dir( $this->export_directory ) ) {
			if ( ! $wp_filesystem->mkdir( $this->export_directory, FS_CHMOD_DIR ) ) {
				return ''; // Todo: do we want to provide any feedback?
			}
		}

		foreach ( $items as $this_item ) {

			// Lookup this template in the posts table
			$args  = array(
				'post_type'   => $this->post_type,
				'post_status' => 'publish',
				'name'        => $this_item
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

				$this->export_to_file( $content, $post );
			}
		}

		return ''; // Todo: do we want to provide any feedback?

	}

	/**
	 * @param string  $content The content to be saved (already filtered)
	 * @param WP_Post $post
	 */
	protected function export_to_file( $content, $post ) {

		/** @global $wp_filesystem WP_Filesystem_Base */
		global $wp_filesystem;

		// Todo: do something besides blindly ignore errors from put_contents?
		$filename = trailingslashit( $this->export_directory ) . $post->post_name . '.php';
		$wp_filesystem->put_contents( $filename, $content, FS_CHMOD_FILE );

	}

}