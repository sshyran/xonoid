<?php
class CompanyController extends Zend_Controller_Action 
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

  /**
   * Initialize  
   */
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

  /**
   * Front page  
   */
  public function indexAction()
  {
    $companies = new Companies();
    $company_list = $companies->getCompanyRootList();

    $grid = new Core_DataGrid(new Core_DataGrid_DataSource_Array($company_list), 100);
    $grid->setDefaultSort(array('name' => 'asc'));

    $grid->addColumn('manage', array(
      'header' => $this->tr->_('Manage'),
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
          'url' => $this->view->baseUrl() . '/company/manage/id/$id/',
          'class' => 'icon',
          'caption' => $this->tr->_('Edit'),
          'image' => $this->view->baseUrl() . '/images/icons/view.png'
        )
      )
    );

    $grid->addColumn('id', new Core_DataGrid_Column('text', $this->tr->_('Id'), 1, 'left'));
    $grid->addColumn('name', new Core_DataGrid_Column('text', $this->tr->_('Title'), null , 'left'));

    $grid->addColumn('edit', array(
      'header' => $this->tr->_('Edit'),
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/edit/id/$id/',
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
        'url' => $this->view->baseUrl() . '/company/remove/id/$id/',
        'class' => 'icon',
        'caption' => $this->tr->_('Remove'),
        'image' => $this->view->baseUrl() . '/images/icons/remove.png'
      )
    ));

    $this->view->grid = $grid;
  }

  /**
   * Add new company billing information  
   */
  public function addAction()
  {
    $companies = new Companies();
    $branches = new CompanyBranchOffices();

    $is_root_company = false;

    $parentcompanyid = $this->getRequest()->getParam('parentcompanyid', false);
    
    if ($parentcompanyid === false)
    {
      $add_to_parent = false;
    }
    else
    {
      $add_to_parent = true;
      $is_root_company = $companies->isRootCompany($parentcompanyid);
    }

    $this->view->add_to_parent = $add_to_parent;
    $this->view->parentcompanyid = $parentcompanyid;
    $this->view->is_root_company = $is_root_company;

    $users = new Users();
    $users_list = $users->getList();
  
    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . $this->getHelper('url')->url(array('controller' => 'company', 'action' => 'add', 'parentcompanyid' => $parentcompanyid), '', true));

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Add'));

    $contact = new Zend_Form_Element_Select('contactid');
    $contact->setRequired(true);
    $contact->setLabel($this->tr->_('Contact person'));
    $contact->addMultiOptions($users_list);

    $name = new Zend_Form_Element_Text('name');
    $name->setRequired(true);
    $name->setLabel($this->tr->_('Company name'));
    $name->addFilter('StringTrim');
    $name->addValidator('NotEmpty', true);
    $name->addValidator('StringLength', false, array(3, 100));

    $street = new Zend_Form_Element_Text('streetaddress');
    $street->setRequired(true);
    $street->setLabel($this->tr->_('Street'));
    $street->addFilter('StringTrim');

    $code = new Zend_Form_Element_Text('postnumber');
    $code->setRequired(true);
    $code->setLabel($this->tr->_('ZIP Code'));
    $code->addFilter('StringTrim');
    $code->addValidator('Digits');

    $office = new Zend_Form_Element_Text('postoffice');
    $office->setRequired(true);
    $office->setLabel($this->tr->_('Post office'));
    $office->addFilter('StringTrim');
    $office->addValidator('Alnum');

    $form->addElement($name);

    $form->addElement($contact);

    $form->addElement($street);
    $form->addElement($code);
    $form->addElement($office);

    $form->addElement($submit);

    $form->addDisplayGroup(array('name'), 'name');
    $form->addDisplayGroup(array('contactid'), 'contact');
    $form->addDisplayGroup(array('streetaddress', 'postnumber', 'postoffice'), 'address');
    $form->addDisplayGroup(array('submit'), 'submit');

    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();
        
        $exists = $branches->addressExists($values['streetaddress'], $values['postnumber'], $values['postoffice']);
  
        if(!$exists)
        {      
          if ($add_to_parent)
          {
            $resellerid = $parentcompanyid;
          }
          else
          {
            $resellerid = new Zend_Db_Expr('NULL');
          }
          
          if ($add_to_parent && !$is_root_company)
          {
            // multilevel hierarcy is invalid
            throw new Zend_Exception($this->tr->_("No rights."));
          }
          
          $insert = array(
            'name' => $values['name'],
            'contactid' => $values['contactid'],
            'streetaddress' => $values['streetaddress'],
            'postnumber' => $values['postnumber'],
            'postoffice' => $values['postoffice'],
            'resellerid' => $resellerid
          );
          
          $this->_db->beginTransaction();
  
          try
          {
            $companies->insert($insert);
  
            $this->_db->commit();
  
            if ($add_to_parent)
            {
              return $this->_helper->redirector->gotoUrl("/company/manage/id/$resellerid");
            }
            else
            {
              return $this->_helper->redirector->gotoUrl("/company");
            }
            
  
          }
          catch (Exception $e)
          {
            $this->_db->rollBack();
            var_dump($e);
          }
        }
        else
        {
          $err = $this->tr->_("Given address exists already");
          $form->getElement('streetaddress')->markAsError();
          $form->getElement('streetaddress')->addError($err);
          $form->getElement('postnumber')->markAsError();
          $form->getElement('postnumber')->addError($err);
          $form->getElement('postoffice')->markAsError();
          $form->getElement('postoffice')->addError($err);
        }

      }
    }

    $this->view->form = $form;
    
  }
  
  /**
   * Edit company billing information
   */
  public function editAction()
  {
    $companyid = $this->getRequest()->getParam('id', false);
    
    if ($companyid === false)
    {
      throw new Zend_Exception("Fail.");
    }
    
    // TODO
  }

  /**
   * Remove company billing information
   */
  public function removeAction()
  {
    $companyid = $this->getRequest()->getParam('id', false);
    
    if ($companyid === false)
    {
      throw new Zend_Exception("Fail.");
    }

    $this->view->companyid = $companyid;

    $companies = new Companies();
    
    $is_root_company = $companies->isRootCompany($companyid);
    $this->view->is_root_company = $is_root_company;

    $this->view->companyname = $companies->getName($companyid); 

    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . "/company/remove/id/$companyid");

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Remove company'));
    
    $form->addElement($submit);

    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $this->_db->beginTransaction();

        try
        {
          $companies->delete($companies->getAdapter()->quoteInto('id = ?', $companyid));

          $this->_db->commit();

          return $this->_helper->redirector->gotoUrl("/company");

        }
        catch (Exception $e)
        {
          $this->_db->rollBack();
          var_dump($e);
        }
       
      }
    }

    $this->view->form = $form;
  }


  /**
   * Manage company
   */
  public function manageAction()
  {
    $companyid = $this->getRequest()->getParam('id', false);
    
    if ($companyid === false)
    {
      throw new Zend_Exception("Fail.");
    }
    
    $this->view->companyid = $companyid;
    $Companies = new Companies();
    $this->view->companyname = $Companies->getName($companyid); 

    $branches = new CompanyBranchOffices();
    $branches_list = $branches->getBranchesList($companyid);
    
    $is_root_company = $Companies->isRootCompany($companyid);
    $this->view->is_root_company = $is_root_company;
    
    $parent_company_id = $Companies->getParentID($companyid);
    $this->view->parent_company_id = $parent_company_id;

    // Branch list
    $grid = new Core_DataGrid(new Core_DataGrid_DataSource_Array($branches_list), 100);
    $grid->setDefaultSort(array('name' => 'asc'));

    $grid->addColumn('manage', array(
      'header' => $this->tr->_('Manage'),
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/manage-branch/id/$id/',
        'class' => 'icon',
        'caption' => $this->tr->_('Manage'),
        'image' => $this->view->baseUrl() . '/images/icons/view.png'
      )
    ));

    $grid->addColumn('id', new Core_DataGrid_Column('text', $this->tr->_('Id'), 1, 'left'));
    $grid->addColumn('name', new Core_DataGrid_Column('text', $this->tr->_('Title'), null , 'left'));

    $grid->addColumn('streetaddress', new Core_DataGrid_Column('text', $this->tr->_('Street address'), null , 'left'));
    $grid->addColumn('postnumber', new Core_DataGrid_Column('text', $this->tr->_('Post number'), null , 'left'));
    $grid->addColumn('postoffice', new Core_DataGrid_Column('text', $this->tr->_('Post office'), null , 'left'));


    $grid->addColumn('edit', array(
      'header' => $this->tr->_('Edit'),
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/edit-branch/id/$id/',
        'class' => 'icon',
        'caption' => $this->tr->_('Edit'),
        'image' => $this->view->baseUrl() . '/images/icons/edit.png'
      )
    ));

    $this->view->branches = $grid;

    // Customer list
    $customer_list = $Companies->getCompanyCustomerList($companyid);

    if(!empty($customer_list))
    {
      $grid = new Core_DataGrid(new Core_DataGrid_DataSource_Array($customer_list), 100);
      $grid->setDefaultSort(array('name' => 'asc'));
  
      $grid->addColumn('manage', array(
        'header' => $this->tr->_('Manage'),
        'sortable' => false,
        'type' => 'action',
        'width' => 1,
        'actions' => array(
          'url' => $this->view->baseUrl() . '/company/manage/id/$id/',
          'class' => 'icon',
          'caption' => $this->tr->_('Edit'),
          'image' => $this->view->baseUrl() . '/images/icons/view.png'
        )
      ));
  
      $grid->addColumn('id', new Core_DataGrid_Column('text', $this->tr->_('Id'), 1, 'left'));
      $grid->addColumn('name', new Core_DataGrid_Column('text', $this->tr->_('Title'), null , 'left'));
  
      $grid->addColumn('streetaddress', new Core_DataGrid_Column('text', $this->tr->_('Street address'), null , 'left'));
      $grid->addColumn('postnumber', new Core_DataGrid_Column('text', $this->tr->_('Post number'), null , 'left'));
      $grid->addColumn('postoffice', new Core_DataGrid_Column('text', $this->tr->_('Post office'), null , 'left'));
  
      $grid->addColumn('edit', array(
        'header' => $this->tr->_('Edit'),
        'sortable' => false,
        'width' => 1,
        'type' => 'action',
        'actions' => array(
          'url' => $this->view->baseUrl() . '/company/edit/id/$id/',
          'class' => 'icon',
          'caption' => $this->tr->_('Edit'),
          'image' => $this->view->baseUrl() . '/images/icons/edit.png'
        )
      ));
  
      $this->view->customers = $grid;
    }
    else
    {
      $this->view->customers = null;
    }

    $users = new Users();
    
    $user_list = $users->fetchAll("companyid = $companyid")->toArray();

    // User list
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

    $this->view->users = $grid;

  }

  /**
   * Add branch office to company
   */
  public function addBranchOfficeAction()
  {
    $companyid = $this->getRequest()->getParam('companyid', false);
    
    if ($companyid === false)
    {
      throw new Zend_Exception($this->tr->_("Fail."));
    }

    $users = new Users();
    $users_list = $users->getList();

    $companies = new Companies();
    $this->view->companyid = $companyid;
    $this->view->companyname = $companies->getName($companyid);

    $branches = new CompanyBranchOffices();

    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . "/company/add-branch-office/companyid/$companyid");

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Add'));

    $contact = new Zend_Form_Element_Select('contactid');
    $contact->setRequired(true);
    $contact->setLabel($this->tr->_('Contact person'));
    $contact->addMultiOptions($users_list);

    $name = new Zend_Form_Element_Text('name');
    $name->setRequired(true);
    $name->setLabel($this->tr->_('Name'));
    $name->addFilter('StringTrim');
    $name->addValidator('NotEmpty', true);
    $name->addValidator('StringLength', false, array(3, 100));

    $company_address = $companies->getAddress($companyid);

    // Load default value
    if (!$this->getRequest()->isPost())
    {
      $name->setValue($companies->getName($companyid) . ' - ' . $company_address['postoffice']);
    }

    $street = new Zend_Form_Element_Text('streetaddress');
    $street->setRequired(true);
    $street->setLabel($this->tr->_('Street'));
    $street->addFilter('StringTrim');

    // Load default value
    if (!$this->getRequest()->isPost())
    {
      $street->setValue($company_address['streetaddress']);
    }


    $code = new Zend_Form_Element_Text('postnumber');
    $code->setRequired(true);
    $code->setLabel($this->tr->_('ZIP Code'));
    $code->addFilter('StringTrim');
    $code->addValidator('Digits');

    // Load default value
    if (!$this->getRequest()->isPost())
    {
      $code->setValue($company_address['postnumber']);
    }


    $office = new Zend_Form_Element_Text('postoffice');
    $office->setRequired(true);
    $office->setLabel($this->tr->_('Post office'));
    $office->addFilter('StringTrim');
    $office->addValidator('Alnum');

    // Load default value
    if (!$this->getRequest()->isPost())
    {
      $office->setValue($company_address['postoffice']);
    }


    $form->addElement($name);

    $form->addElement($contact);

    $form->addElement($street);
    $form->addElement($code);
    $form->addElement($office);

    $form->addElement($submit);

    $form->addDisplayGroup(array('name'), 'name');
    $form->addDisplayGroup(array('contactid'), 'contact');
    $form->addDisplayGroup(array('streetaddress', 'postnumber', 'postoffice'), 'address');
    $form->addDisplayGroup(array('submit'), 'submit');

    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();
        
        $exists = $branches->addressExists($values['streetaddress'], $values['postnumber'], $values['postoffice']);
        
        if(!$exists)
        {
          $insert = array(
            'name' => $values['name'],
            'contactid' => $values['contactid'],
            'streetaddress' => $values['streetaddress'],
            'postnumber' => $values['postnumber'],
            'postoffice' => $values['postoffice'],
            'companyid' => $companyid
          );
          
          $this->_db->beginTransaction();
  
          try
          {
            $branches->insert($insert);
  
            $this->_db->commit();
  
            return $this->_helper->redirector->gotoUrl("/company/manage/id/$companyid");
  
          }
          catch (Exception $e)
          {
            $this->_db->rollBack();
            var_dump($e);
          }
        }
        else
        {
          $err = $this->tr->_("Given address exists already");
          $form->getElement('streetaddress')->markAsError();
          $form->getElement('streetaddress')->addError($err);
          $form->getElement('postnumber')->markAsError();
          $form->getElement('postnumber')->addError($err);
          $form->getElement('postoffice')->markAsError();
          $form->getElement('postoffice')->addError($err);
        }

      }
    }

    $this->view->form = $form;
  
  }
  
  public function manageBranchAction()
  {
    $branchid = $this->getRequest()->getParam('id', false);
    
    if ($branchid === false)
    {
      throw new Zend_Exception("Fail.");
    }
    
    $this->view->branchid = $branchid;

    $CompanyBranchOffices = new CompanyBranchOffices();
    $this->view->branchname = $CompanyBranchOffices->getName($branchid);
    $this->view->companyid = $CompanyBranchOffices->getCompanyID($branchid);

    $Companies = new Companies();
    $this->view->companyname = $Companies->getName($this->view->companyid); 
    
    $NetworkDevices = new NetworkDevices();
    $devices_list = $NetworkDevices->getNetworkDevicesList($branchid);

    $grid = new Core_DataGrid(new Core_DataGrid_DataSource_Array($devices_list), 100);
    $grid->setDefaultSort(array('name' => 'asc'));

    $grid->addColumn('manage', array(
      'header' => $this->tr->_('Manage'),
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/manage-network-device/id/$id/',
        'class' => 'icon',
        'caption' => $this->tr->_('Manage'),
        'image' => $this->view->baseUrl() . '/images/icons/view.png'
      )
    ));

    $grid->addColumn('id', new Core_DataGrid_Column('text', $this->tr->_('Id'), 1, 'left'));
    $grid->addColumn('name', new Core_DataGrid_Column('text', $this->tr->_('Title'), null , 'left'));

    $grid->addColumn('usize', new Core_DataGrid_Column('text', $this->tr->_('Units'), null , 'left'));

    $grid->addColumn('edit', array(
      'header' => $this->tr->_('Edit'),
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/edit-network-device/id/$id/',
        'class' => 'icon',
        'caption' => $this->tr->_('Edit'),
        'image' => $this->view->baseUrl() . '/images/icons/edit.png'
      )
    ));

    $this->view->grid = $grid;

  }
  
  public function editBranchAction()
  {
    // TODO
  }
  
  public function addNetworkDeviceAction()
  {
    $branchid = $this->getRequest()->getParam('branchid', false);
    
    if ($branchid === false)
    {
      throw new Zend_Exception("Fail.");
    }

    $NetworkDevices = new NetworkDevices();

    $users = new Users();
    $users_list = $users->getList();

    $this->view->branchid = $branchid;
    
    $CompanyBranchOffices = new CompanyBranchOffices();
    $this->view->branchname = $CompanyBranchOffices->getName($branchid);
    $this->view->companyid = $CompanyBranchOffices->getCompanyID($branchid);

    $Companies = new Companies();
    $this->view->companyname = $Companies->getName($this->view->companyid); 

    $company_list = $Companies->getList();

    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . "/company/add-network-device/branchid/$branchid");

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Add'));

    $contact = new Zend_Form_Element_Select('contactid');
    $contact->setRequired(true);
    $contact->setLabel($this->tr->_('Contact person'));
    $contact->addMultiOptions($users_list);

    $company = new Zend_Form_Element_Select('companyid');
    $company->setRequired(true);
    $company->setLabel($this->tr->_('Responsible company'));
    $company->addMultiOptions($company_list);

    $name = new Zend_Form_Element_Text('name');
    $name->setRequired(true);
    $name->setLabel($this->tr->_('Name'));
    $name->addFilter('StringTrim');
    $name->addValidator('NotEmpty', true);
    $name->addValidator('StringLength', false, array(3, 100));

    $size = new Zend_Form_Element_Text('usize');
    $size->setRequired(true);
    $size->setLabel($this->tr->_('Rack Unit Size'));
    $size->addFilter('StringTrim');
    $size->addValidator('Digits', true);

    // Load default value
    if (!$this->getRequest()->isPost())
    {
      $size->setValue(1);
    }

    $form->addElement($name);
    $form->addElement($size);

    $form->addElement($contact);
    $form->addElement($company);

    $form->addElement($submit);

