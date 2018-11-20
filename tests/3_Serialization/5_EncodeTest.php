<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group serializer
*/
class EncodeTest extends TestCase
{
    public function testSimpleArraySuccessfull() 
    {        
        $arr = binson_encode([false]);
        $this->assertSame("\x42\x45\x43", $arr);
    }

    public function testSimpleObjectSuccessfull() 
    {        
        $arr = binson_encode(['a' => true]);
        $this->assertSame("\x40\x14\x01\x61\x44\x41", $arr);
    }       

    public function testFailureObjectArrayMixed()
    {        
        $str = binson_encode(['a'=>1, false]);
        $this->assertSame(null, $str);
    }    
}
