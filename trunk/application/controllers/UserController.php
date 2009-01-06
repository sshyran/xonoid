<?php
class UserController extends crmController 
{ 

  public function preDispatch()
  {
    if (!$this->_auth->hasIdentity())
    {
      return $this->_redirect('/index/login');
    }
  } // /function

  /**
   * Front page  
   */
  public function indexAction()
  {
    $users = new Users();
    $user_list = $users->getUserList();

    $grid = new Core_DataGrid(new Core_DataGrid_DataSource_Array($user_list), 100);
    $grid->setDefaultSort(array('lastname' => 'asc'));

    $grid->addColumn('manage', array(
      'header' => $this->tr->_('Manage'),
      'width' => 1,
      'sortable' => false,
      'type' => 'action',
      'actions' => array(
          'url' => $this->view->baseUrl() . '/user/manage/id/$id/',
          'class' => 'icon',
          'caption' => $this->tr->_('Manage'),
          'image' => $this->view->baseUrl() . '/images/icons/view.png'
        )
      )
    );

		$grid->addColumn('id', new Core_DataGrid_Column('id', $this->tr->_('Id'), null , 'left'));
		$grid->addColumn('lastname', new Core_DataGrid_Column('lastname', $this->tr->_('Last name'), null , 'left'));
		$grid->addColumn('firstname', new Core_DataGrid_Column('firstname', $this->tr->_('First name'), null , 'left'));
		$grid->addColumn('email', new Core_DataGrid_Column('email', $this->tr->_('Email'), null , 'left'));
		$grid->addColumn('phone', new Core_DataGrid_Column('phone', $this->tr->_('Phone'), null , 'left'));

    $grid->addColumn('edit', array(
      'header' => $this->tr->_('Edit'),
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/user/edit/id/$id/',
        'class' => 'icon',
        'caption' => $this->tr->_('Edit'),
        'image' => $this->view->baseUrl() . '/images/icons/edit.png'
      )
    ));

