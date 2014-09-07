<form action="/change_language" method="POST">
    <ul class="errors"></ul>
    <?= $content_form ?>
    <input type="submit"
           value="<?= $btn_choose_language_title; ?>"
        />
</form>