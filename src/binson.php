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

    // for debugging only
    public const DBG_INT_TO_TYPE_MAP = [
        0x0000 => 'TYPE_NONE',
        0x0001 => 'TYPE_OBJECT',
        0x0002 => 'TYPE_OBJECT_END',
        0x0004 => 'TYPE_ARRAY',
        0x0008 => 'TYPE_ARRAY_END',
        0x0010 => 'TYPE_BOOLEAN',
        0x0020 => 'TYPE_INTEGER',
        0x0040 => 'TYPE_DOUBLE',
        0x0080 => 'TYPE_STRING',        
        0x0100 => 'TYPE_BYTES'
    ];

    const ERROR_NONE           = 0;
    const ERROR_RANGE          = 1;
    const ERROR_FORMAT         = 2;
    const ERROR_EOF            = 3;
    const ERROR_END_OF_BLOCK   = 4;
    const ERROR_NULL           = 5;
    const ERROR_STATE          = 6;
    const ERROR_WRONG_TYPE     = 7;
    const ERROR_MAX_DEPTH      = 8;
    const ERROR_ARG            = 9;
    const ERROR_INT_OVERFLOW   = 10;

    const CFG_DEFAULT  = [
        'max_raw_size' => 40*1000000, 
        'max_name_len' => 1024,
        'max_string_len' => 10240,
        'max_bytes_len' => 10240,
        'max_field_count' => 1000,
        'parser_int_overflow_action' => 'exception' // [exception|to_float]
    ];
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
            case binson::ERROR_ARG:          $msg = '[Wrong argument]'; break;
            case binson::ERROR_INT_OVERFLOW: $msg = '[Integer overflow]'; break;

            default: 
                $msg = 'Unknown binson exception, code: ' . $exc_code; break;
       }

       $msg .= $message? ', more: ' . $message : '';
       parent::__construct($msg, $code, $previous);
    }
}

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
    public $config;    
    private $data_len;
	private $data;
    
    public function __construct(string &$dst = null)
    {
        $this->config = binson::CFG_DEFAULT;
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
        $type_stack = [];
        $block_type = -1;

        foreach($iterator as $key => $value) {
            
            $depth = $iterator->getDepth();   
          
            while ($depth < $last_depth)
            {                
                $block_type = array_pop($type_stack);
                $res = ($block_type == binson::TYPE_ARRAY) ? $this->arrayEnd() : $this->objectEnd();                
                $last_depth--;
            }
          
            if ($block_type == -1 && $depth == 0)
                $block_type = (is_int($key) && $key === 0) ? binson::TYPE_ARRAY : binson::TYPE_OBJECT;
            
            if ($depth > $last_depth) {  // new block detected
                $block_type = (!is_int($key)) ? binson::TYPE_OBJECT : binson::TYPE_ARRAY;       
                $res = ($block_type == binson::TYPE_ARRAY) ? $this->arrayBegin() : $this->objectBegin();
                $type_stack[] = $block_type;
                
                //continue;
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

            ////if ($last_depth - $depth > 1)
             //$last_depth--;
            //else 
             $last_depth = $depth;
             
        }

        while ($block_type = array_pop($type_stack))
        {            
            $res = ($block_type == binson::TYPE_ARRAY) ? $this->arrayEnd() : $this->objectEnd();
            //$last_depth--;
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
        if ($offset === null) {            
            $this->data[$this->bp->depth] = $value;
            //echo "State update. Depth: {$this->bp->depth}. ".json_encode($value).PHP_EOL;
        } else {
            $this->data[$this->bp->depth][$offset] = $value;
            //echo "State update. Depth: {$this->bp->depth}. $offset => ($value)".PHP_EOL;
        }
    }

    public function offsetExists($offset) {
        return isset($this->data[$this->bp->depth][$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->data[$this->bp->depth][$offset]);
    }
}


class BinsonParser
{
    // for debugging only
    private const DBG_INT_TO_STATE_MAP = [
        0x0001 => 'STATE_UNDEFINED',
        0x0002 => 'STATE_AT_OBJECT_',
        0x0004 => 'STATE_AT_ARRAY_',
        0x0008 => 'STATE_AT_ITEM_KEY',
        0x0010 => 'STATE_AT_VALUE',
        0x0020 => 'STATE_IN_OBJECT_BEGIN',
        0x0040 => 'STATE_IN_OBJECT_END_',
        0x0080 => 'STATE_IN_ARRAY_BEGIN',
        0x0100 => 'STATE_IN_ARRAY_END_',
        0x0200 => 'STATE_OUTOF_OBJECT',
        0x0400 => 'STATE_OUTOF_ARRAY',
        0x0800 => 'STATE_DONE',
        0x1000 => 'STATE_ERROR',
        0x2000 => 'STATE_NO_RULE'
    ];

    // Suffix "_" means helper state: no new data required to make 
    // transition from current state to next state
    private const STATE_UNDEFINED       = 0x0001;  // before any parsing
    private const STATE_AT_OBJECT_      = 0x0002;  // positioned at object start
    private const STATE_AT_ARRAY_       = 0x0004;  // positioned at array start
    private const STATE_AT_ITEM_KEY     = 0x0008;  // positioned at "name" of "name:value" pair
    private const STATE_AT_VALUE        = 0x0010;  // positioned at primitive value of array or "name:value" pair
    private const STATE_IN_OBJECT_BEGIN = 0x0020;  // just entered current object
    private const STATE_IN_OBJECT_END_  = 0x0040;  // end of object detected    
    private const STATE_IN_ARRAY_BEGIN  = 0x0080;  // just entered current array
    private const STATE_IN_ARRAY_END_   = 0x0100;  // end of array detected
    private const STATE_OUTOF_OBJECT    = 0x0200;  // just leaved object, but not moved further
    private const STATE_OUTOF_ARRAY     = 0x0400;  // just leaved array, but not moved further

    private const STATE_DONE            = 0x0800;
    private const STATE_ERROR           = 0x1000;
    private const STATE_NO_RULE         = 0x2000;  // missing state transition rule

    private const STATE_MASK_NEED_INPUT = self::STATE_UNDEFINED | 
                                          self::STATE_AT_VALUE | self::STATE_AT_ITEM_KEY | 
                                          self::STATE_IN_OBJECT_BEGIN | self::STATE_IN_ARRAY_BEGIN |
                                          self::STATE_OUTOF_OBJECT | self:: STATE_OUTOF_ARRAY;
    private const STATE_MASK_HELPER = self::STATE_AT_OBJECT_ | self::STATE_AT_ARRAY_ |
                                      self::STATE_IN_OBJECT_END_ | self::STATE_IN_ARRAY_END_;

    /* states are ok to stop on, when ADVANCE_NEXT is applied  */
    private const STATE_MASK_NEXT = self::STATE_AT_OBJECT_ | self::STATE_AT_ARRAY_ |
                                    self::STATE_AT_VALUE  |
                                    self::STATE_IN_OBJECT_END_ | self::STATE_IN_ARRAY_END_;

    // EndOfBlock                                        
    private const STATE_MASK_EOB = self::STATE_IN_OBJECT_END_ | self::STATE_IN_ARRAY_END_;

    private const STATE_MASK_EXIT   = self::STATE_DONE | self::STATE_ERROR | self::STATE_NO_RULE;

    const TYPE_MASK_VALUE     = binson::TYPE_BOOLEAN | binson::TYPE_INTEGER |
                                binson::TYPE_DOUBLE | binson::TYPE_STRING | binson::TYPE_BYTES;
    

    const ADVANCE_ONE           = 0x01;  /* one step, traversal */
    const ADVANCE_NEXT          = 0x02;  /* traversal until depth become same as initial */
    const ADVANCE_LEAVE_BLOCK   = 0x04;  /* traversal until depth become less than initial */

    /* Priority=2. Default state transition matrix */
    private const BLOCK_TYPE_TO_STATE_MX = [
        binson::TYPE_NONE => [ // top level
            self::STATE_AT_OBJECT_      =>  self::STATE_IN_OBJECT_BEGIN,            
            self::STATE_AT_ARRAY_       =>  self::STATE_IN_ARRAY_BEGIN,            
            self::STATE_OUTOF_OBJECT    =>  self::STATE_DONE,
            self::STATE_OUTOF_ARRAY     =>  self::STATE_DONE,          
            self::STATE_DONE            =>  self::STATE_DONE,
            self::STATE_ERROR           =>  self::STATE_ERROR
        ],
        binson::TYPE_OBJECT => [
            self::STATE_UNDEFINED       =>  self::STATE_ERROR,
            self::STATE_AT_OBJECT_       =>  self::STATE_IN_OBJECT_BEGIN,
            self::STATE_AT_ARRAY_        =>  self::STATE_IN_ARRAY_BEGIN,
            self::STATE_AT_ITEM_KEY      =>  self::STATE_AT_VALUE,
            self::STATE_AT_VALUE         =>  self::STATE_AT_ITEM_KEY,
            self::STATE_IN_OBJECT_BEGIN =>  self::STATE_AT_ITEM_KEY,
            self::STATE_IN_OBJECT_END_   =>  self::STATE_OUTOF_OBJECT,
            self::STATE_IN_ARRAY_BEGIN  =>  self::STATE_ERROR,
            self::STATE_IN_ARRAY_END_    =>  self::STATE_ERROR,
            self::STATE_OUTOF_OBJECT    =>  self::STATE_AT_ITEM_KEY,
            self::STATE_OUTOF_ARRAY     =>  self::STATE_AT_ITEM_KEY,          
            self::STATE_DONE            =>  self::STATE_DONE,
            self::STATE_ERROR           =>  self::STATE_ERROR
        ],
        binson::TYPE_ARRAY => [
            self::STATE_UNDEFINED       =>  self::STATE_ERROR,
            self::STATE_AT_OBJECT_       =>  self::STATE_IN_OBJECT_BEGIN,
            self::STATE_AT_ARRAY_        =>  self::STATE_IN_ARRAY_BEGIN,
            self::STATE_AT_ITEM_KEY     =>  self::STATE_ERROR,
            self::STATE_AT_VALUE         =>  self::STATE_AT_VALUE,
            self::STATE_IN_OBJECT_BEGIN =>  self::STATE_ERROR,
            self::STATE_IN_OBJECT_END_   =>  self::STATE_ERROR,
            self::STATE_IN_ARRAY_BEGIN  =>  self::STATE_AT_VALUE,
            self::STATE_IN_ARRAY_END_    =>  self::STATE_OUTOF_ARRAY,
            self::STATE_OUTOF_OBJECT    =>  self::STATE_AT_VALUE,
            self::STATE_OUTOF_ARRAY     =>  self::STATE_AT_VALUE,          
            self::STATE_DONE            =>  self::STATE_DONE,
            self::STATE_ERROR           =>  self::STATE_ERROR            
        ]
    ];

    /* Priority=1. Default state transition matrix: maps newly consumed chunk's type to new state */
    private const NEW_TYPE_TO_STATE_MX = [   
        binson::TYPE_OBJECT => [
            self::STATE_UNDEFINED       =>  self::STATE_AT_OBJECT_,
            self::STATE_AT_ITEM_KEY     =>  self::STATE_AT_OBJECT_,
            self::STATE_AT_VALUE        =>  [binson::TYPE_OBJECT => self::STATE_AT_OBJECT_,
                                                binson::TYPE_ARRAY  => self::STATE_AT_OBJECT_],
            self::STATE_IN_OBJECT_BEGIN =>  self::STATE_ERROR,
            self::STATE_IN_ARRAY_BEGIN  =>  self::STATE_AT_OBJECT_,
            self::STATE_OUTOF_OBJECT    =>  [binson::TYPE_OBJECT => self::STATE_ERROR,
                                                binson::TYPE_ARRAY  => self::STATE_AT_OBJECT_],
            self::STATE_OUTOF_ARRAY     =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                                binson::TYPE_ARRAY  => self::STATE_AT_OBJECT_] 
        ],                            
        binson::TYPE_OBJECT_END => [
            self::STATE_UNDEFINED       =>  self::STATE_ERROR,
            self::STATE_AT_ITEM_KEY     =>  self::STATE_ERROR,
            self::STATE_AT_VALUE        =>  [binson::TYPE_OBJECT => self::STATE_IN_OBJECT_END_,      
                                                binson::TYPE_ARRAY  => self::STATE_ERROR],
            self::STATE_IN_OBJECT_BEGIN =>  self::STATE_IN_OBJECT_END_,
            self::STATE_IN_ARRAY_BEGIN  =>  self::STATE_ERROR,
            self::STATE_OUTOF_OBJECT    =>  [binson::TYPE_OBJECT => self::STATE_IN_OBJECT_END_,      
                                                binson::TYPE_ARRAY  => self::STATE_ERROR],
            self::STATE_OUTOF_ARRAY     =>  [binson::TYPE_OBJECT => self::STATE_IN_OBJECT_END_,      
                                                binson::TYPE_ARRAY  => self::STATE_ERROR]
        ],                
        binson::TYPE_ARRAY => [
            self::STATE_UNDEFINED       =>  self::STATE_AT_ARRAY_,
            self::STATE_AT_ITEM_KEY     =>  self::STATE_AT_ARRAY_,
            self::STATE_AT_VALUE        =>  [binson::TYPE_OBJECT => self::STATE_AT_ARRAY_,
                                             binson::TYPE_ARRAY  => self::STATE_AT_ARRAY_],
            self::STATE_IN_OBJECT_BEGIN =>  self::STATE_ERROR,
            self::STATE_IN_ARRAY_BEGIN  =>  self::STATE_AT_ARRAY_,
            self::STATE_OUTOF_OBJECT    =>  [binson::TYPE_OBJECT => self::STATE_ERROR,
                                             binson::TYPE_ARRAY  => self::STATE_AT_ARRAY_],
            self::STATE_OUTOF_ARRAY     =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_ARRAY_]      
        ],
        binson::TYPE_ARRAY_END => [
            self::STATE_UNDEFINED       =>  self::STATE_ERROR,
            self::STATE_AT_ITEM_KEY     =>  self::STATE_ERROR,
            self::STATE_AT_VALUE        =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_IN_ARRAY_END_],
            self::STATE_IN_OBJECT_BEGIN =>  self::STATE_ERROR,
            self::STATE_IN_ARRAY_BEGIN  =>  self::STATE_IN_ARRAY_END_,
            self::STATE_OUTOF_OBJECT    =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_IN_ARRAY_END_],
            self::STATE_OUTOF_ARRAY     =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_IN_ARRAY_END_]
        ],
        binson::TYPE_BOOLEAN => [// same as bool, int, double, bytes
            self::STATE_UNDEFINED       =>  self::STATE_ERROR,
            self::STATE_AT_ITEM_KEY     =>  self::STATE_AT_VALUE,
            self::STATE_AT_VALUE        =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE],
            self::STATE_IN_OBJECT_BEGIN =>  self::STATE_ERROR,
            self::STATE_IN_ARRAY_BEGIN  =>  self::STATE_AT_VALUE,
            self::STATE_OUTOF_OBJECT    =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE],
            self::STATE_OUTOF_ARRAY     =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE]            
        ],
        binson::TYPE_INTEGER => [// same as bool, int, double, bytes
            self::STATE_UNDEFINED       =>  self::STATE_ERROR,
            self::STATE_AT_ITEM_KEY     =>  self::STATE_AT_VALUE,
            self::STATE_AT_VALUE        =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE],
            self::STATE_IN_OBJECT_BEGIN =>  self::STATE_ERROR,
            self::STATE_IN_ARRAY_BEGIN  =>  self::STATE_AT_VALUE,
            self::STATE_OUTOF_OBJECT    =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE],
            self::STATE_OUTOF_ARRAY     =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE]            
        ],                            
        binson::TYPE_DOUBLE => [// same as bool, int, double, bytes
            self::STATE_UNDEFINED       =>  self::STATE_ERROR,
            self::STATE_AT_ITEM_KEY     =>  self::STATE_AT_VALUE,
            self::STATE_AT_VALUE        =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE],
            self::STATE_IN_OBJECT_BEGIN =>  self::STATE_ERROR,
            self::STATE_IN_ARRAY_BEGIN  =>  self::STATE_AT_VALUE,
            self::STATE_OUTOF_OBJECT    =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE],
            self::STATE_OUTOF_ARRAY     =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE]            
        ],
        binson::TYPE_STRING => [
            self::STATE_UNDEFINED       =>  self::STATE_ERROR,
            self::STATE_AT_ITEM_KEY     =>  self::STATE_AT_VALUE,
            self::STATE_AT_VALUE        =>  [binson::TYPE_OBJECT => self::STATE_AT_ITEM_KEY,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE],
            self::STATE_IN_OBJECT_BEGIN =>  self::STATE_AT_ITEM_KEY,
            self::STATE_IN_ARRAY_BEGIN  =>  self::STATE_AT_VALUE,
            self::STATE_OUTOF_OBJECT    =>  [binson::TYPE_OBJECT => self::STATE_AT_ITEM_KEY,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE],
            self::STATE_OUTOF_ARRAY     =>  [binson::TYPE_OBJECT => self::STATE_AT_ITEM_KEY,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE]            
        ],
        binson::TYPE_BYTES => [ // same as bool, int, double, bytes
            self::STATE_UNDEFINED       =>  self::STATE_ERROR,
            self::STATE_AT_ITEM_KEY     =>  self::STATE_AT_VALUE,
            self::STATE_AT_VALUE        =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE],
            self::STATE_IN_OBJECT_BEGIN =>  self::STATE_ERROR,
            self::STATE_IN_ARRAY_BEGIN  =>  self::STATE_AT_VALUE,
            self::STATE_OUTOF_OBJECT    =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE],
            self::STATE_OUTOF_ARRAY     =>  [binson::TYPE_OBJECT => self::STATE_ERROR,      
                                             binson::TYPE_ARRAY  => self::STATE_AT_VALUE]            
        ]
   ];


    /* public data members */
    public $config;
    public $depth;
    //public $type;

    /* private data members */
    private $data;
    private $idx;
    private $state;

    private $logger;


    public function __construct(string &$src)
    {
        $this->config = binson::CFG_DEFAULT;
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
        unset($this->state);
        $this->state = new BinsonParserStateStack($this);
        $this->state[] = ['id' => self::STATE_UNDEFINED, 'block_type' => binson::TYPE_NONE];
    }

    public function dump() : string
    {
        return print_r($this, true);
    }

    public function verify() : bool
    {
        $is_valid = true;

        $saved_depth = $this->depth;
        $saved_idx = $this->idx;
        $saved_state = $this->state;

        // add validation function
        try {
            $this->reset();
            $res = $this->advance(self::ADVANCE_ONE, null, binson::TYPE_OBJECT | binson::TYPE_ARRAY);
            if ($res)
                $res = $this->advance(self::ADVANCE_LEAVE_BLOCK);
        }
        catch (Error $err)
        {
            // write to log
            $is_valid = false;
        }
        finally
        {
            $is_valid = $is_valid && $res && $this->isDone();
            
            // restore parser state
            $this->reset();
            $saved_depth = $this->depth;
            $saved_idx = $this->idx;
            $saved_state = $this->state;

            return $is_valid;
        }
    }

    public function enterObject() : BinsonParser
    {
        $this->advance(self::ADVANCE_ONE, null, binson::TYPE_OBJECT);
        return $this;
    }

    public function enterArray() : BinsonParser
    {
        $this->advance(self::ADVANCE_ONE, null, binson::TYPE_ARRAY);
        return $this;
    }

    public function leaveObject() : BinsonParser
    {   
        $this->advance(self::ADVANCE_LEAVE_BLOCK);  // more checks
        return $this;
    }

    public function leaveArray() : BinsonParser
    {
        $this->advance(self::ADVANCE_LEAVE_BLOCK);  // more checks
        return $this;
    }

    public function next() : bool
    {
        return $this->advance(self::ADVANCE_NEXT);  // more checks
    }

    public function ensure(int $type) : bool
    {
        return $this->getType() === $type;
    }

    public function isDone() : bool
    {
        return $this->state['id'] === self::STATE_DONE;
    }

    public function field(string $name) : bool
    {        
        if (is_null($name))
            throw new BinsonException(binson::ERROR_NULL);

        while ($this->advance(self::ADVANCE_NEXT, $name))  //??  why pass name to advance?
        {
            $r = $name <=>  $this->state['name'];
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

    public function getType()
    {
        return $this->state['type'];
    }

    public function getBlockType() : int
    {
        return $this->state['block_type'];
    }

    public function getValue(int $ensure_type = binson::TYPE_NONE)
    {
        $state = $this->state['top'];
        if ($ensure_type != binson::TYPE_NONE && $ensure_type != $state['type']) 
            throw new BinsonException(binson::ERROR_WRONG_TYPE);

        return $state['val'];
    }

    public function getRaw() : string
    {  
        // later
    }


    /*======= Private method implementations ====================================*/
    public function tostr() : string
    {
        $str = '';
        $this->advance(self::ADVANCE_TRAVERSAL, null, 0, [$this, 'cbToString'], $str);
        return $str;
    }

    private function requestStateTransition() : array
    {
        //$pad = "d:{$this->depth} ".str_repeat(" ", $this->depth * 5);   /* DBG */

        $state = $this->state['top'];
        $state_update = [];

        if ($state['id'] & self::STATE_MASK_NEED_INPUT)
        {   
            //echo $pad."current state: ".self::DBG_INT_TO_STATE_MAP[$state['id']]." - NEED_INPUT".PHP_EOL;
            $state_update = $this->processOne();
            //echo $pad."processOne() -> type: ".binson::DBG_INT_TO_TYPE_MAP[$state_update['type']].PHP_EOL;

            $new_state_id = self::STATE_NO_RULE;
            if (isset(self::NEW_TYPE_TO_STATE_MX[$state_update['type']]
                        [$state['id']]
                        [$this->getBlockType()]))
                
                $new_state_id =  self::NEW_TYPE_TO_STATE_MX[$state_update['type']]
                                                           [$state['id']]
                                                           [$this->getBlockType()];

            //echo $pad."try NEW_TYPE_TO_STATE_MX[".binson::DBG_INT_TO_TYPE_MAP[$state_update['type']]."][".
            //        self::DBG_INT_TO_STATE_MAP[$state['id']]."][".
            //        binson::DBG_INT_TO_TYPE_MAP[$this->getBlockType()]." -> ".
            //        self::DBG_INT_TO_STATE_MAP[$new_state_id].PHP_EOL;

            if ($new_state_id === self::STATE_NO_RULE)
            {
                if (isset(self::NEW_TYPE_TO_STATE_MX[$state_update['type']][$state['id']]))
                    $new_state_id = self::NEW_TYPE_TO_STATE_MX[$state_update['type']][$state['id']];

            //    echo $pad."try NEW_TYPE_TO_STATE_MX[".binson::DBG_INT_TO_TYPE_MAP[$state_update['type']].
            //                        "][".self::DBG_INT_TO_STATE_MAP[$state['id']]
            //                        ."] -> ".self::DBG_INT_TO_STATE_MAP[$new_state_id].PHP_EOL;
            }
            $state_update['id'] = $new_state_id;
        }
        else
        {
            if (isset(self::BLOCK_TYPE_TO_STATE_MX[$this->getBlockType()][$state['id']]))
            {
                $new_state_id = self::BLOCK_TYPE_TO_STATE_MX[$this->getBlockType()][$state['id']];
            //    echo $pad."try BLOCK_TYPE_TO_STATE_MX[".binson::DBG_INT_TO_TYPE_MAP[$this->getBlockType()]
            //                ."][".self::DBG_INT_TO_STATE_MAP[$state['id']]
            //                ."] -> ".self::DBG_INT_TO_STATE_MAP[$new_state_id].PHP_EOL;
            }
            $state_update['id'] = $new_state_id;
        }

        if ($state_update['id'] & self::STATE_AT_ITEM_KEY)
            $state_update['name'] = $state_update['val'];

        //if ($state_update['id'] & self::STATE_AT_VALUE)
        //    $state_update[''] = $state_update['val'];
    

        //echo $pad."requestStateTransition() -> ".json_encode($state_update).PHP_EOL;
        //echo $pad."-------------------------------".PHP_EOL;

        return $state_update;
    }

    private function callbackWrapper(?callable $cb, array $state_update, &$param = null) : bool
    {
        $prev_state = $this->state['top'];
        $this->state[] = $state_update; // copy id, type, value
        return $cb? $cb($prev_state, $cb_param) : false;        
    }

    private function advance(int $scan_mode, ?string $scan_name = null, int $ensure_type = null,
                             ?callable $cb = null, &$cb_param = null) : bool
    {
        $orig_depth = $this->depth;

        while (true) {  /* scanning loop */
            if ($this->state['id'] & self::STATE_MASK_EXIT)
                return false;
            
            $state_req = $this->requestStateTransition();
            $update_req = array_replace($this->state['top'], $state_req);
            $cb_called = false;

            switch ($state_req['id']) {
                case self::STATE_ERROR:
                    $this->state[] = $update_req;
                    throw new BinsonException(binson::ERROR_FORMAT, $this->dump());
                
                case self::STATE_NO_RULE:
                    $this->state[] = $update_req;
                    throw new BinsonException(binson::ERROR_FORMAT, $this->dump());

                case self::STATE_DONE:
                    $this->state[] = $update_req;
                    return true;                        

                case self::STATE_AT_OBJECT_:
                case self::STATE_AT_ARRAY_:
                case self::STATE_AT_ITEM_KEY:
                case self::STATE_AT_VALUE:
                case self::STATE_IN_OBJECT_END_:
                case self::STATE_IN_ARRAY_END_:                
                    break;

                case self::STATE_AT_VALUE:
                    //$update_req['val'] = $update_req['val'];
                    //$this->state[] = $update_req;    
                    break;

                case self::STATE_IN_OBJECT_BEGIN:
                case self::STATE_IN_ARRAY_BEGIN:
                    $this->depth++;
                    $update_req['block_type'] = $update_req['type'];
                    $this->state[] = $update_req;
                    break;
                    
                case self::STATE_OUTOF_OBJECT:
                case self::STATE_OUTOF_ARRAY:
                    $this->callbackWrapper($cb, $update_req, $cb_param);            
                    $this->depth--;
                    $this->state['id'] = $state_req['id'];
                    $cb_called = true;
                    break;

                default:
                    throw new BinsonException(binson::ERROR_STATE, "???");
                }

                if (!$cb_called)
                    $this->callbackWrapper($cb, $update_req, $cb_param);
    
                switch ($scan_mode) {
                case self::ADVANCE_ONE:
                    if ($this->state['id'] & self::STATE_MASK_HELPER)
                        break;
                    else
                        return true;
                case self::ADVANCE_LEAVE_BLOCK:                
                    if ($this->depth === 0)
                        $this->state['id'] = self::STATE_DONE; // fix this

                    if ($this->depth === 0 || $this->depth < $orig_depth)
                        return true;
                    break;
                case self::ADVANCE_NEXT:                
                    if ($this->state['id'] & self::STATE_MASK_NEXT && $this->depth === $orig_depth)
                            return ($this->state['id'] & self::STATE_MASK_EOB) ? false : true;
                    break;
                default:
                    throw new BinsonException(binson::ERROR_ARG);
                }                
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
                //$param .= ($_state['id'] & self::STATE_MASK_INNER)? ',' : '';
                return true;
            case self::STATE_LEAVE_OBJECT:
                $param .= '}';
                //$param .= ($parent_state['id'] & self::STATE_MASK_INNER)? ',' : '';
                return true;
            case self::STATE_IN_OBJ_FIELD:
                $param .= '"'.$new_state['val'].'":';
                return true;
            case self::STATE_IN_OBJ_VALUE:
            case self::STATE_IN_ARRAY:
            {
                // totally ignore "unsupported" end types here            
                if (!($new_state['type'] & self::TYPE_MASK_VALUE))
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
        //$new_state = $this->state['top'];
        //$d = $this->depth;
        //$idx = $this->idx;
        //echo "idx:$idx\t, d:$d, ".json_encode($new_state).PHP_EOL;
        return true;
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

        if ($len == 8 && !$is_float && PHP_INT_SIZE < 8)
        {   
            if ($this->config['parser_int_overflow_action'] !== 'to_float')
            {
                //var_dump($this->config['parser_int_overflow_action']);                
                throw new BinsonException(binson::ERROR_INT_OVERFLOW);
            }
                
            // int64 parsing workarount on php32
            $val = unpack('V2', $chunk);
            $combined = (float)$val[1] + (float)$val[2] * 4294967296.0;
            echo $combined.PHP_EOL;  
            if (is_int($combined))          
                echo "!INT!!".PHP_EOL;

            echo bin2hex($filler).PHP_EOL;
            //$res = ($filler === 0x00)? abs($combined) : -abs($combined);
            $res = ($filler === 0x00 && $combined < 0.0) ? -$combined : $combined;
            echo 'to_ret: '.$res.PHP_EOL;
            return $res;
        }

        $val = unpack($is_float? 'e' : (PHP_INT_SIZE < 8? 'V' : 'P'), $chunk);
        $v = $val[1];

        if ($is_float)
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
        if ($size === 0)
            return '';

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
    $val_pack_code = PHP_INT_SIZE > 4? 'P':'V'; // 32bit:64bit unsigned LE

    switch ($type_hint)
    {
        case binson::TYPE_INTEGER:
            $val_bytes[0] = binson::DEF_INT8; break;            
        case binson::TYPE_DOUBLE:
            $val_bytes[0] = binson::DEF_DOUBLE; 
            $val_pack_code = 'e'; // 64bit double LE
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

    return chr($val_bytes[0]) . substr(pack($val_pack_code, $val), 0, $size);
}

?>
