<?
namespace Sep\View\Model;

class ItemSelector {
    /**
     * @var \Sep\ORMHelper
     */
    public $orm_model;
    public $base_query;

    public function init($orm_model, $base_query){
        $this->orm_model = $orm_model;
        $this->base_query = $base_query;
        return $this;
    }

    /**
     * @return \ORMWrapper
     */
    public function get_selector(){
        $base_query = $this->base_query;
        $base_query = is_callable($base_query)?$base_query():$base_query;
        return $base_query;
    }

    /**
     * @param $model_type
     * @param $base_query
     * @param $filter_id
     * @return \ORMWrapper
     */
    public function apply_filter($model_type,$base_query,$filter_id){
        if( $filter_id ){
            $View = \Sep\ORM\Model::factory("Filter")->find_one($filter_id);
            if( $View ){
                foreach( $View->Columns()->find_many() as $c ){
                    $c = $c->name;
                    if( $this->orm_model->is_foreign($model_type,$c) ){
                        $this->apply_join($base_query,$model_type,$c);
                    }
                }
                $conditions = $View->Conditions()->find_many();
                foreach( $conditions as $c ){
                    if( $c->left_field_type == "property" ){
                        $field = $c->left_field;
                        if( $this->orm_model->is_foreign($model_type,$field) ){
                            $this->apply_join($base_query,$model_type,$field);
                        }
                    }
                    if( $c->right_field_type == "property" ){
                        $field = $c->right_field;
                        if( $this->orm_model->is_foreign($model_type,$field) ){
                            $this->apply_join($base_query,$model_type,$field);
                        }
                    }
                }

                foreach( $View->Conditions()->find_many() as $c ){
                    $left_field = $c->left_field;
                    $right_field = $c->right_field;
                    if( $c->left_field_type == "property" ){
                        if( $this->orm_model->is_foreign($model_type,$left_field) ){
                            list($l_prop,$f_prop) = $this->orm_model->split_property($left_field);
                            $f_model_type = $this->orm_model->get_meta($model_type,$left_field,"model_type");
                            $left_field = "$f_model_type.$f_prop";
                        }
                    }
                    if( $c->right_field_type == "property" ){
                        if( $this->orm_model->is_foreign($model_type,$right_field) ){
                            list($l_prop,$f_prop) = $this->orm_model->split_property($right_field);
                            $f_model_type = $this->orm_model->get_meta($model_type,$right_field,"model_type");
                            $right_field = "$f_model_type.$f_prop";
                        }
                    }

                    $condition_pattern = "$c->left_field_type"."$c->operator"."$c->right_field_type";

                    if( preg_match("/^(rawsql|value)(IS NULL|IS NOT NULL)/",
                            "$condition_pattern")>0){
                        $base_query->where_raw("$left_field $c->operator");
                    }elseif( preg_match("/^property(IS NULL|IS NOT NULL)/",
                            "$condition_pattern")>0){
                        if($c->operator=="IS NULL"){
                            $f = $this->orm_model->is_foreign($model_type,$left_field)?$left_field:"$model_type.$left_field";
                            $base_query->where_null("$f");
                        }else if($c->operator=="IS NOT NULL"){
                            $f = $this->orm_model->is_foreign($model_type,$left_field)?$left_field:"$model_type.$left_field";
                            $base_query->where_not_null("$f");
                        }
                    }elseif( preg_match("/property([=!><]{1,2}|LIKE|NOT LIKE)(value|property)/",
                            "$condition_pattern")>0){
                        if($c->operator=="="){
                            $base_query->where("$left_field",$right_field,
                                $c->right_field_type=="property");
                        }elseif($c->operator=="!="){
                            $base_query->where_not_equal("$left_field",$right_field,
                                $c->right_field_type=="property");
                        }elseif($c->operator==">="){
                            $base_query->where_gte("$left_field",$right_field,
                                $c->right_field_type=="property");
                        }elseif($c->operator=="<="){
                            $base_query->where_lte("$left_field",$right_field,
                                $c->right_field_type=="property");
                        }elseif($c->operator=="LIKE"){
                            $base_query->where_like("$left_field",$right_field,
                                $c->right_field_type=="property");
                        }elseif($c->operator=="NOT LIKE"){
                            $base_query->where_not_like("$left_field",$right_field,
                                $c->right_field_type=="property");
                        }
                    }elseif( preg_match("/value([=!><]{1,2}|LIKE|NOT LIKE)(property)/",
                            "$condition_pattern")>0){
                        if($c->operator=="="){
                            $base_query->where("$right_field",$left_field);
                        }elseif($c->operator=="!="){
                            $base_query->where_not_equal("$right_field",$left_field);
                        }elseif($c->operator==">="){
                            $base_query->where_gte("$right_field",$left_field);
                        }elseif($c->operator=="<="){
                            $base_query->where_lte("$right_field",$left_field);
                        }elseif($c->operator=="LIKE"){
                            $base_query->where_like("$right_field",$left_field);
                        }elseif($c->operator=="NOT LIKE"){
                            $base_query->where_not_like("$right_field",$left_field);
                        }
                    }else if( preg_match("/rawsql([=!><]|LIKE|NOT LIKE|IN|NOT IN)property/",
                            "$condition_pattern")>0){
                        $base_query->where_sql("$right_field",$c->operator,$left_field);
                    }else if( preg_match("/property([=!><]|LIKE|NOT LIKE|IN|NOT IN)rawsql/",
                            "$condition_pattern")>0){
                        $base_query->where_sql("$left_field",$c->operator,$right_field);
                    }else if( preg_match("/(value|rawsql)([=!><]|LIKE|NOT LIKE|IN|NOT IN)(value|rawsql)/",
                            "$condition_pattern")>0){
                        $base_query->where_raw("$left_field $c->operator $right_field");
                    }else{
                        var_dump("not matched $condition_pattern");
                    }
                }
            }
        }

        return $base_query;
    }

