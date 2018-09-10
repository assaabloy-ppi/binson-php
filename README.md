# binson-php
A PHP implementation of Binson. Binson is an exceptionally simple data serialization format; see binson.org. 
.
Quick start
============

Just place `src/binson.php` into your project's source directory and "require" it.

BinsonWriter class usage examples:
============

Typical usage (serialization):
```PHP
$writer = new BinsonWriter();
$writer->put( ["a"=>[true, 123, "b", 5], "b"=>false, "c"=>7] );
```

"Streaming" to existing string:
```PHP
$buffer = "";
$writer = new BinsonWriter($buffer);
...
```

Serializing multiple variables/literals:
```PHP
$arr = [1,2,3];
$writer->put($arr, ["a"=>1, "b"=>"c"], true);
```

Specifying binson OBJECT instead of ARRAY:
```PHP
$writer->put([]);              // []
$writer->put([null => null]);  // {}
$writer->put([[]]);            // [[]]
$writer->put([[null=>null]]);  // [{}]
```

Low-level API:
```PHP
$writer->objectBegin();
$writer->objectEnd();

$writer->arrayBegin();
$writer->arrayEnd();

$writer->putBoolean(true);
$writer->putInteger(123);
$writer->putDouble(1.23);
$writer->putName("aaa");
$writer->putString("abc");
$writer->putBytes("\x00\x3f\xff");
$writer->putInline($src_writer);

$len = $writer->length();
$str = $writer->toBytes();
$res = $writer->verify();
```

Method chaining:
```PHP
$writer->objectBegin()
            ->putName("aaa")
            ->arrayBegin()
                ->putBoolean(false)
                ->putInteger(123)
            ->arrayEnd()
       ->objectEnd();
```

BinsonParser class usage examples:
============

Under heavy development. Coming very soon!