<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group deserializer
*/
class ComplexDeserializationTest extends TestCase
{

    public function testComplexObject1() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(["a"=>[true, 123, "b", 5], "b"=>false, "c"=>7]);
        $this->assertSame(bin2hex("\x40\x14\x01\x61\x42\x44\x10\x7b\x14\x01\x62\x10\x05\x43\x14\x01\x62\x45\x14\x01\x63\x10\x07\x41"), bin2hex($writer->toBytes()));
   }    
   

}
