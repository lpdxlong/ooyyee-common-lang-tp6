<?php

namespace ooyyee;
use think\File;
use think\Loader;

class Excel {
    /**
     *
     * @param File $file
     * @param int $titleRowCount 标题行数量
     * @return array
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
	public static function readToArray(File $file,$titleRowCount=1){

		//$extension = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
		$objPHPExcel = \PHPExcel_IOFactory::load($file->getPathname());
		$data = $objPHPExcel->getActiveSheet()->toArray();
		$i=0;
		while ($i<$titleRowCount){
            unset($data[$i]);
		    $i++;
        }

		foreach ($data as $k=>$row){
			foreach ($row as $kk=>$v){
				$row[$kk]=trim($v);
			}
			$data[$k]=$row;
			if(empty($row[0])){
				unset($data[$k]);
			}
		}
		return array_values($data);
	}

    /**
     * @param array $data
     * @param string $filename
     * @param array $properties
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
	public static function report($data,$filename,$properties=['creator'=>'精准云销客']){
		$objPHPExcel = new \PHPExcel();
		//$defined=['creator','last_modified_by','title','subject','description','keywords','category'];
		foreach ($properties as $key=> $property){
			$key=Loader::parseName($key, 1);
			$method='set'.$key;
			$objPHPExcel->getProperties()->$method($property);
		}
		$objPHPExcel->getActiveSheet()->fromArray($data);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
}

?>