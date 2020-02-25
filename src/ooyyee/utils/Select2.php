<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-10-25
 * Time: 11:00
 */

namespace ooyyee\utils;


use think\db\Where;
use think\facade\Db;
use think\facade\Request;

class Select2
{


    /**
     * 单选
     * @param $table
     * @param string $titleField
     * @param string $database
     * @param array $order
     * @param callable|null $whereCallback
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function data($table,$titleField='title',$database='',$order=['id'=>'desc'],callable $whereCallback=null):array {



        $db=Db::connect($database,$table);
        if (Request::param('init', 0)) {
            $id = Request::param('id');
            $data = $db->field("id,{$titleField} as text")->where('id',$id)->find();
            if ($data) {
                return ['data' => $data, 'errcode' => 0];
            }
            return ['errcode' => 404];
        }
        $search = Request::param('search');
        $page = Request::param('page', 1);

        $where = new Where();
        if ($search) {
            $where[$titleField] = ['like', '%' . $search . '%'];
        }

        if($whereCallback){
            $where=$whereCallback($where);
        }


        $db->where($where);
        $count = $db->count();
        $db->page($page, 10);
        $db->field("id,$titleField as text");
        $db->order($order);
        $data =$db->select();

        return ['data' => $data, 'count' => $count];


    }

    /**
     * 多选
     * @param $table
     * @param string $titleField
     * @param string $database
     * @param array $order
     * @param callable|null $whereCallback
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */

    public static function datas($table,$titleField='title',$database='',$order=['id'=>'desc'],callable $whereCallback=null):array {

        $db=Db::connect($database,$table);

        if (Request::param('init', 0)) {
            $id = Request::param('id');
            $city = $db->field("id,{$titleField} as text")->whereIn('id',$id)->select();
            if ($city) {
                if(count($city) ==1){
                    return ['data' => $city[0], 'errcode' => 0];
                }
                return ['datas' => $city, 'errcode' => 0];
            }
            return ['errcode' => 404];
        }
        $search = Request::param('search');
        $page = Request::param('page', 1);

        $where = new Where();
        if ($search) {
            $where[$titleField] = ['like', '%' . $search . '%'];
        }

        if($whereCallback){
            $where=$whereCallback($where);
        }


        $db->where($where);
        $count = $db->count();
        $db->page($page, 10);
        $db->field("id,$titleField as text");
        $db->order($order);
        $data =$db->select();

        return ['data' => $data, 'count' => $count];


    }




}