<?php
namespace Lsenft\CakephpExceptionNotifier\Error;

use Cake\Core\Configure;
use Cake\Error\ErrorHandler as CoreErrorHandler;
use ErrorException;
use Exception;
use Cake\Mailer\Email;
use Cake\Log\Log;

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
            $email = new Email('ExceptionNotifier');
            // $email->setTo('to@example.com', 'To Example');
            // $email->addFrom('no-reply@itp.com.au', 'Exception Notifier');

            $text = self::_getText($errorInfo, $description, $file, $line, $context);
            $email->send($text);
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
            $email = new Email('ExceptionNotifier');
            $text = self::_getText($errorInfo, $description, $file, $line, $context);
            $email->send($text);
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
            $email = new Email('ExceptionNotifier');
            $text = self::_getText('$errorInfo', $exception->getMessage(), '$file', '$line', '$context');
            $email->send($text);
        } catch(Exception $e){
            $message = $e->getMessage();
            Log::write($message, ['ExceptionNotifier']);
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
