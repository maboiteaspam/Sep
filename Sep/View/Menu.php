<?php

namespace Sep\View;

class Menu extends \Sep\View\Base {
    public function render(){
        $app = $this->app;
        $data = [
            "items"=>[]
        ];
        $UserSession = $app->container->get("UserSession");
        if( $UserSession->is_logged() ){
            $has_privileges = function($role)use($UserSession){
                $user = $UserSession->get_logged_user();
                return \Sep\ORM\Model::factory("Admin")->find_one($user["id"])->has_role($role);
            };
            $data["items"]["home"] = [
                "display"=>true,
                "href"=>"/",
                "label"=>$this->intl->get_message("home.page_title"),
                "base"=>"/",
            ];
            $data["items"]["Admin"] = [
                "label"=>$this->intl->get_message("admin.group_menu_label"),
                "display"=>true,
                "items"=>[
                    [
                        "href"=>"/Admin/list",
                        "label"=>$this->intl->get_message("admin.list.page_title"),
                        "base"=>"/Admin",
                        "display"=>true,
                    ],
                    [
                        "href"=>"/Role/list",
                        "label"=>$this->intl->get_message("role.list.page_title"),
                        "base"=>"/Role",
                        "display"=>true,
                    ]
                ]
            ];
            $data["items"]["AppUser"] = [
                "display"=>$has_privileges("AppUser"),
                "label"=>$this->intl->get_message("app_user.group_menu_label"),
                "items"=>[
                    [
                        "display"=>$has_privileges("AppUser"),
                        "href"=>"/AppUser/list",
                        "label"=>$this->intl->get_message("app_user.list.page_title"),
                        "base"=>"/AppUser",
                    ],
                    [
                        "display"=>$has_privileges("AppUser"),
                        "href"=>"/Answer/list",
                        "label"=>$this->intl->get_message("answer.list.page_title"),
                        "base"=>"/Answer",
                    ],
                ]
            ];
            $data["items"]["Question"] = [
                "display"=>$has_privileges("Question"),
                "label"=>$this->intl->get_message("question.group_menu_label"),
                "items"=>[
                    [
                        "display"=>$has_privileges("Question"),
                        "href"=>"/Topic/list",
                        "label"=>$this->intl->get_message("topic.list.page_title"),
                        "base"=>"/Topic",
                    ],
                    [
                        "display"=>$has_privileges("Question"),
                        "href"=>"/Question/list",
                        "label"=>$this->intl->get_message("question.list.page_title"),
                        "base"=>"/Question",
                    ],
                    [
                        "display"=>$has_privileges("Question"),
                        "href"=>"/PredefinedResponse/list",
                        "label"=>$this->intl->get_message("predefined_response.list.page_title"),
                        "base"=>"/PredefinedResponse",
                    ],
                ]
            ];
        }
        $items_view = $app->view()->fetch("partials/MenuView.php",$data);
        return $items_view;
    }
}