<?php

namespace Ruth\RpcBundle\Service;

class ServiceTest {

    public function foo(int $x, int $y) : int
    {
        return $x * $y;
    }

    public function boo() : int
    {
        return 1;
    }

    public function zoo(int $x, int $y) : array 
    {
        return [
            'x'     => $x,
            'y'     => $y,
            'total' => $this->foo($x, $y)
        ];
    }

    public function zooo(int $x, int $y) : object 
    {
        $json = json_encode($this->zoo($x, $y));

        return json_decode($json);
    }

    private function shiu(int $x, int $y) : int
    {
        return $x * $y;
    }
}