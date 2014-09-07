<?php
namespace Sep\View;

class ViewForm extends \Sep\View\Base {

    public $model = array();
    public $item = null;
    public $errorsPartial = 'helpers/FormErrors.php';
    public $itemPartial = 'partials/ItemDetail.php';

    public function init(){
        foreach($this->model as $k=>$v){
            $this->form->add_value_from_post("$k");
        }
        foreach($this->model as $k=>$v){
            $v = $this->form->get_value("$k");
            $v = trim($v);
            $this->form->set_value($k,$v);
        }
        $this->form->about = $_SERVER["REQUEST_URI"];
        return $this;
    }
    public function flash_form_errors(){
        foreach($this->form->errors as $field_name=>$field ){
            foreach($field["messages"] as $err_id=>$err ){
                $this->app->flash("$field_name:$err_id",$err);
            }
        }
    }
    public function submit(){
        $form = $this->form;

        foreach($this->model as $k=>$v){
            if( $v["required"] ){
                $post_value = $form->get_value("$k");
                if( $post_value == "" ){
                    $form->add_intl_error("$k","$k.must_not_be_empty");
                }
            }
            if( isset($v["options"]) ){
                $options = array();
                foreach( $v["options"] as $o=>$option){
                    if( isset($option["value"]) ) $options[] = $option["value"];
                    else $options[] = $option;
                }
                $post_value = $form->get_value("$k");
                if( ! in_array($post_value,$options) ){
                    $form->add_intl_error("$k","$k.is_not_a_valid_option");
                }
            }
        }
        return $form->submit();
    }
    public function render($params=[]){
        $app = $this->app;
        $hasSubmit = $this->form->has_submit;

        $content_form = "";
        foreach($this->model as $k=>$v){
            $v["name"] = "$k";
            if( ! $hasSubmit ){
                if( isset($this->item["$k"]) ){
                    if( isset($v["options"]) ){
                        $v["selected"] = $this->item["$k"];
                    }
                    $this->form->add_value("$k", $this->item["$k"]);
                    if( ! isset($v["value"]) ){
                        $v["value"] = $this->item["$k"];
                    }
                }else{
                    if( isset($v["value"]) ){
                        $vv = $v["value"];
                        if( isset($v["options"]) ) $v["selected"] = $vv;
                        $this->form->add_value("$k", $vv);
                    }else{
                        $v["value"] = isset($v["selected"])?$v["selected"]:"";
                    }
                }
            }else{
                $v["value"] = $this->form->get_value("$k");
            }
            if( !isset($v["read_only"]) )
                $v["read_only"] = false;
            if( !isset($v["label"]) )
                $v["label"] = $this->intl->get_message("$k.field_name");
            if( !isset($v["place_holder"]) )
                $v["place_holder"] = $this->intl->get_message("$k.place_holder");

            if( !isset($v["id"]) )
                $v["id"] = "";

            $content_form .= $app->view()->fetch(
                "partials/htmlnode/".$v["type"].".php", $v );
        }
        $content_form .= $app->view()->fetch($this->errorsPartial,
            array('form' => $this->form));
        $params = array_merge($params,[
            'content_form'=>$content_form,
            'submit_path'=>$this->form->about,]);
        $items_view = $app->view()->fetch($this->itemPartial,$params);

        return $items_view;
    }
} 