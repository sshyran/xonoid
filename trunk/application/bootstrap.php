<?php
// Bootstrap

// Default settings
ini_set('display_startup_errors', 1);  
ini_set('display_errors', 1);
ini_set('default_charset', 'utf-8'); 

// Set XDebug settings
ini_set('xdebug.auto_trace', false);
ini_set('xdebug.collect_params', 2);
ini_set('xdebug.collect_vars', true);
ini_set('xdebug.show_local_vars', false);
ini_set('xdebug.show_exception_trace', true);
ini_set('xdebug.collect_return', true);
ini_set('xdebug.trace_format', 0);
ini_set('xdebug.trace_options', 1);
ini_set('xdebug.default_enable', true);
ini_set('xdebug.overload_var_dump', true);
ini_set('xdebug.var_display_max_children', 1024);
ini_set('xdebug.var_display_max_data', 1024);
ini_set('xdebug.var_display_max_depth', 10);
ini_set('xdebug.manual_url', 'http://fi.php.net');

error_reporting(E_ALL | E_STRICT);
ignore_user_abort(true);
date_default_timezone_set('Europe/Helsinki');
 
set_include_path(realpath(dirname(__FILE__) . '/../library') . PATH_SEPARATOR . get_include_path());  
 
require_once 'Zend/Loader.php'; 
Zend_Loader::registerAutoload();

set_include_path(realpath(dirname(__FILE__) .'/../classes') . PATH_SEPARATOR . get_include_path());

require_once 'db.php';
require_once 'vat.php';
require_once 'joker.php';
require_once 'pdf.php';

$fi = array('' => '');
$translate = new Zend_Translate('array', $fi, 'fi');
$translate->addTranslation($fi, 'fi');
$translate->setLocale("fi");
Zend_Registry::set('Zend_Translate', $translate);


require_once 'crmform.php';

$config = new Zend_Config_Ini(dirname(__FILE__) . '/../config.ini', 'database');

$db_params = array(
  'host'             => $config->host,
  'username'         => $config->username,
  'password'         => $config->password,
  'dbname'           => $config->dbname,
  'adapterNamespace' => 'DB_Adapter',
  'charset'          => 'utf8',
  'profiler'         => true,
  'driver_options'   => array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") 
);

Zend_Registry::set('DB', null);

$db_conn_initialized = false;

try
{
  $db = new Zend_Db_Adapter_Pdo_Mysql($db_params);
  $conn = $db->getConnection();
  Zend_Db_Table_Abstract::setDefaultAdapter($db);
  Zend_Registry::set('DB', $db);
  $db_conn_initialized = true;
}
catch (Exception $e)
{
  throw new $e;
}


set_include_path(realpath(dirname(__FILE__) .'/../classes') . PATH_SEPARATOR . get_include_path());

$plugin = new Zend_Controller_Plugin_ErrorHandler();
$plugin->setErrorHandlerModule('default')
       ->setErrorHandlerController('error')
       ->setErrorHandlerAction('error');

// Setup
$frontController = Zend_Controller_Front::getInstance();
$frontController->registerPlugin($plugin);
$frontController->throwExceptions(true);
$frontController->setBaseUrl('/');
$frontController->setControllerDirectory(array('default' => dirname(__FILE__) . '/controllers'));

$doctypeHelper = new Zend_View_Helper_Doctype();
$doctypeHelper->doctype('XHTML11');

Zend_Layout::startMvc(array('layoutPath' => dirname(__FILE__) . '/views/layouts'));

$view = new Zend_View;
$view->setEncoding('UTF-8');
$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

$auth = Zend_Auth::getInstance();

if ($auth->hasIdentity())
{
  Zend_Layout::getMvcInstance()->setLayout('layout');
}
else
{
  Zend_Layout::getMvcInstance()->setLayout('login_layout');
}

if(!$db_conn_initialized)
{
  Zend_Layout::getMvcInstance()->setLayout('database_error_layout');
}

//$frontController->dispatch(); 

/*try
{*/
  $frontController->dispatch(); 
/*}
catch (Exception $e)
{
  var_dump($e);
}
*/
