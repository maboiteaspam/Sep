<?php
namespace Sep\ORM;


class ORM extends \ORM{

    public static function for_table($table_name, $connection_name = self::DEFAULT_CONNECTION) {
        static::_setup_db($connection_name);
        return new static($table_name, array(), $connection_name);
    }
    public function find_many() {
        if(static::$_config[$this->_connection_name]['return_result_sets']) {
            return $this->find_result_set();
        }
        $dr = $this->_find_many();
        return $dr;
    }
    protected function _find_many() {
        $rows = $this->_run();
        return array_map(array($this, '_create_instance_from_row'), $rows);
    }

    protected static function _execute($query, $parameters = array(), $connection_name = self::DEFAULT_CONNECTION) {
        $time = microtime(true);
        try{
            $statement = self::get_db($connection_name)->prepare($query);
            self::$_last_statement = $statement;

            foreach ($parameters as $key => &$param) {
                if (is_null($param)) {
                    $type = \PDO::PARAM_NULL;
                } else if (is_bool($param)) {
                    $type = \PDO::PARAM_BOOL;
                } else if (is_int($param)) {
                    $type = \PDO::PARAM_INT;
                } else {
                    $type = \PDO::PARAM_STR;
                }

                $statement->bindParam(is_int($key) ? ++$key : $key, $param, $type);
            }
            $q = $statement->execute();
            self::_log_query($query, $parameters, $connection_name, (microtime(true)-$time));
        }catch(\Exception $ex ){
            self::_log_query($query, $parameters, $connection_name, (microtime(true)-$time));
            throw $ex;
        }
        return $q;
    }
} 