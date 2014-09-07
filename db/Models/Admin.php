<?
class Admin extends \Sep\ORM\Model {
    public static $orm_model = [
        "id"=>[
            "read_only"=>true,
            "pk"=>true,
            "type"=>"int",
        ],
        "first_name"=>[
            "type"=>"text",
            "is_nullable"=>false,
        ],
        "last_name"=>[
            "type"=>"text",
            "is_nullable"=>false,
        ],
        "login"=>[
            "type"=>"text",
            "is_nullable"=>false,
            "unique"=>true,
        ],
        "password"=>[
            "type"=>"text",
            "is_nullable"=>false,
        ],
        "Role"=>[
            "model_type"=>"Role",
            "foreign"=>"has_many_through",
            "min_length"=>1,
        ],
        "Filter"=>[
            "model_type"=>"Filter",
            "foreign"=>"has_many_through",
        ],
    ];

    public static function hash_password($password) {
        return sha1($password);
    }
    public function __set($property, $value) {
        if( $property === "password" ) $value = self::hash_password($value);
        parent::__set($property, $value);
    }
    public function Filters() {
        return $this->has_many_through('Filter');
    }
    public function Roles() {
        return $this->has_many_through('Role');
    }
    public function has_role($role) {
        return $this->Roles()->where("Role.name",$role)->count()>0;
    }
    public function addFilter($filter_id,$shared=false) {
        $Filter = \Sep\ORM\Model::factory("Filter")->where("id",$filter_id)->find_one();
        if( $Filter ){
            $l = \Sep\ORM\Model::factory("AdminFilter")
                ->where("admin_id",$this->id)
                ->where("filter_id",$Filter->id)
                ->find_one();
            if( ! $l ) $l = \Sep\ORM\Model::factory("AdminFilter")->create();
            $l->admin_id = $this->id;
            $l->filter_id = $Filter->id;
            $l->shared = $shared;
            return $l->save();
        }
        return false;
    }
}