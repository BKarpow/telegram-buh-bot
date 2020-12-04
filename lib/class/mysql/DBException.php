<?

/**
 * 
 */
class DBException extends ErrorException
{
	
	public function __construct($message, $code = 0, Exception $previous = null) {
        // некоторый код 
    
        // убедитесь, что все передаваемые параметры верны
        parent::__construct($message, $code, $previous);
    }

    function message(){
    	return parent::getMessage();
    }
}