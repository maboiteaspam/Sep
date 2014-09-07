<?
class Role extends \Sep\ORM\Model {
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
        "Admin"=>[
            "model_type"=>"Admin",
            "foreign"=>"has_many_through",
        ],
    ];
}