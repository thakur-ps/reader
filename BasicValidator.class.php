<?php
	namespace bucorel\reader;
	use bucorel\datatype\DataType;
	
	class BasicValidator{
		
		function validate( $dataType, $value, array $rules=array() ){
			switch( $dataType ){
				case DataType::PDT_INTEGER:
					self::validateInteger( $value );
					return;
				case DataType::PDT_FLOAT:
					self::validateFloat( $value );
					return;
				case DataType::PDT_BOOLEAN:
					self::validateBoolean( $value );
					return;
				case DataType::PDT_TUPLE:
					self::validateTuple( $value );
					return;
			}
		}
		
		public static function validateInteger( $value ){
			if( filter_var( $value, FILTER_VALIDATE_INT ) === false ){
				throw new ValidatorException( "INVALID_INTEGER" );
			}
		}
		
		public static function validateFloat( $value ){
			if( filter_var( $value, FILTER_VALIDATE_FLOAT ) === false ){
				throw new ValidatorException( "INVALID_FLOAT" );
			}
		}
		
		public static function validateBoolean( $value ){
			if( $value != 1 && $value != 0 ){
				throw new ValidatorException( "INVALID_BOOLEAN" );
			}
		}
		
		public static function validateTuple( $value ){
			if( !is_array($value) ){
				throw new ValidatorException( "INVALID_TUPLE" );
			}
		}
	}	
?>
