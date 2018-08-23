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

        $this->assertEquals($buf, "\x40\x41__");
        $this->assertEquals($writer->length(), 2);
        $this->assertEquals($writer->toString(), "\x40\x41");
    }

    public function testEmptyBinsonArray() 
    {
        $buf = "____";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
               ->arrayEnd();

        $this->assertEquals($buf, "\x42\x43__");
        $this->assertEquals($writer->length(), 2);
        $this->assertEquals($writer->toString(), "\x42\x43");
    }

    public function testBooleans() 
    {
        $buf = "____";
        $writer = new BinsonWriter($buf);

        $writer->boolean(true)
               ->boolean(false)
               ->trueValue()
               ->falseValue();

        $this->assertEquals($writer->toString(), "\x44\x45\x44\x45");
    }

    public function testIntegers() 
    {
        $buf = str_repeat('_', 128);
        $writer = new BinsonWriter($buf);

        $writer->integer(2147483647);

        $this->assertEquals($writer->toString(), "\x12\xff\xff\xff\x7f");
    }


}

?>