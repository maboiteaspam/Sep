<?
namespace Sep\View;

use Slim\Slim;

abstract class Base {
    /**
     * @var Slim
     */
    public $app;
    /**
     * @var \Sep\IntlMessages
     */
    public $intl;
    /**
     * @var \Sep\View\Form
     */
    public $form;
    /**
     * @var \Sep\Sanitizer
     */
    public $sanitizer;

    public function __construct(Slim $app, \Sep\IntlMessages $intl, \Sep\Sanitizer $sanitizer){
        $this->app = $app;
        $this->sanitizer = $sanitizer;
        $this->intl = $intl;
        $this->form = new \Sep\View\Form($intl,$sanitizer);
    }
    public function add_intl_error($field,$id,$params=array()){
        $this->form->add_intl_error($field,$id,$params);
        return $this;
    }
    public function has_errors(){
        return $this->form->has_errors();
    }
    public function get_value($field){
        return $this->form->get_value($field);
    }
    public function get_route_value($field){
        $params = $this->app->router()->getCurrentRoute()->getParams();
        return isset($params[$field])?$params[$field]:false;
    }
    public function has_route_value($field){
        $params = $this->app->router()->getCurrentRoute()->getParams();
        return isset($params[$field]);
    }
    public abstract function render();
} 