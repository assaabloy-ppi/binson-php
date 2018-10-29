<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group tostring
*/
class DoubleToStringTest extends TestCase
{
    private function processDouble(string $str, float $arg)
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()->putDouble($arg)->arrayEnd();

        $parser = new BinsonParser($buf);
        $this->assertSame($str, $parser->toString());
    }
    
    public function testDoublePlusZero()
    { $this->processDouble('[0.0]', +0.0); }

    public function testDoubleMinusZero()
    { $this->processDouble('[-0.0]', -0.0); }

    public function testDouble()
    { $this->processDouble('[3.141593]', 3.141593); }        

    public function testDoublePositiveExponent()
    { $this->processDouble('[31415000000.0]', +3.1415e+10); }

    public function testDoublePositiveHugeExponent()
    { $this->processDouble('[3.1415E+99]', +3.1415e+99); }

    public function testDoubleNegativeExponent()
    { $this->processDouble('[-3.1415E-10]', -3.1415e-10); }

    public function testDouble_NAN_()
    { $this->processDouble('[NAN]', NAN); }

    public function testDoublePlusInfinity()
    { $this->processDouble('[INF]', +INF); }

    public function testDoubleMinusInfinity()
    { $this->processDouble('[-INF]', -INF); }
}
