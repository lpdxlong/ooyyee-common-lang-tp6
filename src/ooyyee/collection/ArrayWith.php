<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-05-24
 * Time: 12:19
 */

namespace ooyyee\collection;



use think\facade\Db;

class ArrayWith
{
    private $table;
    private $config;
    private $field='name';
    private $key='id';
    private $append=[];
    private $helper;
    public function __construct($table,$config,ArrayHelper $helper)
    {
        $this->table=$table;
        $this->config=$config;
        $this->helper=$helper;
    }

    /**
     * @param $key
     * @return $this
     */
    public function key($key):self {
        $this->key=$key;
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function field($field):self {
        $this->field=$field;
        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function appendData($data):self {
        $this->append=$data;
        return $this;
    }

    /**
     * @return ArrayHelper
     */
    public function end():ArrayHelper{
        return $this->helper;
    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function endBuild():array {
        return $this->helper->build();
    }

    /**
     * @param $values
     * @return array
     */
    public function select($values):array {

        $db=Db::connect($this->config)->name($this->table);

        $data= $db->whereIn($this->key,$values)->column($this->field,$this->key);
        if(empty($this->append)){
            return $data;
        }
        foreach ($this->append as $k=>$v){
            $data[$k]=$v;
        }
        return $data;

    }

    /**
     * @return string
     */
    public function __toString()
    {
        $json=json_encode($this);
        return is_string($json)?$json:'';
    }

}