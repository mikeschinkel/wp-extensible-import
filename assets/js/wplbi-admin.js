jQuery( function($) {
    $("#verify_export").on("click",function($event){
        var exportUrl = $("#export_file_url").val();
        $(".verified-info-row").css("display","table-row");

        $.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: "json",
            data: {
                "action":"verify_export",
                "export_url": exportUrl
            },
            success: function(data){
                if ("error"==data.result) {
                    alert(data.message);
                } else {
                    $("#entry-count").text(data.entryCount.toLocaleString());
                    var link = $("<a/>")
                        .attr("href",data.bloggerAuthorUrl)
                        .attr("target","_blank")
                        .html(data.bloggerAuthorUrl);
                    $("#bloger-author-url").html(link);
                }

            }
        });

        $event.preventDefault();

    });

    $("#blogger_import").click(function($event){
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

