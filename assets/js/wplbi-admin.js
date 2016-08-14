jQuery( function($) {
    $("#verify_export").on("click",function($event){
        $event.preventDefault();
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
                    $("#blogger-author-url").html($("<a/>")
                        .attr("href",data.bloggerAuthorUrl)
                        .attr("target","_blank")
                        .html(data.bloggerAuthorUrl)
                    );
                    $("#blogger-blog-url").html($("<a/>")
                        .attr("href",data.bloggerBlogUrl)
                        .attr("target","_blank")
                        .html(data.bloggerBlogUrl)
                    );
                    $("#entry-count").text(data.entryCount.toLocaleString());
                }

            }
        });
    });

    $("#import_content").click(function($event){
        $event.preventDefault();
        var exportUrl = $("#export_file_url").val();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: "json",
            data: {
                "action":"import_content",
                "export_url": exportUrl
            },
            success: function(data){
                if ("error"==data.result) {
                    alert(data.message);
                } else {
                    alert(data.message);
                }

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

