<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group writer
*/
class Int32WriterTest extends TestCase
{
    public function testIntegerINT32_MAX()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putInteger(binson::INT32_MAX)
               ->arrayEnd();
               
        $this->assertSame("\x42\x12\xff\xff\xff\x7f\x43", $writer->toBytes());
    }    

    public function testIntegerMoreThanINT32_MAX()
    {
        // expect integer overflow on 32bit PHP builds
        if (PHP_INT_SIZE === 4)
            $this->expectException(TypeError::class);

        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putInteger(binson::INT32_MAX+1)
               ->arrayEnd();
               
        $this->assertSame("\x42\x13\x00\x00\x00\x80\x00\x00\x00\x00\x43", $writer->toBytes());
    }      
    
    public function testIntegerINT32_MIN()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putInteger(binson::INT32_MIN)
               ->arrayEnd();
               
        $this->assertSame("\x42\x12\x00\x00\x00\x80\x43", $writer->toBytes());
    } 

    public function testIntegerLessThanINT32_MIN()
    {
        // expect integer overflow on 32bit PHP builds
        if (PHP_INT_SIZE === 4)
            $this->expectException(TypeError::class);

        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putInteger(binson::INT32_MIN-1)
               ->arrayEnd();
               
        $this->assertSame("\x42\x13\xff\xff\xff\x7f\xff\xff\xff\xff\x43", $writer->toBytes());
    }     

}

?>