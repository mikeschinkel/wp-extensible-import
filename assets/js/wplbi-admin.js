jQuery( function($) {
    jQuery("#blogger-import").click(function($event){
        $event.preventDefault();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                param0: $('#textbox0').val(),
                param1: $('#textbox1').val(),
                param2: $('#textbox2').val()
            }
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

