<?
namespace Sep\View\Model;

use Sep\Validation\Validator as Validator;
use Respect\Validation\Exceptions\AbstractNestedException as AbstractNestedException;
use Respect\Validation\Exceptions\AllOfException as AbstractGroupedException;

class ItemDetail extends \Sep\View\Base {

    public $view_model;
    public $model_type;
    /**
     * @var \Sep\ORMHelper
     */
    public $orm_model;
    /**
     * @var ItemSelector
     */
    public $item_selector;
    public $errorsPartial = 'helpers/FormErrors.php';
    public $itemPartial = 'partials/ItemDetail.php';

    public function init($model_type,$view_model,$orm_model){

        $this->view_model = $view_model;
        $this->model_type = $model_type;

        $this->orm_model = $orm_model;

        $this->item_selector = new ItemSelector();
        $view = $this;
        $reader = isset($this->view_model["provider"]["read"])?
            $this->view_model["provider"]["read"]:null;
        $base_query = function()use($model_type,$view,$reader){
            $base_query = \Sep\ORM\Model::factory($model_type);
            if( $reader ){
                call_user_func($reader,$view,$base_query);
            }
            return $base_query;
        };
        $this->item_selector->init($this->orm_model,$base_query);

        if( $this->app->request()->isPost()){
            foreach($this->view_model["detail"]["model"] as $k=>$v){
                $this->form->add_value_from_post("$k",$v);
            }
        }
        $this->form->about = $this->app->request()->getPathInfo();
        return $this;
    }
    public function render(){
        $app = $this->app;
        $model_type = $this->model_type;
        $hasSubmit = $this->form->has_submit;
        $item = $this->get_item_from_route($model_type);
        $is_new = $item->id===null;

        if( !$hasSubmit ){
            foreach($this->view_model["detail"]["model"] as $k=>$v){
                $this->form->add_value("$k", $item->{$k});
            }
        }

        $content_form = "";
        $view_model = $this->get_view_model($item);
        foreach($view_model as $header=>$model){
            if( $model["display"] ){
                $node = new \Sep\View\Node\Anonymous($this->app,$this->intl,$this->sanitizer);
                $params = array_merge(array(),$model);
                if( $hasSubmit ){
                    if( isset($params["options"]) ){
                        $params["value"] = $this->form->get_value($header);
                        $params["selected"] = is_array($params["value"])?
                            $params["value"]:
                            [$params["value"]];
                    }else{
                        $params["value"] = $this->form->get_value($header."");
                        $params["selected"] = [$this->form->get_value($header."")];
                    }
                }
                $node->params = $params;
                $node->init($model["type"]);
                try{
                    $content_form .= $node->render();
                }catch(\Exception $ex ){
                    throw new \Exception("Can not render $this->model_type.$header:".$model["type"],0,$ex);
                }
            }
        }
        $content_form .= $app->view()->fetch($this->errorsPartial,
            array('form' => $this->form));

        $previous_href = $app->container->get("saved_urls");
        $previous_href = $previous_href["list"];
        $previous_href = count($previous_href)>0?$previous_href[0]:"";

        $table_name = $this->orm_model->get_table_name($this->model_type);

        $params = $this->view_model["detail"]["data"];
        $params = array_merge([
            'previous_href'=>$previous_href,
            'previous_title'=>$this->intl->get_message("btn.previous_url"),
            'content_form'=>$content_form,
            'submit_path'=>$this->form->about,
            'page_title'=>!$is_new?
                    $this->intl->get_message("$table_name.edit.page_title"):
                    $this->intl->get_message("$table_name.add.page_title"),
            'detail_title'=>!$is_new?
                    $this->intl->get_message("$table_name.edit.page_title"):
                    $this->intl->get_message("$table_name.add.page_title"),
            'submit_title'=>!$is_new?
                    $this->intl->get_message("$table_name.btn.save"):
                    $this->intl->get_message("$table_name.btn.add_record"),
            'delete_title'=>!$is_new?
                    $this->intl->get_message("$table_name.btn.del_record"):
                    "",
            'delete_confirm_message'=>"",
            'delete_href'=>!$is_new?
                    "/$model_type/delete/$item->id":
                    "",
        ],$params);
        foreach($params as $i=>$p){
            if( is_callable($p) ){
                $params[$i] = $p($this,$item);
            }
        }
        return $app->view()->fetch($this->itemPartial,$params);
    }