/*
    $form->addDisplayGroup(array('name'), 'name');
    $form->addDisplayGroup(array('contactid'), 'contact');
    $form->addDisplayGroup(array('submit'), 'submit');
*/

    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();
        
        $insert = array(
          'name' => $values['name'],
          'usize' => $values['usize'],
          'contactid' => $values['contactid'],
          'companyid' => $values['companyid'],
          'branchid' => $branchid
        );
        
        $this->_db->beginTransaction();

        try
        {
          $NetworkDevices->insert($insert);

          $this->_db->commit();

          return $this->_helper->redirector->gotoUrl("/company/manage-branch/id/$branchid");

        }
        catch (Exception $e)
        {
          $this->_db->rollBack();
          var_dump($e);
        }

      }
    }

    $this->view->form = $form;

  }
  
  public function manageNetworkDeviceAction()
  {
    $networkdeviceid = $this->getRequest()->getParam('id', false);
    
    if ($networkdeviceid === false)
    {
      throw new Zend_Exception("Fail.");
    }
    
    $this->view->deviceid = $networkdeviceid;
    
    $NetworkDevices = new NetworkDevices();
    $this->view->devicename = $NetworkDevices->getName($networkdeviceid); 
    $this->view->branchid = $NetworkDevices->getBranchID($networkdeviceid);

    $CompanyBranchOffices = new CompanyBranchOffices();
    $this->view->branchname = $CompanyBranchOffices->getName($this->view->branchid);
    $this->view->companyid = $CompanyBranchOffices->getCompanyID($this->view->branchid);

    $Companies = new Companies();
    $this->view->companyname = $Companies->getName($this->view->companyid); 
    
    $NetworkDevicePorts = new NetworkDevicePorts();
    $VIEW_NUP_PT = new VIEW_NUP_PT();
    $portlist = $VIEW_NUP_PT->fetchAll("networkunitid = $networkdeviceid")->toArray();

    $grid = new Core_DataGrid(new Core_DataGrid_DataSource_Array($portlist), 100);
    $grid->setDefaultSort(array('name' => 'asc'));

    $grid->addColumn('manage', array(
      'header' => $this->tr->_('Manage'),
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/manage-network-device-port/id/$id/',
        'class' => 'icon',
        'caption' => $this->tr->_('Manage'),
        'image' => $this->view->baseUrl() . '/images/icons/view.png'
      )
    ));

    $grid->addColumn('id', new Core_DataGrid_Column('text', $this->tr->_('Id'), 1, 'left'));
    $grid->addColumn('name', new Core_DataGrid_Column('text', $this->tr->_('Title'), null , 'left'));

    $grid->addColumn('side', new Core_DataGrid_Column('text', $this->tr->_('Side'), null , 'left'));

    $grid->addColumn('porttypename', new Core_DataGrid_Column('text', $this->tr->_('Port type'), null , 'left'));

    $grid->addColumn('edit', array(
      'header' => $this->tr->_('Edit'),
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/edit-port/id/$id/',
        'class' => 'icon',
        'caption' => $this->tr->_('Edit'),
        'image' => $this->view->baseUrl() . '/images/icons/edit.png'
      )
    ));

    $this->view->grid = $grid;

  
  }

  public function addNetworkDevicePortAction()
  {
    $networkdeviceid = $this->getRequest()->getParam('networkdeviceid', false);
    
    if ($networkdeviceid === false)
    {
      throw new Zend_Exception("Fail.");
    }

    $this->view->networkdeviceid = $networkdeviceid;

    $NetworkDevices = new NetworkDevices();
    $NetworkDevicePorts = new NetworkDevicePorts();

    $this->view->devicename = $NetworkDevices->getName($networkdeviceid); 
    $this->view->branchid = $NetworkDevices->getBranchID($networkdeviceid);

    $CompanyBranchOffices = new CompanyBranchOffices();
    $this->view->branchname = $CompanyBranchOffices->getName($this->view->branchid);
    $this->view->companyid = $CompanyBranchOffices->getCompanyID($this->view->branchid);

    $Companies = new Companies();
    $this->view->companyname = $Companies->getName($this->view->companyid); 

    $Ports = new Ports();
    $ports_list = $Ports->getList();

    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . "/company/add-network-device-port/networkdeviceid/$networkdeviceid");

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Add'));

    $name = new Zend_Form_Element_Text('name');
    $name->setRequired(true);
    $name->setLabel($this->tr->_('Name'));
    $name->addFilter('StringTrim');
    $name->addValidator('NotEmpty', true);
    $name->addValidator('StringLength', false, array(3, 100));

    // Load default value
    if (!$this->getRequest()->isPost())
    {
      $name->setValue("Port 1");
    }


    $sidearr = array();
    $sidearr['F'] = $this->tr->_('Front side');
    $sidearr['B'] = $this->tr->_('Back side');
    
    $side = new Zend_Form_Element_Select('side');
    $side->setRequired(true);
    $side->setLabel($this->tr->_('Side'));
    $side->addMultiOptions($sidearr);

    $porttype = new Zend_Form_Element_Select('porttypeid');
    $porttype->setRequired(true);
    $porttype->setLabel($this->tr->_('Port type'));
    $porttype->addMultiOptions($ports_list);

    $form->addElement($name);

    $form->addElement($porttype);

    $form->addElement($side);

    $form->addElement($submit);

