<?php
namespace Sep\ORM;


class ORMWrapper extends \ORMWrapper{

    public static function for_table($table_name, $connection_name = parent::DEFAULT_CONNECTION) {
        self::_setup_db($connection_name);
        return new static($table_name, array(), $connection_name);
    }
    public function find_one($id=null) {
        return $this->_create_model_instance(\Sep\ORM\ORM::find_one($id));
    }
    protected function _create_model_instance($orm) {
        if ($orm === false) {
            return false;
        }
        $model = new $this->_class_name();
        $model->set_orm($orm);
        return $model;
    }
    public function find_many(){
        $results = \Sep\ORM\ORM::find_many();
        foreach($results as $key => $result) {
            $results[$key] = $this->_create_model_instance($result);
        }
        return $results;
    }




    /**
     */
    protected $join_done = array();
    public function has_many_through($associated_class_name,
                                     $associated_as=null,
                                     $join_type=null,
                                     $connection_name=null) {
        if( isset($this->join_done["$associated_class_name.$associated_as"]) )
            return $this;
        $this->join_done["$associated_class_name.$associated_as"] = true;

        $base_class_name = $this->_class_name;

        // The class name of the join model, if not supplied, is
        // formed by concatenating the names of the base class
        // and the associated class, in alphabetical order.

        $model = explode('\\', $base_class_name);
        $model_name = end($model);
        if (substr($model_name, 0, strlen(Model::$auto_prefix_models)) == Model::$auto_prefix_models) {
            $model_name = substr($model_name, strlen(Model::$auto_prefix_models), strlen($model_name));
        }
        $class_names = array($model_name, $associated_class_name);
        sort($class_names, SORT_STRING);
        $join_class_name = join("", $class_names);

        // Get table names for each class
        $base_table_name = is_null($this->_table_alias)?$this->_table_name:$this->_table_alias;
        $real_base_table_name = $this->_table_name;

        $real_associated_table_name = Model::get_table_name(Model::$auto_prefix_models . $associated_class_name);
        $associated_table_name = is_null($associated_as)?$real_associated_table_name:$associated_as;

        $join_table_name = Model::get_table_name(Model::$auto_prefix_models . $join_class_name);

        // Get ID column names
        $base_table_id_column = Model::get_id_column_name($base_class_name);
        $associated_table_id_column = Model::get_id_column_name(Model::$auto_prefix_models . $associated_class_name);

        // Get the column names for each side of the join table
        $key_to_base_table = Model::build_foreign_key_name(null, $real_base_table_name);
        $key_to_associated_table = Model::build_foreign_key_name(null, $real_associated_table_name);

        $join_type = $join_type==null?"join":$join_type;
        return $this
            ->{$join_type}($join_table_name,
                array("{$join_table_name}.{$key_to_base_table}",
                    '=', "{$base_table_name}.{$base_table_id_column}"))
            ->{$join_type}($real_associated_table_name,
                array("{$join_table_name}.{$key_to_associated_table}",
                    '=', "{$associated_table_name}.{$associated_table_id_column}"),
                $associated_as)
            ;
    }

    protected function has_one($associated_class_name,
                               $foreign_key_name=null,
                               $join_type=null) {

        if( isset($this->join_done["$associated_class_name.$foreign_key_name"]) )
            return $this;
        $this->join_done["$associated_class_name.$foreign_key_name"] = true;

        $base_class_name = $this->_class_name;
        $base_table_name = Model::get_table_name($base_class_name);

        $real_associated_table_name = Model::get_table_name(Model::$auto_prefix_models . $associated_class_name);
        $associated_table_name = $real_associated_table_name;
        $associated_table_id_column = Model::get_id_column_name($associated_class_name);

        $base_table_f_id_column = is_null($foreign_key_name)?
            Model::build_foreign_key_name(null, $associated_table_name):
            Model::class_name_to_table_name($foreign_key_name) ."_". Model::get_id_column_name($associated_table_name);

        $associated_table_name = is_null($foreign_key_name)?
            $associated_table_name:
            $foreign_key_name;

        $join_type = $join_type==null?"join":$join_type;
        return $this
            ->{$join_type}($real_associated_table_name,
                array("{$associated_table_name}.{$associated_table_id_column}",
                    '=', "{$base_table_name}.{$base_table_f_id_column}"),
                $foreign_key_name);
    }
    protected function has_many($associated_class_name,
                                $foreign_key_name=null,
                                $join_type=null) {
        if( isset($this->join_done["$associated_class_name.$foreign_key_name"]) )
            return $this;
        $this->join_done["$associated_class_name.$foreign_key_name"] = true;

        $base_class_name = $this->_class_name;
        $base_table_name = Model::get_table_name($base_class_name);
        $base_table_id_column = Model::get_id_column_name($base_class_name);

        $associated_table_id_column = is_null($foreign_key_name)?
            Model::build_foreign_key_name(null, $base_table_name):
            Model::class_name_to_table_name($foreign_key_name) ."_". Model::get_id_column_name($base_class_name);
        $real_associated_table_name = Model::get_table_name(Model::$auto_prefix_models . $associated_class_name);
        $associated_table_name = $real_associated_table_name;

        $join_type = $join_type==null?"join":$join_type;
        return $this
            ->{$join_type}($real_associated_table_name,
                array("{$associated_table_name}.{$associated_table_id_column}",
                    '=', "{$base_table_name}.{$base_table_id_column}"));
    }

