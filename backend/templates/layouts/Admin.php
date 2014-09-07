<html>
<head>
    <meta charset="utf-8">
    <title><?= $page_title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="/js/jquery-2.1.1.min.js"></script>
    <script src="/js/jquery.easing.1.3.js"></script>
    <script src="/js/jquery.transit.js"></script>
    <script src="/js/knockout-3.1.0.js"></script>
    <link rel="stylesheet" href="/css/font-awesome.min.css"/>
    <link rel="stylesheet" href="/css/pure-min.css"/>
    <style type="text/css">
        .hide{
            display: none !important;
        }
        .disconnect_area{
            position: fixed;
            top: 15px;
            right: 15px;
        }
        .choose_language_area{
            position: fixed;
            right: 20px;
            bottom: 10px;
            z-index: 5;
        }
        .top_list_area .link_area[name='add'],
        .bottom_list_area .link_area[name='add']{
            margin-bottom: 10px;
            margin-top: 10px;
        }
        .navigation_list_area{
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .left_area{
            width: 265px !important;
            height: 100%;
            border-width: 0 !important;
            z-index: 3 !important;
            position: fixed;
        }
        .left_area_border{
            position: fixed;
            left: 265px;
            top: 0;
            width: 2px !important;
            height: 100%;
            background-color: #b7b7b7;
            z-index: 3;
        }
        .right_area{
            position: absolute;
            top: 0;
            left: 0;
            padding-left: 275px;
            padding-right: 10px;
            width: 70%;
            min-width: 450px;
            padding-bottom: 70px;
            z-index: 0;
        }
        .footer_area{
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            height: 50px;
            z-index: 4;
            color: white;
            background-image: -ms-linear-gradient(bottom, #303030 0%, #006796 100%);
            background-image: -moz-linear-gradient(bottom, #303030 0%, #006796 100%);
            background-image: -o-linear-gradient(bottom, #303030 0%, #006796 100%);
            background-image: -webkit-gradient(linear, left bottom, left top, color-stop(0, #303030), color-stop(1, #006796));
            background-image: -webkit-linear-gradient(bottom, #303030 0%, #006796 100%);
            background-image: linear-gradient(to top, #303030 0%, #006796 100%);
        }
        html,body{
            padding-bottom: 80px;
        }
    </style>
    <style>
        .body_list_area .empty_list{
            text-align: center;
        }
        .body_list_area a[name='view_more']{
            float: right;
            opacity: 0;
            transition-duration: 250ms;
            transition-property: opacity;
        }
        .body_list_area td:hover a[name='view_more']{
            opacity: 1;
        }
    </style>
    <style>
        .pure-table-striped tr:hover td{
            background-color: #38B6FF !important;
            color: white;
        }
        .button-success,
        .button-error,
        .button-warning,
        .button-secondary {
            color: white;
            border-radius: 4px;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
        }
        .button-success {
            background: rgb(28, 184, 65); /* this is a green */
        }
        .button-error {
            background: rgb(202, 60, 60); /* this is a maroon */
        }
        .button-warning {
            background: rgb(223, 117, 20); /* this is an orange */
        }
        .button-secondary {
            background: rgb(66, 184, 221); /* this is a light blue */
        }

        .button-xsmall {
            font-size: 70%;
        }
        .button-small {
            font-size: 85%;
        }
        .button-large {
            font-size: 110%;
        }
        .button-xlarge {
            font-size: 125%;
        }

        .select-xsmall {
            font-size: 70%;
        }
        .select-small {
            font-size: 85%;
            padding: .2em .1em !important;
        }
        .select-large {
            font-size: 110%;
        }
        .select-xlarge {
            font-size: 125%;
        }

        label.pure-button{
            padding: .3em .3em;
        }


        .fa{
            margin-right: 4px;
        }
        .fa-chevron-right{
            margin-right: 0px;
            margin-left: 4px;
        }
        a[name='excel_export']{
            background-color: #1AB331 !important;
        }
    </style>
</head>
<body>
    <div class="right_area">
        <?= $content ?>
    </div>
    <div class="left_area">
        <?= $left_area ?>
    </div>
    <div class="left_area_border"></div>
    <div class="footer_area">
        footer
    </div>
    <div class="choose_language_area">
        <?= $choose_language ?>
    </div>
    <div class="disconnect_area">
        <form action="/do_logout" method="POST">
            <input type="submit"
                   value="<?= $logout_btn_title; ?>">
        </form>
    </div>
    <script>
        $("[confirm='confirm']").on("click",function(ev){
            if (!window.confirm( $(this).attr("confirm_message") )) {
                ev.stopImmediatePropagation();
                return false;
            }
        });
        $("input[type='submit'],a").each(function(k,v){
            if( $(v).attr("for") ){
                (function(el,for_attr){
                    el.on("click",function(ev){
                        ev.preventDefault();
                        try{
                            var f = $("#"+for_attr);
                            f.length && f.is("form") && f.submit();
                        }catch(ex){
                            $("form[action='"+for_attr+"']").submit();
                        }
                        return false;
                    });
                })($(v), $(v).attr("for"));
                $(v).attr("for",null);
            }
        });
    </script>
    <script>
        $(".left_area").addClass("pure-menu pure-menu-open");

        function init_primary(el,icon,append){
            $(el).addClass("pure-button-primary pure-button button-xsmall");
            if( append == true ) $('<i class="fa '+icon+' fa-lg"></i>').appendTo(el);
            else $('<i class="fa '+icon+' fa-lg"></i>').prependTo(el);
        }
        function init_secondary(el,icon,append){
            $(el).addClass("button-secondary pure-button button-xsmall");
            if( append == true ) $('<i class="fa '+icon+' fa-lg"></i>').appendTo(el);
            else $('<i class="fa '+icon+' fa-lg"></i>').prependTo(el);
        }
        function init_warning(el,icon,append){
            $(el).addClass("button-warning pure-button button-xsmall");
            if( append == true ) $('<i class="fa '+icon+' fa-lg"></i>').appendTo(el);
            else $('<i class="fa '+icon+' fa-lg"></i>').prependTo(el);
        }

        init_primary(".link_area[name='add']","fa-floppy-o");
        init_primary(".link_area[name='edit']","fa-pencil-square-o");
        init_warning(".link_area[name='delete']","fa-trash-o");
        init_secondary(".link_area[name='view_more']","fa-chevron-right", true);
        init_secondary(".link_area[name='back']","fa-chevron-left");
        init_secondary(".link_area[name='excel_export']","fa-file-excel-o");
        init_secondary(".link_area[name='next_page']","fa-chevron-right");
        init_secondary(".link_area[name='previous_page']","fa-chevron-left");
        init_primary(".link_area[name='new_view']","fa-cogs");
        init_primary(".link_area[name='browse_view']","fa-chevron-right");

        $("tr td")
            .filter(function(k,v){return $(v).find(".link_area[name='edit']").length>0;})
            .css("width","80px");
        $("tr td")
            .filter(function(k,v){return $(v).find(".link_area[name='delete']").length>0;})
            .css("width","80px");
        $("tr td")
            .filter(function(k,v){return $(v).find(".span_area[name='id']").length>0;})
            .css("width","20px");



        $("input[type='submit']").addClass("button-xsmall");
        $("select").addClass("select-small");

        $(".inputdate_area label").addClass("pure-u-1-4");
        $(".inputdatetime_area label").addClass("pure-u-1-4");
        $(".geoloc_area label").addClass("pure-u-1-4");
        $(".input_area label").addClass("pure-u-1-4");
        $(".select_area label").addClass("pure-u-1-4");
        $("body").on("click","a[disabled]",function(){
            return false;
        });
        $(".choose_language_area .select_area").css("display","inline-block");
        $(".body_detail_area select").removeClass("select-small");

        var is_doing;
        var set_default = function(){
            if(!is_doing){
                is_doing = true;
                $("table")
                    .not(".pure-table")
                    .addClass("pure-table pure-table-striped");
                $("form").not(".pure-form").addClass("pure-form");
                $("button").not(".pure-button").addClass("pure-button");
                $("input[type='submit']").not(".pure-button").addClass("pure-button");
                $("label")
                    .not(".pure-button")
                    .filter(function(k,v){
                        return $(v).find("input[type='checkbox']").length
                            +$(v).find("input[type='radio']").length>0;
                    })
                    .removeClass("pure-u-1-4")
                    .addClass("pure-button");
                is_doing = false;
            }
        };
        set_default();
        $("body").bind("DOMSubtreeModified", set_default);

        (function(w,resized){
            var resize_t_out;
            var l;
            var resize_right_area = function(){
                var v = (w.width()-285);
                if( l != v){
                    l = v;
                    resized.css("width",v+"px");
                }
            };
            w.on("resize",function(){
                clearTimeout(resize_t_out);
                setTimeout(resize_right_area,150);
            });
            setTimeout(resize_right_area,150);
            setTimeout(resize_right_area,300);
        })($(window),$(".right_area"));
    </script>
    <?= $flash_messages; ?>
</body>
</html>