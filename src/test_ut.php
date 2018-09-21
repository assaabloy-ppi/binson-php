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

?>

