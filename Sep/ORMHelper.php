<?
namespace Sep;

class ORMHelper extends \ArrayObject {

    public $know_models = array();

    public function getORModel($model_type){
        if( !isset($this->know_models[$model_type]) ){
            $properties = get_class_vars($model_type);
            $this->know_models[$model_type] = $properties["orm_model"];
        }
        return $this->know_models[$model_type];
    }

    public function get_table_name($model_type=null){
        return \Sep\ORM\Model::get_table_name($model_type===null?$this->model_type:$model_type);
    }
    public function get_fields($model_type){
        return array_keys($this->getORModel($model_type));
    }
    public function has_field($model_type,$p){
        return array_key_exists($p,$this->getORModel($model_type));
    }
    public function split_property($property){
        $ret = array();
        if( preg_match("/([^.]+)[.]([^.]+)/",$property,$matches)>0){
            $ret[] = $matches[1];
            $ret[] = $matches[2];
        }else{
            $ret[] = $property;
            $ret[] = "";
        }
        return $ret;
    }
    public function is_foreign($model_type,$property,$type=null){
        $orm_model = $this->getORModel($model_type);
        list($p_name,$f_p_name) = $this->split_property($property);
        if( $property == "$p_name.$f_p_name"){
            if( isset($orm_model["$p_name"]["model_type"])
                && isset($orm_model["$p_name"]["foreign"]) ){
                if( $type === null ){
                    return true;
                }else{
                    $f_type = $orm_model["$p_name"]["model_type"];
                    $properties = get_class_vars($f_type);
                    $f_model = $properties["orm_model"];
                    if( isset($f_model["$f_p_name"])
                        && $orm_model["$p_name"]["foreign"] == "$type" ){
                        return true;
                    }
                }
            }
        }else if( isset($orm_model["$property"]["model_type"])
            && isset($orm_model["$property"]["foreign"]) ){
            if( $type === null ){
                return true;
            }else{
                return $orm_model["$property"]["foreign"] == "$type";
            }
        }
        return false;
    }
    public function get_meta($model_type,$property,$meta,$default_value=null){
        $orm_model = $this->getORModel($model_type);
        if( $this->is_foreign($model_type,$property) ){
            list($p_name,$f_p_name) = $this->split_property($property);
            if( $property == "$p_name.$f_p_name"){
                return isset($orm_model["$p_name"]["$meta"])?
                    $orm_model["$p_name"]["$meta"]:
                    $default_value;
            }else{
                return isset($orm_model["$property"]["$meta"])?
                    $orm_model["$property"]["$meta"]:
                    $default_value;
            }
        }else{
            return isset($orm_model["$property"]["$meta"])?
                $orm_model["$property"]["$meta"]:
                $default_value;
        }
    }

    /**
     * @param $model_type
     * @param $property
     * @param $item
     * @param null $max_length
     * @return \ORMWrapper
     */
    public function select_foreign($model_type,$property,$item,$max_length=null){
        $f_model_type = $this->get_meta($model_type,$property,"model_type");
        $foreign_type = $this->get_meta($model_type,$property,"foreign");
        $query = \Sep\ORM\Model::factory( $f_model_type );
        if( $foreign_type == "has_many_through" ){
            $query->select("$f_model_type.*")
                ->has_many_through($model_type)
                ->where_equal("$model_type.id",$item->id);
        }elseif( $foreign_type == "has_many" ){
            list($l_name,$f_name) = $this->split_property($property);
            $f_on = $this->get_meta($model_type,$property,"on_foreign",$l_name);
            $f_on = $f_on===$l_name?null:$f_on;
            $query->select("$f_model_type.*")
                ->has_one($model_type,$f_on) // note it is reversed
                ->where_equal((!$f_on?$model_type:$f_on).".id",$item->id);
        }elseif( $foreign_type == "has_one" ){
            list($l_name,$f_name) = $this->split_property($property);
            $query->select("$f_model_type.*")
                ->has_many($model_type,$l_name) // note it is reversed
                ->where_equal("$model_type.id",$item->id);
        }
        if( $max_length != null ){
            $query->limit( $max_length )->offset(0);
        }
        return $query;
    }

