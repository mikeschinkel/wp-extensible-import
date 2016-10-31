<?php
/**
 * @var WPEI_Settings $settings
 */
$settings_name = WPEI::SETTINGS_NAME;
?>
<input type="text" id="import_file_url" name="<?php echo $settings_name; ?>[import_file_url]" value="<?php echo esc_url( $settings->import_file_url ); ?>" size="80">
<p><input id="upload-import" type="button" class="button" value="<?php _e( 'Upload or Select Import File to Import', 'wpei' ); ?>" />
<span class="description"><?php _e('Upload a Blogger XML Import file.', 'wpei' ); ?></span></p>
<p><?php _e( ' If the file is too big to upload, use FTP to upload to the <code>/wp-content/uploads</code> directory.', 'wpei' ); ?></p>

