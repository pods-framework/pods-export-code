<?php /** @global Pods_Export_Code_Admin $this */ ?>
<div class="wrap">

	<?php screen_icon( 'options-general' ); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<form action="" method="post" class="pods-submittable">
		<div class="stuffbox pods-export-code">
			<h3><label for="link_name"><?php _e( 'Choose which Pods to export', 'pods' ); ?></label></h3>

			<div class="pods-manage-field">
				<div class="pods-field-option-group">
					<p id="toggle-all">
						<a href="#" class="button"><?php _e( 'Toggle all on / off', 'pods' ); ?></a>
					</p>

					<div class="pods-pick-values pods-pick-checkbox pods-zebra">
						<ul>
							<?php
							$zebra = false;
							foreach ( $this->exportable_pods() as $this_pod ) {

								$class = ( $zebra ? 'even' : 'odd' );
								$zebra = ( !$zebra );
								?>
								<li class="pods-zebra-<?php echo $class; ?>">
									<?php echo PodsForm::field( $this_pod[ 'name' ], true, 'boolean', array( 'boolean_yes_label' => $this_pod[ 'name' ] . ( !empty( $this_pod[ 'label' ] ) ? ' (' . $this_pod[ 'label' ] . ')' : '' ) ) ); ?>
								</li>
							<?php
							}
							?>
						</ul>
					</div>
					<div class="submit">
						<a class="button button-primary" id="export" href="#"> Export </a>
					</div>
					<textarea id="feedback"></textarea>
				</div>
			</div>
		</div>
	</form>
</div>
