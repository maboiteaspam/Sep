<?php
return function( \Sep\IntlMessages $intl ){
    return [
        "model_type"=>"Admin",
        "privileges"=>[
            "Admin"
        ],
        "provider"=>[
            "read"=>function($view,$selector){
                    return $selector;
                },
        ],
        "detail"=>[
            "model"=>[
                "id"=>[],
                "login"=>[],
                "password"=>[],
                "first_name"=>[],
                "last_name"=>[],
                "Role.id"=>[
                    "options_text"=>"name",
                ],
            ],
            "data"=>[
                "delete_confirm_message"=>function($view,$item){
                        return $view->intl->get_message(
                            "Admin.confirm_deletion_message",
                            array("login"=>$item->login)
                        );
                    },
            ]
        ],
        "list"=>[
            "model"=>[
                "id"=>[],
                "login"=>[],
                "name"=>[
                    "filter"=>false,
                    "value"=>function($view,$item){
                        return $item->first_name." ".$item->last_name;
                    },
                ],
                "first_name"=>[],
                "password"=>[],
                "last_name"=>[],
                "Role.id"=>[],
                "Role.name"=>[],
                "edit"=>[],
                "delete"=>[
                    "delete_confirm_message"=>function($view,$item){
                        return $view->intl->get_message(
                            "Admin.confirm_deletion_message",
                            array("login"=>$item->login)
                        );
                    },
                ],
            ],
            "default_headers"=>[
                "id",
                "login",
                "name",
                "Role.name",
                "edit",
                "delete",
            ],
            "data"=>[
            ],
        ],
    ];
};