    /**
     * @param $model_type
     * @param $base_query
     * @param $înline_filters
     * @return \ORMWrapper
     */
    public function apply_inline_filters($model_type,$base_query,$înline_filters){
        foreach( $înline_filters as $header=>$inline_filter ){
            $inline_filter = "%$inline_filter%";
            if( $inline_filter != "%%" ){
                if( $this->orm_model->is_foreign($model_type,$header) ){
                    $this->apply_join($base_query,$model_type,$header);
                    $base_query->where_like($header,"$inline_filter");
                }else if( $this->orm_model->has_field($model_type,$header) ){
                    if(!preg_match("/[^.]+[.][^.]+/",$header))
                        $header = $model_type.".$header";
                    $base_query->where_like($header,"$inline_filter");
                }

            }
        }
        return $base_query;
    }

    /**
     * @param $model_type
     * @param $base_query
     * @param $înline_sorts
     * @return \ORMWrapper
     */
    public function apply_inline_sorts($model_type,$base_query,$înline_sorts){
        foreach( $înline_sorts as $header=>$sort ){
            if( $sort && $this->orm_model->has_field($model_type,$header) ){
                if( $this->orm_model->is_foreign($model_type,$header) ){
                    $this->apply_join($base_query,$model_type,$header);
                }
                if(!preg_match("/[^.]+[.][^.]+/",$header)) $header = $model_type.".$header";
                $base_query->order_by($header,$sort);
            }
        }
        return $base_query;
    }

    public function apply_join($base_query,$model_type,$header){
        list($l_prop,$f_prop) = $this->orm_model->split_property($header);
        $f_model_type = $this->orm_model->get_meta($model_type,$header,"model_type");
        if( $this->orm_model->is_foreign($model_type,$header,"has_many_through") ){
            $base_query->has_many_through($f_model_type,
                $f_model_type==$l_prop?null:$l_prop,
                "left_outer_join");
        }else if( $this->orm_model->is_foreign($model_type,$header,"has_one") ){
            $base_query->has_one($f_model_type,
                $f_model_type==$l_prop?null:$l_prop,
                "left_outer_join");
        }else if( $this->orm_model->is_foreign($model_type,$header,"has_many") ){
            $f_on = $this->orm_model->get_meta($model_type,$header,"on_foreign",$l_prop);
            $base_query->has_many($f_model_type,
                $f_model_type==$f_on?null:$f_on,
                "left_outer_join");
        }
    }
} 