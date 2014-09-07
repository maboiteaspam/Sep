<ul>
    <? foreach($items as $group_name=>$item){ ?>
        <? if( $item["display"] ){ ?>
            <? if( isset($item["items"]) ){ ?>
                <li group_name="<?= $group_name; ?>"><b style="cursor: pointer"><?= $item["label"]; ?></b></li>
                <? foreach($item["items"] as $item_name=>$item_data){ ?>
                    <li group_item="<?= $group_name; ?>"><a href="<?= $item_data["href"]; ?>"
                           base="<?= $item_data["base"]; ?>"
                            ><?= $item_data["label"]; ?></a></li>
                <? } ?>
            <? }else{ ?>
                <li><a href="<?= $item["href"]; ?>"
                       base="<?= $item["base"]; ?>"
                        ><?= $item["label"]; ?></a></li>
            <? } ?>
        <? } ?>
    <? } ?>
</ul>
<script>
    if( window.location.pathname == "/" ){
        $(".left_area a[base='/']").addClass("pure-menu-selected");
    }else{
        $(".left_area ul li").each(function(k,v){
            var base = $(v).find("a").attr("base");
            if( base && window.location.pathname.substr(0, base.length) == base ){
                $(v).addClass("pure-menu-selected");
            }
        });
    }
    $(".left_area ul li[group_name]").each(function(k,v){
        var group_name = $(v).attr("group_name");
        if($(".left_area ul li[group_item='"+group_name+"'].pure-menu-selected").length == 0 ){
            $(".left_area ul li[group_item='"+group_name+"']").addClass("hide");
        }
    });
    $(".left_area ul li[group_name]").click(function(e){
        var group_name = $(this).attr("group_name");
        var group_items = $(".left_area ul li[group_item='"+group_name+"']");
        if( group_items.hasClass("hide") ){
            group_items
                .css("height","0")
                .css("overflow","hidden")
                .css("visibility","visible");
            group_items.removeClass("hide");
            group_items.animate({
                height:35
            },250,"easeOutCirc",function(){
                group_items
                    .css("height","auto")
                    .css("overflow","visible");
            });
        }else{
            group_items
                .css("overflow","hidden");
            group_items.animate({
                height:0
            },250,"easeOutCirc",function(){
                group_items.addClass("hide");
                group_items
                    .css("height","auto")
            });
        }
    });
</script>