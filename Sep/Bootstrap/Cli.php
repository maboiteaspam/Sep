<?php
namespace Sep\Bootstrap;


class Cli extends Bootstrap{

    public $action;

    public function init( $env,$config_dirs=[] ){
        parent::init($env,$config_dirs);
        $config = $this->config;
        \ORM::configure( $config["db"] );
        global $argv;
        $this->action = $argv[1];
    }

    public function setupSchema($model_paths,$output_directory){

        $sqlHelper = new \Sep\Database\SqlHelper($this->config["db"]);
        $db_vendor = $sqlHelper->getDbVendor();
        $db_path = $sqlHelper->getDbPath();

        if( $db_vendor == "sqlite" && is_file($db_path) ){
            if( ! unlink($db_path) ){
                echo "cannot delete sqlite database\n";
                die();
            }
        }
        touch($db_path);

        // # load models
        $models = \Sep\Utils::scan_classes($model_paths);

        if( is_dir("$output_directory/$db_vendor/gen/") == false )
            mkdir("$output_directory/$db_vendor/gen/",0777,true);

        foreach( $models as $model_class ){

            $query = "";

            echo "$model_class\n";
            echo "-------------\n";

            if( class_exists($model_class) ){
                $orm_helper = new \Sep\ORMHelper();
                $query = $sqlHelper->create_command($orm_helper,$model_class);
                file_put_contents("$output_directory/$db_vendor/gen/$model_class.sql",$query);

            }else{
                foreach( $model_paths as $model_path ){
                    if( file_exists($model_path."/$model_class.sql") ){
                        $query = file_get_contents("$model_path/$model_class.sql");
                        break;
                    }
                }
            }
            if( $query != "" ){
                \ORM::raw_execute($query);
                echo "$query\n\n";
            }else{
                echo "no query\n\n";
            }
        }
    }

    public function setupFixtures($fixture_paths){

        function pretty_print_record($records){
            $str = "";
            foreach( $records as $record ){
                foreach( $record as $p=>$v){
                    $str .= "$p=>$v\t";
                }
            }
        }

        $files = \Sep\Utils::scan_classes($fixture_paths);
        foreach( $files as $file_path=>$type ){
            $records = \Sep\Utils::include_scoped("$file_path");
            if( is_array($records) ){
                echo "$type / ".count($records)." records\n";
                echo "-------------\n";

                $done = 0;
                $t_t = microtime(true);
                \ORM::get_db()->beginTransaction();
                foreach($records as $record ){
                    $admin = \Model::factory( $type )->create($record);
                    if( $admin->save() ){
                        $done++;
                    }else{
                        pretty_print_record(array($record));
                    }
                }
                \ORM::get_db()->commit();
                $t_t = microtime(true)-$t_t;
                echo "$done new records about $type / $t_t s\n\n";
            }else{
                echo "$type / skipped\n";
                echo "-------------\n\n";
            }
        }

    }

    public function createDump($model_paths){
        $date = date("Ymd His");


        $sqlHelper = new \Sep\Database\SqlHelper($this->config["db"]);

        $conn_str = $this->config["db"]["connection_string"];
        $user = isset($this->config["db"]["user"])?
            $this->config["db"]["user"]:"";
        $password = isset($this->config["db"]["password"])?
            $this->config["db"]["password"]:"";

        $dbh = null;

        try {
            $dbh = new \PDO($conn_str, $user, $password);
        } catch (\PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }

        $orm_helper = new \Sep\ORMHelper();

        if( $dbh ){

            // # load models
            $models = \Sep\Utils::scan_classes($model_paths);

            foreach( $models as $model_class ){

                $table_name = $orm_helper->get_table_name($model_class);
                echo "-- $date\n";
                echo "-- table: $table_name\n";
                echo "-- model: $model_class\n";
                echo "-- -----------\n";

                echo $sqlHelper->drop_command($orm_helper, $model_class)."\n";
                echo $sqlHelper->create_command($orm_helper, $model_class)."\n";

                echo "BEGIN;\n";
                $offset = 0;
                $limit = 150;
                do{
                    $rows = \ORM::for_table($table_name)->select("*")
                        ->offset($offset)->limit($limit)
                        ->find_array();
                    $offset += $limit;
                    foreach($rows as $row ){
                        $query = $sqlHelper->insert_command($table_name, $row);
                        echo "$query\n";
                    }
                }while( count($rows)>0 );
                echo "COMMIT;\n\n";

            }
        }

    }
}