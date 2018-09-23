<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group parser
*/
class Int16ParserTest extends TestCase
{
    private function testInt(int $arg)
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()->putInteger($arg)->arrayEnd();

        $parser = new BinsonParser($buf);
        $parser->enterArray()->next();
        $this->assertSame(binson::TYPE_INTEGER, $parser->getType());
        $this->assertSame($arg, $parser->getValue(binson::TYPE_INTEGER));       
    }

    
    public function testIntegerINT16_MAX()
    { $this->testInt(binson::INT16_MAX); }

    public function testIntegerMoreThanINT16_MAX()
    { $this->testInt(binson::INT16_MAX+1); }
    
    public function testIntegerINT16_MIN()
    { $this->testInt(binson::INT16_MIN); }

    public function testIntegerLessThanINT16_MIN()
    { $this->testInt(binson::INT16_MIN-1); }

}

?>