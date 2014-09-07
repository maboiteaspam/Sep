<?
namespace Sep\View;

class Form{
    public $intl;
    public $sanitizer;
    public $about;
    public $errors;
    public $has_submit;

    public function __construct( \Sep\IntlMessages $intl, \Sep\Sanitizer $sanitizer){
        $this->intl = $intl;
        $this->sanitizer = $sanitizer;
        $this->errors = array();
        $this->has_submit = false;
    }

    protected function init_error($field){
        if( ! isset($this->errors[$field]) ){
            $this->errors[$field] = array(
                "value"=>"",
                "messages"=>array(),
                "options"=>array(),
            );
        }
    }

    public function add($field,$value,$id,$message){
        $this->init_error($field);
        $this->errors[$field]["value"] = $value;
        $this->errors[$field]["messages"][$id] = $message;
    }

    public function add_error($field,$id,$message){
        $this->init_error($field);
        $this->errors[$field]["messages"][$id] = $message;
    }

    public function add_intl_error($field,$id,$params=array()){
        $this->init_error($field);
        $this->errors[$field]["messages"][$id] = $this->intl->get_message($id,$params);
    }

    public function set_value($field,$value,$options=array()){
        $this->init_error($field);
        $this->errors[$field]["value"] = $value;
        $this->errors[$field]["options"] = $options;
    }

    public function add_value($field,$value,$options=array()){
        $this->set_value($field,$value,$options);
    }

    public function add_value_from_post($field,$options=array()){
        $value = $this->sanitizer->sanitized_post("$field");
        $this->add_value("$field",$value,$options);
        return $value;
    }

    public function add_value_from_get($field,$options=array()){
        $value = $this->sanitizer->sanitized_get("$field");
        $this->add_value("$field",$value,$options);
        return $value;
    }

    public function clean_value($field){
        $v = $this->get_value("$field");
        $v = trim($v);
        $this->set_value($field,$v);
    }

    public function has_errors(){
        foreach( $this->errors as $data ){
            if( count($data["messages"])>0) return true;
        }
        return false;
    }

    public function submit(){
        $this->has_submit = true;
        return !$this->has_errors();
    }

    public function get_fields(){
        return array_keys($this->errors);
    }

    public function get_value($field){
        return $this->errors[$field]["value"];
    }

    public function get_errors($field){
        return $this->errors[$field]["messages"];
    }
}