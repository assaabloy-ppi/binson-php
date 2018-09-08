<?php
use PHPUnit\Framework\TestCase;

//require_once(__DIR__ . '/../../out/binson.php');
require_once(SRC_DIR . 'binson.php');

class ExtBinsonWriterTest extends TestCase
{
    public function testEmptyBinsonObject() 
    {
        $buf = "____";
        $writer = new BinsonWriter($buf);

        $writer->objectBegin()
               ->objectEnd();

        //$this->assertSame("_\x40\x41", $buf);
        $this->assertSame(2, $writer->length());
        $this->assertSame("\x40\x41", $writer->toBytes());
    }

    public function testEmptyBinsonArray() 
    {
        $buf = "____";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
               ->arrayEnd();

        //$this->assertSame("_\x42\x43", $buf);
        $this->assertSame(2, $writer->length());
        $this->assertSame("\x42\x43", $writer->toBytes());
    }

    public function testBooleans() 
    {
        $buf = "____";
        $writer = new BinsonWriter($buf);

        $writer->putBoolean(true)
               ->putBoolean(false)
               ->putTrue()
               ->putFalse();

        $this->assertSame(4, $writer->length());
        $this->assertSame("\x44\x45\x44\x45", $writer->toBytes());
    }

    public function testIntegers() 
    {
        $buf = str_repeat('_', 128);
        $writer = new BinsonWriter($buf);

        $writer->putInteger(2147483647);

        $this->assertSame(5, $writer->length());
        $this->assertSame("\x12\xff\xff\xff\x7f", $writer->toBytes());
    }

    public function testDoubles() 
    {
        $buf = str_repeat('_', 128);
        $writer = new BinsonWriter($buf);

        $writer->putDouble(1.23);

        $this->assertSame(9, $writer->length());
        $this->assertSame("\x46\xae\x47\xe1\x7a\x14\xae\xf3\x3f", $writer->toBytes());
    }

    public function testName() 
    {
        $buf = str_repeat('_', 128);
        $writer = new BinsonWriter($buf);

        $writer->putName("a");

        $this->assertSame(3, $writer->length());
        $this->assertSame("\x14\x01\x61", $writer->toBytes());
    }

    public function testStrings() 
    {
        $buf = str_repeat('_', 128);
        $writer = new BinsonWriter($buf);

        $writer->putString("a");

        $this->assertSame(3, $writer->length());
        $this->assertSame("\x14\x01\x61", $writer->toBytes());
    }

    public function testBytes() 
    {
        $buf = str_repeat('_', 128);
        $writer = new BinsonWriter($buf);

        $writer->putBytes("a");

        $this->assertSame(3, $writer->length());
        $this->assertSame("\x18\x01\x61", $writer->toBytes());
    }

    public function testInline() 
    {
        $buf = str_repeat('_', 128);
        $buf_inline = str_repeat('_', 128);

        $writer = new BinsonWriter($buf);
        $writer_inline = new BinsonWriter($buf_inline);

        $writer_inline->putBytes("a");

        $this->assertSame(3, $writer_inline->length());
        $this->assertSame("\x18\x01\x61", $writer_inline->toBytes());

        $writer->arrayBegin()
               ->putInline($writer_inline)
               ->arrayEnd();

        $this->assertSame(5, $writer->length());
        $this->assertSame("\x42\x18\x01\x61\x43", $writer->toBytes());
    }

    public function testExceptions() 
    {
        $buf = str_repeat('_', 1);

        $writer = new BinsonWriter($buf);

        $this->expectException(Exception::class);
        $writer->putString("abcde");
    }


}

?>