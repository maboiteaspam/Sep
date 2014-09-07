<?php
namespace Sep\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint as Blueprint;
use Illuminate\Database\Schema\Grammars as Grammars;

class SqlHelper {

    public $config;
    public $capsule;

    public function __construct($db_config){
        $this->config = $db_config;
    }

    public function getDbVendor(){
        $conn_str = $this->config["connection_string"];
        preg_match("/(sqlite|mysql):(.+)/",$conn_str,$matches);
        return $matches[1];
    }

    public function getDbPath(){
        $conn_str = $this->config["connection_string"];
        preg_match("/(sqlite|mysql):(.+)/",$conn_str,$matches);
        return $matches[2];
    }

    public function getDbHost(){
        preg_match("/host=([^;]+)/",$this->getDbPath(),$matches);
        return isset($matches[1])?$matches[1]:"";
    }

    public function getDbPort(){
        preg_match("/port=([^;]+)/",$this->getDbPath(),$matches);
        return isset($matches[1])?$matches[1]:"";
    }

    public function getDbName(){
        preg_match("/dbname=([^;]+)/",$this->getDbPath(),$matches);
        return isset($matches[1])?$matches[1]:"";
    }

    public function getCapsule(){
        if(!$this->capsule){
            $db_vendor = $this->getDbVendor();
            $this->capsule = new Capsule;
            $this->capsule->setAsGlobal();
            $this->capsule->addConnection([
                'driver'    => $db_vendor,
                'host'      => $this->getDbHost(),
                'port'      => $this->getDbPort(),
                'database'  => $db_vendor=="sqlite"?$this->getDbPath():$this->getDbName(),
                'username'  => !isset($this->config['username'])?"":$this->config['username'],
                'password'  => !isset($this->config['password'])?"":$this->config['password'],
                'charset'   => !isset($this->config['charset'])?"":$this->config['charset'],
                'collation' => !isset($this->config['collation'])?"":$this->config['collation'],
                'prefix'    => '',
            ]);
        }
        return $this->capsule;
    }

    public function drop_command($orm_helper,$model_class){

        $capsule = $this->getCapsule();
        $db_vendor = $this->getDbVendor();

        $table_name = $orm_helper->get_table_name($model_class);

        $table = new Blueprint($table_name);

        $grammar = null;
        if( $db_vendor == "sqlite" ){
            $grammar = new Grammars\SQLiteGrammar();
        }
        if( $db_vendor == "mysql" ){
            $grammar = new Grammars\MySqlGrammar();
        }
        if( $db_vendor == "pgsql" ){
            $grammar = new Grammars\PostgresGrammar();
        }

        $table->dropIfExists();
        $query = $table->toSql($capsule->connection(),$grammar);
        $query = $query[0];
        $query = "$query;";

        $query = preg_replace("/^drop table/ ","DROP TABLE",$query);

        return $query;
    }
    public function create_command($orm_helper,$model_class){

        $capsule = $this->getCapsule();
        $db_vendor = $this->getDbVendor();

        $table_name = $orm_helper->get_table_name($model_class);

        $table = new Blueprint($table_name);

        $grammar = null;
        if( $db_vendor == "sqlite" ){
            $grammar = new Grammars\SQLiteGrammar();
        }
        if( $db_vendor == "mysql" ){
            $grammar = new Grammars\MySqlGrammar();
        }
        if( $db_vendor == "pgsql" ){
            $grammar = new Grammars\PostgresGrammar();
        }

        $table->create();
        foreach( $orm_helper->get_fields($model_class) as $field ){
            if($orm_helper->is_foreign($model_class,$field) == false ){

                $type = $orm_helper->get_meta($model_class,$field,"type");
                $is_pk = $orm_helper->get_meta($model_class,$field,"pk",false)===true;
                $options = $orm_helper->get_meta($model_class,$field,"options",false);

                if( $type == "int" && $is_pk) $col = $table->increments( $field );
                else if(  $options!==false ) $col = $table->enum($field, $options);
                else if( $type == "int" ) $col = $table->integer( $field );
                else if( $type == "bool" ) $col =$table->boolean( $field );
                else if( $type == "text" ) $col =$table->text( $field );
                else if( $type == "phone" ) $col =$table->text( $field );
                else if( $type == "email" ) $col =$table->text( $field );
                else if( $type == "date" ) $col =$table->text( $field );
                else if( $type == "datetime" ) $col =$table->dateTime( $field );
                else if( $type == "longitude" ) $col =$table->text( $field );
                else if( $type == "latitude" ) $col =$table->text( $field );

                if( $is_pk && $type !== "int" ){
                    $table->primary( $field );
                }

                if( $orm_helper->get_meta($model_class,$field,"is_nullable",true) !== false ){
                    $col->nullable($field);
                }
                if( $orm_helper->get_meta($model_class,$field,"unique",false) == true ){
                    $table->unique($field);
                }
            }else if( $orm_helper->is_foreign($model_class,$field,"has_one") ){
                $col_name = \Sep\ORM\Model::class_name_to_table_name($field)."_id";
                $col = $table->integer( $col_name );

                if( $orm_helper->get_meta($model_class,$field,"is_nullable",true) !== false ){
                    $col->nullable( $col_name );
                }
                if( $orm_helper->get_meta($model_class,$field,"unique",false) == true ){
                    $col->unique( $col_name );
                }
            }
        }
        $query = $table->toSql($capsule->connection(),$grammar);
        $query = $query[0];
        $query = "$query;";

        $query = preg_replace("/^create table [\"]?([^\"]+)[\"]?\s+/ ","CREATE TABLE \"$1\"\n",$query);
        $query = preg_replace("/,\s+([\"])/",",\n\t$1",$query);
        $query = preg_replace("/[)]([;]?)$/","\n)$1\n",$query);
        $query = preg_replace("/^[(]([\"]?)/m","(\n\t$1",$query);

        return $query;

    }
    public function insert_command($table_name, $row){

        $quote_character = isset($this->config["identifier_quote_character"])?
            $this->config["identifier_quote_character"]:"";

        $db_fields = array();
        foreach($row as $key => $value) {
            if( !is_numeric($key) ){
                $db_fields[] = $key;
            }
        }
        $db_values = array();
        foreach($db_fields as $key) {
            $db_values[] = $row[$key];
        }
        $quote = function($part)use($quote_character){
            if ($part === '*') return $part;
            return $quote_character.str_replace($quote_character,
                $quote_character . $quote_character,
                $part
            ).$quote_character;
        };
        $quote_str = function($part){
            $quote_character = "'";
            return $quote_character.str_replace($quote_character,
                $quote_character . $quote_character,
                $part
            ).$quote_character;
        };

        $query = "INSERT INTO ";
        $query .= $quote($table_name);
        $field_list = array_map($quote, $db_fields);
        $query .= "(" . join(", ", $field_list) . ") ";
        $query .= "VALUES ";

        $db_values = array_map($quote_str, $db_values);
        $db_values = implode(', ', $db_values);
        $query = "$query ($db_values);";

        return $query;
    }
} 