    $grid->addColumn('remove', array(
      'header' => $this->tr->_('Remove'),
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/user/remove/id/$id/',
        'class' => 'icon',
        'caption' => $this->tr->_('Remove'),
        'image' => $this->view->baseUrl() . '/images/icons/remove.png'
      )
    ));

    $this->view->grid = $grid;

  } // /function
  
  /**
   * Add new user  
   */
  public function addAction()
  {
    $config = new Zend_Config_Ini(dirname(__FILE__) . '/../../config.ini', array('database', 'contact'));

    $defined_companyid = $this->getRequest()->getParam('companyid', false);

    $companies = new Companies();
    $users = new Users();

    $company_list = $companies->getList();

    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . '/user/add');

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Add new user'));

    $contact = new Zend_Form_Element_Select('companyid');
    $contact->setRequired(true);
    $contact->setLabel($this->tr->_('Works in company'));
    $contact->addMultiOptions($company_list);

    // Company ID is defined, set it as default
    if (!$this->getRequest()->isPost() && $defined_companyid !== false)
    {
      $contact->setValue($defined_companyid);
    }

    $fname = new Zend_Form_Element_Text('firstname');
    $fname->setRequired(true);
    $fname->setLabel($this->tr->_('First name'));
    $fname->addFilter('StringTrim');
    $fname->addValidator('NotEmpty', true);
    $fname->addValidator('StringLength', false, array(2, 100));

    $lname = new Zend_Form_Element_Text('lastname');
    $lname->setRequired(true);
    $lname->setLabel($this->tr->_('Last name'));
    $lname->addFilter('StringTrim');
    $lname->addValidator('NotEmpty', true);
    $lname->addValidator('StringLength', false, array(2, 100));

    $email = new Zend_Form_Element_Text('email');
    $email->setRequired(true);
    $email->setLabel($this->tr->_('E-Mail'));
    $email->addFilter('StringTrim');
    $email->addFilter('StringToLower');
    $email->addValidator('StringLength', false, array(7));
    $email->addValidator('EmailAddress');

    $phone = new Zend_Form_Element_Text('phone');
    $phone->setRequired(true);
    $phone->setLabel($this->tr->_('Phone number'));
    $phone->addFilter('StringTrim');
    $phone->addValidator('NotEmpty', true);
    $phone->addValidator('Digits', true);
    $phone->addValidator('StringLength', false, array(6, 100));

    $send = new Zend_Form_Element_Checkbox('send');
    $send->setLabel($this->tr->_('Send login information'));

    $form->addElement($fname);
    $form->addElement($lname);

    $form->addElement($contact);
    $form->addElement($email);
    $form->addElement($phone);

    $form->addElement($send);

    $form->addElement($submit);

    $form->addDisplayGroup(array('firstname', 'lastname'), 'name');
    $form->addDisplayGroup(array('companyid', 'email','phone'), 'contact');
    $form->addDisplayGroup(array('send'), 'send');
    $form->addDisplayGroup(array('submit'), 'submit');

    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();
        
        $exists = $users->emailExists($values['email']);
        
        if(!$exists)
        {
          // Generate new password
          $new_password = uniqid(md5(mt_rand()), true);
  
          // Translate it to SQL format: md5 checksum (salt + new password)
          $pw = md5($config->salt . $new_password);
          
          $insert = array(
            'firstname' => $values['firstname'],
            'lastname' => $values['lastname'],
            'email' => $values['email'],
            'phone' => $values['phone'],
            'companyid' => $values['companyid'],
            'password' => $pw
          );
          
          $this->_db->beginTransaction();
  
          try
          {
            // Add new user to database
            $users->insert($insert);
  
            $this->_db->commit();
            
            // Send login information checkbox was checked. Send email.
            if($form->getElement('send')->isChecked())
            {
              $body = sprintf($this->tr->_("You have been added.\nLogin: %s\nPassword: %s\n\n-- \nXoNoiD"), $values['email'], $new_password);
            
              $mail = new Zend_Mail('UTF-8');
              $mail->setBodyText($body);
              $mail->setFrom($config->sender, 'XoNoiD');
              $mail->addTo($values['email'], $values['email']);
              $mail->setSubject($this->tr->_('[XoNoiD] Register'));
              $mail->send();
            }
  
            return $this->_helper->redirector->gotoUrl("/user");
  
          }
          catch (Exception $e)
          {
            $this->_db->rollBack();
            var_dump($e);
          }
        }
        else
        {
          $err = $this->tr->_("Given e-mail address exists already");
          $form->getElement('email')->markAsError();
          $form->getElement('email')->addError($err);
        }

      }
    }

    $this->view->form = $form;

  } // /function

  public function manageAction()
  {
    // @TODO
  }

  public function removeAction()
  {
    // @TODO
  }

  public function editAction()
  {
    $userid = $this->getRequest()->getParam('id', false);
    
    if ($userid === false)
    {
      throw new Zend_Exception("Fail.");
    }

    $companies = new Companies();
    $users = new Users();

    $company_list = $companies->getList();
    
    $select = $users->select();
    $select->from($users, array('firstname', 'lastname', 'email', 'phone', 'companyid'));
    $select->where('id = ?', $userid);

    $userdata = $users->fetchRow($select)->toArray();

    // Generate form
    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . "/user/edit/id/$userid");

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Edit user'));

    $contact = new Zend_Form_Element_Select('companyid');
    $contact->setRequired(true);
    $contact->setLabel($this->tr->_('Works in company'));
    $contact->addMultiOptions($company_list);

    // Load default value
    if (!$this->getRequest()->isPost())
    {
      $contact->setValue($userdata['companyid']);
    }

    $fname = new Zend_Form_Element_Text('firstname');
    $fname->setRequired(true);
    $fname->setLabel($this->tr->_('First name'));
    $fname->addFilter('StringTrim');
    $fname->addValidator('NotEmpty', true);
    $fname->addValidator('StringLength', false, array(2, 100));

    // Load default value
    if (!$this->getRequest()->isPost())
    {
      $fname->setValue($userdata['firstname']);
    }

    $lname = new Zend_Form_Element_Text('lastname');
    $lname->setRequired(true);
    $lname->setLabel($this->tr->_('Last name'));
    $lname->addFilter('StringTrim');
    $lname->addValidator('NotEmpty', true);
    $lname->addValidator('StringLength', false, array(2, 100));

    // Load default value
    if (!$this->getRequest()->isPost())
    {
      $lname->setValue($userdata['lastname']);
    }


    $email = new Zend_Form_Element_Text('email');
    $email->setRequired(true);
    $email->setLabel($this->tr->_('E-Mail'));
    $email->addFilter('StringTrim');
    $email->addFilter('StringToLower');
    $email->addValidator('StringLength', false, array(7));
    $email->addValidator('EmailAddress');

    // Load default value
    if (!$this->getRequest()->isPost())
    {
      $email->setValue($userdata['email']);
    }


    $phone = new Zend_Form_Element_Text('phone');
    $phone->setRequired(true);
    $phone->setLabel($this->tr->_('Phone number'));
    $phone->addFilter('StringTrim');
    $phone->addValidator('NotEmpty', true);
    $phone->addValidator('Digits', true);
    $phone->addValidator('StringLength', false, array(6, 100));

    // Load default value
    if (!$this->getRequest()->isPost())
    {
      $phone->setValue($userdata['phone']);
    }


    $form->addElement($fname);
    $form->addElement($lname);

    $form->addElement($contact);
    $form->addElement($email);
    $form->addElement($phone);

    $form->addElement($submit);

    $form->addDisplayGroup(array('firstname', 'lastname'), 'name');
    $form->addDisplayGroup(array('companyid', 'email','phone'), 'contact');
    $form->addDisplayGroup(array('submit'), 'submit');

    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();
        
        $exists = $users->emailExists($values['email']);
        $dbuserid = $users->emailToID($values['email']);

        if (!$exists || ($exists && $userid == $dbuserid))
        {
          $update = array(
            'firstname' => $values['firstname'],
            'lastname' => $values['lastname'],
            'email' => $values['email'],
            'phone' => $values['phone'],
            'companyid' => $values['companyid']
          );
          
          $this->_db->beginTransaction();
  
          try
          {
            // Update user information
            $users->update($update, $users->getAdapter()->quoteInto('id = ?', $userid));
  
            $this->_db->commit();
            
            return $this->_helper->redirector->gotoUrl("/user");
  
          }
          catch (Exception $e)
          {
            $this->_db->rollBack();
            var_dump($e);
          } // /catch

        }
        else
        {
          $err = $this->tr->_("Given e-mail address is in use");
          $form->getElement('email')->markAsError();
          $form->getElement('email')->addError($err);
        }

      } // /if is valid
    } // /if is POST

    $this->view->form = $form;

  } // /function

} // /class
