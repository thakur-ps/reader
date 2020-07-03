<?php
	namespace bucorel\reader;
	
	class ReaderException extends \Exception{
	
		protected $fieldName = "";
		
		public function __construct( $message, $code = 0, \Exception $previous = null ) {
			$ps = explode( "\n", $message );
			$message = $ps[0];
			if( isset($ps[1] ) ){
				$this->fieldName = $ps[1];
			}
        	parent::__construct($message, $code, $previous);
    	}
    	
    	public function getFieldName(){
    		return $this->fieldName;
    	}
    }
?>
