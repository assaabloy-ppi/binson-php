<?php

 include_once __DIR__.'/binson.php';

  // {"a":[true,123,"b",5],"b":false,"c":7}
$buf = "\x40\x14\x01\x61\x42\x44\x10\x7b\x14\x01\x62\x10\x05\x43\x14\x01\x62\x45\x14\x01\x63\x10\x07\x41";
$parser = new BinsonParser($buf);

$parser->goIntoObject();
$parser->field("a");
$parser->goIntoArray();
$parser->next();

$out = [];
$out[] = $parser->getValue(binson::TYPE_BOOLEAN);
$parser->next();
$out[] = $parser->getValue(binson::TYPE_INTEGER);
$parser->next();
$out[] = $parser->getValue(binson::TYPE_STRING);
$parser->leaveArray();
$parser->field("c");
$out[] = $parser->getName();
$out[] = $parser->getValue(binson::TYPE_INTEGER);
$parser->leaveObject();
$out[] = $parser->isDone();
//$out[] = $parser->verify();
        
echo implode(PHP_EOL, $out);




        //echo "done:".(int)$parser->isDone();

  /*  {"A":"B"} 
   uint8_t buffer[8] = {
    0x40,
    0x14, 0x01, 0x41, 0x14, 0x01, 0x42,
    0x41*/

    //{ 0x40, 0x14, 0x00, 0x41 };

    



?>

