<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group parser
*/
class InvalidDataTest extends TestCase
{
    public function testDataBeyondTopObject()
    {        
        $raw = "\x40\x41\x00";
        $this->assertSame(false, binson_verify($raw));
    }       

    public function testMoreTopObjects()
    {        
        $raw = "\x40\x41\x40\x41";
        $this->assertSame(false, binson_verify($raw));
    }           

    public function testNoKeyBooleanItemInObjectAfterValue()
    {        
        $raw = "\x40\x14\x01\x41\x45\x44\x41"; // {'A'=>false, true}
        $this->assertSame(false, binson_verify($raw));
    }       

    public function testNoKeyBooleanItemInObjectAfterArray()
    {        
        $raw = "\x40\x14\x01\x41\x42\x43\x44\x41"; // {'A'=>[], true}
        $this->assertSame(false, binson_verify($raw));
    }       

    public function testNoKeyArrayItemInObjectAfterValue()
    {        
        $raw = "\x40\x14\x01\x41\x45\x42\x43\x41"; // {'A'=>false, []}
        $this->assertSame(false, binson_verify($raw));
    }       

    public function testNoKeyArrayItemInObjectAfterArray()
    {        
        $raw = "\x40\x14\x01\x41\x42\x43\x42\x43\x41"; // {'A'=>[], []}
        $this->assertSame(false, binson_verify($raw));
    }       

    public function testNoKeyObjectItemInObjectAfterValue()
    {        
        $raw = "\x40\x14\x01\x41\x45\x40\x41\x41"; // {'A'=>false, {}}
        $this->assertSame(false, binson_verify($raw));
    }       
    
    public function testNoKeyObjectItemInObjectAfterArray()
    {        
        $raw = "\x40\x14\x01\x41\x42\x43\x40\x41\x41"; // {'A'=>[], {}}
        $this->assertSame(false, binson_verify($raw));
    }          

    public function testKeyWrongOrderWithEmptyKey()
    {        
        $raw = "\x40\x14\x01\x41\x14\x00\x14\x00\x42\x43\x41"; // {'A'=>'', ''=>[]}
        $this->assertSame(false, binson_verify($raw));
    }          

    public function testKeyWrongOrderWithNumericKey()
    {        
        $raw = "\x40\x14\x01\x41\x42\x43\x14\x01\x37\x42\x43\x41" ; // {'A'=>[], '7'=>[]}
        $this->assertSame(false, binson_verify($raw));
    }          

}

