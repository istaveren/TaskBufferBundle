<?php
namespace Bundle\TaskBufferBundle\Tests\Model;

class ObjectX
{
    static public function someMethod(){
        throw new \Exception();
    }
}