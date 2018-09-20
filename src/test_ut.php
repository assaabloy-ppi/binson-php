<?php

 include_once __DIR__.'/binson.php';

    $buf = "\x40\x14\x01\x61\x40\x41\x41";  // {'a':{}}
    $parser = new BinsonParser($buf);


    $parser->goIntoObject();
    $parser->next();
    echo "name:".$parser->getName();
    $parser->goIntoObject();
    $parser->leaveArray();
    $parser->leaveArray();

    echo "done:".(int)$parser->isDone();

?>

