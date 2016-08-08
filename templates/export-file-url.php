<?php
/**
 * @var WPLBI_Settings $settings
 */
?>
<input type="text" id="export_file_url" name="wplbi_settings[export_file_url]" value="<?php echo esc_url( $settings->export_file_url ); ?>" size="80">
<input id="upload-export" type="button" class="button" value="<?php _e( 'Upload Export File', 'wplbi' ); ?>" />
<span class="description"><?php _e('Upload a Blogger XML Export file.', 'wplbi' ); ?></span>
