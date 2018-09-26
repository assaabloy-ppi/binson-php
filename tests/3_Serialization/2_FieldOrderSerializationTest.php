<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group serializer
*/
class FieldOrderSerializationTest extends TestCase
{
    public function testSimpleFieldReorder() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(['x' => true, 'a' => true, 'zzz' => true]);
        $this->assertSame("\x40\x14\x01\x61\x44\x14\x01\x78\x44\x14\x03\x7a\x7a\x7a\x44\x41", $writer->toBytes());
    }

    public function testSimpleArrayInnerFieldReorder() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put([['x' => true, 'a' => true, 'zzz' => true]]);
        $this->assertSame("\x42\x40\x14\x01\x61\x44\x14\x01\x78\x44\x14\x03\x7a\x7a\x7a\x44\x41\x43", $writer->toBytes());
    }

    
    public function testPreserveArrayOfStringsOrder() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(['gg', 'x', 'a']);
        $this->assertSame("\x42\x14\x02\x67\x67\x14\x01\x78\x14\x01\x61\x43", $writer->toBytes());
    }     

    public function testComplexFieldReorder() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(['x' => ['gg', 'x', 'a'], 'a' => true, 'zzz' => ['b'=>false, 'a'=>true]]);
        $this->assertSame(bin2hex("\x40\x14\x01\x61\x44\x14\x01\x78"
                            ."\x42\x14\x02\x67\x67\x14\x01\x78\x14\x01\x61\x43"
                            ."\x14\x03\x7a\x7a\x7a"
                            ."\x40\x14\x01\x61\x44\x14\x01\x62\x45\x41\x41"), 
                         bin2hex($writer->toBytes()));
    }

}

?>
