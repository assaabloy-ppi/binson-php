<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group writer
*/
class StringWriterTest extends TestCase
{
    public function testStringEmpty()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putString("")
               ->arrayEnd();
               
        $this->assertSame("\x42\x14\x00\x43", $writer->toBytes());
    }

    public function testStringBasic()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putString("abc")
               ->arrayEnd();
               
        $this->assertSame("\x42\x14\x03\x61\x62\x63\x43", $writer->toBytes());
    }

    public function testString_UTF8_()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putString("größer")
               ->arrayEnd();
               
        $this->assertSame("\x42\x14\x08\x67\x72\xc3\xb6\xc3\x9f\x65\x72\x43", $writer->toBytes());
    }
}

?>