    public function get_item_from_route($model_type,$allow_create=true){
        $item = false;
        if( $this->has_route_value("id") ){
            $item = $this->item_selector
                ->get_selector($model_type)
                ->where("id",$this->get_route_value("id"))
                ->find_one();
        }else if($allow_create){
            $item = \Sep\ORM\Model::factory($model_type)->create();
        }
        return $item;
    }

    public function submit(){
        $form = $this->form;
        $model_type = $this->model_type;
        $item = $this->get_item_from_route($model_type);
        $view_model = $this->get_view_model($item);
        $is_new = $this->has_route_value("id");

        foreach($view_model as $header=>$model){
            $data_type = $this->orm_model->get_meta($model_type,$header,"type","");
            $pk = $this->orm_model->get_meta($model_type,$header,"pk","");
            $read_only = $this->orm_model->get_meta($model_type,$header,"read_only","");


            $validator = new Validator();

            $validator->setName( $model["label"] );
            $post_value = $form->get_value("$header");

            if( in_array($data_type,["int"]) ){
                if( $is_new && !($pk && $read_only) ){
                    $validator->int();
                }
            }
            if( in_array($data_type,["bool"]) ){
                $validator->string()->in(['1','0']);
            }
            if( in_array($data_type,["date","datetime"]) ){
                $validator->date();
            }
            if( in_array($data_type,["text"]) ){
                $validator->string()->notHTML();
            }
            if( in_array($data_type,["phone"]) ){
                $validator->phone();
            }
            if( in_array($data_type,["email"]) ){
                $validator->email();
            }
            if( $model["required"] ){
                $validator->notBlank();
            }
            if( $model["max_length"]+$model["min_length"]>0 ){
                $validator->length($model["min_length"], $model["max_length"]);
            }

            if( !empty($post_value) ){
                if( isset($model["options"]) ){
                    $options = array();
                    foreach( $model["options"] as $v){
                        if( isset($v["value"]) ) $options[] = $v["value"];
                        else $options[] = $v;
                    }
                    $validator->in($options);
                }
            }
            if( !is_array($post_value) ){
                $post_value = [$post_value];
            }
            foreach( $post_value as $p ){
                try {
                    $validator->assert($p);
                } catch(\InvalidArgumentException $e) {
                    foreach($e->getIterator(true,AbstractNestedException::ITERATE_ALL) as $d ){
                        if( !($d instanceof AbstractGroupedException) ){
                            $rule_name = str_replace('Respect\\Validation\\Exceptions\\','', get_class($d));
                            $rule_name = str_replace('Exception','', $rule_name);
                            $rule_name = lcfirst($rule_name);
                            if( $rule_name === "in" ){
                                $form->add_intl_error("$header","$header.invalid_value",$e->getParams());
                            }elseif( $rule_name === "notEmpty" || $rule_name === "notBlank" ){
                                $form->add_intl_error("$header","$header.must_not_be_empty",$e->getParams());
                            }else{
                                $form->add_intl_error("$header","$header.invalid_$rule_name",$e->getParams());
                            }
                        }
                    }
                }
            }
        }
        return $form->submit();
    }

