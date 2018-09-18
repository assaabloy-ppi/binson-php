<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group serializer
*/
class ObjectSerializationTest extends TestCase
{
    public function testEmptyObject() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put([null => null]);
        $this->assertSame("\x40\x41", $writer->toBytes());
    }

    public function testTwoEmptyObjects() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put([null => null], [null => null]);
        $this->assertSame("\x40\x41\x40\x41", $writer->toBytes());
    }

    public function testNestedEmptyObject() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(['a' => [null => null]]);
        $this->assertSame("\x40\x14\x01\x61\x40\x41\x41", $writer->toBytes());
    }
    
    public function testObjectOneItem() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(['a' => true]);
        $this->assertSame("\x40\x14\x01\x61\x44\x41", $writer->toBytes());
    }
 
}

?>