/*
    $form->addDisplayGroup(array('name'), 'name');
    $form->addDisplayGroup(array('contactid'), 'contact');
    $form->addDisplayGroup(array('submit'), 'submit');
*/

    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();
        
        $insert = array(
          'name' => $values['name'],
          'side' => $values['side'],
          'porttypeid' => $values['porttypeid'],
          'networkunitid' => $networkdeviceid
        );
        
        $this->_db->beginTransaction();

        try
        {
          $NetworkDevicePorts->insert($insert);

          $this->_db->commit();

          return $this->_helper->redirector->gotoUrl("/company/manage-network-device/id/$networkdeviceid");

        }
        catch (Exception $e)
        {
          $this->_db->rollBack();
          var_dump($e);
        }

      }
    }

    $this->view->form = $form;
  
  }

  public function manageNetworkDevicePortAction()
  {
    $portid = $this->getRequest()->getParam('id', false);

    if ($portid === false)
    {
      throw new Zend_Exception("Fail.");
    }
    
    $this->view->portid = $portid;
    
    $NetworkDevicePorts = new NetworkDevicePorts();
    $networkdeviceid = $NetworkDevicePorts->getNetworkDevice($portid);
    $networkdevicename = $NetworkDevicePorts->getNetworkDeviceName($portid);
    $this->view->deviceid = $networkdeviceid;
    $this->view->portname = $networkdevicename;

    $NetworkDevices = new NetworkDevices();
    $this->view->devicename = $NetworkDevices->getName($networkdeviceid); 
    $this->view->branchid = $NetworkDevices->getBranchID($networkdeviceid);

    $CompanyBranchOffices = new CompanyBranchOffices();
    $this->view->branchname = $CompanyBranchOffices->getName($this->view->branchid);
    $this->view->companyid = $CompanyBranchOffices->getCompanyID($this->view->branchid);

    $Companies = new Companies();
    $this->view->companyname = $Companies->getName($this->view->companyid); 
    
    $VIEW_P_IP = new VIEW_P_IP();

    $list = $VIEW_P_IP->fetchAll("portid = $portid")->toArray();

    $grid = new Core_DataGrid(new Core_DataGrid_DataSource_Array($list), 100);
    $grid->setDefaultSort(array('ipaddress' => 'asc'));

    $grid->addColumn('id', new Core_DataGrid_Column('text', $this->tr->_('Id'), 1, 'left'));
    $grid->addColumn('ipaddress', new Core_DataGrid_Column('text', $this->tr->_('IP Address'), null , 'left'));

    $grid->addColumn('cidr', new Core_DataGrid_Column('text', $this->tr->_('CIDR'), null , 'left'));
    $grid->addColumn('mask', new Core_DataGrid_Column('text', $this->tr->_('Mask'), null , 'left'));

    $grid->addColumn('network', new Core_DataGrid_Column('text', $this->tr->_('Network'), null , 'left'));
    $grid->addColumn('broadcast', new Core_DataGrid_Column('text', $this->tr->_('Broadcast'), null , 'left'));

    $grid->addColumn('ipcount', new Core_DataGrid_Column('text', $this->tr->_('IP Count'), null , 'left'));

    $grid->addColumn('edit', array(
      'header' => $this->tr->_('Edit'),
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/edit-port/id/$id/',
        'class' => 'icon',
        'caption' => $this->tr->_('Edit'),
        'image' => $this->view->baseUrl() . '/images/icons/edit.png'
      )
    ));

    $this->view->ips = $grid;
    
    $VLANs = new VLANs();

    $list = $VLANs->fetchAll("portid = $portid")->toArray();

    $grid = new Core_DataGrid(new Core_DataGrid_DataSource_Array($list), 100);
    $grid->setDefaultSort(array('vlanid' => 'asc'));

    $grid->addColumn('id', new Core_DataGrid_Column('text', $this->tr->_('Id'), 1, 'left'));
    $grid->addColumn('vlanid', new Core_DataGrid_Column('text', $this->tr->_('VLAN ID'), null , 'left'));

    $grid->addColumn('edit', array(
      'header' => $this->tr->_('Edit'),
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/edit-port/id/$id/',
        'class' => 'icon',
        'caption' => $this->tr->_('Edit'),
        'image' => $this->view->baseUrl() . '/images/icons/edit.png'
      )
    ));

    $this->view->vlans = $grid;


  }


  /**
   * Add VLAN ID to port
   */
  public function addVlanAction()
  {
    $portid = $this->getRequest()->getParam('portid', false);

    if ($portid === false)
    {
      throw new Zend_Exception("Fail.");
    }

    $this->view->portid = $portid;
    
    $VLANs = new VLANs();

    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . "/company/add-vlan/portid/$portid");

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Add'));

    $vlan = new Zend_Form_Element_Text('vlan');
    $vlan->setRequired(true);
    $vlan->setLabel($this->tr->_('VLAN ID'));
    $vlan->addFilter('StringTrim');
    $vlan->addValidator('NotEmpty', true);
    $vlan->addValidator('Between', false, array('min' => 0, 'max' => 4096));

    // Load default as 1
    if (!$this->getRequest()->isPost())
    {
      $vlan->setValue(1);
    }

    $form->addElement($vlan);
    $form->addElement($submit);


    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();
        
        $insert = array(
          'vlanid' => $values['vlan'],
          'portid' => $portid
        );

        $this->_db->beginTransaction();

        try
        {
          $VLANs->insert($insert);

          $this->_db->commit();

          return $this->_helper->redirector->gotoUrl("/company/manage-network-device-port/id/$portid");

        }
        catch (Exception $e)
        {
          $this->_db->rollBack();
          var_dump($e);
        }

      }
    }

    $this->view->form = $form;

  }

  /**
   * Add IPv4 Address to port  
   */
  public function addIpAddressAction()
  {
    $portid = $this->getRequest()->getParam('portid', false);

    if ($portid === false)
    {
      throw new Zend_Exception("Fail.");
    }

    $this->view->portid = $portid;
    
    $IPAddresses = new IPAddresses();

    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . "/company/add-ip-address/portid/$portid");

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Add'));

    $ip = new Zend_Form_Element_Text('ip');
    $ip->setRequired(true);
    $ip->setLabel($this->tr->_('IPv4 Address'));
    $ip->addFilter('StringTrim');
    $ip->addValidator('NotEmpty', true);
    $ip->addValidator('Ip', false);

    $cidr_list = range(0,32);
    
    foreach ($cidr_list as $key => $value)
    {
      $count = (float)pow(2, (32-$value));
      $for_use = null;

      if($value <= 30)
      {
        $for_use = sprintf("%s IP(s) for use [Network,BC,GW]", $count-3);
      }
      
      $mask = $value == 0 ? long2ip(0) : long2ip(0xffffffff << (32-$value));

      $cidr_list[$key] = sprintf("/%s (Mask: %s), %s IP(s) %s", $value, $mask, $count, $for_use);
    }
    
    // /31 is bogus
    unset($cidr_list[31]);
    
    $cidr_list = array_reverse($cidr_list, true);

    $cidr = new Zend_Form_Element_Select('cidr');
    $cidr->setRequired(true);
    $cidr->setLabel($this->tr->_('CIDR'));
    $cidr->addMultiOptions($cidr_list);
    $cidr->addValidator('Between', false, array('min' => 0, 'max' => 32));

    // Load default as one IP (/32)
    if (!$this->getRequest()->isPost())
    {
      $cidr->setValue(28);
    }

    $form->addElement($ip);
    $form->addElement($cidr);
    $form->addElement($submit);

    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();
        
        $insert = array(
          'ipaddr' => new Zend_Db_Expr("INET_ATON('{$values['ip']}')"),
          'cidr' => $values['cidr'],
          'portid' => $portid
        );

        $this->_db->beginTransaction();

        try
        {
          $IPAddresses->insert($insert);

          $this->_db->commit();

          return $this->_helper->redirector->gotoUrl("/company/manage-network-device-port/id/$portid");

        }
        catch (Exception $e)
        {
          $this->_db->rollBack();
          var_dump($e);
        }

      }
    }

    $this->view->form = $form;

  } // /function
  
  public function branchToPdfAction()
  {
    $branchid = $this->getRequest()->getParam('id', false);
    
    if ($branchid === false)
    {
      throw new Zend_Exception("Fail.");
    }
    
    $o = null;
    
    $pdf = new crmPDF();
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM); 
    $pdf->AliasNbPages();

    $pdf->AddPage(); 

    $CompanyBranchOffices = new CompanyBranchOffices();
    $NetworkDevices = new NetworkDevices();
    $Companies = new Companies();

    $branchname = $CompanyBranchOffices->getName($branchid);
    $companyid = $CompanyBranchOffices->getCompanyID($branchid);
    $companyname = $Companies->getName($companyid); 

    $devices_list = $NetworkDevices->getNetworkDevicesList($branchid);
    
    $o .= "<h1>$companyname</h1>";
    $o .= "<h2>Office $branchname</h2>";

    $branch = $CompanyBranchOffices->fetchRow("id = $branchid")->toArray();

    $o .= "{$branch['streetaddress']}<br />";
    $o .= "{$branch['postnumber']} {$branch['postoffice']}<br />";
   
    foreach($devices_list as $d => $dev)
    {
      $o .= "<h3>{$dev['name']} ({$dev['usize']}U)</h3>";

      $o .= "<table>";

      $VIEW_NUP_PT = new VIEW_NUP_PT();
      $portlist = $VIEW_NUP_PT->fetchAll("networkunitid = {$dev['id']}")->toArray();

      foreach($portlist as $p => $port)
      {
        $o .= "<tr>";

        $o .= "<td>";

        switch($port['side'])
        {
          default:
          case 'F':
            $o .= $this->tr->_("Front side");
          break;
          case 'B':
            $o .= $this->tr->_("Back side");
          break;
        }

        $o .= ' : ';

        $o .= $port['name'];
        $o .= '<br />';

        $o .= $port['porttypename'];
        $o .= '<br />';

        $o .= "</td>";

        $VIEW_P_IP = new VIEW_P_IP();
        $iplist = $VIEW_P_IP->fetchAll("portid = {$port['id']}")->toArray();

        $o .= "<td>";

        $o .= $this->tr->_("IP Adresses:"); 
        $o .= "<br />";

        foreach($iplist as $i => $ip)
        {
          $o .= $ip['ipaddress'] . '/' . $ip['cidr'] . '<br />';
        } // /foreach ip list

        $o .= "</td>";

        $o .= "<td>";

        $o .= "VLAN IDs:<br />";

        $VLANs = new VLANs();

        $vlanlist = $VLANs->fetchAll("portid = {$port['id']}")->toArray();

        foreach($vlanlist as $v => $vlan)
        {
          $o .= $vlan['vlanid'] . '<br />';
        } // /foreach ip list

        $o .= "</td>";

        $o .= "</tr>";
        
      } // /foreach port list

      $o .= "</table>";


    } // /foreach devices list
    
   
    $pdf->writeHTML($o); 

    $this->_helper->layout->disableLayout();

    $response = $this->getResponse();
    $response->setHeader('Content-Type', 'application/pdf', true);
    $fname = sprintf('"%s - %s (%s).pdf"', $companyname, $branchname, date("j.n.Y"));
    $response->setHeader('Content-Disposition', "attachment; filename=$fname", true);
    
    $pdf->lastPage(); 

    $this->view->pdf = $pdf->Output('', 'S');
  } // /function

} // /class
