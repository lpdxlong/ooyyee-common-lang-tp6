<?php

namespace ooyyee;

use think\facade\Db;

class CurrentUser
{
    private $user = array (
        'id' => 0,
        'name'=>'',
    );

    public function __construct(){}

    /**
     * @param $uid
     * @return array|mixed|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public  function create($uid){

        $user = cache ( 'login_admin_' . $uid );
        if (! $user) {

            $login=config('login');
            Db::connect($login['database'])->name($login['user']);


            $user = Db::connect($login['database'])->name($login['user'])->where ( 'id', $uid )->field ( 'id,name,mobile,avatar' )->find ();
            $roleIds = Db::connect($login['database'])->name($login['user_role'])->where ( 'uid', $uid )->column ( 'role_id' );
            $permissions = [ ];
            if (! empty ( $roleIds )) {
                if (in_array ( 1, $roleIds,true )) {
                    $permissions = Db::connect($login['database'])->name($login['permission_node'])->column ( 'id' );
                    $user['super'] = 1;
                } else {
                    $roles = Db::connect($login['database'])->name($login['role'])->whereIn ( 'id', $roleIds )->select ()->toArray();
                    $chunks=array_map(function ($role){
                        return explode ( ',', $role['permissions']);
                    },$roles);
                    foreach ($chunks as $permission){
                        foreach ($permission as $id){
                            $permissions[]=(int)$id;
                        }
                    }
                    $permissions = array_unique ( $permissions );
                    $user['super']=0;
                }
                $user['permissions'] = array_values($permissions);
            } else {
                $user['super'] = 0;
                $user['permissions'] = [ ];
            }
            $user['roles']=$roleIds;
            cache ( 'login_admin_' . $uid, json_encode($user), 7200 );
        }
        $this->user = is_string($user)?json_decode($user,true):$user;
        return $this->user;
    }

    public  function isLogin():bool {
        return $this->uid() ? true : false;
    }

    public  function isSuperAdmin(){
        $user=$this->user;
        return  $user['super']?? 0;
    }
    public  function user():array {
        return $this->user;
    }
    public  function uid(){
        $user = $this->user;
        return $user['id'];
    }

    /**
     * @return string
     */
    public  function name():string {
        $user =$this->user;
        return $user['name'];
    }
    public  function permissions(){
        $user =$this->user;
        return $user['permissions']?? [];
    }
    public  function hasRole($roleId):bool {
        $user = $this->user;
        return isset ( $user['roles'] ) ? in_array($roleId,$user['roles'],false) : false;
    }
    public  function hasPermission($permissionId):bool {
        $permissions=$this->permissions();
        return in_array($permissionId, $permissions,false);
    }
}

