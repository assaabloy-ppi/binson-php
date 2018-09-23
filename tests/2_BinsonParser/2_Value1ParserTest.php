<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group parser
*/
class Value1ParserTest extends TestCase
{
    public function testSimpleValueCheck()
    {   // [true, -12345, 3.415, "abcde", "\x00\x81\x00\xff\x00"]
        $buf = "\x42\x44\x11\xc7\xcf\x46\x52\xb8\x1e\x85\xeb\x51\x0b\x40\x14\x05\x61\x62\x63\x64\x65\x18\x05\x00\x81\x00\xff\x00\x43";  // {'a':{{}}}
        $parser = new BinsonParser($buf);
        
        $this->assertSame(0, $parser->depth);
        $parser->enterArray();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());            
        $parser->next();
            $this->assertSame(true, $parser->getValue(binson::TYPE_BOOLEAN));
        $parser->next();
            $this->assertSame(-12345, $parser->getValue(binson::TYPE_INTEGER));
        $parser->next();        
            $this->assertSame(3.415, $parser->getValue(binson::TYPE_DOUBLE));
        $parser->next();
            $this->assertSame("abcde", $parser->getValue(binson::TYPE_STRING));
        $parser->next();
            $this->assertSame("\x00\x81\x00\xff\x00", $parser->getValue(binson::TYPE_BYTES));
        $parser->leaveArray();
            
        $this->assertSame(true, $parser->isDone());
        $this->assertSame(true, $parser->verify());

    } 

}

//    [[false]],true,[]

 