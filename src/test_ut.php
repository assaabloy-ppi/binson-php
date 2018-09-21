<?php

 include_once __DIR__.'/binson.php';

  $buf = "\x42\x42\x43\x42\x43\x43";  // [[],[]]
    $parser = new BinsonParser($buf);

    $parser->goIntoArray();
    $parser->next();
    $parser->next();
    $parser->goIntoArray();
    $parser->leaveArray();
    $parser->leaveArray();

    echo "done:".(int)$parser->isDone();

  /*  {"A":"B"} 
   uint8_t buffer[8] = {
    0x40,
    0x14, 0x01, 0x41, 0x14, 0x01, 0x42,
    0x41*/
};

?>

