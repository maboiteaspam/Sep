<?php
namespace Sep\Bootstrap;


class Backend extends Bootstrap{

    /**
     * @var \Slim\Slim
     */
    public $app;

    public function init( $env,$config_dirs=[] ){
        parent::init($env,$config_dirs);

        $config = $this->config;

        session_start();
        \ORM::configure( $config["db"] );
        \ORM::configure('logging', true);

        $config["router"]["templates.path"] = array_reverse($config["router"]["templates.path"]);
        $this->app = new \Slim\Slim( $config["router"] );
        $app = $this->app;

        $app->error(function ($e) use ($app) {
            echo $app->view()->fetch('helpers/PhpException.php',
                array(
                    "exception"=>$e,
                    "title"=>'Slim Application Error',
                ));
        });
        $app->setName($config["app_name"]);
        $app->container->set("UserSession", new \Sep\UserSession());
        $app->container->set("user_languages", $config["user_languages"]);
    }

    public function get_user_language( ){
        $app = $this->app;
        $user_language = $app->getCookie("language");
        if( ! $user_language ) $user_language = parent::get_user_language();
        return $user_language;
    }

    public function load_intl_messages($dirs=[],$language=null ){
        $app = $this->app;
        parent::load_intl_messages($dirs,$language);
        if(!$language) $language=$this->get_user_language();
        $app->container->set("user_language", $language);
    }

