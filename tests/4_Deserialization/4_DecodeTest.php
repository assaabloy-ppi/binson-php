<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group deserializer
*/
class DecodeTest extends TestCase
{
    public function testSimpleArraySuccessfull() 
    {        
        $arr = binson_decode("\x42\x45\x43");
        $this->assertSame([false], $arr);
    }    

    public function testSimpleObjectSuccessfull() 
    {        
        $arr = binson_decode("\x40\x14\x01\x61\x44\x41");
        $this->assertSame(['a' => true], $arr);
    }    

    public function testFailureEmpty() 
    {        
        $arr = binson_decode("");
        $this->assertSame(null, $arr);
    }    

    public function testFailureTruncated() 
    {        
        $arr = binson_decode("\x42");
        $this->assertSame(null, $arr);
    }    

    public function testFailureUnknownByte() 
    {        
        $arr = binson_decode("\x42\x11");
        $this->assertSame(null, $arr);
    }    
    
    public function testFailureStringLengthBeyoundBuffer() 
    {        
        $arr = binson_decode("\x42\x14\xdd\x61");
        $this->assertSame(null, $arr);
    }    

}

