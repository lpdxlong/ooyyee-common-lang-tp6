<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-05-24
 * Time: 12:17
 */

namespace ooyyee\collection;
use think\db\Query;

class ArrayHelper
{
    private $withs=[];
    private $times=[];
    private $data;

    /**
     * @param array $data
     * @return ArrayHelper
     */
    public static function instance(array $data):ArrayHelper{
        $helper= new static();
        $helper->data=$data;
        return $helper;
    }


    /**
     * 给字段赋值
     * @param $fromField
     * @param $table
     * @param array $config
     * @param string $toField
     * @param string $defaultValue
     * @return ArrayWith
     */
    public function with($fromField,$table,$config=[],$toField='',$defaultValue=''):ArrayWith{
        $this->withs[$fromField]=['toField'=>$toField?:$fromField,'with'=>new ArrayWith($table,$config,$this),'default'=>$defaultValue];
        return   $this->withs[$fromField]['with'];
    }

    /**
     * 不用数据库 赋值
     * @param string $fromField
     * @param array $values
     * @param string $toField
     * @param string $defaultValue
     * @return $this
     */
    public function withNotDB($fromField,$values,$toField='',$defaultValue=''):self {
        $this->withs[$fromField]=['toField'=>$toField?:$fromField,'values'=>$values,'default'=>$defaultValue];
        return   $this;
    }

    /**
     * 格式化时间
     * @param string $fromField
     * @param string $format
     * @param string $toField
     * @return $this
     */
    public function time($fromField,$format='Y-m-d',$toField=''):self {
        $this->times[$fromField]=['toField'=>$toField?:$fromField,'format'=>$format];
        return $this;
    }

    /**
     * @return array
     */
    public function debug():array {

        return $this->withs;
    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function build():array {
        foreach ( $this->withs as $key=> $config ) {
            $values=array_column($this->data,$key);
            if(isset($config['with'])){
                if($config['with'] instanceof Query){
                    $linkDatas=$config['with']->select($values);
                    $this->withs[$key]['bind']=$linkDatas;
                }
            }else{
                $this->withs[$key]['bind']=$config['values'];
            }
        }

        return array_map(function ($data){
            foreach ($this->withs as $key=>$config){
                $data[$config['toField']]=self::assign($config['bind'],$data[$key],$config['default']);
            }
            foreach ($this->times as $key=>$config){
                $data[$config['toField']]=$data[$key]?date($config['format'],$data[$key]):'';
            }
            return $data;
        },$this->data);

    }


    /**
     * 判断$values 中是否包含 $key 的值
     * @param array $values
     * @param string $key
     * @param string $defaultValue
     */
    public static function assign($values,$key,$defaultValue=''){
        if(!$key){
            return $defaultValue;
        }
        return $values[$key]??$defaultValue;
    }
    /**
     * 获取二维数组的某一个字段的值的集合
     * @param array $data
     * @param  $columns [optional]
     * @return array
     */
    public static function column($data,...$columns):array {
        $values=[];
        foreach ($data as $k=>$value){
            foreach ($columns as $column){
                if(isset($value[$column]) && $value[$column]){
                    $values[]=$value[$column];
                }
            }
        }
        return $values;
    }
    /**
     * 获取二维数组最后一个数据的 某一个字段的值
     * @param array $data
     * @param string $key
     */
    public static function end($data,$key=''){
        $end=end($data);
        if($key){
            return $end[$key] ?? null;
        }
        return $end;
    }

}