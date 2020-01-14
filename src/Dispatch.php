<?php


namespace Wandell\Dispatch;


class Dispatch
{
    private $factory;

    public function __construct($config)
    {
        $this->factory = new Api($config);
    }

    public function __call($name,$param)
    {
        return $this->factory->{$name}(...$param);
    }
}