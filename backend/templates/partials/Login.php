<form action="/do_login" method="POST">
    <ul class="errors"></ul>
    <?= $content_form ?>
    <input type="submit"
           value="<?= $btn_login_title; ?>"
        />
</form>