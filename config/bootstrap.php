<?php
use Cake\Core\Configure;
use Lsenft\CakephpExceptionNotifier\Error\ExceptionNotifierErrorHandler;

$errorHandler = new ExceptionNotifierErrorHandler();
$errorHandler->register();