<?php declare(strict_types=1);


abstract class binson {
    const BINSON_API_VERSION = 'binson_php_v0.0.1a';

    /* ported from stdint.h */
    const INT8_MIN   = (-0x7f - 1);
    const INT16_MIN  = (-0x7fff - 1);
    const INT32_MIN  = (-0x7fffffff - 1);
    const INT64_MIN  = (-0x7fffffffffffffff - 1);
    const INT8_MAX   = 0x7f;
    const INT16_MAX  = 0x7fff;
    const INT32_MAX  = 0x7fffffff;
    const INT64_MAX  = 0x7fffffffffffffff;

    const DEF_OBJECT_BEGIN     = 0x40;
    const DEF_OBJECT_END       = 0x41;
    const DEF_ARRAY_BEGIN      = 0x42;
    const DEF_ARRAY_END        = 0x43;
    const DEF_TRUE             = 0x44;
    const DEF_FALSE            = 0x45;
    const DEF_DOUBLE           = 0x46;
    const DEF_INT8             = 0x10;
    const DEF_INT16            = 0x11;
    const DEF_INT32            = 0x12;
    const DEF_INT64            = 0x13;
    const DEF_STRLEN_INT8      = 0x14;
    const DEF_STRLEN_INT16     = 0x15;
    const DEF_STRLEN_INT32     = 0x16;
    const DEF_BYTESLEN_INT8    = 0x18;
    const DEF_BYTESLEN_INT16   = 0x19;
    const DEF_BYTESLEN_INT32   = 0x1A;

    const TYPE_NONE            = 0x0000;
    const TYPE_OBJECT          = 0x0001;
    const TYPE_OBJECT_END      = 0x0002;
    const TYPE_ARRAY           = 0x0004;
    const TYPE_ARRAY_END       = 0x0008;
    const TYPE_BOOLEAN         = 0x0010;
    const TYPE_INTEGER         = 0x0020;
    const TYPE_DOUBLE          = 0x0040;
    const TYPE_STRING          = 0x0080;
    const TYPE_BYTES           = 0x0100;

    const ERROR_NONE           = 0;
    const ERROR_RANGE          = 1;
    const ERROR_FORMAT         = 2;
    const ERROR_EOF            = 3;
    const ERROR_END_OF_BLOCK   = 4;
    const ERROR_NULL           = 5;
    const ERROR_STATE          = 6;
    const ERROR_WRONG_TYPE     = 7;
    const ERROR_MAX_DEPTH      = 8;
}

class BinsonException extends Exception
{
    public function __construct($code, $message = "", Throwable $previous = null)
    {
        $msg = '';
        switch ($code) {
            case 0:
            case binson::ERROR_NONE:
                return;

            case binson::ERROR_RANGE:        $msg = '[Range error (buffer is full)]'; break;
            case binson::ERROR_FORMAT:       $msg = '[Format error]'; break;
            case binson::ERROR_EOF:          $msg = '[End of file detected]'; break;
            case binson::ERROR_END_OF_BLOCK: $msg = '[End of block detected]'; break;
            case binson::ERROR_NULL:         $msg = '[NULL ref]'; break;
            case binson::ERROR_STATE:        $msg = '[Wrong state]'; break;
            case binson::ERROR_WRONG_TYPE:   $msg = '[Wrong type]'; break;
            case binson::ERROR_MAX_DEPTH:    $msg = '[Max nesting depth reached]'; break;

            default: 
                $msg = 'Unknown binson exception, code: ' . $exc_code; break;
       }

       $msg .= $message? ', more: ' . $message : '';
       parent::__construct($msg, $code, $previous);
    }
}

/*class BinsonParserException extends BinsonException
{
    public function __construct($code, BinsonParser $bp, $message = "", Throwable $previous = null)
    {
        $msg = '';
        
        switch ($code) {
        case binson::ERROR_FORMAT:
            $msg = "Parsing failure at index: ".$bp->idx;
            break;
       }

       $msg .= $message? ' Details: ' . $message : '';
       parent::__construct($code, $msg, $previous);
    }    
}*/




class BinsonLogger {

    const EMERGENCY = 1;
    const ALERT = 2;
    const CRITICAL = 3;
    const ERROR = 4;
    const WARNING = 5;
    const NOTICE = 6;
    const INFO = 7;
    const DEBUG = 8;

    private $level;

    public function __construct($level)
    {  
        $this->level = $level;
    }

    public function log($level, $msg)
    {        
        if ($level <= $this->level)
            error_log($msg);
    }

    public function debug($msg)
    {        
        return $this->log(DEBUG, $msg);
    }

};



class BinsonWriter
{
    private $data_len;
	private $data;
    
    public function __construct(string &$dst = null)
    {
    	$this->data = &$dst ?? '';
        $this->data_len = strlen($this->data);
    }

    public function objectBegin() : BinsonWriter
    {
    	$this->writeToken(binson::TYPE_OBJECT, binson::DEF_OBJECT_BEGIN);
    	return $this;
    }

    public function objectEnd() : BinsonWriter
    {
    	$this->writeToken(binson::TYPE_OBJECT_END, binson::DEF_OBJECT_END);
    	return $this;
    }

    public function arrayBegin() : BinsonWriter
    {
    	$this->writeToken(binson::TYPE_ARRAY, binson::DEF_ARRAY_BEGIN);
    	return $this;
    }

    public function arrayEnd() : BinsonWriter
    {
    	$this->writeToken(binson::TYPE_ARRAY_END, binson::DEF_ARRAY_END);
    	return $this;
    }

    public function putBoolean(bool $val) : BinsonWriter
    {
    	$this->writeToken(binson::TYPE_BOOLEAN, $val? binson::DEF_TRUE : binson::DEF_FALSE);
    	return $this;
    }

    public function putTrue() : BinsonWriter
    {   
        return $this->putBoolean(true);
    }

    public function putFalse() : BinsonWriter
    {
        return $this->putBoolean(false);
    }

    public function putInteger(int $val) : BinsonWriter
    {
    	$this->writeToken(binson::TYPE_INTEGER, $val);
    	return $this;
    }

    public function putDouble(float $val) : BinsonWriter
    {
    	$this->writeToken(binson::TYPE_DOUBLE, $val);
    	return $this;
    }

    public function putString(string $val) : BinsonWriter
    {
    	$this->writeToken(binson::TYPE_STRING, $val);
    	return $this;
    }

    public function putName(string $val) : BinsonWriter
    {
    	return $this->putString($val);
    }

    public function putBytes(string $val) : BinsonWriter
    {
    	$this->writeToken(binson::TYPE_BYTES, $val);
    	return $this;
    }

