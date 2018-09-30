<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group serializer
*/
class ArraySerializationTest extends TestCase
{
    public function testPrimitive_PHP_TypeBool() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(true);
        $this->assertSame("\x44", $writer->toBytes());
    }

    public function testPrimitive_PHP_TypeInt() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(2147483647);
        $this->assertSame("\x12\xff\xff\xff\x7f", $writer->toBytes());
    }

    public function testPrimitive_PHP_TypeFloat() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(1.23);
        $this->assertSame("\x46\xae\x47\xe1\x7a\x14\xae\xf3\x3f", $writer->toBytes());
    }

    public function testPrimitive_PHP_TypeString() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put('a');
        $this->assertSame("\x14\x01\x61", $writer->toBytes());
    }

    public function testPrimitive_PHP_TypeStringThenInt() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put('a', 2147483647);
        $this->assertSame("\x14\x01\x61\x12\xff\xff\xff\x7f", $writer->toBytes());
    }

    public function testArrayOneItem() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put([true]);
        $this->assertSame("\x42\x44\x43", $writer->toBytes());
    }



    public function testEmptyArray() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put([]);
        $this->assertSame("\x42\x43", $writer->toBytes());
    }

    public function testTwoEmptyArrays() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put([], []);
        $this->assertSame("\x42\x43\x42\x43", $writer->toBytes());
    }


    public function testTwoNestedEmptyArrays() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put([[]]);
        $this->assertSame("\x42\x42\x43\x43", $writer->toBytes());
    }



    public function testThreeNestedEmptyArrays() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put([[[]]]);
        $this->assertSame("\x42\x42\x42\x43\x43\x43", $writer->toBytes());
    }

    public function testPlainAssortedArray() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put([true,123,"b",[],5]);
        $this->assertSame("\x42\x44\x10\x7b\x14\x01\x62\x42\x43\x10\x05\x43", $writer->toBytes());
    }

    public function testItemAfterNestedArrays() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put([[["xc"]], "err"]);
        $this->assertSame("\x42\x42\x42\x14\x02\x78\x63\x43\x43\x14\x03\x65\x72\x72\x43", $writer->toBytes());
    }

}

?>