<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group parser
*/
class StringParserTest extends TestCase
{
    private function testString(string $arg)
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()->putString($arg)->arrayEnd();

        $parser = new BinsonParser($buf);
        $parser->goIntoArray()->next();
        $this->assertSame(binson::TYPE_STRING, $parser->getType());
        $this->assertSame($arg, $parser->getValue(binson::TYPE_STRING));       
    }

    public function testStringEmpty()
    { $this->testString(""); }

    public function testStringBasic()
    { $this->testString("abcd"); }

    public function testString_UTF8_()
    { $this->testString("größer"); }

    public function testStringLong()
    { $this->testString(str_repeat("x", 1024*100)); }

}

?>