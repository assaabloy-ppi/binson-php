<?php
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../out/binson.php');

class BinsonExtTest extends TestCase
{
    public function testBinsonExtensionIsAvailable()
    {   
        $buf = "_______";
        $writer = new BinsonWriter($buf);
        //$parser = new binson_parser();

        $this->assertInstanceOf(BinsonWriter::class, $writer);
        //$this->assertInstanceOf(binson_parser::class, $parser);
    }

    public function testEmptyBinsonObject() 
    {
        $buf = "____";
        $writer = new BinsonWriter($buf);

        $writer->objectBegin()
               ->objectEnd();

        $this->assertSame($buf, "\x40\x41__");
        $this->assertSame($writer->length(), 2);
        $this->assertSame($writer->toBytes(), "\x40\x41");
    }

    public function testEmptyBinsonArray() 
    {
        $buf = "____";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
               ->arrayEnd();

        $this->assertSame($buf, "\x42\x43__");
        $this->assertSame($writer->length(), 2);
        $this->assertSame($writer->toBytes(), "\x42\x43");
    }

    public function testBooleans() 
    {
        $buf = "____";
        $writer = new BinsonWriter($buf);

        $writer->putBoolean(true)
               ->putBoolean(false)
               ->putTrue()
               ->putFalse();

        $this->assertSame($writer->length(), 4);
        $this->assertSame($writer->toBytes(), "\x44\x45\x44\x45");
    }

    public function testIntegers() 
    {
        $buf = str_repeat('_', 128);
        $writer = new BinsonWriter($buf);

        $writer->putInteger(2147483647);

        $this->assertSame($writer->length(), 5);
        $this->assertSame($writer->toBytes(), "\x12\xff\xff\xff\x7f");
    }

    public function testDoubles() 
    {
        $buf = str_repeat('_', 128);
        $writer = new BinsonWriter($buf);

        $writer->putDouble(1.23);

        $this->assertSame($writer->length(), 9);
        $this->assertSame($writer->toBytes(), "\x46\xae\x47\xe1\x7a\x14\xae\xf3\x3f");
    }

    public function testName() 
    {
        $buf = str_repeat('_', 128);
        $writer = new BinsonWriter($buf);

        $writer->putName("a");

        $this->assertSame($writer->length(), 3);
        $this->assertSame($writer->toBytes(), "\x14\x01\x61");
    }

    public function testStrings() 
    {
        $buf = str_repeat('_', 128);
        $writer = new BinsonWriter($buf);

        $writer->putString("a");

        $this->assertSame($writer->length(), 3);
        $this->assertSame($writer->toBytes(), "\x14\x01\x61");
    }

    public function testBytes() 
    {
        $buf = str_repeat('_', 128);
        $writer = new BinsonWriter($buf);

        $writer->putBytes("a");

        $this->assertSame($writer->length(), 3);
        $this->assertSame($writer->toBytes(), "\x18\x01\x61");
    }

    public function testInline() 
    {
        $buf = str_repeat('_', 128);
        $buf_inline = str_repeat('_', 128);

        $writer = new BinsonWriter($buf);
        $writer_inline = new BinsonWriter($buf_inline);

        $writer_inline->putBytes("a");

        $this->assertSame($writer_inline->length(), 3);
        $this->assertSame($writer_inline->toBytes(), "\x18\x01\x61");

        $writer->arrayBegin()
               ->putInline($writer_inline)
               ->arrayEnd();

        $this->assertSame($writer->length(), 5);
        $this->assertSame($writer->toBytes(), "\x42\x18\x01\x61\x43");
    }

    public function testExceptions() 
    {
        $buf = str_repeat('_', 1);

        $writer = new BinsonWriter($buf);

        $this->expectException(Exception::class);
        $writer->putString("abcde");
    }

    /*public function testSerialization() 
    {
        $buf = str_repeat('_', 128);

        $writer = new BinsonWriter($buf);
        $var = 3;

        $writer->serialize($var);
    }*/

}

?>