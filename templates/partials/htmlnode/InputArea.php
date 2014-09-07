<div class="input_area"
     id="<?= $id?"$id":""; ?>">
    <? if($label){ ?><label for="<?= $id?"$id":""; ?>_input"><?= $label; ?></label><? } ?>
    <input type="text"
           name="<?= $name; ?>"
           id="<?= $id?"$id":""; ?>_input"
           value="<?= $value; ?>"
           <?= $read_only?"readonly":""; ?>
           <?= $required?"required":""; ?>
           placeholder="<?= $place_holder; ?>" />
</div>