<?php
namespace ooyyee\widget;
use think\app\Url;


/**
 * ajax 获取数据表格部件
 * @author lpdx111
 *
 */
class Table extends Widget
{

    /**
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function render($data)
    {
        $default = array();
        $default['id']='table';
        $default['cellMinWidth']=80;
        $default['toolbar']=false;
        $default['report']="";
        $data=array_merge($default,$data);



        $url=$data['url'];

        if($url instanceof Url){
            $url=$url->build();
        }


        $config=[
            'id'=>$data['id'],
            'elem'=>'#'.$data['id'],
            'url'=>$url,
            'cellMinWidth'=>$data['cellMinWidth'],
            'cols'=>[$data['columns']]
        ];
        if(isset($data['where'])){
            $config['where']=$data['where'];
        }
        if(isset($data['height'])){
            $config['height']=$data['height'];
        }
        if(isset($data['totalRow'])){
            $config['totalRow']=$data['totalRow'];
        }

        if($data['toolbar']){
            foreach ($data['toolbar'] as $k=>$v){
                if(is_string($v)){
                    $_class='primary';
                    if(strpos($v, '.')){
                        list($name,$_class)=explode('.', $v);
                    }else{
                        $name=$v;
                    }
                    $v=['event'=>$k,'name'=>$name,'_class'=>$_class];
                }else{
                    $v['event']=$k;
                }
                if(!isset($v['_class'])){
                    $v['_class']='primary';
                }
                $classes=['primary','normal','warm','danger','disabled'];
                $myclassess=explode(' ', $v['_class']);
                foreach ($myclassess as $key=>$_c){
                    if(in_array($_c, $classes)){
                        $myclassess[$key]='layui-btn-'.$_c;
                    }
                }
                $v['_class']=implode(' ', $myclassess);
                $data['toolbar'][$k]=$v;
            }
        }


        foreach ($config['cols'] as $k=> $cols){
            foreach ($cols as $key=>$col){
                if(isset($col['toolbar'])){
                    $col['toolbar']='#'.$config['id'].'_manager';
                    $config['cols'][$k][$key]=$col;
                }
            }
        }



        $data['config']=$this->my_json_decode(json_encode($config,JSON_UNESCAPED_UNICODE));



        return $this->renderFile('table', $data);
    }
    private function my_json_decode($str) {
        $str = preg_replace('/"(\w+)"(\s*:\s*)/is', '$1$2', $str);   //去掉key的双引号
        $str=str_replace('"', "'", $str);
        return $str;
    }

}