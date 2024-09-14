<?php

namespace ooyyee\db\search;


use ooyyee\db\search\processor\Processor;
use think\db\Where;
use think\facade\Request;


class WhereBuilder
{
    private $search = [];
    private $where;
    private $whereProcessors = [];

    /**
     * @param string|array|object|null $search
     * @param array $where
     * @return WhereBuilder
     */
    public static function instance($search = 'where', $where = []):WhereBuilder
    {
        $builder = new static();
        if (is_string($search)) {
            $builder->search = json_decode(request()->request($search), true);
        } else if (is_array($search)) {
            $builder->search = $search;
        } else if (is_object($search) && $search instanceof Base64Search) {
            $builder->search = $search->decode();
        }
        $builder->where = new Where($where);
        return $builder;
    }

    public static function requestGetInstance($where=[]):WhereBuilder{
        return self::instance(Request::get(),$where);
    }
    public static function requestParamInstance($where=[]):WhereBuilder{
        return self::instance(Request::param(),$where);
    }
    public static function requestPostInstance($where=[]):WhereBuilder{
        return self::instance(Request::post(),$where);
    }


    public static function inOrEq($ids):array
    {
        $cnt = count($ids);
        if ($cnt > 1) {
            return [
                'in',
                $ids
            ];
        }

        return [
            'eq',
            $cnt == 1 ? $ids[0] : -1
        ];
    }
    public function has($field):bool {
        return isset($this->search[$field]);
    }
    public function getSearch($field = null)
    {
        if ($field) {
            return $this->search[$field]??false;
        }
        return $this->search;
    }

    /**
     * @param $options
     * @return Where
     */
    public function build($options):Where
    {
        $buildOptions = [];
        foreach ( $options as $k => $v ) {
            if (is_string($k)) {
                if (is_string($v)) {
                    $buildOptions [$k] = [
                        'eq',
                        $v];
                } else if (is_array($v)) {
                    $buildOptions [$k] = $v;
                }
            } else {
                $buildOptions [$v] = [
                    'eq',
                    $v];
            }
        }
        foreach ( $buildOptions as $k => $v ) {
            $type = array_shift($v);
            $processor = $this->getProcessor($type);
            $value = $processor->run($v);
            if ($value !== false) {
                $this->where[$k] = $value;
            }
        }
        return $this->where;
    }

    /**
     *
     * @param string $type
     * @return \ooyyee\db\search\processor\Processor
     */
    public function getProcessor($type):Processor
    {
        if (!isset ($this->whereProcessors [$type])) {
            $class = "ooyyee\\db\\search\\processor\\" . parse_name($type, 1) . 'Processor';
            $this->whereProcessors [$type] = new $class ($this, $this->search, $this->where);
        }
        return $this->whereProcessors [$type];
    }
}
