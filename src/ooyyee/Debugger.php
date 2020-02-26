<?php

namespace ooyyee;


class Debugger
{


    /* 是否开启异步处理信息 */
   public static $log = true;


    /**
     * @param $data
     * @param string $name
     * @param string $file_line
     * @param string $logFile
     */
    public static function file($data, $name = '', $file_line = '', $logFile = 'runtime.log'):void
    {

        if ($data instanceof \Exception) {
            $_data = array('line' => $data->getLine(), 'code' => $data->getCode(), 'file' => $data->getFile(), 'message' => $data->getMessage(), 'trace' => $data->getTraceAsString());
            $data = $_data;
        }
        if ($data != "\r\n" || !empty($data)) {
            if (self::$log) {
                file_put_contents(app()->getRuntimePath().$logFile, "\r\n" . date('Y-m-d H:i:s') . "---------$name $file_line begin---------" . "\r\n", FILE_APPEND);
                file_put_contents(app()->getRuntimePath().$logFile, self::log_details($data), FILE_APPEND);
                file_put_contents(app()->getRuntimePath().$logFile, "\r\n" . date('Y-m-d H:i:s') . "---------$name $file_line end---------" . "\r\n", FILE_APPEND);

            }
        }
    }

    /**
     * @param $data
     * @param string $pref
     * @return string
     */
    private static function log_details($data, $pref = '')
    {
        if (is_array($data)) {
            $str = array('');
            foreach ( $data as $k => $v ) {
                $str[] = $pref . $k . ' => ' . self::log_details($v, $pref . "\t");
            }
            return implode("\n", $str);
        }
        return $data;
    }
}
