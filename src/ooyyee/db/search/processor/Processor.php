<?php

namespace ooyyee\db\search\processor;

use ooyyee\db\search\WhereBuilder;

abstract class Processor
{
    protected $search;
    protected $where;
    /**
     *
     * @var WhereBuilder
     */
    protected $builder;

    /**
     *
     * @param WhereBuilder $builder
     * @param array $search
     * @param array $where
     */
    public function __construct($builder, $search, &$where)
    {
        $this->search = $search;
        $this->where = $where;
        $this->builder = $builder;
    }

    abstract public function run($options);


    public function isExists($key):bool
    {
        return isset($this->search[$key]) && $this->search[$key] != '';
    }

    public function filter($value, $filter = null)
    {

        if ($filter && is_callable($filter)) {
            $value = $filter($value);
        }
        return $value;
    }
}

