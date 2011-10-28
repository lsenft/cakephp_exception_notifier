<?php
class ExceptionNotifierErrorHandler extends ErrorHandler {
    public static function handleError($code, $description, $file = null, $line = null, $context = null) {
        /*if (error_reporting() === 0) {
            return false;
        }*/
        $errorConf = Configure::read('Error');
        if(!($errorConf['level'] & $code)){
            return;
        }
        
        parent::handleError($code, $description, $file, $line, $context);
        
        $errorInfo = self::mapErrorCode($code);
        
        try{
            $mail = new CakeEmail('error');
            $text = self::_getText($errorInfo, $description, $file, $line, $context);
            $mail->send($text);
        } catch(Exception $e){
            $message = $e->getMessage();
            CakeLog::write(LOG_ERROR, $message);
        }
    }
    
    private static function _getText($errorInfo, $description, $file, $line, $context)
    {
        $params = Router::getRequest();
        $trace = Debugger::trace(array('start' => 2, 'format' => 'base'));
        $session = isset($_SESSION) ? $_SESSION : array();

        $msg = array(
            $errorInfo[0] . ':' . $description,
            $file . '(' . $line . ')',
            '',
            '-------------------------------',
            'Request:',
            '-------------------------------',
            '',
            '* URL       : ' . self::_getUrl(),
            '* IP address: ' . env('REMOTE_ADDR'),
            '* Parameters: ' . trim(print_r($params, true)),
            '* Cake root : ' . APP,
            '',
            '-------------------------------',
            'Environment:',
            '-------------------------------',
            '',
            trim(print_r($_SERVER, true)),
            '',
            '-------------------------------',
            'Session:',
            '-------------------------------',
            '',
            trim(print_r($session, true)),
            '',
            '-------------------------------',
            'Cookie:',
            '-------------------------------',
            '',
            trim(print_r($_COOKIE, true)),
            '',
            '-------------------------------',
            'Backtrace:',
            '-------------------------------',
            '',
            trim($trace),
            '',
            '-------------------------------',
            'Context:',
            '-------------------------------',
            '',
            trim(print_r($context, true)),
            '',
            );

        return join("\n", $msg);
    }
    
    private static function _getUrl()
    {
        if (PHP_SAPI == 'cli') {
            return '';
        }
        
        $protocol = array_key_exists('HTTPS', $_SERVER) ? 'https' : 'http';
        return $protocol . '://' . env('HTTP_HOST') . env('REQUEST_URI');
    }
}
