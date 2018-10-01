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

        $writer->put(binson::EMPTY_OBJECT);
        $this->assertSame("\x40\x41", $writer->toBytes());
    }

    public function testTwoEmptyObjects() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(binson::EMPTY_OBJECT, binson::EMPTY_OBJECT);
        $this->assertSame("\x40\x41\x40\x41", $writer->toBytes());
    }

    public function testNestedEmptyObject() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(['a' => binson::EMPTY_OBJECT]);
        $this->assertSame("\x40\x14\x01\x61\x40\x41\x41", $writer->toBytes());
    }
    
    public function testObjectOneItem() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(['a' => true]);
        $this->assertSame("\x40\x14\x01\x61\x44\x41", $writer->toBytes());
    }

    public function testObjectWithNumericFieldname() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(['123.' => true]);
        $this->assertSame("\x40\x14\x03\x31\x32\x33\x44\x41", $writer->toBytes());
    }

    public function testObjectWithNumericFieldnameZero() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(['0.' => true]);
        $this->assertSame("\x40\x14\x01\x30\x44\x41", $writer->toBytes());
    }

    public function testLeavingArrayToObject() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(['a' => [true], 'b' => false]);
        $this->assertSame("\x40\x14\x01\x61\x42\x44\x43\x14\x01\x62\x45\x41", $writer->toBytes());
    }
    
}

?>
