<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
*/
class Int8Test extends TestCase
{
    public function testZero()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()->putInteger(0)->arrayEnd();

        $this->assertSame("\x42\x10\x00\x43", $writer->toBytes());
    }

    public function testMinus1()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()->putInteger(-1)->arrayEnd();

        $this->assertSame("\x42\x10\xff\x43", $writer->toBytes());
    }

    public function testIntegerINT_MAX()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putInteger(binson::INT8_MAX)
               ->arrayEnd();

        $this->assertSame("\x42\x10\x7f\x43", $writer->toBytes());
    }    

    public function testIntegerMoreThanINT8_MAX()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putInteger(binson::INT8_MAX+1)
               ->arrayEnd();
               
        $this->assertSame("\x42\x11\x80\x00\x43", $writer->toBytes());
    }   
    
    public function testIntegerINT8_MIN()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putInteger(binson::INT8_MIN)
               ->arrayEnd();
               
        $this->assertSame("\x42\x10\x80\x43", $writer->toBytes());
    }       

    public function testIntegerLessThanINT8_MIN()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putInteger(binson::INT8_MIN-1)
               ->arrayEnd();
               
        $this->assertSame("\x42\x11\x7f\xff\x43", $writer->toBytes());
    }       

}

?>