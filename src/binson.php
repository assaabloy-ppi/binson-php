<?php declare(strict_types=1);

/**
 * Public 'binson' definitions
 */
abstract class binson {
    const BINSON_API_VERSION = 'binson_php_v1.0.0r';

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

    const EMPTY_KEY            = -1;
    const EMPTY_VAL            = null;
    const EMPTY_ARRAY          = []; 
    const EMPTY_OBJECT         = [binson::EMPTY_KEY => binson::EMPTY_VAL];

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
        'serializer_sort_fields'  => false
    ];

    /**
     * Used to wrap strings to make it looking like BYTES for serializer
     *
     * @param string $s
     * @return void
     */
    static function BYTES(string $s)
    {
        return (object)$s;
    }

    /**
     * Do nothing, just to have a pair: BYTES() & STRING()
     *
     * @param string $s
     * @return void
     */
    static function STRING(string $s)
    {
        return $s;
    }    
    
    /**
     * Check if PHP variable $var could be directly mapped to binson BYTES type
     *
     * @param [type] $var
     * @return boolean
     */
    static function isBYTES($var) : bool
    {
        return (is_object($var) &&
                $var instanceof stdClass &&
                property_exists($var, 'scalar') &&
                is_string($var->scalar))? true : false;
    }    

    /**
     * Check if PHP variable $var could be directly mapped to binson STRING type
     *
     * @param [type] $var
     * @return boolean
     */
    static function isSTRING($var) : bool
    {
        return is_string($var)? true : false;
    }    
}

/**
 * Binson specific exception class
 */
class BinsonException extends Exception
{    
    /**
     * Constructor
     *
     * @param [type] $code
     * @param string $message
     * @param Throwable $previous
     */
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

/**
 * Check if specified bytearray represents valid binson object.
 *
 * @param string $raw
 * @param array $cfg
 * @return boolean
 */
function binson_verify(string $raw, array $cfg = null) : bool
{
    $parser = new BinsonParser($raw, $cfg);

    try
    {
        return $parser->verify();
    }
    catch (Throwable $t)
    {
        return null;
    }
}

/**
 * Returns a binary string containing binson representation of the supplied array value.
 * (serialization)
 *
 * @param array $src
 * @param array $cfg
 * @return string|null
 */
function binson_encode(array $src, array $cfg = null) : ?string
{   
    $dst = null;
    $writer = new BinsonWriter($dst, $cfg);

    try
    {        
        $writer->put($src);        
        return $writer->toBytes();
    }
    catch (Throwable $t)
    {
        return null;
    }    
}

/**
 * Takes binson encoded binary string and converts it into a PHP variable.
 * (deserialization)
 *
 * @param string $raw
 * @param array $cfg
 * @return array|null
 */
function binson_decode(string $raw, array $cfg = null) : ?array
{
    $parser = new BinsonParser($raw, $cfg);

    try
    {
        return $parser->deserialize();
    }
    catch (Throwable $t)
    {
        return null;
    }
}

/**
 * Abstract parent for both BinsonWriter & BinsonParser
 */
abstract class BinsonProcessor
{
    // Suffix "_" means helper state: no new data required to make 
    // transition from current state to next state
    const STATE_UNDEFINED       = 0x0001;  // before any parsing
    const STATE_AT_OBJECT_      = 0x0002;  // positioned at object start
    const STATE_AT_ARRAY_       = 0x0004;  // positioned at array start
    const STATE_AT_ITEM_KEY     = 0x0008;  // positioned at "name" of "name:value" pair
    const STATE_AT_VALUE        = 0x0010;  // positioned at primitive value of array or "name:value" pair
    const STATE_IN_OBJECT_BEGIN = 0x0020;  // just entered current object
    const STATE_IN_OBJECT_END_  = 0x0040;  // end of object detected    
    const STATE_IN_ARRAY_BEGIN  = 0x0080;  // just entered current array
    const STATE_IN_ARRAY_END_   = 0x0100;  // end of array detected
    const STATE_OUTOF_OBJECT    = 0x0200;  // just leaved object, but not moved further
    const STATE_OUTOF_ARRAY     = 0x0400;  // just leaved array, but not moved further

    const STATE_DONE            = 0x0800;
    const STATE_ERROR           = 0x1000;
    const STATE_NO_RULE         = 0x2000;  // missing state transition rule

