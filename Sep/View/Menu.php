<?php

namespace Sep\View;

class Menu extends \Sep\View\Base {
    public $view_model;
    public function init($view_model){
        $this->view_model = $view_model;
    }
    public function render(){
        $items_view = $this->app->view()->fetch("partials/MenuView.php",$this->view_model);
        return $items_view;
    }
}