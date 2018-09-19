<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group writer
*/
class BooleanWriterTest extends TestCase
{
    public function testBooleanTrue()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putBoolean(true)
               ->arrayEnd();
               
        $this->assertSame("\x42\x44\x43", $writer->toBytes());
    }

    public function testBooleanFalse()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putBoolean(false)
               ->arrayEnd();
               
        $this->assertSame("\x42\x45\x43", $writer->toBytes());
    }
}

?>