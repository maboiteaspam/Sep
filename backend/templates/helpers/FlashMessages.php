<? if( count($flash)>0 ){ ?>
<script>
    (function(){
        var flash = <?= json_encode($flash); ?>;
        for( var n in flash ){
            alert(flash[n]);
        }
    })();
</script>
<? } ?>