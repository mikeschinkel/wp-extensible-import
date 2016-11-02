<?php
/**
 * @var WPEI_Settings $settings
 */
$settings_name = WPEI::SETTINGS_NAME;
?>
<p><input id="upload-import" type="button" class="button" value="<?php _e( 'File to Import', 'wpei' ); ?>" />
<span class="description"><?php _e('Upload a local file or select one from the Media Library.', 'wpei' ); ?></span></p>
<p><input type="text" id="import_file_url" name="<?php echo $settings_name; ?>[import_file_url]" value="<?php echo esc_url( $settings->import_file_url ); ?>" size="80"></p>
<p><?php _e( ' If the file is <strong>too big to upload</strong>, use FTP to upload to the <code>/wp-content/uploads</code> directory.', 'wpei' ); ?></p>

