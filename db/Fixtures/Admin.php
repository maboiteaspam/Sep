<?
$records = array(
    array(
        "id"=>0,
        "first_name"=>"John",
        "last_name"=>"Doe",
        "login"=>"admin",
        "password"=>Admin::hash_password("admin"),
    ),
);
for( $i=1;$i<50;$i++){
    $records[] = array(
        "id"=>$i,
        "first_name"=>"John",
        "last_name"=>"Doe",
        "login"=>"admin$i",
        "password"=>Admin::hash_password("admin$i"),
    );
}

return $records;