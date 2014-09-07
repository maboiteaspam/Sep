<?
namespace Sep\View\Model;

class FilterList extends \Sep\View\Base {

    public $model_type;
    public $filter_id;
    public $view_headers_model;
    public $filterPartial = 'partials/FilterList.php';

    public function init($filter_id,$model_type,$view_headers_model){
        $this->model_type = $model_type;
        $this->filter_id = $filter_id;
        $this->view_headers_model = $view_headers_model;
        return $this;
    }
    public function render(){
        $params = array(
            "filterable"=>$this->get_filterable_columns(),
            "headers"=>($this->get_all_headers()),
            "filter_type"=>$this->model_type,
            "views"=>$this->get_views(),
            "conditions_views"=>$this->get_conditions_views(),
            'filter_id' => $this->filter_id,
            'operators' => \FilterCondition::$orm_model["operator"]["options"],
            'missing_name_error' => $this->intl->get_message("filter.missing_name"),
            'missing_columns_error' => $this->intl->get_message("filter.missing_columns"),
            'new_filter_label' => $this->intl->get_message("filter.new_view"),
            'edit_filter_label' => $this->intl->get_message("filter.edit_view"),
            'visit_filter_label' => $this->intl->get_message("filter.visit_view"),
            'edit_condition_label' => $this->intl->get_message("filter.edit_condition"),
            'remove_condition_label' => $this->intl->get_message("filter.remove_condition"),
            'add_condition_label' => $this->intl->get_message("filter.add_condition"),
            'save_filter_label' => $this->intl->get_message("btn.save"),
            'reset_filter_label' => $this->intl->get_message("btn.reset"),
            'delete_filter_label' => $this->intl->get_message("btn.delete"),
            'view_name_label' => $this->intl->get_message("name.field_name"),
        );
        return $this->app->view()->fetch($this->filterPartial,$params);
    }
    public function get_views(){
        $views = array();
        $UserSession = $this->app->container->get('UserSession')->get_logged_user();
        $db_view = \Sep\ORM\Model::factory('Filter')
            ->has_many_through("Admin")
            ->where("AdminFilter.shared", false)
            ->where("Admin.id", $UserSession["id"])
            ->select("Filter.*")
            ->where("Filter.filter_type", $this->model_type)
            ->find_many();
        foreach( $db_view as $v){
            $vv = $v->as_array();
            $vv["conditions"] = $v->Conditions()->find_array();
            foreach($v->Columns()->select("name")->find_array() as $a){
                $vv["columns"][] = $a["name"];
            }
            $views[] = $vv;
        }
        $db_view_shared = \Sep\ORM\Model::factory('Filter')
            ->has_many_through("Admin")
            ->where("AdminFilter.shared", true)
            ->where_not_equal("Admin.id", $UserSession["id"])
            ->select("Filter.*")
            ->where("Filter.filter_type", $this->model_type)
            ->find_many();
        foreach( $db_view_shared as $v){
            $vv = $v->as_array();
            $vv["conditions"] = $v->Conditions()->find_array();
            foreach($v->Columns()->select("name")->find_array() as $a){
                $vv["columns"][] = $a["name"];
            }
            $views[] = $vv;
        }
        return $views;
    }
    public function get_conditions_views(){
        $views = array();
        $UserSession = $this->app->container->get('UserSession')->get_logged_user();
        $db_view = \Sep\ORM\Model::factory('Filter')
            ->has_many_through("Admin")
            ->where("AdminFilter.shared", false)
            ->where("Admin.id", $UserSession["id"])
            ->select("Filter.*")
            ->find_many();
        foreach( $db_view as $v){
            $vv = $v->as_array();
            $vv["conditions"] = $v->Conditions()->find_array();
            foreach($v->Columns()->select("name")->find_array() as $a){
                $vv["columns"][] = $a["name"];
            }
            $views[] = $vv;
        }
        $db_view_shared = \Sep\ORM\Model::factory('Filter')
            ->has_many_through("Admin")
            ->where("AdminFilter.shared", true)
            ->where_not_equal("Admin.id", $UserSession["id"])
            ->select("Filter.*")
            ->find_many();
        foreach( $db_view_shared as $v){
            $vv = $v->as_array();
            $vv["conditions"] = $v->Conditions()->find_array();
            foreach($v->Columns()->select("name")->find_array() as $a){
                $vv["columns"][] = $a["name"];
            }
            $views[] = $vv;
        }
        return $views;
    }
    public function get_all_headers(){
        $list_headers = array();
        foreach( $this->view_headers_model as $d_h=>$m ){
            $list_headers[] = array(
                "text"=>$m["label"]?$m["label"]:$d_h,
                "value"=>$d_h,
            );
        }
        return $list_headers;
    }
    public function get_filterable_columns(){
        $filterable = [];
        foreach( $this->view_headers_model as $column=> $m ){
            $not_filterable = isset($m["filter"]) && $m["filter"] == false;
            $an_action = isset($m["type"]) && $m["type"] == "LinkArea";
            if( !$not_filterable && !$an_action ){
                $filterable[] = array(
                    "text"=>$m["label"]?$m["label"]:$column,
                    "value"=>$column,
                );
            }
        }
        return $filterable;
    }
}