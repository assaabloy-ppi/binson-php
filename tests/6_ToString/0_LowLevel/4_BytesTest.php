<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group tostring
*/
class BytesToStringTest extends TestCase
{
    private function processBytes(string $str, string $arg)
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()->putBytes($arg)->arrayEnd();

        $parser = new BinsonParser($buf);
        $this->assertSame($str, $parser->toString());
    }

    public function testBytesEmpty()
    { $this->processBytes('[""]', ""); }

    public function testBytesSingleZero()
    { $this->processBytes('["00"]', "\x00"); }

    public function testBytesWithZeroes()
    { $this->processBytes('["00ff002200"]', "\x00\xff\x00\x22\x00"); }

    public function testBytesLong()    
    {   
        $long_str = str_repeat("\x03", 1024*100);
        $this->processBytes('["'.bin2hex($long_str).'"]', $long_str); 
    }
}