    public function putInline(BinsonWriter $src_writer) : BinsonWriter
    {
    	$this->data .= $src_writer->data;
    	return $this;
    }

	public function length() : int
    {
    	return strlen($this->data) - $this->data_len;
    }

	public function counter() : int
    {
    	return $this->length();
    }


    public function toBytes() : string
    {
    	return substr($this->data, $this->data_len);
    }

    public function verify() : bool
    {
        //$p = new BinsonParser($this->toBytes());
        //return $p->verify();
    }

    public function put(...$vars) : BinsonWriter
    {
        foreach ($vars as $var)
            $this->putOne($var);

        return $this;
    }

    
    public function putOne($var) : BinsonWriter
    {
        if (!$this->isSerializable($var))
           throw new BinsonException(binson::ERROR_WRONG_TYPE);
                    
        switch(gettype($var))
        {
            case "array":
                break;        

            case "string":   return $this->putString($var);
            case "integer":  return $this->putInteger($var);
            case "double":   return $this->putDouble($var);
            case "boolean":  return $this->putBoolean($var);

            default:
                throw new BinsonException(binson::ERROR_WRONG_TYPE);                
        }

        if (is_array($var) && empty($var)) {  // iterator won't iterate on empty array
            return $this->arrayBegin()->arrayEnd();
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($var),
                                                    RecursiveIteratorIterator::SELF_FIRST);
        $last_depth = -1;
        $type_stack = array();
        $block_type = -1;

        foreach($iterator as $key => $value) {
            
            $depth = $iterator->getDepth();
            if ($block_type == -1 && $depth == 0)
                $block_type = (is_int($key) && $key === 0) ? binson::TYPE_ARRAY : binson::TYPE_OBJECT;

            if ($depth > $last_depth) {  // new block detected
                array_push($type_stack, $block_type);         
                $block_type = (is_int($key) && $key === 0) ? binson::TYPE_ARRAY : binson::TYPE_OBJECT;       
                $res = ($block_type == binson::TYPE_ARRAY) ? $this->arrayBegin() : $this->objectBegin();
            }            
            else if ($depth < $last_depth) {  // block end detected
              $res = ($block_type == binson::TYPE_ARRAY) ? $this->arrayEnd() : $this->objectEnd();
              $block_type = array_pop($type_stack);
              
            }        
        
            if (is_array($value) )
            {
              if ($block_type == binson::TYPE_OBJECT)
                $this->putString($key);
            }

            if (is_array($value) && empty($value))
            {
              $this->arrayBegin()->arrayEnd(); 
            }
            
            if (!is_array($value) && $value !== null)
            {
              if ($block_type == binson::TYPE_OBJECT)
                $this->putString($key);

              $this->putOne($value);
            }            

            $last_depth = $depth;
        }

         while ($block_type = array_pop($type_stack))
         {
            $res = ($block_type == binson::TYPE_ARRAY) ? $this->arrayEnd() : $this->objectEnd();
         }

        return $this;
    }

    private function isArrayEmptyBinsonObject($var) : bool
    {
        // check for [null => null]
        if (!is_array($var))
            return false;

        if (count($var) == 1 && key($var) == null && $var[key($var)] == null)
            return true;

        return false;
    }

    private function isSerializable($var) : bool
    {
            if (is_array($var))
            {
                if ($this->isArrayEmptyBinsonObject($var))
                    return true;

                $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($var),
                                                     RecursiveIteratorIterator::SELF_FIRST);
                foreach($iterator as $key => $value)
                {   
                    if ($key == null && $value == null)  // specific case: 'null => null' means object instead of array
                        return true;

                    //if ( !(is_int($key) && is_string($var) && is_array($var)) || !$this->isSerializable($value))
                    //    return false;
                }             
                return true;
            }

            if ( is_string($var) ||
                 is_int($var) ||
                 is_float($var) ||
                 is_bool($var) )
            return true;

            return false;
    }


    /*======= Private method implementations ====================================*/


    private function writeToken(int $token_type, $val = null) : void
    {
        switch ($token_type) {
                case binson::TYPE_OBJECT:
                case binson::TYPE_OBJECT_END:
                case binson::TYPE_ARRAY:
                case binson::TYPE_ARRAY_END:
                case binson::TYPE_BOOLEAN:
                    $this->data .= chr($val);
                    return;

                case binson::TYPE_DOUBLE:
                case binson::TYPE_INTEGER:
                    $this->data .= util_pack_size($val, $token_type);
                    return;

                case binson::TYPE_STRING:
                case binson::TYPE_BYTES:
                    $this->data .= util_pack_size(strlen($val), $token_type);
                    $this->data .= $val;
                    return;

                default:
                    throw new BinsonException(binson::ERROR_STATE);
            }
    }
}

class BinsonParserStateStack implements ArrayAccess
{
    private $data = [];
    private $bp;

    public function __construct(BinsonParser &$bp)
    {
        $this->bp = &$bp;
    }

    public function reset()
    {
        $this->data = [];
    }

    public function offsetGet($offset) {
        if ($offset === 'top')
            return $this->data[$this->bp->depth] ?? null;
        elseif ($offset === 'parent')
            return $this->data[$this->bp->depth - 1] ?? null;
        else
            return isset($this->data[$this->bp->depth][$offset]) ?
                     $this->data[$this->bp->depth][$offset] : null;
    }

    public function offsetSet($offset, $value) {
        //$value_str = 

        if ($offset === null) {            
            $this->data[$this->bp->depth] = $value;

            echo "State update. Depth: {$this->bp->depth}. ".json_encode($value).PHP_EOL;
        } else {
            $this->data[$this->bp->depth][$offset] = $value;
            echo "State update. Depth: {$this->bp->depth}. $offset => ($value)".PHP_EOL;
        }
    }

    public function offsetExists($offset) {
        return isset($this->data[$this->bp->depth][$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->data[$this->bp->depth][$offset]);
    }

/*    public function __get( $key )
    {
        list($dd, $k) = $this->parseKey($key);
        $d = $bp->depth + $dd;
        //if ($d < 0)
        //    throw new BinsonException();

        return $this->stack[$d][$k];
    }

    public function __set( $key, $value )
    {
        if (empty($key))
            $this->stack[$bp->depth] = $value;
        else
            $this->stack[$bp->depth][$key] = $value;
    }

    private function parseKey($key)
    {
        $chunks = explode(".", $key);
        return ($chunks[0] === 'prev' && count($chunks) == 2)? ['dd'=>-1, 'k'=>$chunks[1]] : ['dd'=>0, 'k'=>$key];
    }
*/
}


class BinsonParser
{
    private const STATE_UNDEFINED              = 0x0000;
    private const STATE_ENTER_OBJECT           = 0x0001;
    private const STATE_IN_OBJ_FIELD = 0x0002;
    private const STATE_IN_OBJ_VALUE = 0x0004;
    //const STATE_IN_OBJECT              = STATE_IN_OBJ_FIELD | STATE_IN_OBJ_VALUE;//0x0003;
    private const STATE_LEAVE_OBJECT           = 0x0008;
    private const STATE_ENTER_ARRAY            = 0x0010;
    private const STATE_IN_ARRAY               = 0x0020;
    private const STATE_LEAVE_ARRAY            = 0x0040;

