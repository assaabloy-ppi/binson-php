<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group parser
*/
class Int8ParserTest extends TestCase
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

    public function testZero()
    { $this->testInt(0); }
    
    public function testMinus1() 
    { $this->testInt(-1); }
    
    public function testIntegerINT8_MAX()
    { $this->testInt(binson::INT8_MAX); }

    public function testIntegerMoreThanINT8_MAX()
    { $this->testInt(binson::INT8_MAX+1); }
    
    public function testIntegerINT8_MIN()
    { $this->testInt(binson::INT8_MIN); }

    public function testIntegerLessThanINT8_MIN()
    { $this->testInt(binson::INT8_MIN-1); }

}

?>