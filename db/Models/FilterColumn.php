<?
class FilterColumn extends \Sep\ORM\Model {
    public static $orm_model = [
        "id"=>[
            "read_only"=>true,
            "pk"=>true,
            "type"=>"int",
        ],
        "filter_id"=>[
            "type"=>"int",
            "is_nullable"=>false,
        ],
        "name"=>[
            "type"=>"text",
            "is_nullable"=>false,
        ],
        "position"=>[
            "type"=>"int",
            "is_nullable"=>false,
        ],
    ];
}