    private const STATE_MASK_OBJECT    = self::STATE_ENTER_OBJECT | self::STATE_IN_OBJ_FIELD |
                                         self::STATE_IN_OBJ_VALUE | self::STATE_LEAVE_OBJECT; 
    private const STATE_MASK_ARRAY     = self::STATE_ENTER_ARRAY |
                                         self::STATE_IN_ARRAY | self::STATE_LEAVE_ARRAY;


    private const STATE_MASK_ENTER     = self::STATE_ENTER_ARRAY | self::STATE_ENTER_OBJECT;
    private const STATE_MASK_LEAVE     = self::STATE_LEAVE_ARRAY | self::STATE_LEAVE_OBJECT;

    //const STATE_IN_ARRAY_2             = 0x0008;
    //const STATE_IN_ARRAY               = STATE_IN_ARRAY_1 | STATE_IN_ARRAY_2; //0x000C; 
    private const STATE_DONE           = 0x0100;
    private const STATE_ERROR          = 0x0200;

    private const STATE_MASK_INNER   = self::STATE_IN_OBJ_VALUE | self::STATE_IN_ARRAY;

    private const STATE_MASK_EXIT   = self::STATE_DONE | self::STATE_ERROR;
    private const STATE_MASK_CTX_UPD   = self::STATE_MASK_ENTER | self::STATE_MASK_LEAVE;




    /* Next state after processing one item. */
    /*const STATE_PARSED_STRING          = 0x0010;
    const STATE_PARSED_BOOLEAN         = 0x0020;
    const STATE_PARSED_DOUBLE          = 0x0040;
    const STATE_PARSED_INTEGER         = 0x0080;
    const STATE_PARSED_BYTES           = 0x0100;
    const STATE_PARSED_OBJECT_BEGIN    = 0x0200;
    const STATE_PARSED_OBJECT_END      = 0x0400;
    const STATE_PARSED_ARRAY_BEGIN     = 0x0800;
    const STATE_PARSED_ARRAY_END       = 0x1000;
    const STATE_ERROR                  = 0x2000;
    const STATE_PARSED_FIELD_NAME      = 0x8000;
    const STATE_VALUE_FLAG             = 0x0BF0;*/

    /*const TYPE_NONE            = 0x0000;
    const TYPE_OBJECT          = 0x0001;
    //const TYPE_OBJECT_END      = 0x0002;
    const TYPE_ARRAY           = 0x0004;
    //const TYPE_ARRAY_END       = 0x0008;
    const TYPE_BOOLEAN         = 0x0010;
    const TYPE_INTEGER         = 0x0020;
    const TYPE_DOUBLE          = 0x0040;
    const TYPE_STRING          = 0x0080;
    const TYPE_BYTES           = 0x0100;*/
    
    const TYPE_FIELD              = 0x0200;
    const TYPE_MASK_VALUE         = 0x01f0;
    const TYPE_MASK_PRIMITIVE     = binson::TYPE_BOOLEAN | binson::TYPE_INTEGER |
                                    binson::TYPE_DOUBLE | binson::TYPE_STRING | binson::TYPE_BYTES;

    
    const ADVANCE_ONE           = 0x01;
    const ADVANCE_ITEM          = 0x02;
    const ADVANCE_LEAVE_BLOCK   = 0x03;
    const ADVANCE_TRAVERSAL     = 0x04;


