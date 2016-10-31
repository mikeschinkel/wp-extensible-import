<?php
/**
 * @var
 */
?>
<style type="text/css">
.submit .button { margin-right: 1em; }
.progress-bar {
	height: 20px;  /* Can be anything */
	position: relative;
	background: #555;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
	padding: 10px;
	box-shadow: inset 0 -1px 1px rgba(255,255,255,0.3);
}
.progress-bar > span {
	display: block;
	height: 100%;
	border-top-right-radius: 8px;
	border-bottom-right-radius: 8px;
	border-top-left-radius: 4px;
	border-bottom-left-radius: 4px;
	background-color: rgb(43,194,83);
	background-image: linear-gradient(
		center bottom,
		rgb(43,194,83) 37%,
		rgb(84,240,84) 69%
	);
	box-shadow:
		inset 0 2px 9px  rgba(255,255,255,0.3),
		inset 0 -2px 6px rgba(0,0,0,0.4);
	position: relative;
	overflow: hidden;
}
#progress-bars, #entry-count, #row-count {display: none};
</style>
<div class="wrap">
	<h1><?php _e( 'Extensible Import', 'wpei' ); ?></h1>
	<?php settings_errors(); ?>
	<form method="post" action="<?php echo wpei()->postback_url() ?>">
		<?php
			settings_fields( 'wpei_settings' );
			do_settings_sections( WPEI::PAGE_NAME );

		?>
		<div id="import_file_info" style="display:none;"></div>
       <?php
			echo '<p class="submit">';
			submit_button( __( 'Save Changes', 'wpei' ), 'primary', 'submit', false );
			submit_button( __( 'Veryify Import File', 'wpei' ), 'secondary', 'verify_import', false );
			submit_button( __( 'Import Content', 'wpei' ), 'secondary', 'blogger_import', false );
			echo '</p>';
		?>
		<div id="progress-bars">
			<h2>Blogger Import File Parsing <span id="entry-count">[]</span>:</h2>
			<div id="parser-progress-bar" class="progress-bar"><span></span></div>
			<h2>Content Importing <span id="row-count">[]</span>:</h2>
			<div id="importer-progress-bar"  class="progress-bar"><span></span></div>
		</div>
	</form>
</div>