    public function load_app(){

        $config = $this->config;
        $www_path = $config->www_path;
        $intl = $this->intl;
        $app = $this->app;
        $view_models = [];
        $view_models_path = $config["view_models_path"];
        $sanitizer = new \Sep\Sanitizer();

// # load models
        foreach($view_models_path as $view_model_path ){
            $files = \Sep\Utils::scan_classes($view_model_path);
            $view_models = [];
            foreach( $files as $filepath=>$class ){
                $view_models[$class] = require("$filepath");
            }
        }
        $orm_model = new \Sep\ORMHelper();

// # Declare routes

// middleware
//----------------
// Controls login status
        $authenticateForRole = function ( $role = 'member' ) use($app) {
            return function () use ( $role,$app ) {
                $UserSession = $app->container->get("UserSession");
                if ( $UserSession->is_logged() === false ) {
                    $app->flash('error', 'Login required');
                    $app->redirect('/login');
                }
            };
        };
        $satisfyModelPrivileges = function ( ) use($app,$intl,$sanitizer,$view_models) {
            return function () use ($app,$intl,$sanitizer,$view_models ) {
                $params = $app->router()->getCurrentRoute()->getParams();
                $model_type = isset($params["model"])?$params["model"]:false;
                if( $model_type ){
                    $UserSession = $app->container->get("UserSession");
                    $has_privileges = function($role)use($UserSession){
                        $user = $UserSession->get_logged_user();
                        return \Sep\ORM\Model::factory("Admin")
                            ->find_one($user["id"])
                            ->has_role($role);
                    };
                    $privileges = "";
                    $model = $view_models[$model_type];
                    $model = $model($intl);
                    $privileges = isset($model["privileges"])?$model["privileges"]:[];
                    foreach($privileges as $p ){
                        if( $has_privileges($p) == false ){
                            $app->flash('error', 'Forbidden access');
                            $app->redirect('/');
                        }
                    }
                }
            };
        };

// Saves url browsed
        $saveUrl = function ($type=false) use($app) {
            if( isset($_SESSION["saved_urls"]) == false ){
                $_SESSION["saved_urls"] = array();
            }
            if( isset($_SESSION["saved_urls"]["all"]) == false ){
                $_SESSION["saved_urls"]["all"] = array();
            }
            if( $type && isset($_SESSION["saved_urls"]["$type"]) == false ){
                $_SESSION["saved_urls"]["$type"] = array();
            }
            $app->container->set("saved_urls",$_SESSION["saved_urls"]);
            return function () use ( $app,$type ) {
                $urls = $_SESSION["saved_urls"]["all"];
                if( $app->request()->isGet()){
                    $url = $app->request()->getPathInfo();
                    $url .= isset($_SERVER["QUERY_STRING"])?"?".$_SERVER["QUERY_STRING"]:"";
                    $last = count($urls)>0?$urls[0]:"";
                    if( $last!=$url ){
                        array_unshift($_SESSION["saved_urls"]["all"],$url);
                        if( $type ) array_unshift($_SESSION["saved_urls"]["$type"],$url);
                        $app->container->set("saved_urls",$_SESSION["saved_urls"]);
                    }
                }
            };
        };

// layouts
//----------------
        $adminLayout = function ( $content_view, $params=array() ) use($app,$intl,$sanitizer) {
            $MenuView = new \Sep\View\Menu($app,$intl,$sanitizer);
            $ChangeLanguageView = new \Sep\View\ChangeLanguage($app,$intl,$sanitizer);
            $ChangeLanguageView->init();
            $FlashMessagesView = new \Sep\View\FlashMessages($app,$intl,$sanitizer);
            if( $content_view instanceof \Sep\View\Base )
                $content_view = $content_view->render();
            $params['left_area'] = $MenuView->render();
            $params['page_title'] = isset($params['page_title'])?$params['page_title']:"";
            $params['content'] = $content_view;
            $params['choose_language'] = $ChangeLanguageView->render();
            $params['flash_messages'] = $FlashMessagesView->render();
            $params['logout_btn_title'] = $intl->get_message("btn.logout");
            $admin_layout = $app->view()->fetch('layouts/Admin.php',$params);
            return $admin_layout;
        };
        $loginLayout = function ( \Sep\View\Login $loginView ) use($app,$intl,$sanitizer) {
            $ChangeLanguageView = new \Sep\View\ChangeLanguage($app,$intl,$sanitizer);
            $ChangeLanguageView->init();
            $page_title =$intl->get_message("login.page_title");
            if( $loginView->has_errors() ){
                $page_title =$intl->get_message("login_failure.page_title");
            }
            $login_layout = $app->view()->fetch('layouts/Login.php',
                array(
                    'page_title'=>$page_title,
                    'content' => $loginView->render(),
                    'choose_language' => $ChangeLanguageView->render(),
                ));
            echo $login_layout;
        };

// index
//----------------
        $app->get("/",
            $authenticateForRole("logged"),
            $saveUrl(),
            function() use($app,$intl,$sanitizer,$adminLayout){
                $content_view = $app->view()->fetch('partials/Home.php',array());
                echo $adminLayout($content_view);
            });

// login
//----------------
        $app->get("/login",
            function() use($app,$intl,$sanitizer,$loginLayout){
                $UserSession = $app->container->get("UserSession");
                if ( $UserSession->is_logged() ) {
                    $app->redirect('/');
                }else{
                    $LoginView =  new \Sep\View\Login($app,$intl,$sanitizer);
                    $LoginView->init();
                    echo $loginLayout( $LoginView );
                }
            });
// identify and record logged in user
        $app->post("/do_login",
            function() use($app,$intl,$sanitizer,$loginLayout){
                $LoginView = new \Sep\View\Login($app,$intl,$sanitizer);
                $LoginView->init();
                if( !$LoginView->submit() ){
                    echo $loginLayout( $LoginView );
                }else{
                    $app->redirect('/');
                }
            });
// change user language
        $app->post("/change_language",
            function() use($app,$intl,$sanitizer){
                $ChangeLanguageView =  new \Sep\View\ChangeLanguage($app,$intl,$sanitizer);
                $ChangeLanguageView->init();
                $ChangeLanguageView->submit();
                $previous_href = $app->container->get("saved_urls");
                $previous_href = $previous_href["all"];
                $previous_href = count($previous_href)>0?$previous_href[0]:"/";
                $app->redirect($previous_href);
            });
// log out user
        $app->post("/do_logout",
            function() use($app){
                $UserSession = $app->container->get("UserSession");
                if( $UserSession->is_logged() ){
                    $UserSession->unset_logged_user();
                }
                $app->redirect('/login');
            });


// model to view
//----------------
        $app->get("/:model/list",
            $saveUrl("list"),
            $authenticateForRole("logged"),
            $satisfyModelPrivileges(),
            function($model_type) use($app,$intl,$sanitizer,$adminLayout,$view_models,$orm_model){
                $content_view = new \Sep\View\Model\ItemList($app,$intl,$sanitizer);
                $view_model = $view_models[$model_type];
                $view_model = $view_model($intl);
                $content_view->init($model_type,$view_model,$orm_model);
                $params = array();
                $table_name = $orm_model->get_table_name($model_type);
                $params['page_title'] = $this->intl->get_message("$table_name.list.page_title");
                echo $adminLayout( $content_view, $params );
            });
        $app->get("/:model/list/:items_cnt",
            $saveUrl("list"),
            $authenticateForRole("logged"),
            $satisfyModelPrivileges(),
            function($model_type) use($app,$intl,$sanitizer,$adminLayout,$view_models,$orm_model){
                $content_view = new \Sep\View\Model\ItemList($app,$intl,$sanitizer);
                $view_model = $view_models[$model_type];
                $view_model = $view_model($intl);
                $content_view->init($model_type,$view_model,$orm_model);
                $params = array();
                $table_name = $orm_model->get_table_name($model_type);
                $params['page_title'] = $this->intl->get_message("$table_name.list.page_title");
                echo $adminLayout( $content_view, $params );
            })
            ->conditions(array('items_cnt' => '\d+'));
        $app->get("/:model/list/:items_cnt/:page",
            $saveUrl("list"),
            $authenticateForRole("logged"),
            $satisfyModelPrivileges(),
            function($model_type) use($app,$intl,$sanitizer,$adminLayout,$view_models,$orm_model){
                $content_view = new \Sep\View\Model\ItemList($app,$intl,$sanitizer);
                $view_model = $view_models[$model_type];
                $view_model = $view_model($intl);
                $content_view->init($model_type,$view_model,$orm_model);
                $params = array();
                $table_name = $orm_model->get_table_name($model_type);
                $params['page_title'] = $this->intl->get_message("$table_name.list.page_title");
                echo $adminLayout( $content_view, $params );
            })
            ->conditions(array('page' => '\d+','items_cnt' => '\d+'));
        $app->get("/:model/export_excel",
            $saveUrl(),
            $authenticateForRole("logged"),
            $satisfyModelPrivileges(),
            function($model_type) use($app,$intl,$sanitizer,$adminLayout,$view_models,$orm_model){
                $content_view = new \Sep\View\Model\ItemList($app,$intl,$sanitizer);
                $view_model = $view_models[$model_type];
                $view_model = $view_model($intl);
                $content_view->init($model_type,$view_model,$orm_model);
                $content_view->render_excel();
            });
        $app->map("/:model/add",
            $saveUrl(),
            $authenticateForRole("logged"),
            $satisfyModelPrivileges(),
            function($model_type) use($app,$intl,$sanitizer,$adminLayout,$view_models,$orm_model){
                $content_view = new \Sep\View\Model\ItemDetail($app,$intl,$sanitizer);
                $view_model = $view_models[$model_type];
                $view_model = $view_model($intl);
                $content_view->init($model_type,$view_model,$orm_model);
                if($app->request()->isPost()){
                    if( $content_view->write() ){
                        $previous_href = $app->container->get("saved_urls");
                        $previous_href = $previous_href["list"];
                        $previous_href = count($previous_href)>0?$previous_href[0]:"/$model_type/list";
                        $app->redirect($previous_href);
                    }
                }
                $params = array();
                $table_name = $orm_model->get_table_name($model_type);
                $params['page_title'] = $this->intl->get_message("$table_name.add.page_title");
                echo $adminLayout( $content_view, $params );
            })->via('GET', 'POST');
        $app->map("/:model/edit/:id",
            $saveUrl(),
            $authenticateForRole("logged"),
            $satisfyModelPrivileges(),
            function($model_type) use($app,$intl,$sanitizer,$adminLayout,$view_models,$orm_model){
                $content_view = new \Sep\View\Model\ItemDetail($app,$intl,$sanitizer);
                $view_model = $view_models[$model_type];
                $view_model = $view_model($intl);
                $content_view->init($model_type,$view_model,$orm_model);
                if($app->request()->isPost()){
                    if( $content_view->write() ){
                        $previous_href = $app->container->get("saved_urls");
                        $previous_href = $previous_href["list"];
                        $previous_href = count($previous_href)>0?$previous_href[0]:"/$model_type/list";
                        $app->redirect($previous_href);
                    }
                }
                $params = array();
                $table_name = $orm_model->get_table_name($model_type);
                $params['page_title'] = $this->intl->get_message("$table_name.edit.page_title");
                echo $adminLayout( $content_view, $params );
            })->via('GET', 'POST');
        $app->map("/:model/add/complete/:property/:value",
            $saveUrl(),
            $authenticateForRole("logged"),
            $satisfyModelPrivileges(),
            function($model_type,$property,$value) use($app,$intl,$sanitizer,$adminLayout,$view_models,$orm_model){
                //-
            })->via('GET', 'POST');
        $app->map("/:model/edit/:id/complete/:property/:value",
            $saveUrl(),
            $authenticateForRole("logged"),
            $satisfyModelPrivileges(),
            function($model_type,$property,$value) use($app,$intl,$sanitizer,$adminLayout,$view_models,$orm_model){
                //-
            })->via('GET', 'POST');
        $app->post("/:model/delete/:id",
            $saveUrl(),
            $authenticateForRole("logged"),
            $satisfyModelPrivileges(),
            function($model_type) use($app,$intl,$sanitizer,$adminLayout,$view_models,$orm_model){
                $content_view = new \Sep\View\Model\ItemDetail($app,$intl,$sanitizer);
                $view_model = $view_models[$model_type];
                $view_model = $view_model($intl);
                $content_view->init($model_type,$view_model,$orm_model);
                if($app->request()->isPost()){
                    if( $content_view->delete() ){
                        $previous_href = $app->container->get("saved_urls");
                        $previous_href = $previous_href["list"];
                        $previous_href = count($previous_href)>0?$previous_href[0]:"/$model_type/list";
                        $app->redirect($previous_href);
                    }
                }
                $params = array();
                $table_name = $orm_model->get_table_name($model_type);
                $params['page_title'] = $this->intl->get_message("$table_name.list.page_title");
                echo $adminLayout( $content_view, $params );
            });

// Filter
//----------------
        $app->post("/Filter/:id",
            $authenticateForRole("logged"),
            function($id) use($app,$intl,$sanitizer){
                $Admin = \Sep\ORM\Model::factory("Admin")->find_one($app->container["UserSession"]->id);
                $Filter = \Sep\ORM\Model::factory("Filter")
                    ->where("id",$id)
                    ->find_one();
                if( $Filter ){
                    $Filter->name = $sanitizer->sanitized_post("name");
                    $Filter->save();

                    $Admin->addFilter($Filter->id);

                    $Filter->deleteConditions();
                    $conditions = $sanitizer->each_post("conditions");
                    foreach($conditions as $i=>$condition){
                        $condition_id = $condition->sanitized_post("id");
                        $C = \Sep\ORM\Model::factory("FilterCondition")
                            ->where("id",$condition_id)
                            ->find_one();
                        if(!$C) $C = \Sep\ORM\Model::factory("FilterCondition")->create();
                        $C->filter_id = $Filter->id;
                        $C->left_field_type = $condition->sanitized_post("left_field_type");
                        $C->left_field = $condition->sanitized_post("left_field");
                        $C->operator = $condition->sanitized_post("operator");
                        $C->right_field_type = $condition->sanitized_post("right_field_type");
                        $C->right_field = $condition->sanitized_post("right_field");
                        $C->position = $i;
                        $C->save();
                    }
                    $Filter->deleteColumns();
                    $columns = $sanitizer->each_post("columns");
                    foreach($columns as $i=>$column){
                        $C = \Sep\ORM\Model::factory("FilterColumn")->create();
                        if(!$C) $C = \Sep\ORM\Model::factory("FilterCondition")->create();
                        $C->filter_id = $Filter->id;
                        $C->name = $column->sanitized_post("$i");
                        $C->position = $i;
                        $C->save();
                    }

                    $vv = $Filter->as_array();
                    $vv["conditions"] = $Filter->Conditions()->find_array();
                    foreach($Filter->Columns()->select("name")->find_array() as $a){
                        $vv["columns"][] = $a["name"];
                    }
                    echo json_encode($vv);
                    return;
                }
                echo json_encode(false);
            });
        $app->post("/Filter",
            $authenticateForRole("logged"),
            function() use($app,$intl,$sanitizer){
                $Filter = \Sep\ORM\Model::factory("Filter")->create();
                $Filter->name = $sanitizer->sanitized_post("name");
                $Filter->filter_type = $sanitizer->sanitized_post("filter_type");
                $Filter->save();
                if( $Filter->save() ){
                    $Admin = \Sep\ORM\Model::factory("Admin")->find_one($app->container["UserSession"]->id);
                    if( $Admin->addFilter($Filter->id) ){
                        $conditions = $sanitizer->each_post("conditions");
                        foreach($conditions as $i=>$condition){
                            $condition_id = $condition->sanitized_post("id");
                            $C = \Sep\ORM\Model::factory("FilterCondition")
                                ->where("id",$condition_id)
                                ->find_one();
                            if(!$C) $C = \Sep\ORM\Model::factory("FilterCondition")->create();
                            $C->filter_id = $Filter->id;
                            $C->left_field_type = $condition->sanitized_post("left_field_type");
                            $C->left_field = $condition->sanitized_post("left_field");
                            $C->operator = $condition->sanitized_post("operator");
                            $C->right_field_type = $condition->sanitized_post("right_field_type");
                            $C->right_field = $condition->sanitized_post("right_field");
                            $C->position = $i;
                            $C->save();
                        }
                        $columns = $sanitizer->each_post("columns");
                        foreach($columns as $i=>$column){
                            $C = \Sep\ORM\Model::factory("FilterColumn")->create();
                            if(!$C) $C = \Sep\ORM\Model::factory("FilterCondition")->create();
                            $C->filter_id = $Filter->id;
                            $C->name = $column->sanitized_post("$i");
                            $C->position = $i;
                            $C->save();
                        }

                        $vv = $Filter->as_array();
                        $vv["conditions"] = $Filter->Conditions()->find_array();
                        foreach($Filter->Columns()->select("name")->find_array() as $a){
                            $vv["columns"][] = $a["name"];
                        }
                        echo json_encode($vv);
                        return;
                    }
                }
                echo json_encode(false);
            });
        $app->post("/Filter/delete/:id",
            $authenticateForRole("logged"),
            function($id) use($app,$intl,$sanitizer){
                $Filter = \Sep\ORM\Model::factory("Filter")
                    ->where("id",$id)
                    ->find_one();
                if( $Filter ){
                    echo $Filter->safe_delete();
                }
                else echo false;
            });
        $app->post("/Filter/:id/conditions",
            $authenticateForRole("logged"),
            function($id) use($app,$intl,$sanitizer){
                $Filter = \Sep\ORM\Model::factory("Filter")
                    ->where("id",$id)
                    ->find_one();
                if( $Filter ){
                    $conditions = $sanitizer->each_post("conditions");
                    foreach($conditions as $i=>$condition){
                        $condition_id = $condition->sanitized_post("id");
                        $C = \Sep\ORM\Model::factory("FilterCondition")
                            ->where("id",$condition_id)
                            ->find_one();
                        if(!$C) $C = \Sep\ORM\Model::factory("FilterCondition")->create();
                        $C->filter_id = $Filter->id;
                        $C->left_field_type = $condition->sanitized_post("left_field_type");
                        $C->left_field = $condition->sanitized_post("left_field");
                        $C->operator = $condition->sanitized_post("operator");
                        $C->right_field_type = $condition->sanitized_post("right_field_type");
                        $C->right_field = $condition->sanitized_post("right_field");
                        $C->position = $i;
                        $C->save();
                    }
                    $columns = $sanitizer->each_post("columns");
                    $Filter->Columns()->delete_many();
                    foreach($columns as $i=>$column){
                        $C = \Sep\ORM\Model::factory("FilterColumn")->create();
                        if(!$C) $C = \Sep\ORM\Model::factory("FilterCondition")->create();
                        $C->filter_id = $Filter->id;
                        $C->name = $columns->sanitized_post("$i");
                        $C->position = $i;
                        $C->save();
                    }
                }
                else echo false;
            });

// Static assets
//----------------
        $app->get(":path",
            function($path) use($www_path){
                foreach( $www_path as $p ){
                    // @todo : improve to avoid security issues
                    if( file_exists($p."/".$path) ){
                        return file_get_contents($p."/".$path);
                    }
                }
            })
            ->conditions(array('path' => '.+'));
    }
} 