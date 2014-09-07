<?
namespace Sep;

class Utils {
    public static function load_configs($env, $paths=array() ){
        $config = [];
        foreach($paths as $path ){
            $c = Utils::include_scoped("$path/config.php");
            $config = self::array_merge_recursive($config, $c);
        }
        $distrib_config = isset($config["distrib"])?$config["distrib"]:[];
        $env_config = isset($config[$env])?$config[$env]:[];
        $config = self::array_merge_recursive($distrib_config, $env_config);
        return $config;
    }
    public static function include_scoped($path,$vars=array()){
        extract($vars);
        return include($path);
    }
    public static function scan_classes($path){
        $files = scandir($path);
        array_shift($files);
        array_shift($files);
        $classes = array();
        foreach($files as $file ){
            $classes["$path/$file"] = basename($file,".php");
        }
        return $classes;
    }
    /**
     * http://php.net/manual/en/function.array-merge-recursive.php#92195
     *
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    public static function array_merge_recursive ( array &$array1, array &$array2 ) {
        $merged = $array1;

        foreach ( $array2 as $key => &$value ) {
            if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) ) {
                $merged [$key] = self::array_merge_recursive ( $merged [$key], $value );
            } else if(is_integer($key)) {
                if( ! in_array($value,$merged) ) $merged[] = $value;
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}