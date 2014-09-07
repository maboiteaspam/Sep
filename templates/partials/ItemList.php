<div class="top_list_area">
    <h2><?= $list_title ?></h2>
    <div class="filter_list_area">
        <?= $filter_list ?>
    </div>
    <div class="navigation_list_area">
        <?= $navigation_list ?>
    </div>
    <a href="<?= $add_record_href ?>"
        <?= $add_record_href?"":"disabled" ?>
       class="link_area <?= $add_record?"":"hide" ?>"
       name="add"><?= $add_record ?></a>
    <a href="<?= $export_excel_href ?>"
       class="link_area <?= $export_excel?"":"hide" ?>"
        <?= count($rows)?"":"disabled" ?>
       name="excel_export"><?= $export_excel ?></a>
    <a href="<?= $previous_page_href ?>"
       class="link_area"
        <?= $previous_page_href?"":"disabled" ?>
       name="previous_page"><?= $previous_page ?></a>
    <a href="<?= $next_page_href ?>"
       class="link_area"
        <?= $next_page_href?"":"disabled" ?>
       name="next_page"><?= $next_page ?></a>
</div>
<div class="body_list_area">
    <? if( count($rows)>0){ ?>
        <form method="GET" name="quick_filter">
        <table style="width:100%">
            <thead>
                <tr>
                    <? foreach($list_headers as $header=>$text){ ?>
                        <th><?= $text ?></th>
                    <? } ?>
                </tr>
            </thead>
            <tbody>
            <? foreach($rows as $row){?>
                <tr>
                    <? foreach($list_headers as $header=>$text){ ?>
                        <td><?= $row[$header] ?></td>
                    <? } ?>
                </tr>
            <? } ?>
            </tbody>
        </table>
            <input type="hidden" name="filter_id" value="<?= $filter_id; ?>" />
        </form>
    <? }else{ ?>
        <div class="empty_list">
            <h2>List is empty !<br/>:-(</h2>
        </div>
        <? if( $has_inline_filters ){ ?>
        <div class="adjust_filters">
            <h2>Adjust your filters</h2>
            <form method="GET" name="quick_filter">
                <ul>
                <? foreach($list_headers as $header=>$text){ ?>
                    <li class="input_area">
                        <?= $text ?>
                    </li>
                <? } ?>
                    <li class="input_area">
                        <label class="span_area">&nbsp;</label>
                        <input type="submit"
                               class="link_area"
                               name="edit"
                               value="Adjust filters" />
                        <input type="submit"
                               class="link_area"
                               name="delete"
                               value="Clear filters" />
                    </li>
                </ul>
            </form>
        </div>
        <? } ?>
    <? } ?>
</div>
<div class="bottom_list_area">
    <a href="<?= $add_record_href ?>"
        <?= $add_record_href?"":"disabled" ?>
        class="link_area <?= $add_record?"":"hide" ?>"
        name="add"><?= $add_record ?></a>
    <a href="<?= $export_excel_href ?>"
        <?= count($rows)?"":"disabled" ?>
       class="link_area <?= $export_excel?"":"hide" ?>"
       name="excel_export"><?= $export_excel ?></a>
    <a href="<?= $previous_page_href ?>"
       class="link_area"
        <?= $previous_page_href?"":"disabled" ?>
       name="previous_page"><?= $previous_page ?></a>
    <a href="<?= $next_page_href ?>"
       class="link_area"
        <?= $next_page_href?"":"disabled" ?>
       name="next_page"><?= $next_page ?></a>
</div>
<style>
    .body_list_area th{
        position: relative;
    }
    .body_list_area th .span_area{
        padding-left: 15px;
        padding-right: 15px;
    }
    .body_list_area .sort{
        position: absolute;
        left: -3;
        height: 34px;
        top: -1px;
    }
    .body_list_area .sort .fa-sort-desc,
    .body_list_area .sort .fa-sort-asc{
        position:absolute;
        font-size: 140%;
        left: 9px;
        cursor: pointer;
        line-height: 0;
    }
    .body_list_area .sort .fa-sort-asc{
        top: 15px;
    }
    .body_list_area .sort .fa-sort-desc{
        bottom: 15px;
    }
    .body_list_area .sort .fa-sort-asc:hover,
    .body_list_area .sort .fa-sort-desc:hover{
        color: #0078e7;
    }
    .body_list_area .sort.asc .fa-sort-asc,
    .body_list_area .sort.desc .fa-sort-desc{
        opacity: 0.5;
    }
    .body_list_area .sort.asc:hover .fa-sort-asc,
    .body_list_area .sort.desc:hover .fa-sort-desc{
        color: inherit;
    }
    .body_list_area th .fa-pencil-square-o{
        display:none;
        position:absolute;
        right: 0px;
        top:9px;
        color: #0078e7;
    }
    .body_list_area th .filter_value{
        display:none;
        font-size: 90%;
        margin-left:10px;
    }
    .body_list_area  th:hover .fa-pencil-square-o{
        display:inline;
    }
    .body_list_area  th.edit .filter_value{
        display:inline;
        padding: 0 !important;
    }
    .body_list_area  th.edit .fa-pencil-square-o{
        display:inline;
    }
    .body_list_area  th.edit .span_area{
        display:none;
    }
</style>
<style>
    .adjust_filters{
        margin-left: 150px;
        margin-bottom: 50px;
    }
    .adjust_filters form{
        text-align: left;
        position: relative;
    }
    .adjust_filters ul{
        list-style-type: none;
        padding: 0;
    }
    .adjust_filters .sort{
        display: none;
    }
    .adjust_filters .like{
        display: none;
    }
</style>
<script>
    (function(){
        $(".adjust_filters [name='delete']").on("click",function(ev){
            $(':input','.adjust_filters form')
                .not(':button, :submit, :reset, :hidden')
                .val('')
                .removeAttr('checked')
                .removeAttr('selected');
            window.location.href = window.location.pathname;
            return false;
        });
    })();
</script><script>
    (function(){
        $(".body_list_area .sort").on("click",function(ev){
            if( $(ev.target).is("i") ){
                var c = $(ev.target).find("input");
                var checked = c.attr('checked')=='checked';
                $(this).find("input").attr('checked', null);
                c.attr('checked', 'checked');
                c.trigger("blur");
                $("form[name='quick_filter']").submit();
            }
            return false;
        });
    })();
    (function(){
        $(".body_list_area th").on("click",function(ev){
            if( $(this).find(".filter_value").length &&
                !$(ev.target).is(".filter_value") &&
                $(ev.target).is(".span_area") ){
                if( $(this).hasClass("edit") ){
                    $(this).removeClass("edit");
                }else {
                    $(this).addClass("edit");
                    $(".body_list_area th .filter_value").one("blur",function(ev){
                        $(this).parent().removeClass("edit");
                    }).trigger("focus");
                }
            }
        });
        $(".body_list_area th .filter_value").each(function(k,v){
            $(v).data("ovalue",$(v).val())
        })
        $(".body_list_area th .filter_value").on("keyup",function(ev){
            if (ev.which == 13) {
                if( $(this).data("ovalue") != $(this).val() ){
                    $("form[name='quick_filter']").submit();
                }
            }
        });
        $("form[name='quick_filter']").on("submit",function(ev){
            $(this).find("input").each(function(k,v){
                if( !$(v).val() ) $(v).remove();
            });
        })
    })();
</script>