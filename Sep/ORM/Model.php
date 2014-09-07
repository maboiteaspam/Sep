<?php
namespace Sep\ORM;


class Model extends \Model{

    /**
     * @param string $class_name
     * @param null $connection_name
     * @return \Sep\ORM\ORMWrapper
     */
    public static function factory($class_name, $connection_name = null) {
        $class_name = self::$auto_prefix_models . $class_name;
        $table_name = self::_get_table_name($class_name);

        if ($connection_name == null) {
            $connection_name = self::_get_static_property(
                $class_name,
                '_connection_name',
                ORMWrapper::DEFAULT_CONNECTION
            );
        }
        $wrapper = \Sep\ORM\ORMWrapper::for_table($table_name, $connection_name);
        $wrapper->set_class_name($class_name);
        $wrapper->use_id_column(self::_get_id_column_name($class_name));
        return $wrapper;
    }
    public static function __callStatic($method, $parameters) {
        if(function_exists('get_called_class')) {
            $model = static::factory(get_called_class());
            return call_user_func_array(array($model, $method), $parameters);
        }
    }
    public static function get_table_name($class_name) {
        return self::_get_table_name($class_name);
    }
    public static function class_name_to_table_name($class_name) {
        return self::_class_name_to_table_name($class_name);
    }
    public static function get_id_column_name($class_name) {
        return self::_get_id_column_name($class_name);
    }
    public static function build_foreign_key_name($specified_foreign_key_name, $table_name) {
        return self::_build_foreign_key_name($specified_foreign_key_name, $table_name);
    }
} 