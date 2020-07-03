<?php
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
		
		function setField( $name, $dataType, $required=true, $rules = array() ){
			$a = array($dataType,$required, $rules );
			$this->fields[ $name ] = $a;
		}
		
		function read(){
			$data = array();
			
			if( $_SERVER['REQUEST_METHOD'] == 'PUT' ){
				parse_str(file_get_contents("php://input"),$_REQUEST);
			}
			
			foreach( $this->fields as $k=>$v ){
				if( $v[1] == true ){
					if( !array_key_exists( $k, $_REQUEST ) ){
						throw new ReaderException( "ERROR_VALUE_REQUIRED\n".$k );
					}
					
					if( $_REQUEST[ $k ] == "" ){
						throw new ReaderException( "ERROR_VALUE_REQUIRED\n".$k );
					}
				}
				
				if( array_key_exists( $k, $_REQUEST ) && $_REQUEST[ $k ] != "" ){
					try{
						$this->validate( $k, $v[0], $v[2] );
					}catch( ValidatorException $ve ){
						throw new ReaderException( $ve->getMessage()."\n".$k );
					}
				}
				
				if( $this->filterHtmlEntities ){
					$data[ $k ] = htmlentities( $_REQUEST[ $k ], ENT_QUOTES, 'UTF-8' );
				}else{
					$data[ $k ] = $_REQUEST[ $k ];
				}
			}
			
			return $data;
		}
		
		function validate( $name, $dataType, $rules ){
			$this->validator->validate(  $dataType, $_REQUEST[$name], $rules );
		}
	}
?>
