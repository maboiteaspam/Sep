<?
namespace Sep;


namespace Sep;

class Sanitizer{
    protected $get;
    protected $post;
    public function __construct($get=null,$post=null){
        $this->get = $get===NULL?$_GET:$get;
        $this->post = $post===NULL?$_POST:$post;
    }
    public function sanitize($variable,$filter= FILTER_DEFAULT,$options=null){
        if( is_array($variable) ){
            foreach( $variable as $k=>$v){
                $variable[$k] = filter_var($v,$filter,$options);
            }
        }elseif(null!==null){
            $variable = filter_var($variable,$filter,$options);
        }
        return $variable;
    }
    public function sanitized_get($input_var_name,$filter=FILTER_DEFAULT,$options=null){
        $variable = isset($this->get["$input_var_name"]) ? $this->get["$input_var_name"] : null;
        return $this->sanitize($variable,$filter,$options);
    }
    public function sanitized_post($input_var_name,$filter=FILTER_DEFAULT,$options=null){
        $input_var_name = str_replace(".","_",$input_var_name);
        $variable = isset($this->post["$input_var_name"]) ? $this->post["$input_var_name"] : null;
        return $this->sanitize($variable,$filter,$options);
    }
    public function each_get($subset_key){
        $subset = isset($this->get["$subset_key"]) ? $this->get["$subset_key"] : array();
        $res = array();
        foreach( $subset as $i=>$s ){
            $s = is_array($s)?$s:array($i=>$s);
            $res[] = new Sanitizer($s,array());
        }
        return $res;
    }
    public function each_post($subset_key){
        $subset = isset($this->post["$subset_key"]) ? $this->post["$subset_key"] : array();
        $res = array();
        foreach( $subset as $i=>$s ){
            $s = is_array($s)?$s:array($i=>$s);
            $res[] = new Sanitizer(array(),$s);
        }
        return $res;
    }
}