<?php

 include_once __DIR__.'/binson.php';


$buf = "\x42\x42\x42\x43\x43\x42\x42\x43\x43\x43";

$p = new BinsonParser($buf);

$p->advance_test1(BinsonParser::ADVANCE_TRAVERSAL, null, 0, null, null);
//$p->advance();
//$p->advance();



?>

