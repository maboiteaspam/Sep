<div class="top_detail_area">
    <h2><?= $detail_title ?></h2>
</div>
<div class="body_detail_area">
    <form action="<?= $submit_path ?>" method="POST">
        <ul class="errors"></ul>
        <?= $content_form ?>
        <br/>
        <div style="display: inline-block;width: 25%;">
            <? if( $previous_href) { ?>
                <a href="<?= $previous_href ?>"
                   class="link_area"
                   name="back"><?= $previous_title ?></a>
            <? } ?>
            <? if( $delete_href) { ?>
                <a href="<?= $delete_href ?>"
                   class="link_area"
                   for="<?= $delete_href ?>"
                   name="delete"
                   confirm="<?= $delete_confirm_message?"confirm":""; ?>"
                   confirm_message="<?= $delete_confirm_message ?>"><?= $delete_title ?></a>
            <? } ?>
        </div>
        <div style="display: inline-block;">
            <a href="<?= $submit_path ?>"
               class="link_area"
               for="<?= $submit_path ?>"
               name="<?= $delete_href?"edit":"add" ?>"><?= $submit_title ?></a>
            <input type="submit"
                   name="s_<?= $delete_href?"edit":"add" ?>" style="opacity: 0;" />
        </div>
    </form>
    <form action="<?= $delete_href ?>" method="POST"></form>
</div>
<div class="bottom_detail_area">
</div>

<style type="text/css">
    .has_errors{
        border-color: #e9322d !important
    }
    .modified{
        border-color: #0078e7 !important
    }
</style>
<script>
    (function(){
        var form = $("form[action='<?= $submit_path ?>']");
        form.find("input,select,textarea").each(function(k,v){
            var c_data = $(v).val();
            $(v).data("initial_data_forcheck__",c_data);
        });
        var prevent_leave = true;
        form.on("submit",function(){
            prevent_leave = false;
        });
        $(".link_area[name='delete']").on("click",function(){
            prevent_leave = false;
        });
        $(window).on("beforeunload",function(){
            if( prevent_leave ){
                form.find("input,select,textarea").each(function(k,v){
                    $(v).removeClass("modified");
                    var c_data = $(v).val();
                    if( c_data != $(v).data("initial_data_forcheck__") ){
                        $(v).addClass("modified");
                    }
                });
                if( form.find(".modified").length>0){
                    return "Your document is modified.";
                }
            }
        });
    })();
</script>