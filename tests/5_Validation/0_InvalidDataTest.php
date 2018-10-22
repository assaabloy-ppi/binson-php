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

    public function testMoreTopObjects_ddd()
    {        
        $raw = "\x40\x14\x01\x41\x45\x42\x43\x41"; // {'A'=>false, []}
        $this->assertSame(false, binson_verify($raw));
    }       

    public function testMoreTopObjects_fff()
    {        
        $raw = "\x40\x14\x01\x20\x14\x01\x62\x42\x43\x41"; // {' ' => 'b', []}
        $this->assertSame(false, binson_verify($raw));
    }       
}

