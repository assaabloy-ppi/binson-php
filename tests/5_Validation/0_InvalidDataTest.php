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

    public function testKeyWrongOrderCase()
    {        
        $raw = "\x40\x14\x01\x61\x45\x14\x01\x41\x45\x41" ; // {'a'=>false, 'A'=>false}
        $this->assertSame(false, binson_verify($raw));
    }          

    public function testKeyWrongOrderWithNumericKey()
    {        
        $raw = "\x40\x14\x01\x41\x42\x43\x14\x01\x37\x42\x43\x41" ; // {'A'=>[], '7'=>[]}
        $this->assertSame(false, binson_verify($raw));
    }          

    public function testBrokenInternalArray()
    {        
        $raw = "\x42\x43\x43"; // []]
        $this->assertSame(false, binson_verify($raw));
    }

    public function testOnlyFieldName()
    {        
        $raw = "\x40\x14\x01\x00\x41"; // {''=>}
        $this->assertSame(false, binson_verify($raw));
    }

    public function testNoData()
    {        
        $raw = "";
        $this->assertSame(false, binson_verify($raw));
    }
    
    public function testObjectNested3Empty()
    { 
        $buf = "\x40\x14\x01\x61\x40\x40\x41\x41\x41";  // {'a':{{}}}
        $parser = new BinsonParser($buf);
        $this->assertSame(false, $parser->verify());
    }    

    public function test_AFL_Reported1()
    { 
        $buf = "\x40\x19\xd3\x03\x41";
        $parser = new BinsonParser($buf);
        $this->assertSame(false, $parser->verify());        
    }  

    public function testBrokenStrLen()
    { 
        $buf = "\x40\x14\xFF\x41\x14\x01\x43\x41";
        $parser = new BinsonParser($buf);
        $this->assertSame(false, $parser->verify());        
    }  

}