    /* state transition matrix */
    /*const STATE_MX = [
                        self::STATE_UNDEFINED => [
                                                'states' => STATE_ENTER_OBJECT | STATE_ENTER_ARRAY,
                                                'types' => TYPE_OBJECT | TYPE_ARRAY
                                           ],
                        STATE_ENTER_OBJECT => [
                                                'states' => STATE_IN_OBJ_FIELD | STATE_LEAVE_OBJECT,
                                                'types' => TYPE_FIELD
                                                        ],
                        STATE_IN_OBJ_FIELD => [
                                                'states' => STATE_IN_OBJ_VALUE,
                                                'types' => TYPE_MASK_VALUE | TYPE_OBJECT | TYPE_ARRAY
                                                        ],                        
                        STATE_IN_OBJ_VALUE => [
                                                'states' => STATE_ENTER_OBJECT | STATE_IN_OBJ_FIELD |
                                                            STATE_LEAVE_OBJECT | STATE_ENTER_ARRAY,
                                                'types' =>  TYPE_FIELD | TYPE_OBJECT | TYPE_ARRAY
                                                        ],                        
                        STATE_LEAVE_OBJECT => [
                                                'states' => STATE_ENTER_OBJECT | STATE_IN_OBJ_FIELD |
                                                            STATE_LEAVE_OBJECT | STATE_ENTER_ARRAY |
                                                            STATE_IN_ARRAY | STATE_LEAVE_ARRAY | STATE_DONE,
                                                'types' =>  TYPE_FIELD | TYPE_MASK_VALUE | TYPE_OBJECT | TYPE_ARRAY
                                                        ],  
                        STATE_ENTER_ARRAY => [
                                                'states' => STATE_ENTER_OBJECT | STATE_ENTER_ARRAY |
                                                            STATE_IN_ARRAY | STATE_LEAVE_ARRAY,
                                                'types' =>  TYPE_MASK_VALUE | TYPE_OBJECT | TYPE_ARRAY
                                                        ],      
                        STATE_IN_ARRAY => [
                                                'states' => STATE_ENTER_OBJECT | STATE_ENTER_ARRAY | 
                                                            STATE_IN_ARRAY | STATE_LEAVE_ARRAY,
                                                'types' =>  TYPE_MASK_VALUE | TYPE_OBJECT | TYPE_ARRAY
                                                        ],
                        STATE_LEAVE_ARRAY => [
                                                'states' => STATE_ENTER_OBJECT | STATE_IN_OBJ_FIELD |
                                                            STATE_LEAVE_OBJECT | STATE_ENTER_ARRAY |
                                                            STATE_IN_ARRAY | STATE_LEAVE_ARRAY | STATE_DONE,
                                                'types' =>  TYPE_FIELD | TYPE_MASK_VALUE | TYPE_OBJECT | TYPE_ARRAY
                                            ],
                        STATE_DONE => [
                                                'states' => STATE_DONE,
                                                'types' =>  TYPE_NONE
                                                        ]
                     ];
*/
    private const TYPE_STATE_MX = [   
                    binson::TYPE_OBJECT => [
                            self::STATE_UNDEFINED          => self::STATE_ENTER_OBJECT,  //  >{<}, entering top object 
                            self::STATE_ENTER_OBJECT       => self::STATE_ERROR,         // {>{<}}, object is illegal object's key
                            self::STATE_IN_OBJ_FIELD       => self::STATE_ENTER_OBJECT,  // {'a':>{<}}, object is ok to be a value
                            self::STATE_IN_OBJ_VALUE       => self::STATE_ERROR,         // {'a':3,>{<}}, object is illegal object's key
                            //self::STATE_LEAVE_OBJECT       => self::STATE_ENTER_OBJECT,  // [{}>{<}], ok IF in array
                            self::STATE_ENTER_ARRAY        => self::STATE_ENTER_OBJECT,  // [>{<}]
                            self::STATE_IN_ARRAY           => self::STATE_ENTER_OBJECT,  // [1,>{<},3]
                            //self::STATE_LEAVE_ARRAY        => self::STATE_ENTER_OBJECT,  // [[]>{<}]
                            //self::STATE_DONE               => self::STATE_ERROR,         // one more object beyond top object
                            //self::STATE_ERROR              => self::STATE_ERROR          // no sense                             
                        ],                            
                    binson::TYPE_OBJECT_END => [
                                'current' => [
                                    self::STATE_UNDEFINED               => self::STATE_ERROR,         // >}<, single closing tag
                                    self::STATE_ENTER_OBJECT            => self::STATE_LEAVE_OBJECT,  // {>}<, empty object's end
                                    self::STATE_IN_OBJ_FIELD            => self::STATE_ERROR,         // {'a':>}<}
                                    self::STATE_IN_OBJ_VALUE            => self::STATE_LEAVE_OBJECT,  // {'a':3>}<
                                    //self::STATE_LEAVE_OBJECT            => self::STATE_LEAVE_OBJECT,  // {'a':{'b':3}>}<
                                    self::STATE_ENTER_ARRAY             => self::STATE_ERROR,         // [>}<]
                                    self::STATE_IN_ARRAY                => self::STATE_ERROR,         // [1,>}<,3]
                                    //self::STATE_LEAVE_ARRAY             => self::STATE_LEAVE_OBJECT,   // {'a':[]>}<
                                    //self::STATE_DONE                    => self::STATE_ERROR,         // []>}<
                                    //self::STATE_ERROR                   => self::STATE_ERROR          // no sense
                                ],
                                'parent' => [
                                    self::STATE_UNDEFINED               => self::STATE_ERROR,         // no sense
                                    self::STATE_ENTER_OBJECT            => self::STATE_LEAVE_OBJECT,  // no sense, handled before
                                    self::STATE_IN_OBJ_FIELD            => self::STATE_IN_OBJ_VALUE,  // ??processed object as value 
                                    self::STATE_IN_OBJ_VALUE            => self::STATE_ERROR,         // no sense 
                                    //self::STATE_LEAVE_OBJECT            => self::STATE_LEAVE_OBJECT,  // ??
                                    self::STATE_ENTER_ARRAY             => self::STATE_IN_ARRAY,      // [{'s':1}><] 
                                    self::STATE_IN_ARRAY                => self::STATE_IN_ARRAY,      // [1,{'s':1}><] 
                                    //self::STATE_LEAVE_ARRAY             => self::STATE_LEAVE_OBJECT,   // ??
                                    //self::STATE_DONE                    => self::STATE_ERROR,
                                    //self::STATE_ERROR                   => self::STATE_ERROR
                                ],


                            ],
                            binson::TYPE_ARRAY => [
                                self::STATE_UNDEFINED               => self::STATE_ENTER_ARRAY,  // >[<], top array
                                self::STATE_ENTER_OBJECT            => self::STATE_ERROR,
                                self::STATE_IN_OBJ_FIELD            => self::STATE_ENTER_ARRAY,  // {'a':>[<]}
                                self::STATE_IN_OBJ_VALUE            => self::STATE_ERROR,        // {'a':3,>[<]}
                                //self::STATE_LEAVE_OBJECT            => self::STATE_ENTER_ARRAY,
                                self::STATE_ENTER_ARRAY             => self::STATE_ENTER_ARRAY,
                                self::STATE_IN_ARRAY                => self::STATE_ENTER_ARRAY,  // [1,>[<],3]
                                //self::STATE_LEAVE_ARRAY             => self::STATE_ENTER_ARRAY
                                //self::STATE_DONE                    => self::STATE_ERROR,
                                //self::STATE_ERROR                   => self::STATE_ERROR
                            ],
                            binson::TYPE_ARRAY_END => [
                                'current' => [                                
                                    self::STATE_UNDEFINED               => self::STATE_ERROR,  // >]<
                                    self::STATE_ENTER_OBJECT            => self::STATE_ERROR,
                                    self::STATE_IN_OBJ_FIELD            => self::STATE_ERROR,  // {'a':>]<}
                                    self::STATE_IN_OBJ_VALUE            => self::STATE_ERROR,  // {'a':3,>]<}
                                    //self::STATE_LEAVE_OBJECT            => self::STATE_LEAVE_ARRAY,
                                    self::STATE_ENTER_ARRAY             => self::STATE_LEAVE_ARRAY,
                                    self::STATE_IN_ARRAY                => self::STATE_LEAVE_ARRAY,  // [1,2,3>]<
                                    //self::STATE_LEAVE_ARRAY             => self::STATE_LEAVE_ARRAY
                                    //self::STATE_DONE                    => self::STATE_ERROR,
                                    //self::STATE_ERROR                   => self::STATE_ERROR                                
                                ],
                                'parent' => [
                                    self::STATE_UNDEFINED               => self::STATE_DONE, // [], done top array
                                    self::STATE_ENTER_OBJECT            => self::STATE_ENTER_OBJECT,  
                                    self::STATE_IN_OBJ_FIELD            => self::STATE_IN_OBJ_VALUE, // {'a':>[]<}, array as value
                                    self::STATE_IN_OBJ_VALUE            => self::STATE_ERROR,   // handled before {'a':3,[]]><}
                                    //self::STATE_LEAVE_OBJECT            => self::STATE_LEAVE_ARRAY,
                                    self::STATE_ENTER_ARRAY             => self::STATE_LEAVE_ARRAY,
                                    self::STATE_IN_ARRAY                => self::STATE_IN_ARRAY, // no change, [1,[]><,3]
                                    self::STATE_LEAVE_ARRAY             => self::STATE_LEAVE_ARRAY
                                    //self::STATE_DONE                    => self::STATE_ERROR,   
                                    //self::STATE_ERROR                   => self::STATE_ERROR                                
                                ]                                
                            ],
                            binson::TYPE_BOOLEAN => [
                                self::STATE_UNDEFINED               => self::STATE_ERROR,
                                self::STATE_ENTER_OBJECT            => self::STATE_ERROR,
                                self::STATE_IN_OBJ_FIELD            => self::STATE_IN_OBJ_VALUE,
                                self::STATE_IN_OBJ_VALUE            => self::STATE_ERROR,
                                //self::STATE_LEAVE_OBJECT            =>  0,//?
                                self::STATE_ENTER_ARRAY             => self::STATE_IN_ARRAY,
                                self::STATE_IN_ARRAY                => self::STATE_IN_ARRAY,
                                //self::STATE_LEAVE_ARRAY             => 0,//?
                                //self::STATE_DONE                    => self::STATE_ERROR,
                                //self::STATE_ERROR                   => self::STATE_ERROR
                            ],
                            binson::TYPE_INTEGER => [
                                self::STATE_UNDEFINED               => self::STATE_ERROR,
                                self::STATE_ENTER_OBJECT            => self::STATE_ERROR,
                                self::STATE_IN_OBJ_FIELD            => self::STATE_IN_OBJ_VALUE,
                                self::STATE_IN_OBJ_VALUE            => self::STATE_ERROR,
                                //self::STATE_LEAVE_OBJECT            =>  0,//?
                                self::STATE_ENTER_ARRAY             => self::STATE_IN_ARRAY,
                                self::STATE_IN_ARRAY                => self::STATE_IN_ARRAY,
                                //self::STATE_LEAVE_ARRAY             => 0,//?
                                //self::STATE_DONE                    => self::STATE_ERROR,
                                //self::STATE_ERROR                   => self::STATE_ERROR
                            ],                            
                            binson::TYPE_DOUBLE => [
                                self::STATE_UNDEFINED               => self::STATE_ERROR,
                                self::STATE_ENTER_OBJECT            => self::STATE_ERROR,
                                self::STATE_IN_OBJ_FIELD            => self::STATE_IN_OBJ_VALUE,
                                self::STATE_IN_OBJ_VALUE            => self::STATE_ERROR,
                                //self::STATE_LEAVE_OBJECT            =>  0,//?
                                self::STATE_ENTER_ARRAY             => self::STATE_IN_ARRAY,
                                self::STATE_IN_ARRAY                => self::STATE_IN_ARRAY,
                                //self::STATE_LEAVE_ARRAY             => 0,//?
                                //self::STATE_DONE                    => self::STATE_ERROR,
                                //self::STATE_ERROR                   => self::STATE_ERROR,
                            ],
                            binson::TYPE_STRING => [
                                self::STATE_UNDEFINED               => self::STATE_ERROR,
                                self::STATE_ENTER_OBJECT            => self::STATE_IN_OBJ_FIELD,
                                self::STATE_IN_OBJ_FIELD            => self::STATE_IN_OBJ_VALUE,
                                self::STATE_IN_OBJ_VALUE            => self::STATE_IN_OBJ_FIELD,
                                //self::STATE_LEAVE_OBJECT            =>  0,//?
                                self::STATE_ENTER_ARRAY             => self::STATE_IN_ARRAY,
                                self::STATE_IN_ARRAY                => self::STATE_IN_ARRAY,
                                //self::STATE_LEAVE_ARRAY             => 0,//?
                                //self::STATE_DONE                    => self::STATE_ERROR,
                                //self::STATE_ERROR                   => self::STATE_ERROR,
                            ],
                            binson::TYPE_BYTES => [
                                self::STATE_UNDEFINED               => self::STATE_ERROR,
                                self::STATE_ENTER_OBJECT            => self::STATE_ERROR,
                                self::STATE_IN_OBJ_FIELD            => self::STATE_IN_OBJ_VALUE,
                                self::STATE_IN_OBJ_VALUE            => self::STATE_ERROR,
                                //self::STATE_LEAVE_OBJECT            =>  0,//?
                                self::STATE_ENTER_ARRAY             => self::STATE_IN_ARRAY,
                                self::STATE_IN_ARRAY                => self::STATE_IN_ARRAY,
                                //self::STATE_LEAVE_ARRAY             => 0,//?
                                //self::STATE_DONE                    => self::STATE_ERROR,
                                //self::STATE_ERROR                   => self::STATE_ERROR,
                            ],
                      ];


