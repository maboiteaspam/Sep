<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page_title ?></title>
    <script src="js/jquery-2.1.1.min.js"></script>
    <style type="text/css">
        .login_area{
            height: 146px;
            left: 50%;
            margin-left: -270px;
            margin-top: -123px;
            position: fixed;
            top: 50%;
            width: 540px;
        }
        .choose_language_area{
            position: fixed;
            right: 20px;
            bottom: 5px;
        }
        .footer_area{
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            height: 50px;
            color: white;
            background-image: -ms-linear-gradient(bottom, #303030 0%, #006796 100%);
            background-image: -moz-linear-gradient(bottom, #303030 0%, #006796 100%);
            background-image: -o-linear-gradient(bottom, #303030 0%, #006796 100%);
            background-image: -webkit-gradient(linear, left bottom, left top, color-stop(0, #303030), color-stop(1, #006796));
            background-image: -webkit-linear-gradient(bottom, #303030 0%, #006796 100%);
            background-image: linear-gradient(to top, #303030 0%, #006796 100%);
        }
    </style>

    <style>
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


        .fa{
            margin-right: 4px;
        }
        a[name='excel_export']{
            background-color: #1AB331 !important;
        }
    </style>
</head>
<body>
    <div class="login_area">
        <?= $content ?>
    </div>
    <div class="footer_area">
        footer
    </div>
    <div class="choose_language_area">
        <?= $choose_language ?>
    </div>
</body>
<script>
    var set_default = function(){
        $("table").not(".pure-table").addClass("pure-table pure-table-striped");
        $("form").not(".pure-form").addClass("pure-form");
        $("button").not(".pure-button").addClass("pure-button");
        $("input[type='submit']").not(".pure-button").addClass("pure-button");
    };
    $('<link rel="stylesheet" href="/css/font-awesome.min.css"/>').prependTo("head");
    $('<link rel="stylesheet" href="/css/pure-min.css"/>').prependTo("head");
    $(".left_area").addClass("pure-menu pure-menu-open");

    $(".link_area[name='add']")
        .addClass("pure-button-primary pure-button button-xsmall");
    $('<i class="fa fa-floppy-o fa-lg"></i>').prependTo(".link_area[name='add']");

    $(".link_area[name='edit']")
        .addClass("button-secondary pure-button button-xsmall");
    $(".body_detail_area .link_area[name='edit']")
        .removeClass("button-secondary")
        .addClass("pure-button-primary");
    $('<i class="fa fa-pencil-square-o fa-lg"></i>').prependTo(".link_area[name='edit']");

    $(".link_area[name='delete']").addClass("button-warning pure-button button-xsmall");
    $('<i class="fa fa-trash-o fa-lg"></i>').prependTo(".link_area[name='delete']");

    $(".link_area[name='back']").addClass("button-secondary pure-button button-xsmall");
    $('<i class="fa fa-chevron-left fa-lg"></i>').prependTo(".link_area[name='back']");


    $("input[type='submit']").addClass("button-xsmall");
    $("select").addClass("select-small");

    $(".login_area label").addClass("pure-u-1-2");
    $(".login_area input").addClass("pure-input-1");
    $(".login_area label").addClass("pure-u-1-2");
    $(".login_area select").addClass("pure-input-1");

    $(".choose_language_area .select_area").css("display","inline-block");
    set_default();
    $("body").bind("DOMSubtreeModified", set_default);
    $(".left_area ul li").each(function(k,v){
        var u = $(v).find("a").attr("base");
        if( u == "/" ){
            if( window.location.pathname == "/" ){
                $(v).addClass("pure-menu-selected");
            }
        }else if( window.location.pathname.substr(0, u.length) == u ){
            $(v).addClass("pure-menu-selected");
        }
    })
    $(".login_area input[type='submit']").removeClass("button-xsmall");
</script>
</html>