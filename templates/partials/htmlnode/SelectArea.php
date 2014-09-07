<div class="select_area"
     id="<?= $id?"$id":""; ?>">
    <? $selected=is_string($selected)?array($selected):$selected; ?>
    <? foreach($options as $option=>$value){
        $options[$option] = array(
            "value"=>isset($value["value"])?$value["value"]:$value,
            "text"=>isset($value["text"])?$value["text"]:$value,
        );
    } ?>
    <? if($label){ ?><label><?= $label; ?></label><? } ?>
    <? if($read_only){ ?>
        <input type="hidden" name="<?= $name; ?>" value="<?= $selected[0]["value"]; ?>" />
        <?= $selected[0]["text"]; ?>
    <? }else{ ?>
        <select
            <?= $read_only?"disabled":""; ?>
            name="<?= $name; ?>"
            >
            <? foreach($options as $option){ ?>
                <option
                    value="<?= $option["value"]; ?>"
                    <?= in_array($option["value"],$selected)?"selected":""; ?>
                    ><?= $option["text"]; ?></option>
            <? }?>
        </select>
    <? } ?>
</div>