    /* public data members */
    public $depth;
    //public $type;

    /* private data members */
    private $data;
    private $idx;
    private $state;

    private $logger;


    public function __construct(string &$src)
    {
        $this->logger = new BinsonLogger(BinsonLogger::DEBUG);
        $this->state = new BinsonParserStateStack($this);

        $this->reset($src);
    }

    public function reset(string &$src = null)
    {
        if ($src !== null)
            $this->data = &$src;    
        
        $this->idx = 0;
        $this->depth = 0;
        $this->state['id'] = self::STATE_UNDEFINED;
    }

    public function dump() : string
    {
        return print_r($this, true);
    }

    public function verify() : bool
    {
        /*$res = false;

        try
        {
            $this->reset();
            $res = $this->advance(BINSON_ADVANCE_VERIFY);
            $this->reset();
        }
        catch (Exception $ex)
            return false;

        return $res;*/
    }


    public function parse( $cb ) : bool
    {

    }


    public function toJSON( bool $nice = false) : string
    {

    }


    public function toArray() : array
    {

    }


    public function goIntoObject() : BinsonParser
    {
        $this->advance(BINSON_ADVANCE_ENTER_OBJECT);
        return $this;
    }

    public function goIntoArray() : BinsonParser
    {
        $this->advance(BINSON_ADVANCE_ENTER_ARRAY);
        return $this;
    }

    public function leaveObject() : BinsonParser
    {   
        $upper_idx = ($this->depth > 0) ? $this->depth-1 : 0;
        if (!$this->state[$upper_idx]['flags'] === binson::BINSON_STATE_IN_OBJECT)
            throw new BinsonException(binson::ERROR_STATE);

        $this->advance(BINSON_ADVANCE_LEAVE_OBJECT);
        return $this;
    }

    public function leaveArray() : BinsonParser
    {
        $upper_idx = ($this->depth > 0) ? $this->depth-1 : 0;
        if (!$this->state[$upper_idx]['flags'] === binson::BINSON_STATE_IN_ARRAY)
            throw new BinsonException(binson::ERROR_STATE);

        $this->advance(BINSON_ADVANCE_LEAVE_ARRAY);
        return $this;
    }

