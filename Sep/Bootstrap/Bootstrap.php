<?php
namespace Sep\Bootstrap;


class Bootstrap {

    /**
     * @var \Sep\IntlMessages
     */
    public $intl;
    public $config;

    public function init( $env,$config_dirs=[] ){
        $this->intl = new \Sep\IntlMessages();
        $this->config = \Sep\Utils::load_configs($env,$config_dirs);
    }
    public function get_user_language( ){
        $config = $this->config;
        $user_languages = $config["user_languages"];
        $user_language = null;
        if( isset($user_languages[0]) ) $user_language = $user_languages[0];
        return $user_language;
    }

    public function load_intl_messages($dirs=[],$language=null ){
        $intl = $this->intl;
        $dirs = $this->config["intl_path"];
        $dirs = array_reverse($dirs);
        if(!$language)$language=$this->get_user_language();
        foreach($dirs as $dir ){
            $intl->load_from_dir( $dir,$language);
        }
    }
} 