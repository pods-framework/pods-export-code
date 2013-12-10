<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 */
?>
	<div class="wrap">

		<?php screen_icon( 'options-general' ); ?>
		<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

		<form action="" method="post" class="pods-submittable">
			<div class="stuffbox pods-export-code">
				<h3><label for="link_name"><?php _e( 'Choose which Pods to export', 'pods' ); ?></label></h3>

				<div class="pods-manage-field">
					<div class="pods-field-option-group">
						<p>
							<a href="#" class="button" ><?php _e( 'Toggle all on / off', 'pods' ); ?></a>
						</p>

						<div class="pods-pick-values pods-pick-checkbox pods-zebra">
							<ul>
								<?php
								$pods = pods_api()->load_pods( array( 'fields' => false ) );
								$zebra = false;
								foreach ( $pods as $this_pod ) {

									// We only support meta-based Pods
									if ( 'table' != $this_pod[ 'storage' ] ) {
										continue;
									}

									$class = ( $zebra ? 'even' : 'odd' );
									$zebra = ( !$zebra );
									?>
									<li class="pods-zebra-<?php echo $class; ?>">
										<?php echo PodsForm::field( 'pods' . '[' . $this_pod[ 'id' ] . ']', true, 'boolean', array( 'boolean_yes_label' => $this_pod[ 'name' ] . ( !empty( $this_pod[ 'label' ] ) ? ' (' . $this_pod[ 'label' ] . ')' : '' ) ) ); ?>
									</li>
								<?php
								}
								?>
							</ul>
						</div>
						<div class="submit">
							<a class="button button-primary" id="export" href="#"> Export </a>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
<?php

$export_to_code = new Pods_Export_Code_API();
$api = pods_api();

$pods = $api->load_pods( array( 'names' => true ) );

foreach ( $pods as $this_pod => $label ) {
	echo "<pre>" . $export_to_code->export_pod( $this_pod ) . "</pre>";
}
