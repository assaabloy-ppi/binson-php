<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group writer
*/
class BytesWriterTest extends TestCase
{
    public function testBytesEmpty()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putBytes("")
               ->arrayEnd();
               
        $this->assertSame("\x42\x18\x00\x43", $writer->toBytes());
    }

    public function testBytesSingleNull()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putBytes("\x00")
               ->arrayEnd();
               
        $this->assertSame("\x42\x18\x01\x00\x43", $writer->toBytes());
    }  

    public function testBytesSingleNullWithMoreBytes()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putBytes("\x00\x01\x02\xff\x00")
               ->arrayEnd();
               
        $this->assertSame("\x42\x18\x05\x00\x01\x02\xff\x00\x43", $writer->toBytes());
    }  

}

?>