    public function write(){
        $form = $this->form;
        $model_type = $this->model_type;

        $item = $this->get_item_from_route($model_type);
        $view_model = $this->get_view_model($item);
        if( $this->submit() ){
            foreach($view_model as $header=>$model){
                if( ! $model["read_only"] ){
                    $data_type = $this->orm_model->get_meta($model_type,$header,"type","");
                    if($data_type!=""){
                        $post_value = $form->get_value("$header");
                        if( $item->{$header} != $post_value ){
                            $item->{$header} = $post_value;
                        }
                    }
                }
            }
            // set one-to-one value bound on the current item
            foreach($view_model as $header=>$model){
                if( ! $model["read_only"] ){
                    $data_type = $this->orm_model->get_meta($model_type,$header,"type","");
                    if($data_type==""){
                        if( $this->orm_model->is_foreign($model_type,$header,"has_one") ){
                            $post_value = $form->get_value("$header");
                            $post_value = is_array($post_value)?$post_value:array($post_value);
                            foreach($post_value as $f_id ){
                                if(!$this->orm_model->set_link($model_type,$header,$item,$f_id)){
                                    $this->form->add_intl_error("general","general.db_write_failure");
                                }
                            }
                        }
                    }
                }
            }
            try{
                \ORM::get_db()->beginTransaction();
                if( !$item->save() ){
                    $this->form->add_intl_error("general","general.db_write_failure");
                }else{
                    foreach($view_model as $header=>$model){
                        if( ! $model["read_only"] ){
                            $data_type = $this->orm_model->get_meta($model_type,$header,"type","");
                            if($data_type==""){
                                if( $this->orm_model->is_foreign($model_type,$header) ){
                                    $post_value = $form->get_value("$header");
                                    $post_value = is_array($post_value)?$post_value:array($post_value);
                                    $f_items = $this->orm_model
                                        ->select_foreign($model_type,$header,$item)
                                        ->find_many();
                                    foreach($f_items as $f_item ){
                                        if( in_array($f_item->id,$post_value) == false ){
                                            if(!$this->orm_model->break_link($model_type,$header,$item,$f_item)){
                                                $this->form->add_intl_error("$header","general.db_write_failure");
                                            }
                                        }
                                    }
                                    if( $this->orm_model->is_foreign($model_type,$header,"has_many") ||
                                        $this->orm_model->is_foreign($model_type,$header,"has_many_through") ){
                                        foreach($post_value as $f_id ){
                                            if(!$this->orm_model->set_link($model_type,$header,$item,$f_id)){
                                                $this->form->add_intl_error("$header","general.db_write_failure");
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if( isset($this->view_model["provider"]["write"]) ){
                    $writer = $this->view_model["provider"]["write"];
                    call_user_func($writer,$this,$item);
                }
                \ORM::get_db()->commit();
            }catch(\Exception $Ex ){
                \ORM::get_db()->rollBack();
                var_dump($Ex->getMessage());
                $this->form->add_intl_error("general","general.db_write_failure");
            }
        }
        return $this->form->submit();
    }

    public function delete(){
        $model_type = $this->model_type;
        $hasId = $this->has_route_value("id");
        if( $hasId ){
            $item = $this->get_item_from_route($model_type,false);
            try{
                \ORM::get_db()->beginTransaction();
                if( ! $item->delete() ){
                    $this->form->add_intl_error("general","general.db_write_failure");
                }else{
                    $this->orm_model->break_all_relationship_links($item);
                }
                if( isset($this->view_model["provider"]["delete"]) ){
                    $deleter = $this->view_model["provider"]["delete"];
                    call_user_func($deleter,$this,$item);
                }
                \ORM::get_db()->commit();
            }catch(\Exception $Ex ){
                \ORM::get_db()->rollBack();
                $this->form->add_intl_error("general","general.db_write_failure");
            }
        }
        return $this->form->submit();
    }

    public function complete($header,$value){
        $model_type = $this->model_type;
        if( $this->orm_model->is_foreign($model_type,$header) ){
            $model_type = $this->orm_model->get_meta($model_type,$header,"model_type");
        }
        $item = \Sep\ORM\Model::factory($model_type);

        return [];
    }

    public function get_view_model( $item ){
        $view_model = $this->view_model["detail"]["model"];
        $model_type = $this->model_type;
        foreach($view_model as $header=>$model){
            if( !isset($model["display"]) ){
                $model["display"] = true;
            }
            if( !isset($model["type"]) ){
                if( $this->orm_model->get_meta($model_type,$header,"pk",false) === true ){
                    $model["type"] = "Hidden";
                }else if( $this->orm_model->get_meta($model_type,$header,"type",false) === "text" ){
                    if( $this->orm_model->get_meta($model_type,$header,"options",false) !== false ){
                        $model["type"] = "SelectArea";
                        $model["options"] = $this->orm_model->get_meta($model_type,$header,"options",false);
                    }else{
                        $model["type"] = "InputArea";
                    }
                }else if( $this->orm_model->get_meta($model_type,$header,"type",false) === "phone" ){
                    $model["type"] = "PhoneArea";
                }else if( $this->orm_model->get_meta($model_type,$header,"type",false) === "email" ){
                    $model["type"] = "EmailArea";
                }else if( $this->orm_model->get_meta($model_type,$header,"type",false) === "date" ){
                    $model["type"] = "InputDateArea";
                }else if( $this->orm_model->get_meta($model_type,$header,"type",false) === "datetime" ){
                    $model["type"] = "InputDatetimeArea";
                }else if( $this->orm_model->is_foreign($model_type,$header,"has_many_through") ){
                    $max = $this->orm_model->get_meta($model_type,$header,"max_length");
                    if($max===1){
                        $model["type"] = "SelectArea";
                    }else{
                        $model["type"] = "CheckboxArea";
                    }
                }else if( $this->orm_model->is_foreign($model_type,$header,"has_many")){
                    $model["type"] = "CheckboxArea";
                }else if( $this->orm_model->is_foreign($model_type,$header,"has_one") ){
                    $model["type"] = "SelectArea";
                }else{
                    $model["type"] = "InputArea";
                }
            }
            // the primary keys value is required if the current exists only
            if( !isset($view_model["$header"]["required"]) ){
                if( $this->orm_model->get_meta($model_type,$header,"pk",false) === true ){
                    $model["required"] = $item->id!==null;
                }elseif( $this->orm_model->get_meta($model_type,$header,"is_nullable",true) === false ){
                    $model["required"] = true;
                }else if( $this->orm_model->is_foreign($model_type,$header)){
                    $model["required"] = $this->orm_model->get_meta($header,"min_length",0)>0;
                }
            }
            if( !isset($model["max_length"]) ){
                $model["max_length"] = null;
                if( $this->orm_model->is_foreign($model_type,$header)){
                    $model["max_length"] = $this->orm_model->get_meta($model_type,$header,"max_length");
                }
            }
            if( !isset($model["min_length"]) ){
                $model["min_length"] = null;
                if( $this->orm_model->is_foreign($model_type,$header)){
                    $model["min_length"] = $this->orm_model->get_meta($model_type,$header,"min_length");
                }
            }
            if( !isset($model["name"]) )
                $model["name"] = "$header";
            if( !isset($model["read_only"]) )
                $model["read_only"] = false;
            if( !isset($model["label"]) ){
                $h = \Sep\ORM\Model::class_name_to_table_name($header);
                $model["label"] = $this->intl->get_message("$h.field_name");
            }
            if( !isset($model["place_holder"]) )
                $model["place_holder"] = $this->intl->get_message("$header.place_holder");


            if( $this->orm_model->is_foreign($model_type,$header) ){
                list($p_name,$f_prop) = $this->orm_model->split_property($header);
                if( !isset($model["selected"]) ){
                    if( $this->orm_model->is_foreign($model_type,$header) ){
                        $has_one = $this->orm_model->is_foreign($model_type,$header,"has_one");
                        $f_items = $this->orm_model->select_foreign($model_type,$header,$item);
                        $f_items = $f_items->find_many();
                        $text = [];
                        foreach( $f_items as $f_item){
                            $text[] = $f_item->{$f_prop};
                        }
                        $max_length = $this->orm_model->get_meta($model_type,$header,"max_length",$has_one?1:6);
                        $model["selected"] = $max_length===1&&count($text)>0?$text[0]:$text;
                    }
                }
                if( !isset($model["options"]) ){
                    if( $this->orm_model->is_foreign($model_type,$header) ){
                        $f_model_type = $this->orm_model->get_meta($model_type,$header,"model_type");
                        $f_items = \Sep\ORM\Model::factory($f_model_type)->select("$f_model_type.*");
                        $f_items = $f_items->find_many();
                        $options_text = "id";
                        $text = [];
                        foreach( $f_items as $f_item){
                            if( isset($model["options_text"]) ){
                                $options_text = $model["options_text"];
                            }
                            $text[] = array(
                                'value'=>$f_item->id,
                                'text'=>$f_item->{$options_text},
                            );
                        }
                        $model["options"] = $text;
                    }
                }
            }

            if( isset($model["options"]) && !isset($model["selected"]) ){
                $model["selected"] = array();
                if( isset($item->{$header}) ){
                    $model["selected"] = array($item->{$header});
                }
            }
            if( ! isset($model["value"]) ){
                if( isset($item->{$header}) ){
                    $model["value"] = $item->{$header};
                }else if( isset($model["selected"]) ){
                    $model["value"] = $model["selected"];
                }
            }


            $view_model[$header] = $model;
        }
        foreach($view_model as $header=>$model){
            foreach($model as $k=>$v){
                if( is_callable($v) ){
                    $view_model[$header][$k] = $v($this,$item);
                }
            }
        }
        return $view_model;
    }
}