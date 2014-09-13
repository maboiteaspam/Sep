<? if( $sort !== false ){ ?>
    <span class="sort <?= $sort; ?>">
    <i class="fa fa-sort-asc">
        <input type="radio" class="hide"
               name="<?= $input_name?"sort[$input_name]":""; ?>"
            <?= $sort==="asc"?"checked='checked'":""; ?>
               value="asc" />
    </i>
    <i class="fa fa-sort-desc">
        <input type="radio" class="hide"
               name="<?= $input_name?"sort[$input_name]":""; ?>"
            <?= $sort==="desc"?"checked='checked'":""; ?>
               value="desc" />
    </i>
</span>
<? } ?>
    <label class="span_area"
           id="<?= $id?"$id":""; ?>"
           name="<?= $name?"$name":""; ?>"
        >
        <?= $label; ?>
        <? if( strlen($inline_filter)>0 ){ ?>
            <span class="like">like '<?= $inline_filter; ?>'</span>
        <? } ?>
    </label>
<? if( $filter !== false ){ ?>
    <input type="text"
           name="<?= $input_name?"filter[$input_name]":""; ?>"
           autocomplete="off"
           value="<?= $inline_filter; ?>"
           class="filter_value"
           placeholder="<?= $label; ?> like..." />
    <i class="fa fa-pencil-square-o"></i>
<? } ?>