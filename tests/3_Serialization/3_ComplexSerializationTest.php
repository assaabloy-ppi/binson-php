<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group serializer
*/
class ComplexSerializationTest extends TestCase
{
    public function testNestedEmptyObjectInsideArray() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put([[null=>null]]);  // [{}]
        $this->assertSame("\x42\x40\x41\x43", $writer->toBytes());
    }

    public function testComplexObject() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(["a"=>[true, 123, "b", 5], "b"=>false, "c"=>7]);
        $this->assertSame(bin2hex("\x40\x14\x01\x61\x42\x44\x10\x7b\x14\x01\x62\x10\x05\x43\x14\x01\x62\x45\x14\x01\x63\x10\x07\x41"), bin2hex($writer->toBytes()));
   }    
   
}

