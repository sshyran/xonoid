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
      'header' => 'Manage',
      'sortable' => false,
      'type' => 'action',
      'actions' => array(
          'url' => $this->view->baseUrl() . '/company/manage/id/$id/',
          'class' => 'icon',
          'caption' => 'Edit',
          'image' => $this->view->baseUrl() . '/images/icons/view.png'
        )
      )
    );

		$grid->addColumn('id', new Core_DataGrid_Column('id', 'Id', null , 'left'));
		$grid->addColumn('name', new Core_DataGrid_Column('name', 'Title', null , 'left'));

    $grid->addColumn('edit', array(
      'header' => 'Edit',
      'sortable' => false,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/edit/id/$id/',
        'class' => 'icon',
        'caption' => 'Edit',
        'image' => $this->view->baseUrl() . '/images/icons/edit.png'
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
          throw new Zend_Exception("No rights.");
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
   * Edit company billing information
   */
  public function editAction()
  {
    // @TODO
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

    $grid = new Core_DataGrid(new Core_DataGrid_DataSource_Array($branches_list), 100);
    $grid->setDefaultSort(array('name' => 'asc'));

    $grid->addColumn('manage', array(
      'header' => 'Manage',
      'sortable' => false,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/manage-branch/id/$id/',
        'class' => 'icon',
        'caption' => 'Edit',
        'image' => $this->view->baseUrl() . '/images/icons/view.png'
      )
    ));

		$grid->addColumn('id', new Core_DataGrid_Column('id', 'Id', null , 'left'));
		$grid->addColumn('name', new Core_DataGrid_Column('name', 'Title', null , 'left'));

		$grid->addColumn('streetaddress', new Core_DataGrid_Column('streetaddress', 'Street address', null , 'left'));
		$grid->addColumn('postnumber', new Core_DataGrid_Column('postnumber', 'Post number', null , 'left'));
		$grid->addColumn('postoffice', new Core_DataGrid_Column('postoffice', 'Post office', null , 'left'));


    $grid->addColumn('edit', array(
      'header' => 'Edit',
      'sortable' => false,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/edit-branch/id/$id/',
        'class' => 'icon',
        'caption' => 'Edit',
        'image' => $this->view->baseUrl() . '/images/icons/edit.png'
      )
    ));

    $this->view->branches = $grid;

    $customer_list = $Companies->getCompanyCustomerList($companyid);

    if(!empty($customer_list))
    {
      $grid = new Core_DataGrid(new Core_DataGrid_DataSource_Array($customer_list), 100);
      $grid->setDefaultSort(array('name' => 'asc'));
  
      $grid->addColumn('manage', array(
        'header' => 'Manage',
        'sortable' => false,
        'type' => 'action',
        'actions' => array(
          'url' => $this->view->baseUrl() . '/company/manage/id/$id/',
          'class' => 'icon',
          'caption' => 'Edit',
          'image' => $this->view->baseUrl() . '/images/icons/view.png'
        )
      ));
  
  		$grid->addColumn('id', new Core_DataGrid_Column('id', 'Id', null , 'left'));
  		$grid->addColumn('name', new Core_DataGrid_Column('name', 'Title', null , 'left'));
  
  		$grid->addColumn('streetaddress', new Core_DataGrid_Column('streetaddress', 'Street address', null , 'left'));
  		$grid->addColumn('postnumber', new Core_DataGrid_Column('postnumber', 'Post number', null , 'left'));
  		$grid->addColumn('postoffice', new Core_DataGrid_Column('postoffice', 'Post office', null , 'left'));
  
      $grid->addColumn('edit', array(
        'header' => 'Edit',
        'sortable' => false,
        'type' => 'action',
        'actions' => array(
          'url' => $this->view->baseUrl() . '/company/edit/id/$id/',
          'class' => 'icon',
          'caption' => 'Edit',
          'image' => $this->view->baseUrl() . '/images/icons/edit.png'
        )
      ));
  
      $this->view->customers = $grid;
    }
    else
    {
      $this->view->customers = null;
    }

  }

  /**
   * Add branch office to company
   */
  public function addBranchOfficeAction()
  {
    $companyid = $this->getRequest()->getParam('companyid', false);
    
    if ($companyid === false)
    {
      throw new Zend_Exception("Fail.");
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
      'header' => 'Manage',
      'sortable' => false,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/manage-network-device/id/$id/',
        'class' => 'icon',
        'caption' => 'Edit',
        'image' => $this->view->baseUrl() . '/images/icons/view.png'
      )
    ));

		$grid->addColumn('id', new Core_DataGrid_Column('id', 'Id', null , 'left'));
		$grid->addColumn('name', new Core_DataGrid_Column('name', 'Title', null , 'left'));

		$grid->addColumn('usize', new Core_DataGrid_Column('usize', 'Units', null , 'left'));

    $grid->addColumn('edit', array(
      'header' => 'Edit',
      'sortable' => false,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/edit-network-device/id/$id/',
        'class' => 'icon',
        'caption' => 'Edit',
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
    $company->setLabel($this->tr->_('Company'));
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
    $portlist = $NetworkDevicePorts->getNetworkDevicePortList($networkdeviceid);

    $grid = new Core_DataGrid(new Core_DataGrid_DataSource_Array($portlist), 100);
    $grid->setDefaultSort(array('name' => 'asc'));

    $grid->addColumn('manage', array(
      'header' => 'Manage',
      'sortable' => false,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/manage-network-device-port/id/$id/',
        'class' => 'icon',
        'caption' => 'Edit',
        'image' => $this->view->baseUrl() . '/images/icons/view.png'
      )
    ));

		$grid->addColumn('id', new Core_DataGrid_Column('id', 'Id', null , 'left'));
		$grid->addColumn('name', new Core_DataGrid_Column('name', 'Title', null , 'left'));

		$grid->addColumn('side', new Core_DataGrid_Column('side', 'Side', null , 'left'));

		$grid->addColumn('porttypeid', new Core_DataGrid_Column('porttypeid', 'Type', null , 'left'));

    $grid->addColumn('edit', array(
      'header' => 'Edit',
      'sortable' => false,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/company/edit-port/id/$id/',
        'class' => 'icon',
        'caption' => 'Edit',
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
    $sidearr['F'] = 'Front';
    $sidearr['B'] = 'Back';
    
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
  }


} // /class
