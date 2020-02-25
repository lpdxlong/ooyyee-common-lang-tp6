<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-05-29
 * Time: 9:20
 */

namespace ooyyee\ui;


use think\facade\View;

class TabBuilder
{
    private $filter='tab';
    private $tabs=[];
    private $module='';

    /**
     * @param $module
     * @return $this
     */
    public function module($module):self
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @param $filter
     * @return $this
     */
    public function filter($filter):self
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @param $tabs
     * @return $this
     */
    public function tabs($tabs):self
    {
        $this->tabs = array_map(function ($tab){
            list($id,$name)=explode(':',$tab);
            return ['id'=>$id,'name'=>$name];
        },$tabs);
        return $this;
    }


    /**
     *
     * @return string
     */
    public function fetch():string {

        return View::fetch(__DIR__.'/view/tab.html',['filter'=>$this->filter,'tabs'=>$this->tabs,'module'=>$this->module]);
    }
    public function __toString()
    {
        return $this->fetch();
    }

}