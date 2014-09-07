<?php
namespace Sep\View;

class Login extends ViewForm {

    public function init(){
        $this->model = array(
            "login"=>array(
                "type"=>"InputArea",
                "required"=>true,
            ),
            "password"=>array(
                "type"=>"PasswordArea",
                "required"=>true,
            ),
        );
        parent::init();
        $this->form->about = "/do_login";
        $this->itemPartial = 'partials/Login.php';
        return $this;
    }
    public function render($params=[]){
        $intl = $this->intl;
        $params = array_merge($params,['btn_login_title'=>$intl->get_message("btn.login"),]);
        return parent::render($params);
    }
    public function submit(){
        $success = parent::submit();
        $UserSession = $this->app->container->get("UserSession");
        if( ! $UserSession->is_logged() && $success ){
            $login = $this->get_value("login");
            $password = $this->get_value("password");
            $Admin = \Sep\ORM\Model::factory('Admin')
                ->where('login', $login)
                ->where('password', \Admin::hash_password($password) )
                ->find_one();
            if( $Admin !== false ){
                $UserSession->set_logged_user($Admin->as_array());
            }else{
                $this->add_intl_error("credentials","credentials.are_incorrect");
            }
        }
        return $this->form->submit();
    }
} 