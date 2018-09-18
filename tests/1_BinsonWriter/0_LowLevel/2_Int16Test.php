<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
*/
class Int16Test extends TestCase
{
    public function testIntegerINT16_MAX()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putInteger(binson::INT16_MAX)
               ->arrayEnd();
               
        $this->assertSame("\x42\x11\xff\x7f\x43", $writer->toBytes());
    }    

    public function testIntegerMoreThanINT16_MAX()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putInteger(binson::INT16_MAX+1)
               ->arrayEnd();
               
        $this->assertSame("\x42\x12\x00\x80\x00\x00\x43", $writer->toBytes());
    }   
    
    public function testIntegerINT16_MIN()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putInteger(binson::INT16_MIN)
               ->arrayEnd();
               
        $this->assertSame("\x42\x11\x00\x80\x43", $writer->toBytes());
    }       

    public function testIntegerLessThanINT16_MIN()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putInteger(binson::INT16_MIN-1)
               ->arrayEnd();
               
        $this->assertSame("\x42\x12\xff\x7f\xff\xff\x43", $writer->toBytes());
    }       

}

?>