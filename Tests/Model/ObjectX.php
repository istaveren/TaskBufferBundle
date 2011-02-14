<?php
namespace Smentek\TaskBufferBundle\Tests\Model;

class ObjectX
{
    static public function someMethod()
    {
        throw new \Exception();
    }
    
    static public function someMethodOk()
    {
    }
    
    public function someMethodOk2()
    {
    }    
}