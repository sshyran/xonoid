<?php
class ErrorController extends Zend_Controller_Action
{
	public function errorAction()
  {
    public function errorAction()
    {
      $errors = $this->_getParam('error_handler');

      switch ($errors->type)
      {
        case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
        case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
          // 404 error -- controller or action not found
          $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
          $content ='<h1>Error!</h1><p>The page you requested was not found.</p>';
        break;

        default:
          // application error
          $content ='<h1>Error!</h1><p>An unexpected error occurred. Please try again later.</p>';
        break;
      }

      // Clear previous content
      $this->getResponse()->clearBody();
      $this->view->content = $content;
    }
	}

}
