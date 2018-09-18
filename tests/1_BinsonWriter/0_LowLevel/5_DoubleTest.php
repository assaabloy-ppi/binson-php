<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
*/
class DoubleTest extends TestCase
{
    public function testDoublePlusZero()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putDouble(+0.0)
               ->arrayEnd();
               
        $this->assertSame("\x42\x46\x00\x00\x00\x00\x00\x00\x00\x00\x43", $writer->toBytes());
    }
    public function testDoubleMinusZero()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putDouble(-0.0)
               ->arrayEnd();
               
        $this->assertSame("\x42\x46\x00\x00\x00\x00\x00\x00\x00\x80\x43", $writer->toBytes());
    }    

    public function testDoublePositiveExponent()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putDouble(+3.1415e+10)
               ->arrayEnd();
               
        $this->assertSame("\x42\x46\x00\x00\x00\x6f\xeb\x41\x1d\x42\x43", $writer->toBytes());
    }

    public function testDoubleNegativeExponent()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putDouble(-3.1415e-10)
               ->arrayEnd();
               
        $this->assertSame("\x42\x46\xfc\x17\xac\xd2\x95\x96\xf5\xbd\x43", $writer->toBytes());
    }
    
    public function testDouble_NAN_()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putDouble(NAN)
               ->arrayEnd();
               
        $this->assertSame("\x42\x46\x00\x00\x00\x00\x00\x00\xf8\x7f\x43", $writer->toBytes());
    }

    public function testDouble_INFINITY_()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putDouble(+INF)
               ->arrayEnd();
               
        $this->assertSame("\x42\x46\x00\x00\x00\x00\x00\x00\xf0\x7f\x43", $writer->toBytes());
    }

    public function testDoubleNegative_INFINITY_()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putDouble(-INF)
               ->arrayEnd();
               
        $this->assertSame("\x42\x46\x00\x00\x00\x00\x00\x00\xf0\xff\x43", $writer->toBytes());
    }

}

?>