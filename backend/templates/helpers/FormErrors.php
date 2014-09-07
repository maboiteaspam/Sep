<? if( $form->has_submit ){ ?>
<script>
    (function(){
        var about_form = $("form[action='<?= $form->about; ?>']");
        var item;
        <? foreach( $form->errors as $field_name=>$data){ ?>
        item =  about_form.find("[name='<?= $field_name.(is_array($data["value"])?"[]":""); ?>']");
        if( item.length > 0){
            <? if( count($data["messages"])>0){ ?>
                item.addClass("has_errors");
            <? } ?>
            <? foreach( $data["messages"] as $id=>$message){ ?>
            about_form.find(".errors").append("<li name='<?= $id; ?>'><?= $message; ?></li>");
            <? } ?>
        }else{
            <? foreach( $data["messages"] as $id=>$message){ ?>
            about_form.find(".errors").append("<li name='<?= $id; ?>'><?= $message; ?></li>");
            <? } ?>
        }
        <? } ?>
    })();
</script>
<? } ?>