<?php
class IndexController extends Zend_Controller_Action 
{ 
  /**
   * Database  
   */
  protected $_db;

  /**
   * Authorization  
   */
  protected $_auth;

  /**
   * Translation  
   */
  protected $tr;

  public function init()
  {
    $this->_db = Zend_Registry::get('DB');

    $this->_auth = Zend_Auth::getInstance();

    $this->tr = Zend_Registry::get('Zend_Translate');
  }

  public function preDispatch()
  {
    $action = $this->getRequest()->getActionName(); 

    if (!$this->_auth->hasIdentity())
    {
      switch ($action)
      {
        case 'login': break; // /login
        case 'reset-password': break; // /reset-password

        default:
          return $this->_redirect('/index/login');
        break;
      }
    }
    else
    {
      switch ($action)
      {
        case 'login':
        case 'reset-password':
          return $this->_redirect('/index');
        break;

        default: break;
      }
    }

  }

  public function indexAction()
  {
    if(!$this->_auth->hasIdentity())
    {
      return $this->_helper->redirector('login');
    }
  }

  public function logoutAction()
  {
    if ($this->_auth->hasIdentity())
    {
      $this->_auth->clearIdentity();
    }
      
    return $this->_helper->redirector('index');
  }

  public function loginAction()
  {
    $config = new Zend_Config_Ini(dirname(__FILE__) . '/../../config.ini', 'database');

    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . '/index/login');

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Log in'));

    $email = new Zend_Form_Element_Text('username');
    $email->setRequired(true);
    $email->setLabel($this->tr->_('E-Mail'));
    $email->addFilter('StringTrim');
    $email->addFilter('StringToLower');
    $email->addValidator('StringLength', false, array(7));
    $email->addValidator('EmailAddress');

    $pass = new Zend_Form_Element_Password('password');
    $pass->setRequired(true);
    $pass->setLabel($this->tr->_('Password'));
    $pass->addFilter('StringTrim');

    $form->addElement($email);
    $form->addElement($pass);
    $form->addElement($submit);
    
    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();

        $authAdapter = new Zend_Auth_Adapter_DbTable($this->_db);
        $authAdapter->setTableName('USERS');
        $authAdapter->setIdentityColumn('email');
        $authAdapter->setCredentialColumn('password');

        $pw = md5($config->salt . $values['password']);

        $authAdapter->setIdentity($values['username']);
        $authAdapter->setCredential($pw);

        $result = $this->_auth->authenticate($authAdapter);

        if ($result->isValid())
        {
          Zend_Session::rememberMe(60*60*24*7*4); 

          $this->_auth->getStorage()->write($authAdapter->getResultRowObject(null, 'password'));

          $userid = $this->_auth->getIdentity()->id;

          return $this->_helper->redirector('index');

        }
        else
        {
          switch ($result->getCode())
          {
            case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
              $this->view->message = $this->tr->_('User or password is wrong');
            break;

            case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
              $this->view->message = $this->tr->_('User or password is wrong');
            break;

            default:
              $this->view->message = $this->tr->_('Authentication Error');
            break;
          }
        }
      }
    }

    $this->view->form = $form;
    
  }

}
