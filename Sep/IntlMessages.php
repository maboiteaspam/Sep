<?
namespace Sep;

class IntlMessages {

    protected $messages = array();

    public function __construct( ){
    }
    public function load_from_dir($path, $lng_code="en"){
        $files=scandir($path);
        array_shift($files);
        array_shift($files);
        foreach($files as $file ){
            if( preg_match("/[.]php$/","$file")>0){
                $this->load_from_php("$path/$file",$lng_code);
            }else if( preg_match("/[.]xml$/","$file")>0){
                $this->load_from_xml("$path/$file",$lng_code);
            }
        }
    }
    public function load_from_php($file, $lng_code="en"){
        $sxe = include($file);
        foreach( $sxe as $message_code => $messages ){
            foreach( $messages as $msg_lng_code=> $message_pattern ){
                if( $msg_lng_code == $lng_code ){
                    $this->set_message( $message_code, (string)$message_pattern);
                }
            }
        }
    }
    public function load_from_xml($file, $lng_code="en"){
        $sxe = new \SimpleXMLElement($file, NULL, true);
        foreach( $sxe->message as $message ){
            foreach( $message->translated as $message_pattern ){
                if( $message_pattern["lng"] == $lng_code ){
                    $this->set_message( (string)$message['code'], (string)$message_pattern);
                }
            }
        }
    }
    public function set_message($message_code, $message_pattern){
        $this->messages[$message_code] = $message_pattern;
    }
    public function get_message($message_code, $params=array()){
        $message = "$message_code";
        if( isset($this->messages[$message_code]) ){
            $message = $this->messages[$message_code];
        }else{
            foreach( $this->messages as $code_pattern => $message_pattern ){
                if( preg_match("/^$code_pattern/i", $message_code) ){
                    $message = preg_replace("/$code_pattern/i", $message_pattern, $message_code);
                    break;
                }
            }
        }
        return vsprintf($message, $params);
    }
} 