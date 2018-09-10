# binson-php
A PHP implementation of Binson. Binson is an exceptionally simple data serialization format; see binson.org. 

Quick start
-----------

Just place `src/binson.php` into your project's source directory and "require" it.

Testing
-----------

Initialize environment first:
```
make init
make update
```

Now run PHPUnit tests:
```
make test
```

BinsonWriter class usage examples:
-----------

Typical usage (serialization):
```PHP
$writer = new BinsonWriter();
$writer->put( ["a"=>[true, 123, "b", 5], "b"=>false, "c"=>7] );
```
&nbsp;  
"Streaming" to existing string:
```PHP
$buffer = "";
$writer = new BinsonWriter($buffer);
...
```
&nbsp;  
Serializing multiple variables/literals:
```PHP
$arr = [1,2,3];
$writer->put($arr, ["a"=>1, "b"=>"c"], true);
```
&nbsp;  
Specifying binson OBJECT instead of ARRAY:
```PHP
$writer->put([]);                // [] - empty binson array
$writer->put([null => null]);    // {} - empty binson object
$writer->put([[]]);              // [[]] - nested empty arrays
$writer->put([[null => null]]);  // [{}] - empty object inside the empty array
```
&nbsp;  
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
&nbsp;  
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
-----------

Under heavy development. Coming very soon!
