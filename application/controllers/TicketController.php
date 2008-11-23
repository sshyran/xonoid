<?php
class TicketController extends Zend_Controller_Action 
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
    if (!$this->_auth->hasIdentity())
    {
      return $this->_redirect('/index/login');
    }
  }

  public function indexAction()
  {
  }
  
  public function addAction()
  {
    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . '/company/add');

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Add'));

    $name = new Zend_Form_Element_Text('name');
    $name->setRequired(true);
    $name->setLabel($this->tr->_('Name'));
    $name->addFilter('StringTrim');
    $name->addValidator('NotEmpty', true);
    $name->addValidator('StringLength', false, array(3, 100));

    $descr = new Zend_Form_Element_Textarea('description');
    $descr->setRequired(true);
    $descr->setLabel($this->tr->_('Description'));
    $descr->addFilter('StringTrim');
    $descr->addValidator('NotEmpty', true);
    $descr->addValidator('StringLength', false, array(10, 65535));

    $form->addElement($name);
    $form->addElement($descr);

    $form->addElement($submit);

    $form->addDisplayGroup(array('name', 'description'), 'info');
    $form->addDisplayGroup(array('submit'), 'submit');
    
    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();
        
/*
        $insert = array(
          'name' => $values['name'],
          'contactid' => $values['contactid'],
          'streetaddress' => $values['streetaddress'],
          'postnumber' => $values['postnumber'],
          'postoffice' => $values['postoffice'],
          'resellerid' => null
        );
        
        $this->_db->beginTransaction();

        try
        {
          $companies->insert($insert);

          $this->_db->commit();

          return $this->_helper->redirector->gotoUrl("/company");

        }
        catch (Exception $e)
        {
          $this->_db->rollBack();
          var_dump($e);
        }
*/
      }
    }

    $this->view->form = $form;    
    
  }

}