    const STATE_MASK_BLOCK_BEGIN = self::STATE_IN_OBJECT_BEGIN | self::STATE_IN_ARRAY_BEGIN;

    const STATE_MASK_NEED_INPUT = self::STATE_UNDEFINED | 
                                        self::STATE_AT_VALUE | self::STATE_AT_ITEM_KEY | 
                                        self::STATE_IN_OBJECT_BEGIN | self::STATE_IN_ARRAY_BEGIN |
                                        self::STATE_OUTOF_OBJECT | self:: STATE_OUTOF_ARRAY;

    /* states are ok to stop on, when ADVANCE_NEXT is applied  */
    const STATE_MASK_NEXT = self::STATE_AT_OBJECT_ | self::STATE_AT_ARRAY_ |
                                self::STATE_AT_VALUE  |
                                self::STATE_IN_OBJECT_END_ | self::STATE_IN_ARRAY_END_;

    const STATE_MASK_EOB = self::STATE_IN_OBJECT_END_ | self::STATE_IN_ARRAY_END_;

    const STATE_MASK_EXIT   = self::STATE_DONE | self::STATE_ERROR | self::STATE_NO_RULE;

    const TYPE_MASK_VALUE     = binson::TYPE_BOOLEAN | binson::TYPE_INTEGER |
                                binson::TYPE_DOUBLE | binson::TYPE_STRING | binson::TYPE_BYTES;
    
    const ADVANCE_ONE           = 0x01;  /* one step, traversal */
    const ADVANCE_NEXT          = 0x02;  /* traversal until depth become same as initial */
    const ADVANCE_LEAVE_BLOCK   = 0x04;  /* traversal until depth become less than initial */
    const ADVANCE_SKIP_BLOCK    = self::ADVANCE_ONE | self::ADVANCE_LEAVE_BLOCK;

    const PRIMITIVE_TYPE_STATE_MX = [
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
    ];

