<?php
class CssController extends Zend_Controller_Action 
{
  public function init()
  {
    $this->_helper->layout->disableLayout();

    $response = $this->getResponse();
    $response->setHeader('Content-Type', 'text/css; charset=utf8', true);
  }

  // CSS for logged in users
  public function indexAction()
  {
  }

  // CSS for logged out users
  public function loginAction()
  {
  }

}
