<?
namespace Sep\View\Model;

class ItemList extends \Sep\View\Base {
    public $page = 0;
    public $items_cnt = 15;
    public $filter_id;
    public $înline_filters;
    public $înline_sorts;
    public $view_model;
    /**
     * @var \Sep\ORMHelper
     */
    public $orm_model;
    /**
     * @var ItemSelector
     */
    public $item_selector;
    public $model_type;
    public $listPartial = 'partials/ItemList.php';

    public function init($model_type,$view_model, $orm_model){

        $this->view_model = $view_model;
        $this->model_type = $model_type;

        if( $this->has_route_value("page") )
            $this->page = $this->get_route_value("page");
        if( $this->has_route_value("items_cnt") )
            $this->items_cnt = $this->get_route_value("items_cnt");

        $this->form->add_value_from_get("sort");
        $this->form->add_value_from_get("filter");

        $înline_filters = $this->form->get_value("filter");
        $înline_sorts = $this->form->get_value("sort");
        if( is_array($înline_filters) ){
            $this->înline_filters = [];
            foreach($înline_filters as $filter_header=>$filter_value ){
                $filter_header = preg_match("/[A-Z][^_]+_[^_]+/",$filter_header)
                    ?str_replace("_",".",$filter_header)
                    :$filter_header;
                $this->înline_filters[$filter_header] = $filter_value;
            }
        }
        if( is_array($înline_sorts) ){
            $this->înline_sorts = [];
            foreach($înline_sorts as $sort_header=>$sort_value ){
                $sort_header = preg_match("/[A-Z][^_]+_[^_]+/",$sort_header)
                    ?str_replace("_",".",$sort_header)
                    :$sort_header;
                $this->înline_sorts[$sort_header] = $sort_value;
            }
        }

        $this->filter_id = $this->form->sanitizer->sanitized_get("filter_id");

        $this->orm_model = $orm_model;

        $this->item_selector = new ItemSelector();
        $view = $this;
        $reader = isset($this->view_model["provider"]["read"])?
            $this->view_model["provider"]["read"]:null;
        $base_query = function()use($model_type,$view,$reader){
            $base_query = \Sep\ORM\Model::factory($model_type);
            if( $reader ){
                call_user_func($reader,$view,$base_query);
            }
            return $base_query;
        };
        $this->item_selector->init($this->orm_model,$base_query);


        return $this;
    }
    public function render(){
        $model_type = $this->model_type;
        $items = $this->fetch_items();
        $total_cnt = $this->fetch_count();
        $headers = $this->get_view_header_labels();
        $header_models = $this->get_view_headers_model();
        if( $total_cnt==0 ){
            unset($headers["delete"]);
            unset($headers["edit"]);
            unset($header_models["delete"]);
            unset($header_models["edit"]);
        }
        $header_list = [];
        foreach($headers as $header=>$label ){
            if( isset($header_models[$header]) ){
                $node = new \Sep\View\Node\Anonymous($this->app,$this->intl,$this->sanitizer);
                $node->params = $header_models[$header];
                $node->init("TableHeaderArea");
                $header_list[$header] = $node->render();
            }
        }

        $rows = [];
        foreach($items as $item){
            $item_view_model = $this->get_view_model($item,$header_list);
            $row = array();
            foreach($header_list as $header=>$label ){
                $node = new \Sep\View\Node\Anonymous($this->app,$this->intl,$this->sanitizer);
                $node->params = $item_view_model[$header];
                $node->init($item_view_model[$header]["type"]);
                $row[$header] = $node->render();
            }
            $rows[] = $row;
        }
        $NavigationList = new NavigationList($this->app,$this->intl,$this->sanitizer);
        $NavigationList->init($total_cnt,$this->model_type);
        $navigation_view = $NavigationList->render();

        $FilterList = new FilterList($this->app,$this->intl,$this->sanitizer);
        $FilterList->init($this->filter_id,$this->model_type,$this->get_view_headers_model());
        $filter_view = $FilterList->render();

        $qs = isset($_SERVER["QUERY_STRING"])?$_SERVER["QUERY_STRING"]:"";
        $qs = $qs?"?$qs":"";

        $previous_page_href = "";
        $current_page_href = "/$this->model_type/list/".($this->items_cnt)."/".($this->page)."$qs";
        if( $this->page>0){
            $previous_page_href = "/$this->model_type/list/".($this->items_cnt)."/".($this->page-1)."$qs";
        }
        $next_page_href = "";
        if( ($this->page+1)*($this->items_cnt)<$total_cnt)
            $next_page_href = "/$this->model_type/list/".($this->items_cnt)."/".($this->page+1)."$qs";

        $table_name = $this->orm_model->get_table_name($this->model_type);
        $params = $this->view_model["list"]["data"];
        $params = array_merge([
            'navigation_list' => $navigation_view,
            'filter_id' => $this->filter_id,
            'filter_list' => $filter_view,
            'has_inline_filters' => count($this->înline_filters)>0,
            'rows' => $rows,
            'list_headers' => $header_list,
            'add_record_href' => "/$model_type/add",
            'export_excel' => $this->intl->get_message("$table_name.btn.export_excel"),
            'export_excel_href' => "/$model_type/export_excel$qs",
            'current_page_href' => "$current_page_href",
            'previous_page_href' => "$previous_page_href",
            'previous_page' => $this->intl->get_message("$table_name.btn.previous_page"),
            'next_page_href' => "$next_page_href",
            'next_page' => $this->intl->get_message("$table_name.btn.next_page"),
            'page_title'=>$this->intl->get_message("$table_name.list.page_title"),
            "list_title"=>$this->intl->get_message("$table_name.list.page_title"),
            "add_record"=>$this->intl->get_message("$table_name.btn.add_record"),
        ],$params);
        foreach($params as $i=>$p){
            if( is_callable($p) ){
                $params[$i] = $p($this);
            }
        }
        return $this->app->view()->fetch($this->listPartial,$params);
    }
    public function render_excel(){
        $model_type = $this->model_type;
        $header_list = $this->get_view_header_labels();
        $header_models = $this->get_view_headers_model();
        $rows = [];
        $current = 0;
        $fetch_by = 3;
        do{
            $selector = $this->item_selector->get_selector()
                ->select("$model_type.*")
                ->group_by("$model_type.id")
                ->offset($current*$fetch_by)
                ->limit($fetch_by);
            if( $this->înline_filters ){
                $this->item_selector->apply_inline_filters($model_type,$selector,$this->înline_filters);
            }
            if( $this->înline_sorts ){
                $this->item_selector->apply_inline_sorts($model_type,$selector,$this->înline_sorts);
            }
            $items = $selector->find_many();
            $current++;
            foreach($items as $item){
                $item_view_model = $this->get_view_model($item,$header_list);
                $row = array();
                foreach($header_list as $header=>$label ){
                    $row[$header] = $item_view_model[$header]["value"];
                }
                $rows[] = $row;
            }
        }while(count($items)>0);
        $params = $this->view_model["list"]["data"];
        $params = array_merge([
            'rows' => $rows,
            'list_headers' => $header_list,
        ],$params);
        foreach($params as $i=>$p){
            if( is_callable($p) ){
                $params[$i] = $p($this);
            }
        }


        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties();
        $activeSheet = $objPHPExcel->getSheet(0);
        $row_index = 1;
        $col_index = 0;
        foreach($header_list as $header=>$label ){
            $activeSheet->setCellValueByColumnAndRow($col_index, $row_index,
                $header_models[$header]["label"]);
            $col_index++;
        }
        $row_index++;
        foreach($rows as $row ){
            $col_index = 0;
            foreach($row as $header=>$value ){
                $activeSheet->setCellValueByColumnAndRow($col_index, $row_index,
                    $value);
                $col_index++;
            }
            $row_index++;
        }

// Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="01simple.xlsx"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
    public function fetch_items(){
        $model_type = $this->model_type;
        $selector = $this->item_selector->get_selector();
        if( $this->filter_id ){
            $this->item_selector->apply_filter($model_type,$selector,$this->filter_id);
        }
        $selector->select("$model_type.*")
            ->group_by($model_type.".".\Sep\ORM\Model::get_id_column_name($model_type))
            ->offset($this->page*$this->items_cnt)
            ->limit($this->items_cnt);
        if( $this->înline_filters ){
            $this->item_selector->apply_inline_filters($model_type,$selector,$this->înline_filters);
        }
        if( $this->înline_sorts ){
            $this->item_selector->apply_inline_sorts($model_type,$selector,$this->înline_sorts);
        }
        $items = $selector->find_many();
        while( count($items) == 0 && $this->page>0){
            $this->page--;
            $selector = $this->item_selector->get_selector();
            if( $this->filter_id ){
                $this->item_selector->apply_filter($model_type,$selector,$this->filter_id);
            }
            $selector->select("$model_type.*")
                ->group_by($model_type.".".\Sep\ORM\Model::get_id_column_name($model_type))
                ->offset($this->page*$this->items_cnt)
                ->limit($this->items_cnt);
            if( $this->înline_filters ){
                $this->item_selector->apply_inline_filters($model_type,$selector,$this->înline_filters);
            }
            if( $this->înline_sorts ){
                $this->item_selector->apply_inline_sorts($model_type,$selector,$this->înline_sorts);
            }
            $items = $selector->find_many();
            if( count($items) > 0 ){
                $model_type = $this->view_model["model_type"];
                $redirect_url = "/$model_type/list/$this->items_cnt/$this->page";
                if( isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"] != "") $redirect_url .= "?".$_SERVER["QUERY_STRING"];
                if( $this->filter_id ) $redirect_url = \Sep\Utils::append_to_url($redirect_url,["filter_id"=>$this->filter_id]);
                $this->app->redirect( $redirect_url );
            }
        }
        if( isset($_GET["debug"]) ){
            var_dump(\ORM::get_last_query());
        }
        return $items;
    }
    public function fetch_count(){
        $model_type = $this->model_type;
        $selector = $this->item_selector->get_selector();
        if( $this->filter_id ){
            $this->item_selector->apply_filter($model_type,$selector,$this->filter_id);
        }
        if( $this->înline_filters ){
            $this->item_selector->apply_inline_filters($model_type,$selector,$this->înline_filters);
        }
        $count = $selector->count_distinct("$this->model_type.id");
        if( isset($_GET["debug"]) ){
            var_dump(\ORM::get_last_query());
        }
        return $count;
    }
    public function get_view_model( $item, $header_list ){
        $view_headers_model = $this->get_view_headers_model();
        $model_type = $this->model_type;

        $view_model = array();
        foreach($view_headers_model as $header=>$model){
            if( isset($header_list[$header]) ){
                unset($model["filter"]);
                if(!array_key_exists("type",$model)){
                    $model["type"] = "SpanArea";
                }
                if(!array_key_exists("name",$model)){
                    $model["name"] = "$header";
                }
                if(!array_key_exists("href",$model)){
                    if( $header == "edit" ){
                        $model["href"] = "/$model_type/edit/$item->id";
                    }else if( $header == "delete" ){
                        $model["href"] = "/$model_type/delete/$item->id";
                    }
                }
                if(!array_key_exists("value",$model) ){
                    if( $this->orm_model->is_foreign($model_type,$header) ){
                        list($p_name,$f_prop) = $this->orm_model->split_property($header);
                        $max_length = $this->orm_model->get_meta($model_type,$header,"max_length",6);
                        $f_items = $this->orm_model
                            ->select_foreign($model_type,$header,$item,$max_length+1)
                            ->find_many();
                        $text = [];
                        foreach( $f_items as $i=>$f_item){
                            if($i<$max_length || $max_length === NULL ){
                                $text[] = $f_item->{$f_prop};
                            }
                        }
                        $text = join(", ",$text);
                        if( count($f_items)>$max_length)
                            $text .= "...";
                        $f_model_type = $this->orm_model->get_meta($model_type,$header,"model_type");
                        $model["view_more_href"] = "/$f_model_type/list?filter[{$model_type}_id]={$item->id}";
                        $model["view_more_label"] = $this->intl->get_message("$f_model_type.view_more");
                        $model["value"] = $text;
                    }else if( isset($item->{$header}) ){
                        $model["value"] = $item->{$header};
                    }
                }
                $view_model[$header] = $model;
            }
        }
        foreach($view_model as $header=>$model){
            foreach($model as $k=>$v){
                if( is_callable($v) ){
                    $view_model[$header][$k] = $v($this, $item);
                }
            }
        }
        return $view_model;
    }
    public function get_view_headers_model( ){
        $view_model = $this->view_model["list"]["model"];

        if(!array_key_exists("edit",$view_model) ){
            $view_model["edit"] = [];
        }
        $view_model["edit"] = array_merge([
            "type"=>"LinkArea",
            "filter"=>false,
            "sort"=>false,
        ],$view_model["edit"]);
        if(!array_key_exists("delete",$view_model) ){
            $view_model["delete"] = [];
        }
        $view_model["delete"] = array_merge([
            "type"=>"LinkArea",
            "method"=>"post",
            "filter"=>false,
            "sort"=>false,
            "delete_confirm_message"=>$this->intl->get_message("deletion.confirm_message"),
        ],$view_model["delete"]);

        foreach($view_model as $header=>$model){
            if(!array_key_exists("type",$model)){
                $model["type"] = "SpanArea";
            }
            if(!array_key_exists("name",$model)){
                $model["name"] = "$header";
            }
            if(!array_key_exists("input_name",$model)){
                $model["input_name"] = str_replace(".","_",$model["name"]);
            }
            if(!array_key_exists("filter",$model)){
                $model["filter"] = null;
            }
            if(!array_key_exists("sort",$model)){
                $model["sort"] = $model["filter"]!==false?"":false;
                if(isset($this->înline_sorts[$header])) $model["sort"] = $this->înline_sorts["$header"];
            }
            if(!array_key_exists("inline_filter",$model)){
                $model["inline_filter"] = $model["filter"]!==false?"":false;
                if(isset($this->înline_filters[$header])) $model["inline_filter"] = $this->înline_filters["$header"];
            }
            if(!array_key_exists("label",$model)){
                $cc = \Sep\ORM\Model::class_name_to_table_name($header);
                $model["label"] = $this->intl->get_message("$cc.header_name");
            }
            $view_model[$header] = $model;
        }
        return $view_model;
    }
    public function get_view_header_labels(){
        $default_headers = $this->view_model["list"]["default_headers"];
        $list_headers = array();
        $View = null;
        if( $this->filter_id ){
            $View = \Sep\ORM\Model::factory("Filter")->find_one($this->filter_id);
        }
        if( $View ){
            foreach( $View->Columns()->find_many() as $c ){
                $list_headers[$c->name] = $c->name;
            }
        }else{
            foreach( $default_headers as $d_h ){
                $list_headers[$d_h] = $d_h;
            }
        }
        if( $this->înline_filters ){
            foreach($this->înline_filters as $filter_header=>$filter ){
                if( isset($default_headers[$filter_header]) && !isset($list_headers["$filter_header"]) ){
                    $list_headers["$filter_header"] = [];
                }
            }
        }
        if( in_array("edit",$list_headers) == false ){
            $list_headers["edit"] = "edit";
        }
        if( in_array("delete",$list_headers) == false ){
            $list_headers["delete"] = "delete";
        }
        return $list_headers;
    }
}