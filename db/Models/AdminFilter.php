<?
class AdminFilter extends \Sep\ORM\Model {
    public static $orm_model = [
        "id"=>[
            "read_only"=>true,
            "pk"=>true,
            "type"=>"int",
        ],
        "admin_id"=>[
            "type"=>"int",
            "is_nullable"=>false,
        ],
        "filter_id"=>[
            "type"=>"int",
            "is_nullable"=>false,
        ],
        "shared"=>[
            "type"=>"bool",
            "is_nullable"=>false,
        ],
    ];
}