    public function next() : BinsonParser
    {
        $this->advance(BINSON_ADVANCE_VALUE);
        return $this;
    }

    public function ensure(int $type) : bool
    {
        return $this->getType() === $type;
    }


    public function field(string $name) : bool
    {        
        if (is_null($name))
            throw new BinsonException(binson::ERROR_NULL);

        while ($this->advance(BINSON_ADVANCE_VALUE, $name))
        {
            $r = $name <=> $this->stateRef(BINSON_STATE_PREV)['name'];
            if (0 === $r)
                return true;
            else if ($r < 0)
                break;
        }

        return false;
    }
    
    public function getName() : string
    {
        return $this->state['name'];
    }

    public function getValue()
    {
        return $this->state['val'];
    }

    public function getRaw() : string
    {  
        // later
    }


    /*======= Private method implementations ====================================*/
    //private function advance(int $scan_flags, int $steps, string $scan_name, int $ensure_type,
    //                         callable $cb, $cb_param = null) : bool
    public function tostr() : string
    {
        $str = '';
        $this->advance(self::ADVANCE_TRAVERSAL, null, 0, [$this, 'cbToString'], $str);
        return $str;
    }

    public function advance_test1(int $scan_mode, ?string $scan_name, int $ensure_type,
    ?callable $cb, $cb_param = null) : bool
    {   
        
        //$this->advance(self::ADVANCE_TRAVERSAL, null, 0, [$this, 'cbDebug1'], null);
        
        $str = '';
        $this->advance(self::ADVANCE_TRAVERSAL, null, 0, [$this, 'cbToString'], $str);
        echo "'".$str."'".PHP_EOL; 

        /*
        $data = [];
        $this->advance(self::ADVANCE_TRAVERSAL, null, 0, [$this, 'cbDeserializer'], $data);
        print_r($data['data']);
        echo json_encode($data['data']);
        */

        return true;      
    }

    private function advance(int $scan_mode, ?string $scan_name, int $ensure_type,
                             ?callable $cb, &$cb_param = null) : bool
    {
        //if ($this->state->id === STATE_DONE)
        //    throw new BinsonException(binson::ERROR_STATE);
        


//        if ($this->depth == 0 && $scan_mode != ADVANCE_TRAVERSAL && $scan_mode != ADVANCE_ONE)
//            throw new BinsonException(binson::ERROR_END_OF_BLOCK);            

        while (true) {  /* scanning loop */

            /* context checks for field name search */
            //if ($state_flags != PARSER_STATE_NAME && $this->isInObject()
            //    && ($scan_flag & PARSER_ADVANCE_CMP_NAME) && $this->depth == $orig_depth) 
            //{
            //    $cmp_res = $this->stateRef(BINSON_STATE_PREV)['name'] <=> $scan_name;
            //    if ($cmp_res == 0) {
            //        return $this->ensureFilter($scan_flag, $ensure_type);
            //    }
            //    if ($cmp_res > 0) /* current name is lexicographically greater than requested */
            //        throw new BinsonException(binson::BINSON_ID_PARSE_NO_FIELD_NAME);      
            //}   

           // switch ($state_flags) {
           //     case PARSER_STATE_BLOCK: /* it's time to enter current block */
           //         $req_state['flags'] = PARSER_STATE_INBLOCK;
           //         break;

           //     case PARSER_STATE_INBLOCK_END: /* it's time to leave current block */
           //         $req_state['flags'] = PARSER_STATE_BLOCK_END;
           //         break;

           //     default:
           //         $req_state = $this->processOne();
           // }

            // check for default state transition, if any

            $state_update = $this->processOne();

            $type_req = $state_update['type'];
            $rules_sets = []; // clear on each iter
            $rules_sets[] = self::TYPE_STATE_MX[$type_req]['current'] ??
                            self::TYPE_STATE_MX[$type_req];            

            if (isset(self::TYPE_STATE_MX[$type_req]['parent']))                                        
                $rules_sets[] = self::TYPE_STATE_MX[$type_req]['parent'];


            foreach ($rules_sets as $rule_no => $rules) {        
                $state_update['id'] = $rules[$this->state['id']] ?? null;

                switch ($state_update['id']) {
                case null:
                    throw new BinsonException(binson::ERROR_STATE, 
                        "Missing rule for rule_no: $rule_no, type: $type_req, state: ".$this->state['id']);
                case self::STATE_ERROR:
                    throw new BinsonException(binson::ERROR_FORMAT, $this->dump());

                case self::STATE_DONE:
                    return true;    
                    
                }

                if ($state_update['id'] & self::STATE_MASK_ENTER)
                {
                    $this->depth++;

                    // update state
                    $prev_state = $this->state['top'];
                    $this->state[] = $state_update; // copy id, type, value
                    $cb_res = $cb($prev_state, $cb_param) ?? null;
                }

                // only apply to current *LEAVE* states, not to parent
                if ($rule_no == 0 && ($state_update['id'] & self::STATE_MASK_LEAVE))
                {
                    $prev_state = $this->state['top'];
                    $this->state[] = $state_update;
                    $cb_res = $cb($prev_state, $cb_param) ?? null;  // first cb call with *LEAVE* state
                    
                    $this->depth--;
                }

                if ($state_update['id'] & self::STATE_MASK_INNER) 
                {
                    $prev_state = $this->state['top'];
                    $this->state[] = $state_update;
                    $cb_res = $cb($prev_state, $cb_param) ?? null;
                }
            }

            //if ($state_update['id'] & self::STATE_MASK_CTX_UPD) 
             //   throw new Exception("no more context updates at this point"); 

//            if (!(bool)($req_state['type'] & self::STATE_MX[$this->state->id]['type'])]
  //              throw new BinsonException(binson::ERROR_FORMAT);

           


     
            /* extra validation, when requested via 'scan_flag' */
            // throw exception ?? or just return with bool result
            //_binson_parser_ensure_filter(pp, scan_flag, ensure_type)

            //if ($state_update['id'] & self::STATE_MASK_CTX_UPD) 
             //   throw new Exception("no more context updates at this point"); 
        }
    }

    /* Utility function which return false in case of type mismatch */
    private function ensureFilter(int $scan_flag, int $ensure_type) : bool
    {               
        $type = $this->getType(); 
        if ($ensure_type == binson::BINSON_ID_UNKNOWN || !($scan_flag & PARSER_ADVANCE_ENSURE_TYPE))
            return true;

        if ($ensure_type == BINSON_ID_BLOCK && !$this->isBlock())
            return false;

        if ($ensure_type != BINSON_ID_BLOCK && $ensure_type != $type)
            return false;

        return true;
    }

