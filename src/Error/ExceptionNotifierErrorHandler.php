<?php
namespace Lsenft\CakephpExceptionNotifier\Error;

use Cake\Core\Configure;
use Cake\Error\ErrorHandler as CoreErrorHandler;
use ErrorException;
use Exception;
use Cake\Mailer\Email;
use Cake\Log\Log;
use Cake\Error\Debugger;
use Lsenft\CakephpExceptionNotifier\Utility\Laxative;

class ExceptionNotifierErrorHandler extends CoreErrorHandler {
    /**
     * 
     * @param type $code
     * @param type $description
     * @param type $file
     * @param type $line
     * @param type $context
     * @return type
     */    
    public function handleError($code, $description, $file = null, $line = null, $context = null)
    {
        
        parent::handleError($code, $description, $file, $line, $context);
        
        $errorInfo = self::mapErrorCode($code);
        
        try{
            $text = self::_getText($errorInfo, $description, $file, $line, $context);
            self::send($text);
        } catch(Exception $e){
            $message = $e->getMessage();
            Log::write($message, ['ExceptionNotifier']);
        }
    }
    
    public function handleFatalError($code, $description, $file, $line)
    {        
        parent::handleFatalError($code, $description, $file, $line, $context);
        
        $errorInfo = self::mapErrorCode($code);
        
        try{
            $text = self::_getText($errorInfo, $description, $file, $line, $context);
            self::send($text);
        } catch(Exception $e){
            $message = $e->getMessage();
            Log::write($message, ['ExceptionNotifier']);
        }
    }

    
    public function handleException(Exception $exception)
    {
        parent::handleException($exception);
        
        //$errorInfo = self::mapErrorCode($code);
        
        try{
            $text = self::_getText('$errorInfo', $exception->getMessage(), '$file', '$line', '$context');
            self::send($text);
        } catch(Exception $e){
            $message = $e->getMessage();
            Log::write($message, ['ExceptionNotifier']);
        }
    }
    
    private static function _getText($errorInfo, $description, $file, $line, $context)
    {
        $params = $_REQUEST;
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
            '* Parameters: ' . Laxative::dump($params),
            '* Cake root : ' . APP,
            '',
            '-------------------------------',
            'Environment:',
            '-------------------------------',
            '',
            Laxative::dump($_SERVER),
            '',
            '-------------------------------',
            'Session:',
            '-------------------------------',
            '',
            Laxative::dump($session),
            '',
            '-------------------------------',
            'Cookie:',
            '-------------------------------',
            '',
            Laxative::dump($_COOKIE),
            '',
            '-------------------------------',
            'Backtrace:',
            '-------------------------------',
            '',
            $trace,
            '',
            '-------------------------------',
            'Context:',
            '-------------------------------',
            '',
            Laxative::dump($context),
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
    
    private static function send($text)
    {
        if (!empty(getenv('TRAVIS'))) {
            echo "\n\n" . $text . "\n\n";
        }  else {
            $email = new Email('ExceptionNotifier');
            $email->send($text);
        }
    }
}
