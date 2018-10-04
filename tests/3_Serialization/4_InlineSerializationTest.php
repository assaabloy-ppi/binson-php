<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group serializer
*/
class InlineSerializationTest extends TestCase
{
    public function testInlineArray1() 
    {
        $buf_inline = "";
        $writer_inline = new BinsonWriter($buf_inline);
        $writer_inline->arrayBegin()
                         ->putFalse()
                      ->arrayEnd();

        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put([$writer_inline]);
        $this->assertSame("\x42\x42\x45\x43\x43", $writer->toBytes());
    }

    public function testInlineArray2() 
    {
        $buf_inline = "";
        $writer_inline = new BinsonWriter($buf_inline);
        $writer_inline->arrayBegin()
                         ->putFalse()
                      ->arrayEnd();

        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put([true, [$writer_inline], false]); 
        $this->assertSame("\x42\x44\x42\x42\x45\x43\x43\x45\x43", $writer->toBytes());
    }

    public function testInlineObject() 
    {
        $buf_inline = "";
        $writer_inline = new BinsonWriter($buf_inline);
        $writer_inline->objectBegin()   //  "\x40\x14\x01\x61\x45\x41"
                         ->putName("a")
                         ->putFalse()
                      ->objectEnd();

        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(['c' => $writer_inline]); 
        $this->assertSame("\x40\x14\x01\x63\x40\x14\x01\x61\x45\x41\x41", $writer->toBytes());
    }

    public function testInlineObjectObtainedRaw() 
    {
        $writer_inline = new BinsonWriter();
        $writer_inline->putRaw("\x40\x14\x01\x61\x45\x41");

        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->put(['c' => $writer_inline]); 
        $this->assertSame("\x40\x14\x01\x63\x40\x14\x01\x61\x45\x41\x41", $writer->toBytes());
    }
   
}

