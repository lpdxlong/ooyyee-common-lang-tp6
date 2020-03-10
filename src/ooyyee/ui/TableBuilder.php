<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-12-26
 * Time: 10:21
 */

namespace ooyyee\ui;

use think\facade\View;
use think\response\Json;


class TableBuilder
{
    private $columns = [];
    private $setting = [
        'toolbar' => [
            'edit' => '编辑.warm',
            'del' => '删除.danger'
        ],
        'cellMinWidth' => 120
    ];
    private $url = 'data';
    private $title = '';
    private $headerTitle;
    private $addTitle;
    private $searchFile = __DIR__.'/view/search.html';
    private $jsFile = 'common.table';
    private $values = [];
    private $moduleName='data';
    private $moduleUrl='';
    private $area=['width'=>'800px','height'=>'600px'];
    private $id='';





    /**
     * @param array $columns
     * @return $this
     */
    public function column(array $columns):self
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @param array $setting
     * @param int $type 0 合并 1 覆盖
     * @return $this
     */
    public function setting(array $setting, $type = 0):self
    {
        if ($type == 0) {
            $this->setting = array_merge($this->setting, $setting);
        } else if ($type == 1) {
            $this->setting = $setting;
        }
        return $this;
    }

    /**
     * @param string $url
     * @return TableBuilder
     */
    public function report($url='report'):self {
        return $this->setting([ 'report' => url($url)]);
    }

    /**
     * @param array $toolbar
     * @param int $type 0 合并 1 覆盖
     * @return $this
     */
    public function toolbar(array $toolbar, $type = 0):self
    {

        if ($type == 0) {
            $this->setting['toolbar'] = array_merge($this->setting['toolbar'], $toolbar);
        } else {
            $this->setting['toolbar'] = $toolbar;
        }
        return $this;
    }

    /**
     * @param $url
     * @return $this
     */
    public function data($url):self
    {
        $this->url = $url;
        return $this;
    }

    public function fetchTable():string
    {
        return $this->fetch(__DIR__.'/view/table.html');
    }

    /**
     * @param string $template
     * @return string
     */
    public function fetch($template = ''):string
    {
        $columns = TableColumnParser::parse(array_values($this->columns));
        foreach ($this->values as $key =>$v){
            foreach ($columns as $k=> $column){
                if($column['field'] == $key){
                    $column['templet']='values';
                }
                $columns[$k]=$column;
            }
        }

        $this->setting['columns']=$columns;
        $this->setting['url'] = url($this->url);
        $this->setting['values']=$this->values;
        View::assign([
            'title' => $this->getTitle(),
            'headerTitle' => $this->getHeaderTitle(),
            'addTitle' => $this->getAddTitle(),
            'search' => $this->getSearchFile(),
            'setting' => $this->setting,
            'jsfile' => $this->jsFile,
            'values' => json_encode($this->values, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),
            'moduleName'=>$this->moduleName,
            'moduleUrl'=>$this->moduleUrl,
            'area'=>$this->area
        ]);
        return View::fetch($template);
    }

    /**
     * @return string
     */
    public function getTitle():string
    {
        return $this->title;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title):self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeaderTitle()
    {
        return $this->headerTitle ?: $this->title . '管理';
    }

    /**
     * @param mixed $headerTitle
     */
    public function setHeaderTitle($headerTitle):void
    {
        $this->headerTitle = $headerTitle;
    }

    /**
     * @return mixed
     */
    public function getAddTitle()
    {
        return $this->addTitle ?: '添加' . $this->title;
    }

    /**
     * @param mixed $addTitle
     */
    public function setAddTitle($addTitle):void
    {
        $this->addTitle = $addTitle;
    }

    /**
     * @return string
     */
    public function getSearchFile():string
    {
        return $this->searchFile;
    }

    /**
     * @param $searchFile
     * @return $this
     */
    public function setSearchFile($searchFile):self
    {
        $this->searchFile = $searchFile;
        return $this;
    }

    /**
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function setArea($width,$height):self
    {
        $this->area = ['width'=>$width.'px','height'=>$height.'px'];
        return $this;
    }

    /**
     * @param $data
     * @param int $count
     * @return \think\response\Json
     */
    public function result($data, $count = 0,$totalRow=false):Json
    {
        $result=['errcode' => 0, 'data' => $data, 'total' => $count];
        if($totalRow){
            $result['totalRow']=$totalRow;
        }
        return json($result);
    }

    public function __toString()
    {
        return $this->fetch();
    }

    /**
     * @param $jsFile
     * @return $this
     */
    public function setJsFile($jsFile):self
    {
        $this->jsFile = $jsFile;
        return $this;
    }

    /**
     * @param $field
     * @param $values
     * @return $this
     */
    public function addValues($field, $values):self
    {
        $this->values[$field] = $values;
        return $this;
    }

    /**
     * @param $field
     * @param $aValue
     * @param $bValue
     * @return $this
     */
    public function addABValues($field, $aValue, $bValue):self
    {
        $this->values[$field] = [0 => $aValue, 1 => $bValue];
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id):self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $moduleName
     * @param $moduleUrl
     * @param string $title
     * @return $this
     */
    public function table($moduleName, $moduleUrl, $title = ''):self
    {
        $this->moduleName = $moduleName;
        $this->moduleUrl = $moduleUrl;
        $this->title = $title;
        return $this;
    }

}