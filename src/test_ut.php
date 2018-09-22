<?php

 include_once __DIR__.'/binson.php';

 // {'a':false, 'b':true, 'c':false}
 $buf = "\x40\x14\x01\x61\x45\x14\x01\x62\x44\x14\x01\x63\x45\x41";  
$parser = new BinsonParser($buf);

        $parser->goIntoObject();
        $parser->field("a");
        $type = $parser->getType();
        $val = $parser->getValue();

        echo "t:{$type}, v:{$val}: ".PHP_EOL;
        
        //echo "done:".(int)$parser->isDone();

  /*  {"A":"B"} 
   uint8_t buffer[8] = {
    0x40,
    0x14, 0x01, 0x41, 0x14, 0x01, 0x42,
    0x41*/

    //{ 0x40, 0x14, 0x00, 0x41 };

    



?>

