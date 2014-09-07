<?
namespace Sep;

class UserSession{

    public function is_logged(){
        return isset($_SESSION["logged_user"]);
    }

    public function set_logged_user( $user ){
        $_SESSION["logged_user"] = $user;
    }

    public function get_logged_user(  ){
        return $_SESSION["logged_user"];
    }

    public function unset_logged_user(  ){
        unset($_SESSION["logged_user"]);
    }

    public function __get($id){
        return $_SESSION["logged_user"]["$id"];
    }
}