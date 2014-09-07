<?php

namespace Sep\View;

class ChangeLanguage extends ViewForm {

    public function init(){
        $user_languages = $this->app->container->get("user_languages");
        $user_language = $this->app->container->get("user_language");
        $this->model = array(
            "language"=>array(
                "type"=>"SelectArea",
                "required"=>true,
                "selected"=>$user_language,
                "options"=>$user_languages,
                "label"=>"",
            ),
        );
        parent::init();
        $this->form->about = "/change_language";
        $this->itemPartial = 'partials/ChangeLanguage.php';
        return $this;
    }
    public function render($params=[]){
        $intl = $this->intl;
        $params = array_merge($params,['btn_choose_language_title'=>$intl->get_message("btn.choose_language"),]);
        return parent::render($params);
    }
    public function submit(){
        $success = parent::submit();
        if( $success ){
            $this->app->setCookie("language",$this->get_value("language"));
        }else{
            $this->flash_form_errors();
        }
        return $this->form->submit();
    }
}