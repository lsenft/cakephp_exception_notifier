<?php
class ExceptionNotifierErrorHandler extends ErrorHandler {
    /*public static function handleException(Exception $exception) {
        echo 'なんてこった！ ' . $exception->getMessage();
    }*/
    
    public static function handleError($code, $description, $file = null, $line = null, $context = null) {
        /*if (error_reporting() === 0) {
            return false;
        }*/
        
        parent::handleError($code, $description, $file, $line, $context);
        
        try{
            $mail = new CakeEmail('error');
            $text = self::_getText($description, $file, $line);
            $mail->send($text);
        } catch(Exception $e){
            $message = $e->getMessage();
            CakeLog::write(LOG_ERROR, $message);
        }
    }
    
    private static function _getText($description, $file, $line)
    {
        $params = Router::getRequest();
        $trace = Debugger::trace(array('start' => 2, 'format' => 'base'));
        $session = isset($_SESSION) ? $_SESSION : array();

        $msg = array(
            $description,
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