jQuery( function($) {
    $("#verify_import").on("click",function($event){
        $event.preventDefault();
        var importUrl = $("#import_file_url").val();
        $(".verified-info-row").css("display","table-row");

        $.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: "json",
            data: {
                "action":"verify_import",
                "import_url": importUrl
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
        var importUrl = $("#import_file_url").val();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: "json",
            data: {
                "action":"import_content",
                "import_url": importUrl
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

    $("#upload-import").on("click", function() {
        window.send_to_editor = function(importUrl) {
            $("#import_file_url").val(importUrl);
            tb_remove();
        };
        tb_show('', 'media-upload.php?type=file&TB_iframe=true');
        return false;
    });

}(jQuery));

