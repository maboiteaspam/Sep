<?php

namespace Sep\View;

class FlashMessages extends \Sep\View\Base {
    public function render(){
        $app = $this->app;
        $data = [
            "flash"=>$app->environment['slim.flash']->getMessages()
        ];
        $items_view = $app->view()->fetch("helpers/FlashMessages.php",$data);
        return $items_view;
    }
}