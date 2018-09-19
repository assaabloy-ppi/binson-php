<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group parser
*/
class Int64ParserTest extends TestCase
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
    
    public function testIntegerINT64_MAX()
    { $this->testInt(binson::INT64_MAX); }
    
    public function testIntegerINT64_MIN()
    { $this->testInt(binson::INT64_MIN); }

}

?>