<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group parser
*/
class BytesParserTest extends TestCase
{
    private function testBytes(string $arg)
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()->putBytes($arg)->arrayEnd();

        $parser = new BinsonParser($buf);
        $parser->enterArray()->next();
        $this->assertSame(binson::TYPE_BYTES, $parser->getType());
        $this->assertSame($arg, $parser->getValue(binson::TYPE_BYTES));
    }

    public function testBytesEmpty()
    { $this->testBytes(""); }

    public function testBytesSingleZero()
    { $this->testBytes("\x00"); }

    public function testBytesWithZeroes()
    { $this->testBytes("\x00\xff\x00\x22\x00"); }

    public function testBytesLong()
    { $this->testBytes(str_repeat("x", 1024*100)); }

}

?>