    /**
     * Helper method to manage one-to-one and one-to-many relations where
     * the foreign key is on the base table.
     */
    protected function belongs_to($associated_class_name,
                                  $foreign_key_name=null) {

        $base_class_name = $this->_class_name;
        $base_table_name = Model::get_table_name($base_class_name);

        $associated_table_id_column = Model::get_id_column_name($associated_class_name);
        $real_associated_table_name = Model::get_table_name(Model::$auto_prefix_models . $associated_class_name);
        $associated_table_name = is_null($foreign_key_name)?
            $real_associated_table_name:
            $foreign_key_name;

        $base_table_fk_column = $associated_table_name ."_". $associated_table_id_column;

        return $this
            ->join($real_associated_table_name,
                array("{$associated_table_name}.{$associated_table_id_column}",
                    '=', "{$base_table_name}.{$base_table_fk_column}"),
                $foreign_key_name);
    }

    public static function _resolvePropertyTarget($property_target){
        $parts = explode('.', $property_target);
        if(count($parts)>1){
            if( class_exists($parts[0]) && in_array("Model",class_parents($parts[0]))){
                $parts[0] = Model::get_table_name($parts[0]);
            }
            $property_target = implode(".",$parts);
        }
        return $property_target;
    }

    public function select($column,$alias=null) {
        $column = self::_resolvePropertyTarget($column);
        return parent::select($column,$alias);
    }

    public function count($column = '*') {
        $column = self::_resolvePropertyTarget($column);
        return parent::count($column);
    }

    public function count_distinct($column) {
        $column = self::_resolvePropertyTarget($column);
        $retour = parent::select_expr("COUNT(DISTINCT($column))","cnt")->find_one();
        return $retour->{"cnt"};
    }

    public function where_raw($clause) {
        return parent::where_raw("$clause");
    }

    public function where_sql($column,$operator,$sql) {
        $column = self::_resolvePropertyTarget($column);
        return parent::where_raw("$column $operator $sql");
    }

    public function where($column,$value,$is_column_value=false) {
        $column = self::_resolvePropertyTarget($column);
        if( $is_column_value ){
            $value = self::_resolvePropertyTarget($value);
        }
        return $is_column_value?
            parent::where_raw("$column=$value"):
            parent::where($column,$value);
    }

    public function where_equal($column,$value,$is_column_value=false) {
        $column = self::_resolvePropertyTarget($column);
        if( $is_column_value ){
            $value = self::_resolvePropertyTarget($value);
        }
        return parent::where_equal($column,$value);
    }

    public function where_not_equal($column,$value,$is_column_value=false) {
        $column = self::_resolvePropertyTarget($column);
        if( $is_column_value ){
            $value = self::_resolvePropertyTarget($value);
        }
        return $is_column_value?
            parent::where_raw("$column!=$value"):
            parent::where_not_equal($column,$value);
    }

    public function where_like($column,$value,$is_column_value=false) {
        $column = self::_resolvePropertyTarget($column);
        if( $is_column_value ){
            $value = self::_resolvePropertyTarget($value);
        }
        return $is_column_value?
            parent::where_raw("$column LIKE $value"):
            parent::where_like($column,$value);
    }

    public function where_gte($column,$value,$is_column_value=false) {
        $column = self::_resolvePropertyTarget($column);
        if( $is_column_value ){
            $value = self::_resolvePropertyTarget($value);
        }
        return $is_column_value?
            parent::where_raw("$column>=$value"):
            parent::where_gte($column,$value);
    }

    public function where_lte($column,$value,$is_column_value=false) {
        $column = self::_resolvePropertyTarget($column);
        if( $is_column_value ){
            $value = self::_resolvePropertyTarget($value);
        }
        return $is_column_value?
            parent::where_raw("$column<=$value"):
            parent::where_lte($column,$value);
    }

    public function where_in($column,$value) {
        $column = self::_resolvePropertyTarget($column);
        return parent::where_in($column,$value);
    }

    public function where_not_in($column,$value) {
        $column = self::_resolvePropertyTarget($column);
        return parent::where_not_in($column,$value);
    }

    public function where_null($column) {
        $column = self::_resolvePropertyTarget($column);
        return parent::where_null($column);
    }

    public function where_not_null($column) {
        $column = self::_resolvePropertyTarget($column);
        return parent::where_not_null($column);
    }

    public function order_by($column,$dir) {
        $column = self::_resolvePropertyTarget($column);
        return parent::_add_order_by($column,$dir);
    }

    public function order_by_asc($column) {
        return $this->order_by($column,"ASC");
    }

    public function order_by_desc($column) {
        return $this->order_by($column,"DESC");
    }

    public function group_by($column) {
        $column = self::_resolvePropertyTarget($column);
        return parent::group_by($column);
    }
} 