    /* Priority=1. Default state transition matrix: maps newly consumed chunk's type to new state */
     const NEW_TYPE_TO_STATE_MX = [   
        binson::TYPE_OBJECT => [
            self::STATE_UNDEFINED       =>  self::STATE_AT_OBJECT_,
            self::STATE_AT_ITEM_KEY     =>  self::STATE_AT_OBJECT_,
            self::STATE_AT_VALUE        =>  [binson::TYPE_OBJECT => self::STATE_ERROR,
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
            self::STATE_AT_VALUE        =>  [binson::TYPE_OBJECT => self::STATE_ERROR,
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

        binson::TYPE_BOOLEAN => self::PRIMITIVE_TYPE_STATE_MX,
        binson::TYPE_INTEGER => self::PRIMITIVE_TYPE_STATE_MX,      
        binson::TYPE_DOUBLE => self::PRIMITIVE_TYPE_STATE_MX,        
        binson::TYPE_BYTES => self::PRIMITIVE_TYPE_STATE_MX
   ];

    /* Priority=2. Default state transition matrix */
    const BLOCK_TYPE_TO_STATE_MX = [
        binson::TYPE_NONE => [ 
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

    public    $config; 
    public    $depth; 
    protected $state;
    private   $data;

    /**
     * Reset to initial state
     *
     * @return void
     */
    public function reset()
    {
        $this->depth = 0;
        unset($this->state);
        $this->state = new BinsonStateStack($this);
        $this->state[] = ['id' => self::STATE_UNDEFINED, 'block_type' => binson::TYPE_NONE];
    }    

    /**
     * Returns the name of last parsed item
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->state['name'];
    }

    /**
     * Returns the type of last parsed item
     *
     * @return void
     */
    public function getType()
    {
        return $this->state['type'];
    }

    /**
     * Returns current block scope type (TYPE_OBJECT vs TYPE_ARRAY)
     *
     * @return integer
     */
    public function getBlockType() : int
    {
        return $this->state['block_type'];
    }

    /**
     * Returns current value
     *
     * @param integer $ensure_type
     * @return void
     */
    public function getValue(int $ensure_type = binson::TYPE_NONE)
    {
        $state = $this->state['top'];
        if ($ensure_type != binson::TYPE_NONE && $ensure_type != $state['type']) 
            throw new BinsonException(binson::ERROR_WRONG_TYPE);

        return $state['val'];
    }    

    protected function requestStateTransition(callable $data_input_cb) : array
    {
        $state = $this->state['top'];
        $state_update = [];

        if ($state['id'] & self::STATE_MASK_NEED_INPUT)
        {   
            $state_update = $data_input_cb();
            $new_state_id = self::NEW_TYPE_TO_STATE_MX[$state_update['type']]
                                                      [$state['id']]
                                                      [$this->getBlockType()] ?? self::STATE_NO_RULE;

            if ($new_state_id === self::STATE_NO_RULE)
                $new_state_id = self::NEW_TYPE_TO_STATE_MX[$state_update['type']][$state['id']] ??
                                            self::STATE_NO_RULE;

            $state_update['id'] = $new_state_id;
        }
        else
        {
            $new_state_id = self::BLOCK_TYPE_TO_STATE_MX[$this->getBlockType()][$state['id']] ?? 
                                        self::STATE_NO_RULE;
            $state_update['id'] = $new_state_id;
        }

        return $state_update;
    }
}

/**
 * Class used for binson generation
 */
class BinsonWriter extends BinsonProcessor
{
    private $data_len;	

    /**
     * Constructor
     *
     * @param string $dst
     * @param array $cfg
     */
    public function __construct(string &$dst = null, array $cfg = null)
    {
        $this->config = $cfg ?? binson::CFG_DEFAULT;
        $this->data = &$dst ?? '';
        
        if(!is_string($this->data))
            $this->data = '';

        $this->data_len = strlen($this->data);
        parent::reset();
    }

    /**
     * Write new OBJECT begin marker to the output stream
     *
     * @return BinsonWriter
     */
   public function objectBegin() : BinsonWriter
    {
        $this->depth++;
        $this->state['block_type'] = binson::TYPE_OBJECT;
        $this->writeToken(binson::TYPE_OBJECT, binson::DEF_OBJECT_BEGIN);
        return $this;
    }

    /**
     * Write new OBJECT end marker to the output stream
     *
     * @return BinsonWriter
     */
    public function objectEnd() : BinsonWriter
    {
        unset($this->state['name']);        
        $this->depth--;
        $this->writeToken(binson::TYPE_OBJECT_END, binson::DEF_OBJECT_END);
        return $this;
    }

    /**
     * Write new ARRAY begin marker to the output stream
     *
     * @return BinsonWriter
     */
    public function arrayBegin() : BinsonWriter
    {
        $this->depth++;
        $this->state['block_type'] = binson::TYPE_ARRAY;
        $this->writeToken(binson::TYPE_ARRAY, binson::DEF_ARRAY_BEGIN);
    	return $this;
    }

    /**
     * Write new ARRAY end marker to the output stream
     *
     * @return BinsonWriter
     */
    public function arrayEnd() : BinsonWriter
    {
        unset($this->state['name']);
        $this->depth--;
        $this->writeToken(binson::TYPE_ARRAY_END, binson::DEF_ARRAY_END);
    	return $this;
    }

    /**
     * Write specified BOOLEAN value to the output stream
     *
     * @param boolean $val
     * @return BinsonWriter
     */
    public function putBoolean(bool $val) : BinsonWriter
    {
    	$this->writeToken(binson::TYPE_BOOLEAN, $val? binson::DEF_TRUE : binson::DEF_FALSE);
    	return $this;
    }

    /**
     * Write 'true' BOOLEAN value to the output stream
     *
     * @return BinsonWriter
     */
    public function putTrue() : BinsonWriter
    {   
        return $this->putBoolean(true);
    }

    /**
     * Write 'false' BOOLEAN value to the output stream
     *
     * @return BinsonWriter
     */
    public function putFalse() : BinsonWriter
    {
        return $this->putBoolean(false);
    }

    /**
     * Write specified INTEGER value to the output stream
     *
     * @param integer $val
     * @return BinsonWriter
     */
    public function putInteger(int $val) : BinsonWriter
    {
    	$this->writeToken(binson::TYPE_INTEGER, $val);
    	return $this;
    }

    /**
     * Write specified DOUBLE value to the output stream
     *
     * @param float $val
     * @return BinsonWriter
     */
    public function putDouble(float $val) : BinsonWriter
    {
    	$this->writeToken(binson::TYPE_DOUBLE, $val);
    	return $this;
    }

    /**
     * Write specified STRING value to the output stream
     *
     * @param string $val
     * @return BinsonWriter
     */
    public function putString(string $val) : BinsonWriter
    {
    	$this->writeToken(binson::TYPE_STRING, $val);
    	return $this;
    }

    /**
     * Write specified string to the output stream
     * as a name part of the OBJECT's name:value pair
     *
     * @param string $val
     * @return BinsonWriter
     */
    public function putName(string $val) : BinsonWriter
    {
        if ($this->state['block_type'] === binson::TYPE_ARRAY)
            throw new BinsonException(binson::ERROR_STATE);

        if (isset($this->state['name']) && ($val <=> $this->state['name']) <= 0)
            throw new BinsonException(binson::ERROR_ARG);
        
        $this->state['name'] = $val;

        $this->writeToken(binson::TYPE_STRING, $val);
        return $this;
    }

    /**
     * Write specified BYTES value to the output stream
     *
     * @param string $val
     * @return BinsonWriter
     */
    public function putBytes(string $val) : BinsonWriter
    {
    	$this->writeToken(binson::TYPE_BYTES, $val);
    	return $this;
    }

    /**
     * Write specified byte array to the output stream
     *
     * @param string $bytes
     * @return BinsonWriter
     */
    public function putRaw(string $bytes) : BinsonWriter
    {
    	$this->data .= $bytes;
    	return $this;
    }

    /**
     * Put full output of the specified 'BinsonWriter' instance 
     * to the current output stream
     *
     * @param BinsonWriter $src_writer
     * @return BinsonWriter
     */
    public function putInline(BinsonWriter $src_writer) : BinsonWriter
    {
    	$this->data .= $src_writer->data;
    	return $this;
    }

    /**
     * Return bytes written to the output stream since reset
     *
     * @return integer
     */
	public function length() : int
    {
    	return strlen($this->data) - $this->data_len;
    }

    /**
     * Return bytes written to the output stream since reset
     *
     * @return integer
     */
	public function counter() : int
    {
    	return $this->length();
    }

    /**
     * Return full output as a binary string
     *
     * @return string
     */
    public function toBytes() : string
    {
    	return substr($this->data, $this->data_len);
    }

    /**
     * Serialize PHP native array(-s)
     *
     * @param [type] ...$vars
     * @return BinsonWriter
     */
    public function put(...$vars) : BinsonWriter
    {
        foreach ($vars as $var)
            $this->putOne($var);

        return $this;
    }

    private static function sortDeeply(array $arr) : array
    {
        foreach ($arr as $key => $val) {
            if (is_array($val))
                $arr[$key] = self::sortDeeply($val);
        }
        uksort($arr, "strcmp");
        return $arr;
    }

    private function putOne($var) : BinsonWriter
    {
        if (!$this->isSerializable($var))
           throw new BinsonException(binson::ERROR_WRONG_TYPE);
                    
        switch(gettype($var))
        {
            case "array":
                if (binson::EMPTY_ARRAY === $var)
                    return $this->arrayBegin()->arrayEnd();                     

                if ($this->config['serializer_sort_fields']) 
                    $var = self::sortDeeply($var);
                break;        

            case "integer":  return $this->putInteger($var);
            case "double":   return $this->putDouble($var);
            case "boolean":  return $this->putBoolean($var);
            case "string":   return $this->putString($var);

            case "object":
                if (binson::isBYTES($var))
                    return $this->putBytes($var->scalar);
                elseif ($var instanceof BinsonWriter)
                    return $this->putInline($var);

                /* fallthrough */
            default:
                throw new BinsonException(binson::ERROR_WRONG_TYPE);                
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($var, 
                                                        RecursiveArrayIterator::CHILD_ARRAYS_ONLY),
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
                
                $block_type = end($type_stack);
            }          
        
            if ($depth > $last_depth) {  // new block detected
                $block_type = (is_int($key) && $key === 0)/*!== binson::EMPTY_KEY)*/ ?
                                binson::TYPE_ARRAY :  binson::TYPE_OBJECT;       
                $res = ($block_type == binson::TYPE_ARRAY) ? $this->arrayBegin() : $this->objectBegin();
                $type_stack[] = $block_type;
            }            
            elseif ($depth < $last_depth) {  // block end detected              
                $res = ($block_type == binson::TYPE_ARRAY) ? $this->arrayEnd() : $this->objectEnd();
                $block_type = array_pop($type_stack);              
            }        
                        
            if ($value !== null && $block_type === binson::TYPE_OBJECT)
            {   
                if (is_int($key) && $key == 0)
                    throw new BinsonException(binson::ERROR_WRONG_TYPE);

                if (!is_string($key) && !is_int($key))
                    throw new BinsonException(binson::ERROR_WRONG_TYPE);

                $this->putName((string)$key); 
            }
            
            if (is_array($value) )
            {
              if ($value === [])
                $this->arrayBegin()->arrayEnd(); 
            }
            elseif ($value !== null)  // $value is NOT an array
                $this->putOne($value);

            $last_depth = $depth;
        }

        while ($block_type = array_pop($type_stack))
            $res = ($block_type == binson::TYPE_ARRAY) ? $this->arrayEnd() : $this->objectEnd();

        return $this;
    }

    private function isArrayEmptyBinsonObject($var) : bool
    {
        return (is_array($var) && count($var) === 1 &&
                 isKeyValEmptyMarker(key($var), $var[key($var)]))? true : false;
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
                    if (isKeyValEmptyMarker($key, $value))
                        return true;
                }             
                return true;
            }

            if ( is_string($var) ||
                 is_int($var) ||
                 is_float($var) ||
                 is_bool($var) || 
                 $var instanceof BinsonWriter || 
                 $var instanceof stdClass)
            return true;

            return false;
    }

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

/**
 * Utility stack structure for keeping track of depth dependant context data
 */
class BinsonStateStack implements ArrayAccess
{
    private $data = [];
    private $bp;

    public function __construct(BinsonProcessor &$bp)
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
            return $this->data[$this->bp->depth][$offset] ?? null;
    }

    public function offsetSet($offset, $value) {
        if ($offset === null)
            $this->data[$this->bp->depth] = $value;
        else
            $this->data[$this->bp->depth][$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->data[$this->bp->depth][$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->data[$this->bp->depth][$offset]);
    }
}

/**
 * Class used for binson data parsing
 */
class BinsonParser extends BinsonProcessor
{
    private $idx;

    /**
     * Constructor
     *
     * @param string $src
     * @param array $cfg
     */
    public function __construct(string &$src, array $cfg = null)
    {
        $this->config = $cfg ?? binson::CFG_DEFAULT;
        $this->reset($src);
    }

    /**
     * Reset to just initialized state
     *
     * @param string $src
     * @return void
     */
    public function reset(string &$src = null)
    {
        if ($src !== null)
            $this->data = &$src;    
        
        $this->idx = 0;
        
        parent::reset();
    }

    /**
     * Returs 'true' if related byte stream represents valid BINSON structure
     *
     * @return boolean
     */
    public function verify() : bool
    {
        $is_valid = true;
        $ctx = []; // context for verification callback `cbVerify`

        $saved_depth = $this->depth;
        $saved_idx = $this->idx;
        $saved_state = $this->state;
        
        try {
            $this->reset();
            $res = $this->advance(self::ADVANCE_SKIP_BLOCK, 0, [$this, 'cbVerify'], $ctx);
        }
        catch (Throwable $err)
        {
            $is_valid = false;
        }
        finally
        {
            $is_valid = $is_valid && $res && $this->isDone() &&
                        $this->idx === strlen($this->data);  // no more data beyond top parsed object
            
            // restore parser state
            $this->reset();
            $saved_depth = $this->depth;
            $saved_idx = $this->idx;
            $saved_state = $this->state;

            return $is_valid;
        }
    }

    /**
     * Parsing navigation: enter expected OBJECT
     *
     * @return BinsonParser
     */
    public function enterObject() : BinsonParser
    {
        $this->advance(self::ADVANCE_ONE, binson::TYPE_OBJECT);
        return $this;
    }

    /**
     * Parsing navigation: enter expected ARRAY
     *
     * @return BinsonParser
     */
    public function enterArray() : BinsonParser
    {
        $this->advance(self::ADVANCE_ONE, binson::TYPE_ARRAY);
        return $this;
    }

    /**
     * Parsing navigation: leave expected OBJECT
     *
     * @return BinsonParser
     */
    public function leaveObject() : BinsonParser
    {   
        $this->advance(self::ADVANCE_LEAVE_BLOCK);
        return $this;
    }

    /**
     * Parsing navigation: leave expected ARRAY
     *
     * @return BinsonParser
     */
    public function leaveArray() : BinsonParser
    {
        $this->advance(self::ADVANCE_LEAVE_BLOCK);
        return $this;
    }

    /**
     * Go to next ARRAY's item or OBJECT's name:value item for parsing
     *
     * @return boolean
     */
    public function next() : bool
    {
        return $this->advance(self::ADVANCE_NEXT);
    }

    /**
     * Check if current item has same type as specified
     *
     * @param integer $type
     * @return boolean
     */
    public function ensure(int $type) : bool
    {
        return $this->getType() === $type;
    }

    /**
     * Return 'true' if end of top level block reached when parsing
     *
     * @return boolean
     */
    public function isDone() : bool
    {
        return $this->state['id'] === self::STATE_DONE;
    }

    /**
     * Seek within current OBJECT to name:value pair with 'name' specified
     * Return 'true' if pair found, and 'false' otherwice
     *
     * @param string $name
     * @return boolean
     */
    public function field(string $name) : bool
    {        
        if (is_null($name))
            throw new BinsonException(binson::ERROR_NULL);

        while ($this->advance(self::ADVANCE_NEXT))
        {
            $r = $name <=> $this->state['name'];
            if (0 === $r)
                return true;
            else if ($r < 0)
                break;
        }

        return false;
    }

    /**
     * Return JSON-like textual representation
     *
     * @param boolean $php_native
     * @return string
     */
    public function toString(bool $php_native = false) : string
    {
        $ctx = ['data'=>'', 'comma'=>false];
        $res = $this->advance(self::ADVANCE_SKIP_BLOCK, 0, [$this, 'cbToString'], $ctx);
        return $res? $ctx['data'] : null;
    }

    /**
     * Deserialize input stream to PHP array
     *
     * @return array
     */
    public function deserialize() : array
    {
        $ctx = [];
        $res = $this->advance(self::ADVANCE_SKIP_BLOCK, 0, [$this, 'cbDeserializer'], $ctx);
        return $res? $ctx['data'][0] : null;
    }    

    private function callbackWrapper(?callable $cb, array $state_update, &$param = null) : bool
    {
        $prev_state = $this->state['top'];
        $this->state[] = $state_update; // copy id, type, value

        return $cb? $cb($prev_state, $param) : true;        
    }

    private function advance(int $scan_mode, int $ensure_type = null,
                            ?callable $cb = null, &$cb_param = null) : bool
    {
        $skip_state = self::ADVANCE_ONE;
        $orig_depth = $this->depth;

        while (true) {  /* scanning loop */
            if ($this->state['id'] & self::STATE_MASK_EXIT)
                return false;
            
            $state_req = $this->requestStateTransition([$this, 'processOne']);
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
                case self::STATE_AT_VALUE:
                case self::STATE_IN_OBJECT_END_:
                case self::STATE_IN_ARRAY_END_:                
                    break;

                case self::STATE_AT_ITEM_KEY:                    
                    $this->state['name'] = $update_req['name'] = $update_req['val'];
                    break;

                case self::STATE_IN_ARRAY_BEGIN:                
                case self::STATE_IN_OBJECT_BEGIN:
                    $this->depth++;
                    $this->state['block_type'] = $update_req['block_type'] = $update_req['type'];
                    break;
                    
                case self::STATE_OUTOF_OBJECT:
                case self::STATE_OUTOF_ARRAY:
                    $this->callbackWrapper($cb, $update_req, $cb_param);
                    $cb_called = true;

                    $this->depth--;
                    $this->state['id'] = $state_req['id'];                    
                    break;

                default:
                    throw new BinsonException(binson::ERROR_STATE, "???");
                }

                if (!$cb_called)
                    $this->callbackWrapper($cb, $update_req, $cb_param);
    
                switch ($scan_mode) {
                case self::ADVANCE_SKIP_BLOCK:
                    if ($skip_state === self::ADVANCE_ONE && $this->state['id'] & self::STATE_MASK_NEED_INPUT)
                        $skip_state = self::ADVANCE_LEAVE_BLOCK; 

                    if ($skip_state === self::ADVANCE_LEAVE_BLOCK)
                    {
                        if ($this->depth === 0)
                            $this->state['id'] = self::STATE_DONE;

                        if ($this->depth === 0 || $this->depth < $orig_depth)
                            return true;                        
                    }
                    break;

                case self::ADVANCE_ONE:
                    if ($this->state['id'] & self::STATE_MASK_NEED_INPUT)
                        return true;
                    break;

                case self::ADVANCE_LEAVE_BLOCK:
                    if ($this->depth === 0)
                        $this->state['id'] = self::STATE_DONE;

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
        return true;
    }

    private function isBlock() : bool
    {
        $type = $this->state->type;
        return $type === binson::BINSON_ID_OBJECT || $type === binson::BINSON_ID_ARRAY;
    }

    /* return associative array:  type, value */
    protected function processOne() : array
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
                return ['type' => binson::TYPE_BOOLEAN, 
                        'val' => ($byte === binson::DEF_TRUE)];

            case binson::DEF_DOUBLE:                 
                return ['type' => binson::TYPE_DOUBLE, 
                        'val' => $this->parseNumeric($this->consume(8), true)];

            case binson::DEF_INT8:
            case binson::DEF_INT16:
            case binson::DEF_INT32:
            case binson::DEF_INT64:                             
                $size = 1 << ($byte - 16);
                return ['type' => binson::TYPE_INTEGER, 
                        'val' => $this->parseNumeric($this->consume($size))];

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

    private function cbVerify(?array $prev_state, &$param = null) : bool
    {
        if (!is_array($param))
            throw new BinsonException(binson::ERROR_WRONG_TYPE, "cbVerify() require `array` parameter for context storage");

        $new_state = $this->state['top'];
        $depth = $this->depth;

        switch ($new_state['id']) {
        case self::STATE_IN_OBJECT_BEGIN:
            unset($param[$depth]);
            break;

        case self::STATE_AT_ITEM_KEY:            
            if (!isset($param[$depth]) || ($param[$depth] <=> $new_state['val']) < 0)
                $param[$depth] = $new_state['val'];
            else
                throw new BinsonException(binson::ERROR_FORMAT, "field name is out of order");
        }

        return true;
    }

    private function cbDeserializer(?array $prev_state, &$param = null) : bool
    {
        if (!is_array($param))
            throw new BinsonException(binson::ERROR_WRONG_TYPE, "cbDeserializer() require `array` parameter");

        if (empty($param)) {  // first cb run
                $param = ['data'=>[], 'parent'=>[], 'names'=>[]];
                $param['current'] = &$param['data'];
        }            

        $new_state = $this->state['top'];
        $parent_state = $this->state['parent'];

        $depth = $this->depth;

        switch ($new_state['id']) {
            case self::STATE_AT_OBJECT_:
            case self::STATE_AT_ARRAY_:
            case self::STATE_IN_OBJECT_END_:
            case self::STATE_IN_ARRAY_END_:
                return true;

            case self::STATE_IN_OBJECT_BEGIN:
                unset($param['names'][$depth]);
                /* fallthrough */
            case self::STATE_IN_ARRAY_BEGIN:
                $param['parent'][] = &$param['current'];    

                $container = ($new_state['block_type'] === binson::TYPE_OBJECT)?
                                binson::EMPTY_OBJECT : binson::EMPTY_ARRAY;

                if ($parent_state['block_type'] === binson::TYPE_OBJECT)
                    $param['current'][fixNumField($new_state['name'])] = $container;
                else
                    $param['current'][] = $container;

                end($param['current']);                
                $param['current'] = &$param['current'][key($param['current'])];
                return true;
                
            case self::STATE_OUTOF_ARRAY:
            case self::STATE_OUTOF_OBJECT:
                if (count($param['current']) > 1)
                    unset($param['current'][binson::EMPTY_KEY]); // remove empty object marker
                    
                unset($param['current']);
                end($param['parent']);
                $param['current'] = &$param['parent'][key($param['parent'])];
                unset($param['parent'][key($param['parent'])]);
                end($param['current']);
                return true;

            case self::STATE_AT_ITEM_KEY:
                if (!isset($param['names'][$depth]) || 
                          ($param['names'][$depth] <=> $new_state['val']) < 0)
                    $param['names'][$depth] = $new_state['val'];
                else
                    throw new BinsonException(binson::ERROR_FORMAT, "field name is out of order");
                return true;
                
            case self::STATE_AT_VALUE:
            {                
                switch ($new_state['type']) {
                case binson::TYPE_BOOLEAN:
                case binson::TYPE_DOUBLE:
                case binson::TYPE_INTEGER:
                case binson::TYPE_STRING:                
                case binson::TYPE_BYTES:                
                    end($param['current']);
                    if (isset($new_state['name']) && $new_state['block_type'] === binson::TYPE_OBJECT)
                        $param['current'][fixNumField($new_state['name'])] = $new_state['val'];
                    else
                        $param['current'][] = $new_state['val'];
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
        if (!is_string($param['data']))
            throw new BinsonException(binson::ERROR_WRONG_TYPE, "wrong context");

        $new_state = $this->state['top'];
        $parent_state = $this->state['parent'];
        $depth = $this->depth;
        
        switch ($new_state['id']) {
            case self::STATE_AT_OBJECT_:
            case self::STATE_AT_ARRAY_:
                $param['data'] .= $param['comma']? ',' : '';
            case self::STATE_IN_OBJECT_END_:
            case self::STATE_IN_ARRAY_END_:
                return true;
            case self::STATE_IN_ARRAY_BEGIN:                
                $param['comma'] = false;
                $param['data'] .= '[';
                return true;
            case self::STATE_IN_OBJECT_BEGIN:
                $param['comma'] = false;
                $param['data'] .= '{';
                return true;
            case self::STATE_OUTOF_ARRAY:
                $param['data'] .= ']';
                $param['comma'] = true;
                return true;
            case self::STATE_OUTOF_OBJECT:
                $param['data'] .= '}';
                $param['comma'] = true;
                return true;
            case self::STATE_AT_ITEM_KEY:
                $param['data'] .= $param['comma']? ',' : '';
                $param['data'] .= '"'.$new_state['val'].'":';
                $param['comma'] = false;
                return true;
            case self::STATE_AT_VALUE:
            {
                if (!($new_state['type'] & self::TYPE_MASK_VALUE))
                    return true;

                $param['data'] .= $param['comma']? ',' : '';
                $param['comma'] = true;

                switch ($new_state['type']) {
                case binson::TYPE_BOOLEAN:
                case binson::TYPE_DOUBLE:                
                case binson::TYPE_INTEGER:
                    $param['data'] .= var_export($new_state['val'], true);
                    return true;

                case binson::TYPE_STRING:
                    $param['data'] .= '"'.$new_state['val'].'"';
                    return true;
                case binson::TYPE_BYTES:
                    $param['data'] .= '"0x'.bin2hex($new_state['val']).'"';
                    return true;
    
                default: /* we should not get here */
                    break;
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

    private function parseNumeric(string $chunk, bool $is_float = false)
    {
        $len = strlen($chunk);
        $filler = chr(ord($chunk[-1]) & 0x80 ? 0xff : 0x00);
        $chunk = str_pad($chunk, 8, $filler);

        $val = unpack($is_float? 'e':'P', $chunk);
        $v = $val[1];

        if ($is_float)
        {
            if (is_float($v))
                return $v;
            else
                throw new BinsonException(binson::ERROR_FORMAT);
        }

        if ($len === 1 && ($v >= binson::INT8_MIN && $v <= binson::INT8_MAX))
            return $v;
        else if ($len === 2 && ($v < binson::INT8_MIN || $v > binson::INT8_MAX))
            return $v;
        else if ($len === 4 && ($v < binson::INT16_MIN || $v > binson::INT16_MAX))
            return $v;
        else if ($len === 8 && ($v < binson::INT32_MIN || $v > binson::INT32_MAX))
            return $v;

        throw new BinsonException(binson::ERROR_FORMAT);
    }

    private function consume(int $size, bool $peek = false) : string
    {
        if ($size === 0)
            return '';

        $chunk = substr($this->data, $this->idx, $size);
        
        if ($chunk === '')
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
    $val_pack_code = 'P'; // 64bit unsigned LE

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

    return chr($val_bytes[0]).substr(pack($val_pack_code, $val), 0, $size);
}

function isKeyValEmptyMarker($key, $val)
{
    return ($key === binson::EMPTY_KEY && $val === binson::EMPTY_VAL)? true : false; 
}

function fixNumField(string $name) : string
{
    return ((string) (int) $name === $name)? $name.'.' : $name;
}
