<?php

namespace ooyyee\db;


use ooyyee\Excel;
use think\db\Query;



class Report {

	
	private $column; // pk
	private $dataProcessor;
	private $chunkSize=50;
	private $query;
	private $completeProcessor;
	private $dataCount=0;
	private $dataList=[];
	private $titles=[];
	private $keys=[];
	private $title;
	private $sortFunction;

    /**
     * @param string $title
     */
    public function setTitle($title):void
    {
        $this->title = $title;
    }



	/**
	 * @return callable $completeProcessor
	 */
	public function getCompleteProcessor():callable {
		return $this->completeProcessor;
	}

	/**
	 * @param callable $completeProcessor
	 */
	public function setCompleteProcessor($completeProcessor):void {
		$this->completeProcessor = $completeProcessor;
	}

	/**
	 * @return Query $query
	 */
	public function getQuery():Query {
		return $this->query;
	}

	/**
	 * @param Query $query
	 */
	public function setQuery($query):void {
		$this->query = $query;
	}

	/**
	 * @return array $column
	 */
	public function getColumn():array {
		return $this->column;
	}


	/**
	 * @return int $chunkSize
	 */
	public function getChunkSize():int {
		return $this->chunkSize;
	}

	/**
	 * @param string $column
	 */
	public function setColumn($column):void {
		$this->column = $column;
	}


	/**
	 * @param int $chunkSize
	 */
	public function setChunkSize($chunkSize):void {
		$this->chunkSize = $chunkSize;
	}

	
	
	/**
	 * @return callable $dataProcessor
	 */
	public function getDataProcessor() :callable {
		return $this->dataProcessor;
	}

	/**
	 * @param callable $dataProcessor
	 */
	public function setDataProcessor($dataProcessor):void {
		$this->dataProcessor = $dataProcessor;
	}

	/**
	 * @return int $dataCount
	 */
	public function getDataCount():int {
		return $this->dataCount;
	}

	/**
	 * @param number $dataCount
	 */
	public function setDataCount($dataCount):void {
		$this->dataCount = $dataCount;
	}
	/**
	 * @param number $dataCount
	 */
	public function incDataCount($dataCount):void {
		$this->dataCount+=$dataCount;
	}

    /**
     * @param $columns
     * @return array
     */
	private function processColumn($columns):array {
        $_columns=\ooyyee\ui\TableColumnParser::parse($columns);
		return empty($_columns)?$columns:$_columns;
	}
	public function sort(Callable $sortFunction):void {
	    $this->sortFunction=$sortFunction;
    }
	/**
	 * 
	 * @param array $columns
	 * @param string $filename
	 * @return int
     * @throws
	 */
	public function build($columns ,$filename):int {
	    $this->setTitle('导出'.$filename);
		$columns=$this->processColumn($columns);

		try{
			$this->setColumns($columns);
			$this->getQuery()->chunk($this->getChunkSize(), function($dataList){
				$dataList=call_user_func($this->dataProcessor,$dataList);

				return $this->processData($dataList);
			},$this->getColumn());
			if($this->getCompleteProcessor()){
				call_user_func($this->getCompleteProcessor(),$this->dataList,count($this->dataList));
			}
			if(is_callable($this->sortFunction)){
			    $this->dataList=call_user_func($this->sortFunction,$this->dataList);
            }
			$reportData=[];
            foreach ($this->dataList as $v){
                $row=[];
                foreach ($this->keys as $k){
                    $row[]=$v[$k];
                }
                $reportData[]=$row;
            }

            Excel::report(array_merge([$this->titles],$reportData), $filename);
			return count($reportData);
		}catch(\Exception $e){
			header_remove('Content-type');
			header_remove('Content-Disposition');
			header_remove('Pragma');
			header_remove('Expires');
			throw $e;
		}
	}
	
	
	/**
	 * @param array: $columns
	 */
	private function setColumns($columns):void {
	    $this->titles=array_map(function($column){
			return preg_replace('/\(.*?\)/', '', $column['title']);
		}, array_values($columns));
		$this->keys = array_column($columns,'field');
	}

    /**
     * @param $dataList
     * @return bool
     */
	private function processData($dataList):bool {
		foreach ($dataList as $v){
		    $this->dataList[]=$v;
		}
		$count=count($dataList);
        return !($count<$this->getChunkSize());

	}
}

