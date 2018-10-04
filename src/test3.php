<?php

 include_once __DIR__.'/binson.php';

 $buf_inline = "";
 $writer_inline = new BinsonWriter($buf_inline);
 $writer_inline->arrayBegin()
                  ->putFalse()
               ->arrayEnd();

 $buf = "";
 $writer = new BinsonWriter($buf);

 $writer->put([$writer_inline]); 
 
 print_r($writer->toBytes());

?>

