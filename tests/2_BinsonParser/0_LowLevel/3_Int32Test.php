<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group parser
*/
class Int32ParserTest extends TestCase
{
    private function processInt(int $arg)
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()->putInteger($arg)->arrayEnd();

        $parser = new BinsonParser($buf);
        $parser->enterArray()->next();
        $this->assertSame(binson::TYPE_INTEGER, $parser->getType());
        $this->assertSame($arg, $parser->getValue(binson::TYPE_INTEGER));       
    }

    public function testIntegerINT32_MAX()
    { $this->processInt(binson::INT32_MAX); }

    public function testIntegerINT32_MIN()
    { $this->processInt(binson::INT32_MIN); }
    
    public function testIntegerMoreThanINT32_MAX()
    { 
        $buf = "\x42\x13\x00\x00\x00\x80\x00\x00\x00\x00\x43";
        $parser = new BinsonParser($buf);

        $parser->enterArray()->next();
        $this->assertSame(binson::TYPE_INTEGER, $parser->getType());
        $this->assertSame(binson::INT32_MAX+1, $parser->getValue(binson::TYPE_INTEGER));
    }

    public function testIntegerLessThanINT32_MIN()
    { 
        $buf = "\x42\x13\xff\xff\xff\x7f\xff\xff\xff\xff\x43";
        $parser = new BinsonParser($buf);

        $parser->enterArray()->next();
        $this->assertSame(binson::TYPE_INTEGER, $parser->getType());
        $this->assertSame(binson::INT32_MIN-1, $parser->getValue(binson::TYPE_INTEGER));
    }
}
