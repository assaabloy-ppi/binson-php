<?php
use PHPUnit\Framework\TestCase;

//require_once(__DIR__ . '/../out/binson.php');
require_once(SRC_DIR . 'binson.php');


class ExtBinsonTest extends TestCase
{
    public function testBinsonExtensionIsAvailable()
    {   
		$this->assertNotEmpty(binson::BINSON_API_VERSION);

        $buf = "_______";
        $writer = new BinsonWriter($buf);
        $parser = new BinsonParser($buf);

        $this->assertInstanceOf(BinsonWriter::class, $writer);
        $this->assertInstanceOf(BinsonParser::class, $parser);
    }
    

}

?>