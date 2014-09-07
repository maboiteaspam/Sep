<div class="inputdatetime_area"
     id="<?= $id?"$id":""; ?>">
    <? if($label){ ?><label for="<?= $id?"$id":""; ?>_input"><?= $label; ?></label><? } ?>
    <?
    $date = date_create($value);
    if($min_range){
        $min_range = date_create($min_range);
    }else{
        $min_range = date_create("now");
        date_sub($min_range, date_interval_create_from_date_string('80 year'));
    }
    if($max_range){
        $max_range = date_create($max_range);
    }else{
        $max_range = date_create("now");
        date_add($max_range, date_interval_create_from_date_string('80 year'));
    }
    ?>
    <input type="hidden"
           name="<?= $name; ?>"
           value="<?= $date->format('Y-m-d H:i:s'); ?>"
        <?= $required?"required":""; ?> />
    <? if( $read_only){ ?>
        <?= date_format($date, 'l jS F Y, g:ia'); ?>
    <? }else{ ?>
        <select name="<?= $name; ?>_year"
                id="<?= $id?"$id":""; ?>_input">
            <? for($i=$min_range->format('Y'),$e=$max_range->format('Y');$i<$e;$i++){ ?>
                <option value="<?= $i<10?"0$i":$i ?>"
                    <?= $date->format('Y')==$i?"selected":""; ?>
                    ><?= $i<10?"0$i":$i ?></option>
            <? } ?>
        </select>
        <select name="<?= $name; ?>_month">
            <? for($i=1,$e=13;$i<$e;$i++){ ?>
                <option value="<?= $i<10?"0$i":$i ?>"
                    <?= $date->format('m')==$i?"selected":""; ?>
                    ><?= $i<10?"0$i":$i ?></option>
            <? } ?>
        </select>
        <select name="<?= $name; ?>_day">
            <? for($i=1,$e=32;$i<$e;$i++){ ?>
                <option value="<?= $i<10?"0$i":$i ?>"
                    <?= $date->format('d')==$i?"selected":""; ?>
                    ><?= $i<10?"0$i":$i ?></option>
            <? } ?>
        </select>
        <select name="<?= $name; ?>_hour">
            <? for($i=0,$e=24;$i<$e;$i++){ ?>
                <option value="<?= $i<10?"0$i":$i ?>"
                    <?= $date->format('H')==$i?"selected":""; ?>
                    ><?= $i<10?"0$i":$i ?></option>
            <? } ?>
        </select>
        <select name="<?= $name; ?>_minute">
            <? for($i=0,$e=60;$i<$e;$i++){ ?>
                <option value="<?= $i<10?"0$i":$i ?>"
                    <?= $date->format('i')==$i?"selected":""; ?>
                    ><?= $i<10?"0$i":$i ?></option>
            <? } ?>
        </select>
        <select name="<?= $name; ?>_second">
            <? for($i=0,$e=60;$i<$e;$i++){ ?>
                <option value="<?= $i<10?"0$i":$i ?>"
                    <?= $date->format('s')==$i?"selected":""; ?>
                    ><?= $i<10?"0$i":$i ?></option>
            <? } ?>
        </select>
    <? } ?>
</div>
<script>
    (function(el){
        if( el.length ){
            el.find("select").on("change",function(){
                el.find("input[type='hidden']").val(
                    el.find("select[name='<?= $name; ?>_year']").val()+"-"+
                    el.find("select[name='<?= $name; ?>_month']").val()+"-"+
                    el.find("select[name='<?= $name; ?>_day']").val()+" "+
                    el.find("select[name='<?= $name; ?>_hour']").val()+":"+
                    el.find("select[name='<?= $name; ?>_minute']").val()+":"+
                    el.find("select[name='<?= $name; ?>_second']").val()+""
                )
            })
        }
    })($("#<?= $id?"$id":""; ?>"));
</script>