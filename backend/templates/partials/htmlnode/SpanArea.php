<span class="span_area"
      id="<?= $id?"$id":""; ?>"
      name="<?= $name?"$name":""; ?>"
    >
    <?= $value; ?>
</span>
<? if( $view_more_href ){ ?>
    <a href="<?= $view_more_href; ?>"
       target="_blank"
       name="view_more"
       class="link_area"><?= $view_more_label; ?></a>
<? } ?>