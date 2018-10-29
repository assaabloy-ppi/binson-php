<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group tostring
*/
class StringToStringTest extends TestCase
{
    private function processString(string $str, string $arg)
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()->putString($arg)->arrayEnd();

        $parser = new BinsonParser($buf);
        $this->assertSame($str, $parser->toString());
    }

    public function testStringEmpty()
    { $this->processString('[""]', ""); }

    public function testStringBasic()
    { $this->processString('["abcd"]', "abcd"); }

    public function testString_UTF8_()
    { $this->processString('["größer"]', "größer"); }

    public function testStringLong()    
    {   
        $long_str = str_repeat("x", 1024*100);
        $this->processString('["'.$long_str.'"]', $long_str); 
    }
}
