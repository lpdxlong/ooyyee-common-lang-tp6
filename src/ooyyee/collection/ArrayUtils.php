<?php
namespace ooyyee\collection;

class ArrayUtils
{
    /**
     *
     * @param array $array 二维数组
     * @param string 数组里面的key $column
     * @param string  $type ASC|DESC
     * @example
     * $users = array(  <br/>
    array(  <br/>
    'id'   => 1,  <br/>
    'name' => '张三',  <br/>
    'age'  => 25,  <br/>
    ),
    array(
    'id'   => 2,  <br/>
    'name' => '李四',  <br/>
    'age'  => 23,  <br/>
    )<br/>
    ); <br/>
    ArrayUtils::sort($users,'age','ASC');
    ArrayUtils::sort($users,'id','DESC');
     * @return array
     */
    public static function sort($array,$column,$type='ASC'):array {
        $type=strtoupper($type);
        usort($array, function ($array1,$array2) use($column,$type){
            if($array1[$column]==$array2[$column]){
                return 0;
            }
            if($array1[$column]>$array2[$column]){
                return $type =='ASC'?1:-1;
            }
            return $type =='ASC'?-1:1;
        });
        return $array;
    }


    public static function index(array $array, $name):array
    {
        $indexedArray = array();

        if (empty($array)) {
            return $indexedArray;
        }

        foreach ($array as $item) {
            if (isset($item[$name])) {
                $indexedArray[$item[$name]] = $item;
                continue;
            }
        }

        return $indexedArray;
    }

    public static function column(array $array,$key,$field='*'):array {
        $array=self::index($array,$key);

        if($field=='*') {
            return $array;
        }
        if(strpos($field,',')>0){
            $fields=explode(',',$field);
            foreach ($array as $k=> $item){
                $newItem=[];
                foreach ($fields as $f){
                    if(isset($item[$f])){
                        $newItem[$f]=$item[$f];
                    }
                }
                $array[$k]=$newItem;
            }
            return $array;
        }
        foreach ($array as $k=> $item){
            $array[$k]=$item[$field];
        }
        return $array;

    }

    public static function groupIndex(array $array, $key, $index):array
    {
        $grouped = array();

        foreach ($array as $item) {
            if (empty($grouped[$item[$key]])) {
                $grouped[$item[$key]] = array();
            }

            $grouped[$item[$key]][$item[$index]] = $item;
        }

        return $grouped;
    }

    /**
     * @param array $array
     * @param $key
     * @param bool $removeKey 是否在结果中把key删除掉
     * @return array
     */
    public static function group(array $array, $key,$removeKey=false):array
    {
        $grouped = array();

        foreach ($array as $item) {
            $myKey=$item[$key];
            if($removeKey){
                unset($item[$key]);
            }
            if (empty($grouped[$myKey])) {
                $grouped[$myKey] = array();
            }

            if($removeKey && count($item)==1){
                $grouped[$myKey][] = array_values($item)[0];
            }else{
                $grouped[$myKey][] = $item;
            }
        }

        return $grouped;
    }
}


