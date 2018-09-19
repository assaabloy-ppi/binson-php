<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group writer
*/
class Int64WriterTest extends TestCase
{
    public function testIntegerINT64_MAX()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putInteger(binson::INT64_MAX)
               ->arrayEnd();
               
        $this->assertSame("\x42\x13\xff\xff\xff\xff\xff\xff\xff\x7f\x43", $writer->toBytes());
    }    

    public function testIntegerINT64_MIN()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putInteger(binson::INT64_MIN)
               ->arrayEnd();
               
        $this->assertSame("\x42\x13\x00\x00\x00\x00\x00\x00\x00\x80\x43", $writer->toBytes());
    }    
}

?>