    /**
     * @param $model_type
     * @param $property
     * @param $item
     * @param null $foreignItem
     * @return bool
     */
    public function break_various_links($model_type,$property,$item,$foreignItem=null){
        $f_model_type = $this->get_meta($model_type,$property,"model_type");
        $foreign_type = $this->get_meta($model_type,$property,"foreign");
        if( $foreign_type == "has_many_through" ){
            $className = $model_type.$f_model_type;
            if( !class_exists($className) ) $className = $f_model_type.$model_type;
            $p_table_id = \Sep\ORM\Model::class_name_to_table_name($model_type)."_id";
            $f_table_id = \Sep\ORM\Model::class_name_to_table_name($f_model_type)."_id";
            $selector = \Sep\ORM\Model::factory( $className )
                ->where_equal($p_table_id,$item->id);
            if( $foreignItem ){
                $selector->where_equal($f_table_id,$foreignItem->id);
            }
            $selector->find_result_set()->delete();
            return true;
        }elseif( $foreign_type == "has_many" ){
            list($l_name,$f_name) = $this->split_property($property);
            $f_on = $this->get_meta($model_type,$property,"on_foreign",$l_name);
            $f_on_table = \Sep\ORM\Model::class_name_to_table_name($f_on);
            $selector = \Sep\ORM\Model::factory( $f_model_type )
                ->where_equal($f_on_table."_id",$item->id);
            if( $foreignItem ){
                $selector->where_equal("id",$foreignItem->id);
            }
            $items = $selector->find_result_set();
            if( $foreignItem && $items->count() == 0 ) return false;
            $items->set($f_on_table."_id", null)->save();
            return true;
        }elseif( $foreign_type == "has_one" ){
            list($l_name,$f_name) = $this->split_property($property);
            $f_on = $this->get_meta($model_type,$property,"on_foreign",$l_name);
            $f_on_table = \Sep\ORM\Model::class_name_to_table_name($f_on);
            $item->{$f_on_table."_id"} = null;
            return true;
        }
        return false;
    }

    /**
     * @param $model_type
     * @param $property
     * @param $item
     * @param $foreignItem
     * @return bool
     */
    public function break_link($model_type,$property,$item,$foreignItem){
        return $this->break_various_links($model_type,$property,$item,$foreignItem);
    }

    /**
     * @param $model_type
     * @param $property
     * @param $item
     * @return bool
     */
    public function break_all_links($model_type,$property,$item){
        return $this->break_various_links($model_type,$property,$item,null);
    }

    /**
     * @param $model_type
     * @param $property
     * @param $item
     * @param $f_id
     * @return bool|null
     */
    public function set_link($model_type,$property,$item,$f_id){
        $f_model_type = $this->get_meta($model_type,$property,"model_type");
        $foreign_type = $this->get_meta($model_type,$property,"foreign");
        if( $foreign_type == "has_many_through" ){
            $className = $model_type.$f_model_type;
            if( !class_exists($className) ) $className = $f_model_type.$model_type;
            $p_table_id = \Sep\ORM\Model::class_name_to_table_name($model_type)."_id";
            $f_table_id = \Sep\ORM\Model::class_name_to_table_name($f_model_type)."_id";
            $R = \Sep\ORM\Model::factory( $className )
                ->where_equal($p_table_id,$item->id)
                ->where_equal($f_table_id,$f_id)
                ->find_one();
            if(!$R) $R = \Sep\ORM\Model::factory( $className )->create();
            $R->{$p_table_id} = $item->id;
            $R->{$f_table_id} = $f_id;
            return $R->save();
        }elseif( $foreign_type == "has_many" ){
            list($l_name,$f_name) = $this->split_property($property);
            $f_on = $this->get_meta($model_type,$property,"on_foreign",$l_name);
            $f_on_table = \Sep\ORM\Model::class_name_to_table_name($f_on);
            $F = \Sep\ORM\Model::factory( $f_model_type )
                ->where_equal("id",$f_id)
                ->find_one();
            if( $F ){
                return $F->set($f_on_table."_id", $item->id)->save();
            }
        }elseif( $foreign_type == "has_one" ){
            list($l_name,$f_name) = $this->split_property($property);
            $f_on = $this->get_meta($model_type,$property,"on_foreign",$l_name);
            $f_on_table = \Sep\ORM\Model::class_name_to_table_name($f_on);
            $item->{$f_on_table."_id"} = $f_id;
            return true;
        }
        return false;
    }

    /**
     * @param $item
     * @return bool
     */
    public function break_all_relationship_links($item){
        $model_type = get_class($item);
        $fields = $this->get_fields( $model_type );
        foreach($fields as $header){
            if( $this->is_foreign($model_type,$header) ){
                $this->break_all_links($model_type,$header,$item);
            }
        }
    }
}