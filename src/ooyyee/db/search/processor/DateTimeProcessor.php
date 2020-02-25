<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-28
 * Time: 10:58
 */

namespace ooyyee\db\search\processor;


class DateTimeProcessor extends Processor {

    /**
     * @param array $options [dateTimeRange]
     * @see \ooyyee\db\search\processor\Processor::run()
     */
    public function run($options) {
        $valueKey=$options[0];
        if($this->isExists($valueKey)){
            $timeRange=$this->search [$valueKey];
            [ $min, $max] = explode ( ' - ', $timeRange );
            $min.=' 00:00:00';
            $max.=' 23:59:59';
            return [
                'between',
                [
                    $min,
                    $max
                ]
            ];
        }
        return false;
    }
}