    private function isInObject() : bool
    {
        return (bool)($this->state->id & self::STATE_MASK_OBJECT);
    }

    private function isBlock() : bool
    {
        $type = $this->state->type;
        return $type === binson::BINSON_ID_OBJECT || $type === binson::BINSON_ID_ARRAY;
    }

    /* return associative array:  type, value */
    private function processOne() : array
    {
        $byte = ord($this->consume(1));

        switch ($byte)
        {            
            case binson::DEF_OBJECT_BEGIN:
                return ['type' => binson::TYPE_OBJECT];
            case binson::DEF_OBJECT_END:
                return ['type' => binson::TYPE_OBJECT_END];
            case binson::DEF_ARRAY_BEGIN:
                return ['type' => binson::TYPE_ARRAY];
            case binson::DEF_ARRAY_END:
                return ['type' => binson::TYPE_ARRAY_END];

            case binson::DEF_FALSE:
            case binson::DEF_TRUE:
                return ['type' => binson::TYPE_BOOLEAN, 'val' => ($byte === binson::DEF_TRUE)];

            case binson::DEF_DOUBLE:                 
                return ['type' => binson::TYPE_DOUBLE, 'val' => $this->parseNumeric($this->consume(8), true)];

            case binson::DEF_INT8:
            case binson::DEF_INT16:
            case binson::DEF_INT32:
            case binson::DEF_INT64:                             
                $size = 1 << ($byte - 16);
                return ['type' => binson::TYPE_INTEGER, 'val' => $this->parseNumeric($this->consume($size))];

            /* string and field names processing */
            case binson::DEF_STRLEN_INT8:
            case binson::DEF_STRLEN_INT16:
            case binson::DEF_STRLEN_INT32:                 
            case binson::DEF_BYTESLEN_INT8:
            case binson::DEF_BYTESLEN_INT16:
            case binson::DEF_BYTESLEN_INT32:
                $def_bytes = $byte >= binson::DEF_BYTESLEN_INT8;
                $delta = $def_bytes? binson::DEF_BYTESLEN_INT8 : binson::DEF_STRLEN_INT8;
                $len_size = 1 << ($byte - $delta);
                $len = $this->parseNumeric($this->consume($len_size));

                if ($len < 0 || $len > binson::INT32_MAX)
                    throw new BinsonException(binson::ERROR_FORMAT);

                return ['type' => $def_bytes? binson::TYPE_BYTES : binson::TYPE_STRING, 
                        'val' => $this->consume($len)];                        
        }

        throw new BinsonException(binson::ERROR_WRONG_TYPE);
    }

    private function cbValidator(array $prev_state, &$param = null) : bool
    {}

    private function cbDeserializer(array $prev_state, &$param = null) : bool
    {
        if (!is_array($param))
            throw new BinsonException(binson::ERROR_WRONG_TYPE, "cbDeserializer() require `array` parameter");

        if (empty($param)) {  // first cb run
                $param = ['data'=>[], 'parent'=>[]];
                $param['current'] = &$param['data'];
        }            

        $new_state = $this->state['top'];
        $depth = $this->depth;

        switch ($new_state['id']) {
            case self::STATE_ENTER_ARRAY:
            case self::STATE_ENTER_OBJECT:
                $param['parent'][] = $param['current'];
                $param['current'][] = [];

                end($param['current']);                
                $param['current'] = &$param['current'][key($param['current'])];

                //$param['current'] = &$param['current'];


                return true;
            case self::STATE_LEAVE_ARRAY:
            case self::STATE_LEAVE_OBJECT:
                unset($param['current']);
                $param['current'] = array_pop($param['parent']);
                debug_zval_dump($param['parent']);

                //end($param['current']);

                return true;

            case self::STATE_IN_OBJ_FIELD:
                $param .= '"'.$new_state['val'].'":';
                return true;
            case self::STATE_IN_OBJ_VALUE:
            case self::STATE_IN_ARRAY:
            {
                switch ($new_state['type']) {
                case binson::TYPE_BOOLEAN:
                    $param .= var_export($new_state['val'], true);
                    return true;
                case binson::TYPE_DOUBLE:
                case binson::TYPE_INTEGER:
                    $param .= $new_state['val'];
                    return true;
                case binson::TYPE_STRING:
                    $param .= '"'.$new_state['val'].'"';
                    return true;
                case binson::TYPE_BYTES:
                    $param .= '"'.bin2hex($new_state['val']).'"';
                    return true;
    
                default: /* we should not get here */
                    throw new BinsonException(binson::ERROR_WRONG_TYPE, "unsupported type detected");
                }
            }
            case self::STATE_DONE:
            case self::STATE_UNDEFINED;
                return true;

            default:
                throw new BinsonException(binson::ERROR_STATE, "unsupported state");
        }
        return true;        
    }

    private function cbToString(?array $prev_state, &$param = null) : bool
    {        
        if (!is_string($param))
            throw new BinsonException(binson::ERROR_WRONG_TYPE, "cbToString() require `string` parameter");

        $new_state = $this->state['top'];
        $parent_state = $this->state['parent'];
        $depth = $this->depth;
                
        switch ($new_state['id']) {
            case self::STATE_ENTER_ARRAY:
                $param .= ($parent_state['id'] & self::STATE_MASK_INNER)? ',' : '';
                $param .= '[';
                return true;
            case self::STATE_ENTER_OBJECT:
                $param .= ($parent_state['id'] & self::STATE_MASK_INNER)? ',' : '';
                $param .= '{';
                return true;
            case self::STATE_LEAVE_ARRAY:
                $param .= ']';
                return true;
            case self::STATE_LEAVE_OBJECT:
                $param .= '}';
                return true;
            case self::STATE_IN_OBJ_FIELD:
                $param .= '"'.$new_state['val'].'":';
                return true;
            case self::STATE_IN_OBJ_VALUE:
            case self::STATE_IN_ARRAY:
            {
                // totally ignore "unsupported" end types here            
                if (!($new_state['type'] & self::TYPE_MASK_PRIMITIVE))
                    return true;

                $param .= ($prev_state['id'] & self::STATE_MASK_INNER) ? ',' : '';
                switch ($new_state['type']) {
                case binson::TYPE_BOOLEAN:
                case binson::TYPE_DOUBLE:
                case binson::TYPE_INTEGER:
                    $param .= var_export($new_state['val'], true);
                    return true;
                case binson::TYPE_STRING:
                    $param .= '"'.$new_state['val'].'"';
                    return true;
                case binson::TYPE_BYTES:
                    $param .= '"'.bin2hex($new_state['val']).'"';
                    return true;
    
                default: /* we should not get here */
                    break;
                    //throw new BinsonException(binson::ERROR_WRONG_TYPE, 
                    //        "unsupported type detected: ".$new_state['type']);
                }
            }
            case self::STATE_DONE:
            case self::STATE_UNDEFINED;
                return true;

            default:
                throw new BinsonException(binson::ERROR_STATE, "unsupported state");
        }
        return true;
    }

