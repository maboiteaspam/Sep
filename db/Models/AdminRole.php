<?
class AdminRole extends \Sep\ORM\Model {
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
        "role_id"=>[
            "type"=>"int",
            "is_nullable"=>false,
        ],
    ];
}