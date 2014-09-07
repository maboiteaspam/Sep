<?
namespace Sep\View\Node;

class Anonymous extends \Sep\View\Base {
    public $params=array();
    public $type="";
    public static $_unq_id=0;
    public function init($type){
        $this->type = $type;
        $params = $this->params;
        if(!array_key_exists("id",$params)) $params["id"] = "unq_id_".(self::$_unq_id++);
        if(!array_key_exists("read_only",$params)) $params["read_only"] = false;
        if(!array_key_exists("required",$params)) $params["required"] = false;
        if(!array_key_exists("name",$params)) $params["name"] = "";
        if(!array_key_exists("placeholder",$params)) $params["placeholder"] = "";
        if(!array_key_exists("place_holder",$params)) $params["place_holder"] = "";
        if(!array_key_exists("method",$params)) $params["method"] = "";
        if(!array_key_exists("href",$params)) $params["href"] = "";
        if(!array_key_exists("delete_confirm_message",$params)) $params["delete_confirm_message"] = "";
        if(!array_key_exists("view_more_href",$params)) $params["view_more_href"] = "";
        if(!array_key_exists("value",$params)) $params["value"] = "";
        if(!array_key_exists("max_length_list",$params)) $params["max_length_list"] = 3;
        $this->params = $params;
        return $this;
    }
    public function render(){
        return $this->app->view()->fetch("partials/htmlnode/$this->type.php",$this->params);
    }
}