    private function cbDebug1(array $prev_state, &$param = null) : bool
    {
        /*switch ($this->state['id']) {
            self::STATE
        }*/
        $new_state = $this->state['top'];
        $d = $this->depth;
        $idx = $this->idx;
        echo "idx:$idx\t, d:$d, ".json_encode($new_state).PHP_EOL;
        return true;

/*        private const STATE_UNDEFINED              = 0x0000;
        private const STATE_ENTER_OBJECT           = 0x0001;
        private const STATE_IN_OBJ_FIELD = 0x0002;
        private const STATE_IN_OBJ_VALUE = 0x0004;
        //const STATE_IN_OBJECT              = STATE_IN_OBJ_FIELD | STATE_IN_OBJ_VALUE;//0x0003;
        private const STATE_LEAVE_OBJECT           = 0x0008;
        private const STATE_ENTER_ARRAY            = 0x0010;
        private const STATE_IN_ARRAY               = 0x0020;
        private const STATE_LEAVE_ARRAY            = 0x0040;
    
        private const STATE_MASK_OBJECT    = self::STATE_ENTER_OBJECT | self::STATE_IN_OBJ_FIELD |
                                             self::STATE_IN_OBJ_VALUE | self::STATE_LEAVE_OBJECT; 
        private const STATE_MASK_ARRAY     = self::STATE_ENTER_ARRAY |
                                             self::STATE_IN_ARRAY | self::STATE_LEAVE_ARRAY;
    
    
        private const STATE_MASK_ENTER     = self::STATE_ENTER_ARRAY | self::STATE_ENTER_OBJECT;
        private const STATE_MASK_LEAVE     = self::STATE_LEAVE_ARRAY | self::STATE_LEAVE_OBJECT;
        */
    }

    private function cbLoggerOutput($new_state_flags, &$param) : bool
    {
        $new_state = $this->stateRef(BINSON_STATE_CURRENT);
        //$prev_state = $this->stateRef(BINSON_STATE_PREV);

        switch ($new_state_flags) {
        case PARSER_STATE_BLOCK:
            $this->logger->debug($new_state['type'] === binson::BINSON_ID_OBJECT? '{' : '[');
            //$new_state['dst_ref'][] = [];
            return true;
        case PARSER_STATE_BLOCK_END:
            $this->logger->debug($new_state['type'] === binson::BINSON_ID_OBJECT? '}' : ']');
            //$new_state['dst_ref'] = $prev_state['dst_ref'] 
            return true;
        case PARSER_STATE_NAME:
            $this->logger->debug('"'.$new_state['name'].'":');
            return true;

        case PARSER_STATE_VAL:
            switch ($new_state['type']) {
            case BINSON_ID_BOOLEAN:
            case BINSON_ID_DOUBLE:
            case BINSON_ID_INTEGER:
                $this->logger->debug($new_state['val']);
                return true;
            case BINSON_ID_STRING:
                $this->logger->debug('"'.$new_state['val'].'"');
                return true;
            case BINSON_ID_BYTES:
                $this->logger->debug('"'.bin2hex($new_state['val']).'"');            
                return true;

            default: /* we should not get here */
                return true;
            }
            break;

        default:
            return true; /* do nothing */
        }

        return true;
    }


    private function parseNumeric(string $chunk, bool $is_float = false)
    {
        $len = strlen($chunk);
        $filler = chr(ord($chunk[-1]) & 0x80 ? 0xff : 0x00);
        $chunk = str_pad($chunk, 8, $filler);
        
        $val = unpack($is_float? 'e' : 'P', $chunk);
        $v = $val[1];  // for beter code readability only        

        if (is_float($v))
        {
            if (is_float($v))
                return $v;
            else
                throw new BinsonException(binson::ERROR_FORMAT);
        }

        if ($len == 1 && ($v >= binson::INT8_MIN && $v <= binson::INT8_MAX))
            return $v;
        else if ($len == 2 && ($v < binson::INT8_MIN || $v > binson::INT8_MAX))
            return $v;
        else if ($len == 4 && ($v < binson::INT16_MIN || $v > binson::INT16_MAX))
            return $v;
        else if ($len == 8 && ($v < binson::INT32_MIN || $v > binson::INT32_MAX))
            return $v;

        throw new BinsonException(binson::ERROR_FORMAT);
    }



 

    private function consume(int $size, bool $peek = false) : string
    {
        $chunk = substr($this->data, $this->idx, $size);
        
        if (empty($chunk))
            throw new BinsonException(binson::ERROR_RANGE);

        if (!$peek)
            $this->idx += $size;

        return $chunk;
    }


}


function util_pack_size($val, int $type_hint) : string
{
    $val_bytes = array_fill(0, 9, 0);
    $size = 0;
    $val_unpack_code = 'P'; // 64bit unsigned LE

    switch ($type_hint)
    {
        case binson::TYPE_INTEGER:
            $val_bytes[0] = binson::DEF_INT8; break;            
        case binson::TYPE_DOUBLE:
            $val_bytes[0] = binson::DEF_DOUBLE; 
            $val_unpack_code = 'e'; // 64bit double LE
            break;
        case binson::TYPE_STRING:
            $val_bytes[0] = binson::DEF_STRLEN_INT8; break;
        case binson::TYPE_BYTES:
            $val_bytes[0] = binson::DEF_BYTESLEN_INT8; break;

        default: break;
    }


    if ($type_hint == binson::TYPE_DOUBLE) {
        $size = 8;
    }
    else {
        if (($val >= binson::INT8_MIN) && ($val <= binson::INT8_MAX)) {
            $size = 1; // sizeof(int8_t);
        }
        else if (($val >= binson::INT16_MIN) && ($val <= binson::INT16_MAX)) {
            $val_bytes[0] += 1;
            $size = 2; // sizeof(int16_t);
        }
        else if (($val >= binson::INT32_MIN) && ($val <= binson::INT32_MAX)) {
            $val_bytes[0] += 2;
            $size = 4; // sizeof(int32_t);
        }
        else {
            $size = 8; // sizeof(int64_t);
            $val_bytes[0] += 3;
        }
    }

    return chr($val_bytes[0]) . substr(pack($val_unpack_code, $val), 0, $size);
}

?>
