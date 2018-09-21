<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group parser
*/
class WrongSimpleParserTest extends TestCase
{
    public function testObjectNested3Empty()
    { 
        $buf = "\x40\x14\x01\x61\x40\x40\x41\x41\x41";  // {'a':{{}}}
        $parser = new BinsonParser($buf);
        $this->assertSame(false, $parser->verify());
    }    

}