<?
class Filter extends \Sep\ORM\Model {
    public static $orm_model = [
        "id"=>[
            "read_only"=>true,
            "pk"=>true,
            "type"=>"int",
        ],
        "name"=>[
            "type"=>"text",
            "is_nullable"=>false,
        ],
        "filter_type"=>[
            "type"=>"text",
            "is_nullable"=>false,
        ],
        "FilterColumn"=>[
            "model_type"=>"FilterColumn",
            "foreign"=>"has_many",
        ],
        "FilterCondition"=>[
            "model_type"=>"FilterCondition",
            "foreign"=>"has_many",
        ],
    ];

    public function safe_delete() {
        $deleted = \Sep\ORM\Model::factory('AdminFilter')
            ->where("filter_id",$this->id)
            ->delete_many();
        if( $deleted ){
            $deleted = $this ->deleteColumns();
        }
        if( $deleted ){
            $deleted = $this ->deleteConditions();
        }
        if( $deleted ){
            $deleted = $this->delete();
        }
        return $deleted;
    }
    public function deleteColumns() {
        return \Sep\ORM\Model::factory('FilterColumn')
            ->where("filter_id",$this->id)
            ->delete_many();
    }
    public function deleteConditions() {
        return \Sep\ORM\Model::factory('FilterCondition')
            ->where("filter_id",$this->id)
            ->delete_many();
    }
    public function Conditions() {
        return $this->has_many('FilterCondition')->order_by_asc("FilterCondition.position");
    }
    public function Columns() {
        return $this->has_many('FilterColumn')->order_by_asc("FilterColumn.position");
    }
}