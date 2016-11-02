<?php
/**
 * @var WPEI_Settings $settings
 */
$settings_name = WPEI::SETTINGS_NAME;
?>
<select id="import_type" name="<?php echo $settings_name; ?>[import_type]">
	<option><?php _e( 'Select an Importer', 'wpei' ); ?></option>
	<?php
	$import_types = wpei()->import_types();
	if ( 1 === count( $import_types ) ):
		$settings->import_type_name = key( $import_types );
	endif;
	foreach( $import_types as $index => $import_type ):
		?>
		<option value="<?php esc_attr( $index ); ?>"
			<?php selected( $index, $settings->import_type_name, true ); ?>><?php
			echo esc_attr( $import_type->label_text );
		?></option>
		<?php
	endforeach;
	?>
</select>
