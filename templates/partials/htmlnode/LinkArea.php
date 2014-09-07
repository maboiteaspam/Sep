<a class="link_area"
    id="<?= $id?"$id":""; ?>"
    name="<?= $name?"$name":""; ?>"
<? if( $method == "post" ){ ?>
    for="link_form_<?= $id?"$id":""; ?>"
<? }else{ ?>
    href="<?= $href?"$href":""; ?>"
<? } ?>
    confirm="<?= $delete_confirm_message?"confirm":""; ?>"
    confirm_message="<?= $delete_confirm_message; ?>"
    >
    <?= $label; ?>
</a>
<? if( $method == "post" ){ ?>
<form method='POST' action='<?= $href; ?>' id='link_form_<?= $id; ?>'></form>
<? } ?>