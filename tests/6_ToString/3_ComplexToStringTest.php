<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group tostring
*/
class ComplexToStringTest extends TestCase
{
    private function process(string $str, string $arg)
    {
        $parser = new BinsonParser($arg);
        $this->assertSame($str, $parser->toString());
    } 

    public function testMinimalComplex()
    { 
        $this->process('{"a":[true,123,"b",5],"b":false,"c":7}', 
                "\x40\x14\x01\x61\x42\x44\x10\x7b\x14\x01\x62\x10\x05".
                "\x43\x14\x01\x62\x45\x14\x01\x63\x10\x07\x41");
    }  

    public function testComplex1()
    { 
        $this->process('{"A":"B","B":{"A":"B"},"C":["A","A",{"A":"B","B":["A","A",{"A":"B"},[[[[{"A":"B"}]]]]]},"A"],"D":3.141593,"E":false,"F":127,"G":"0x0202"}',
                    "\x40\x14\x01\x41\x14\x01\x42\x14\x01\x42\x40\x14\x01\x41\x14\x01\x42".
                    "\x41\x14\x01\x43\x42\x14\x01\x41\x14\x01\x41\x40\x14\x01\x41\x14\x01".
                    "\x42\x14\x01\x42\x42\x14\x01\x41\x14\x01\x41\x40\x14\x01\x41\x14\x01".
                    "\x42\x41\x42\x42\x42\x42\x40\x14\x01\x41\x14\x01\x42\x41\x43\x43\x43".
                    "\x43\x43\x41\x14\x01\x41\x43\x14\x01\x44\x46\x18\x2d\x44\x54\xfb\x21".
                    "\x09\x40\x14\x01\x45\x45\x14\x01\x46\x10\x7f\x14\x01\x47\x18\x02\x02\x02\x41");
    }  
}
