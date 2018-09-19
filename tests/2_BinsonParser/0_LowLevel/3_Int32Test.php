<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group parser
*/
class Int32ParserTest extends TestCase
{
    private function testInt(int $arg)
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()->putInteger($arg)->arrayEnd();

        $parser = new BinsonParser($buf);
        $parser->goIntoArray()->next();
        $this->assertSame(binson::TYPE_INTEGER, $parser->getType());
        $this->assertSame($arg, $parser->getValue(binson::TYPE_INTEGER));       
    }

    
    public function testIntegerINT32_MAX()
    { $this->testInt(binson::INT32_MAX); }

    public function testIntegerMoreThanINT32_MAX()
    { $this->testInt(binson::INT32_MAX+1); }
    
    public function testIntegerINT32_MIN()
    { $this->testInt(binson::INT32_MIN); }

    public function testIntegerLessThanINT32_MIN()
    { $this->testInt(binson::INT32_MIN-1); }

}

?>