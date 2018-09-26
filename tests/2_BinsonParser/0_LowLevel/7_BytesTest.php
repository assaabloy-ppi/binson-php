<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group parser
*/
class BytesParserTest extends TestCase
{
    private function processBytes(string $arg)
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
    { $this->processBytes(""); }

    public function testBytesSingleZero()
    { $this->processBytes("\x00"); }

    public function testBytesWithZeroes()
    { $this->processBytes("\x00\xff\x00\x22\x00"); }

    public function testBytesLong()
    { $this->processBytes(str_repeat("x", 1024*100)); }

}

?>