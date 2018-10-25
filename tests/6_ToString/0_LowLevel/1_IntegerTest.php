<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group tostring
*/
class IntegerToStringTest extends TestCase
{
    private function processInt(string $str, int $arg)
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()->putInteger($arg)->arrayEnd();

        $parser = new BinsonParser($buf);
        $this->assertSame($str, $parser->toString());
    }

    public function testZero()
    { $this->processInt('[0]', 0); }

    public function testPlusZero()
    { $this->processInt('[0]', +0); }

    public function testMinusZero()
    { $this->processInt('[0]', -0); }

    public function testMinus1() 
    { $this->processInt('[-1]', -1); }
    
    public function testIntegerINT8()
    { $this->processInt('[45]', 45); }

    public function testIntegerINT64_MAX()
    { $this->processInt('[9223372036854775807]', binson::INT64_MAX); }

    public function testIntegerINT64_MIN()
    { $this->processInt('[-9223372036854775808]', binson::INT64_MIN); }
}
