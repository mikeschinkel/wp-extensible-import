jQuery( function($) {
    jQuery("#blogger-import").click(function($event){
        $event.preventDefault();
        alert( 'This does not work yet.' );
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {}
        });
    });

    $("#upload-export").on("click", function() {
        window.send_to_editor = function(exportUrl) {
            $("#export_file_url").val(exportUrl);
            tb_remove();
        };
        tb_show('', 'media-upload.php?type=file&TB_iframe=true');
        return false;
    });

}(jQuery));

