<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group parser
*/
class NavigationParserTest extends TestCase
{
    public function testNextBeyondLastArrayItem()
    {
        $buf = "\x42\x44\x43";  // [true]
        $parser = new BinsonParser($buf);

        $parser->goIntoArray();
        $parser->next();
            $this->assertSame(true, $parser->getValue(binson::TYPE_BOOLEAN));

        $res = $parser->next(); // next after last item
            $this->assertSame(false, $res);
        $parser->leaveArray();
            $this->assertSame(0, $parser->depth);
            $this->assertSame(true, $parser->isDone());
            $this->assertSame(true, $parser->verify());
    }




    /*public function testLinearFieldSearch()
    {
        $buf = "\x40\x14\x01\x61\x40\x14\x01\x61\x45\x41\x41";  // {'a':{'a':false}}
        $parser = new BinsonParser($buf);

            $this->assertSame(0, $parser->depth);
        $parser->goIntoObject();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_OBJECT, $parser->getType());                        
        $parser->field("a");
            $this->assertSame(1, $parser->depth);
            $this->assertSame("a", $parser->getName());
        $parser->goIntoObject();
            $parser->field("a");
            $this->assertSame(2, $parser->depth);
            //$this->assertSame(binson::TYPE_BOOLEAN, $parser->getType());
            $this->assertSame(false, $parser->getValue());

            $this->assertSame(true, $parser->verify());  
} */


}
    