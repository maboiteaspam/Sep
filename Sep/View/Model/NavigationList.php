<?
namespace Sep\View\Model;

class NavigationList extends \Sep\View\Base {
    public $total_cnt = 0;
    public $page = 0;
    public $items_cnt = 15;
    public $model_type;
    public $navigationPartial = 'partials/NavigationList.php';

    public function init($total_cnt,$model_type){
        if( $this->has_route_value("page") )
            $this->page = $this->get_route_value("page");
        if( $this->has_route_value("items_cnt") )
            $this->items_cnt = $this->get_route_value("items_cnt");
        $this->model_type = $model_type;
        $this->total_cnt = $total_cnt;
        return $this;
    }
    public function render(){
        $page_cnt = ($this->total_cnt/$this->items_cnt);
        $params = array(
            "page"=>$this->page,
            "list_href"=>"/".$this->model_type."/list",
            "item_cnt"=>$this->items_cnt,
            "items_cnt"=>array(
                5,10,15,25,50,100
            ),
            "total_cnt"=>$this->total_cnt,
            "page_cnt"=>$page_cnt,
        );
        return $this->app->view()->fetch($this->navigationPartial,$params);
    }
}