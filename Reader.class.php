<?php
	/* Reader (C) Business Computing Research Laboratory
	 * Author - Pushpendra Singh Thakur <thakur@bucorel.com>
	 * 
	 * REQUIREMENTS -
	 *
	 * Global Configuration -
	 * Convert string inputs into htmlentities use -
	 * const VALIDATOR_FILTER_HTMLENTITIES = true (default is false)
	 *
	 * By default reader validates Primary Data types only if you need Secondary
	 * data type validation use -
	 * const VALIDATOR_ADVANCE_VALIDATION = true
	 *
	 */
	 
	namespace bucorel\reader;
	
	class Reader{
		
		protected $fields = array();
		protected $filterHtmlEntities = false;
		protected $enableAdvanceValidation = false;
		protected $validator = null;
		
		function __construct(){
			if( defined( 'VALIDATOR_FILTER_HTMLENTITIES' ) && 
				VALIDATOR_FILTER_HTMLENTITIES == true
				){
				$this->filterHtmlEntities = true;
			}
			
			if( defined( 'VALIDATOR_ADVANCE_VALIDATION' ) && 
				VALIDATOR_ADVANCE_VALIDATION == true
				){
				$this->enableAdvanceValidation = true;
			}
			
			if( $this->enableAdvanceValidation ){
				$this->validator = new AdvanceValidator();
			}else{
				$this->validator = new BasicValidator();
			}
		}
		
		function setField( $name, $dataType, $required=true, $multiValue=false, $rules = array() ){
			$a = array( $dataType, $required, $multiValue, $rules );
			$this->fields[ $name ] = $a;
		}
		
		function read(){
			$data = array();
			
			if( $_SERVER['REQUEST_METHOD'] == 'PUT' ){
				parse_str(file_get_contents("php://input"),$_REQUEST);
			}
			
			foreach( $this->fields as $k=>$v ){
				if( $v[2] == true ){
					$data[ $k ] = $this->readMultiValue( $k, $v[0], $v[1], $v[3] );
				}else{
					$data[ $k ] = $this->readSingleValue( $k, $v[0], $v[1], $v[3] );
				}
			}
			
			return $data;
		}
		
		function validate( $dataType, $value, $rules ){
			$this->validator->validate(  $dataType, $value, $rules );
		}
		
		function readSingleValue( $name, $dataType, $required, $rules ){
			if( !array_key_exists( $name, $_REQUEST ) ){
				throw new ReaderException( "ERROR_FIELD_MISSING\n".$name );
			}
			
			if( is_array($_REQUEST[$name]) ){
				throw new ReaderException( "ERROR_UNEXPECTED_ARRAY\n".$name );
			}
			
			if( $required && $_REQUEST[ $name ] == "" ){
				throw new ReaderException( "ERROR_VALUE_REQUIRED\n".$name );
			}
			

			if( $_REQUEST[ $name ] != "" ){
				try{
					$this->validate( $dataType, $_REQUEST[ $name ], $rules );
				}catch( ValidatorException $ve ){
					throw new ReaderException( $ve->getMessage()."\n".$name );
				}
			}
			
			if( $this->filterHtmlEntities ){
				return htmlentities( $_REQUEST[ $name ], ENT_QUOTES, 'UTF-8' );
			}else{
				return $_REQUEST[ $name ];
			}
		}
		
		function readMultiValue( $name, $dataType, $required, $rules ){
			if( !array_key_exists( $name, $_REQUEST ) ){
				throw new ReaderException( "ERROR_FIELD_MISSING\n".$name );
			}
			
			if( !is_array($_REQUEST[$name]) ){
				throw new ReaderException( "ERROR_ARRAY_EXPECTED\n".$name );
			}
			
			if( $required && count( $_REQUEST[ $name ] ) == 0 ){
				throw new ReaderException( "ERROR_EMPTY_ARRAY\n".$name );
			}
			
			$t = array();
			
			foreach( $_REQUEST[ $name ] as $v ){
				
				try{
					$this->validate( $dataType, $v, $rules );
				}catch( ValidatorException $ve ){
					throw new ReaderException( $ve->getMessage()."\n".$name );
				}
				
				if( $this->filterHtmlEntities ){
					array_push( $t, htmlentities( $v, ENT_QUOTES, 'UTF-8' ) );
				}else{
					array_push( $t, $v );
				}
			}
			
			return $t;
		}
	}
?>
