<?php
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../out/binson.php');

class BinsonExtTest extends TestCase
{
    public function testBinsonExtensionIsAvailable()
    {
        $writer = new binson_writer();        
        $parser = new binson_parser();

        $this->assertInstanceOf(binson_writer::class, $writer);
        $this->assertInstanceOf(binson_parser::class, $parser);
    }

    public function testEmptyBinsonObject()
    {
        $writer = new binson_writer();        
        $buf =  str_repeat( '0', 1024 );

        //binson::binson_writer_init( $writer, (SWIGTYPE_p_uint8_t)$buf, 1024 );
        //binson::binson_writer_object_begin( $writer );
        //binson::binson_writer_object_end( $writer );

        //$this->assertTrue($buf[0] == 0x40);
        //$this->assertTrue($buf[1] == 0x41);

    }
}

?>