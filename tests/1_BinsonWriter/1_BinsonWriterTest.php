<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group writer
*/
class BinsonWriterTest extends TestCase
{
    public function testEmptyBinsonObject() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->objectBegin()
               ->objectEnd();

        $this->assertSame(2, $writer->length());       
        $this->assertSame("\x40\x41", $writer->toBytes());
    }

    public function testEmptyBinsonArray() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
               ->arrayEnd();

        $this->assertSame(2, $writer->length());
        $this->assertSame("\x42\x43", $writer->toBytes());
    }


    public function testObjectWith_UTF8_Name() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        // {"爅웡":123}
        $writer->objectBegin()
                    ->putName("爅웡")
                    ->putInteger(123)
               ->objectEnd();

        $this->assertSame("\x40\x14\x06\xe7\x88\x85\xec\x9b\xa1\x10\x7b\x41", $writer->toBytes());
    }    

    public function testNestedObjectsWithEmptyKeyNames() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        // {"":{"":{"":{}}}}
        $writer->objectBegin()
                    ->putName("")
                    ->objectBegin()
                        ->putName("")
                        ->objectBegin()
                            ->putName("")
                            ->objectBegin()
                            ->objectEnd()             
                        ->objectEnd()         
                    ->objectEnd()     
               ->objectEnd();

        $this->assertSame("\x40\x14\x00\x40\x14\x00\x40\x14\x00\x40\x41\x41\x41\x41", $writer->toBytes());
    } 
    
    public function testNestedArraysAsObjectValue() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        // {"b":[[[]]]}
        $writer->objectBegin()
                    ->putName("b")
                    ->arrayBegin()
                        ->arrayBegin()                            
                            ->arrayBegin()
                            ->arrayEnd()
                        ->arrayEnd()
                    ->arrayEnd()
               ->objectEnd();

        $this->assertSame("\x40\x14\x01\x62\x42\x42\x42\x43\x43\x43\x41", $writer->toBytes());
    }

    public function testNestedStructures_1_AsObjectValue() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        // {"b":[[],{},[]]}
        $writer->objectBegin()
                    ->putName("b")
                    ->arrayBegin()
                        ->arrayBegin()
                        ->arrayEnd()
                        ->objectBegin()                            
                        ->objectEnd()
                        ->arrayBegin()
                        ->arrayEnd()
                    ->arrayEnd()
               ->objectEnd();

        $this->assertSame("\x40\x14\x01\x62\x42\x42\x43\x40\x41\x42\x43\x43\x41", $writer->toBytes());
    }
    
    public function testNestedStructures_2_AsObjectValue() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        // {"b":[[{}],[{}]]} 
        $writer->objectBegin()
                    ->putName("b")
                    ->arrayBegin()
                        ->arrayBegin()
                            ->objectBegin()                            
                            ->objectEnd()
                        ->arrayEnd()                        
                        ->arrayBegin()
                            ->objectBegin()                            
                            ->objectEnd()
                        ->arrayEnd()
                    ->arrayEnd()
               ->objectEnd();

        $this->assertSame("\x40\x14\x01\x62\x42\x42\x40\x41\x43\x42\x40\x41\x43\x43\x41", $writer->toBytes());
    }

    public function testComplexObjectStructure1() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        // {"abc":{"cba":{}}, "b":{"abc":{}}}
        $writer->objectBegin()
                    ->putName("abc")
                    ->objectBegin()
                        ->putName("cba")
                            ->objectBegin()
                            ->objectEnd()        
                    ->objectEnd()
                    ->putName("b")
                    ->objectBegin()
                        ->putName("abc")
                            ->objectBegin()
                            ->objectEnd()        
                    ->objectEnd()
               ->objectEnd();

        $this->assertSame("\x40\x14\x03\x61\x62\x63\x40\x14\x03\x63\x62\x61\x40\x41\x41\x14\x01\x62\x40\x14\x03\x61\x62\x63\x40\x41\x41\x41", $writer->toBytes());
    }    

    public function testComplexObjectStructure2() 
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        // {"b":[true,13,"cba",{"abc":false, "b":"0x008100ff00", "cba":"abc"},9223372036854775807]}
        $writer->objectBegin()
                    ->putName("b")
                    ->arrayBegin()
                        ->putTrue()
                        ->putInteger(13)
                        ->putString("cba")
                        ->objectBegin()
                            ->putName("abc")
                            ->putBoolean(false)
                            ->putName("b")
                            ->putBytes("\x00\x81\x00\xff\x00")
                            ->putName("cba")
                            ->putString("abc")
                        ->objectEnd()         
                        ->putInteger(9223372036854775807)
                    ->arrayEnd()
               ->objectEnd();

        $this->assertSame("\x40\x14\x01\x62\x42\x44\x10\x0d\x14\x03\x63\x62\x61\x40\x14\x03"
                          ."\x61\x62\x63\x45\x14\x01\x62\x18\x05\x00\x81\x00\xff\x00\x14\x03"
                          ."\x63\x62\x61\x14\x03\x61\x62\x63\x41\x13\xff\xff\xff\xff\xff\xff"
                          ."\xff\x7f\x43\x41", $writer->toBytes());
    }       


    public function testRawArray() 
    {
        $buf = '';

        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()
                    ->putRaw("\x42\x45\x43")
            ->arrayEnd();

        $this->assertSame("\x42\x42\x45\x43\x43", $writer->toBytes());
    }

    public function testInlineArray() 
    {
        $buf = '';
        $buf_inline = '';

        $writer = new BinsonWriter($buf);
        $writer_inline = new BinsonWriter($buf_inline);

        $writer_inline->arrayBegin()
                         ->putFalse()
                      ->arrayEnd();

        $writer->arrayBegin()
                    ->putInline($writer_inline)
               ->arrayEnd();

        $this->assertSame("\x42\x42\x45\x43\x43", $writer->toBytes());
    }

 /*  public function testVerifySuccess() 
    {
        $this->markTestSkipped('implement in parser first');

        $buf = "\x40\x41";
        $writer = new BinsonWriter($buf);
        $this->assertSame(true, $writer->verify());
    }

    public function testVerifyFailure() 
    {
        $this->markTestSkipped('implement in parser first');

        $buf = "\x40\x11\x41";
        $writer = new BinsonWriter($buf);
        $this->assertSame(false, $writer->verify());
    }
*/    

/*    public function testExceptions() 
    {
        $buf = str_repeat('_', 1);

        $writer = new BinsonWriter($buf);

        $this->expectException(Exception::class);
        $writer->putString("abcde");
    }
*/    


}

?>