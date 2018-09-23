<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group parser
*/
class BooleanParserTest extends TestCase
{

    public function testBooleanTrue()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()->putBoolean(true)->arrayEnd();

        $parser = new BinsonParser($buf);
        $parser->enterArray()->next();
        $this->assertSame(binson::TYPE_BOOLEAN, $parser->getType());
        $this->assertSame(true, $parser->getValue(binson::TYPE_BOOLEAN));
    }

    public function testBooleanFalse()
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()->putFalse()->arrayEnd();

        $parser = new BinsonParser($buf);
        $parser->enterArray()->next();
        $this->assertSame(binson::TYPE_BOOLEAN, $parser->getType());
        $this->assertSame(false, $parser->getValue(binson::TYPE_BOOLEAN));
    }

}

?>