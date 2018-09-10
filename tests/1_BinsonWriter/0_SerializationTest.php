<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

class SerializationTest extends TestCase
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

    public function testObjectOneItem() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(['a' => true]);
        $this->assertSame("\x40\x14\x01\x61\x44\x41", $writer->toBytes());
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

    public function testTwoNestedEmptyArrays() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put([[]]);
        $this->assertSame("\x42\x42\x43\x43", $writer->toBytes());
    }

    public function testNestedEmptyObject() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(['a' => [null => null]]);
        $this->assertSame("\x40\x14\x01\x61\x40\x41\x41", $writer->toBytes());
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

    public function testComplexObject() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(["a"=>[true, 123, "b", 5], "b"=>false, "c"=>7]);
        $this->assertSame(bin2hex("\x40\x14\x01\x61\x42\x44\x10\x7b\x14\x01\x62\x10\x05\x43\x14\x01\x62\x45\x14\x01\x63\x10\x07\x41"), bin2hex($writer->toBytes()));

        //-'401401614244107b140162100543 14016245140163 100741'
       //+'401401614244107b140162100543 45 100741'

    }

}

?>