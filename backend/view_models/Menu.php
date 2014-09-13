<?php
return function( \Slim\Slim $app, \Sep\IntlMessages $intl ){
    $data = [
        "items"=>[]
    ];
    $UserSession = $app->container->get("UserSession");
    if( $UserSession->is_logged() ){
        $data["items"]["home"] = [
            "display"=>true,
            "href"=>"/",
            "label"=>$intl->get_message("home.page_title"),
            "base"=>"/",
        ];
        $data["items"]["Admin"] = [
            "label"=>$intl->get_message("admin.group_menu_label"),
            "display"=>true,
            "items"=>[
                [
                    "href"=>"/Admin/list",
                    "label"=>$intl->get_message("admin.list.page_title"),
                    "base"=>"/Admin",
                    "display"=>true,
                ],
                [
                    "href"=>"/Role/list",
                    "label"=>$intl->get_message("role.list.page_title"),
                    "base"=>"/Role",
                    "display"=>true,
                ]
            ]
        ];
    }
    return $data;
};