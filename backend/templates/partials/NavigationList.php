<form>
    Display items
    <?= $page*$item_cnt; ?> to <?= ($page*$item_cnt)+$item_cnt; ?>,
    total is <?= $total_cnt; ?>,
    remains
    <? $remains=$total_cnt-$item_cnt*($page+1); ?>
    <?= $remains<0?0:$remains ?>
    <br/><br/>
    <label>Items</label> <select name="item_cnt" class="item_cnt_selector">
        <? foreach($items_cnt as $i=>$c){ ?>
            <option value="<?= $c ?>"
                <?= $c==$item_cnt?"selected":"" ?>
                ><?= $c ?></option>
        <? } ?>
    </select>
    <label>Page</label> <select name="page" class="page_selector">
        <option value="0"> - </option>
        <? for($i=1;$i<$page_cnt;$i++){ ?>
        <option value="<?= $i ?>"
            <?= $i==$page?"selected":"" ?>
            ><?= $i ?></option>
        <? } ?>
    </select>
</form>
<script>
    $(".item_cnt_selector").on("change",function(){
        var i = $(".item_cnt_selector").val();
        var p = $(".page_selector").val();
        var h = "<?= $list_href ?>";
        h += (i>0?"/"+i:"");
        h += (p>0?"/"+p:"");
        h += window.location.search;
        window.location.href = h;
    });
    $(".page_selector").on("change",function(){
        var i = $(".item_cnt_selector").val();
        var p = $(".page_selector").val();
        var h = "<?= $list_href ?>";
        h += (i>0?"/"+i:"");
        h += (p>0?"/"+p:"");
        h += window.location.search;
        window.location.href = h;
    });
</script>