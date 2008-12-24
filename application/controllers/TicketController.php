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
    $VIEW_T_OPEN = new VIEW_T_OPEN();
    $ticket_list = $VIEW_T_OPEN->fetchAll()->toArray();
  
    $grid = new Core_DataGrid(new Core_DataGrid_DataSource_Array($ticket_list), 50);
    $grid->setDefaultSort(array('priority' => 'asc'));

    $grid->addColumn('read', array(
      'header' => $this->tr->_('Read'),
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/ticket/read/id/$id/',
        'class' => 'icon',
        'caption' => $this->tr->_('Read'),
        'image' => $this->view->baseUrl() . '/images/icons/view.png'
      )
    ));

    $grid->addColumn('id', new Core_DataGrid_Column('text', $this->tr->_('Id'), 1, 'left'));
    $grid->addColumn('added', new Core_DataGrid_Column('text', $this->tr->_('Date added'), 1, 'left'));

    $subject = new Core_DataGrid_Column('link', $this->tr->_('Subject'), null , 'left');
    $subject->setLinks($this->view->baseUrl() . '/ticket/read/id/$id/subject/$subject');
    $grid->addColumn('subject', $subject);

    $grid->addColumn('companyname', new Core_DataGrid_Column('text', $this->tr->_('Company'), 150, 'left'));
    $grid->addColumn('addername', new Core_DataGrid_Column('text', $this->tr->_('Added by'), 150, 'left'));

    $grid->addColumn('priority', array(
      'header' => $this->tr->_('Priority'),
      'sortable' => true,
      'width' => 1,
      'type' => 'options',
      'options' => array(
        1 => 'ASAP'
      )
    ));

    $grid->addColumn('up', array(
      'header' => '',
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/ticket/edit-priority/id/$id/direction/up',
        'class' => 'icon',
        'caption' => $this->tr->_('Move up'),
        'image' => $this->view->baseUrl() . '/images/icons/arrow_u.png'
      )
    ));

    $grid->addColumn('down', array(
      'header' => '',
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/ticket/edit-priority/id/$id/direction/down',
        'class' => 'icon',
        'caption' => $this->tr->_('Move down'),
        'image' => $this->view->baseUrl() . '/images/icons/arrow_d.png'
      )
    ));

    $grid->addColumn('first', array(
      'header' => '',
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/ticket/edit-priority/id/$id/direction/first',
        'class' => 'icon',
        'caption' => $this->tr->_('Move to first'),
        'image' => $this->view->baseUrl() . '/images/icons/arrow_uu.png'
      )
    ));

    $grid->addColumn('last', array(
      'header' => '',
      'sortable' => false,
      'width' => 1,
      'type' => 'action',
      'actions' => array(
        'url' => $this->view->baseUrl() . '/ticket/edit-priority/id/$id/direction/last',
        'class' => 'icon',
        'caption' => $this->tr->_('Move to last'),
        'image' => $this->view->baseUrl() . '/images/icons/arrow_dd.png'
      )
    ));



    $this->view->tickets = $grid;

  }
  
  public function addAction()
  {
    $tickets = new Tickets();
    $ticketreplies = new TicketReplies();

    $ticket_priorities = array(
      1 => 'ASAP', 2 => 'High', 3 => 'Medium', 4 => 'Low'
    );
  
    $companies = new Companies();

    $company_list = $companies->getList();

    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . '/ticket/add');

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Add'));

    $name = new Zend_Form_Element_Text('subject');
    $name->setRequired(true);
    $name->setLabel($this->tr->_('Subject'));
    $name->addFilter('StringTrim');
    $name->addValidator('NotEmpty', true);
    $name->addValidator('StringLength', false, array(3, 100));

    $descr = new Zend_Dojo_Form_Element_Editor('description');
    $descr->setRequired(true);
    $descr->setLabel($this->tr->_('Description'));
    $descr->setDescription($this->tr->_('Description'));
    $descr->addFilter('StringTrim');
    $descr->addValidator(new CRM_Validate_XML());
    $descr->addValidator('NotEmpty', true);
    $descr->addValidator('StringLength', false, array(15, 65535));
    $descr->setPlugins(array(
      'undo', 'redo', '|', 
      'cut', 'copy', 'paste', '|',
      'removeFormat', 'bold', 'italic', 'underline', '|',
      'insertOrderedList', 'insertUnorderedList', '|',
      'createLink', 'unlink', 'formatBlock'
    ));

    $company = new Zend_Form_Element_Select('companyid');
    $company->setRequired(true);
    $company->setLabel($this->tr->_('Add to company'));
    $company->addMultiOptions($company_list);

    // Load default value
    if (!$this->getRequest()->isPost())
    {
      $company->setValue($this->_auth->getIdentity()->companyid);
    }

    $form->addElement($company);

    $form->addElement($name);
    $form->addElement($descr);

    $form->addElement($submit);

    $form->addDisplayGroup(array('companyid', 'subject', 'description'), 'info');
    $form->addDisplayGroup(array('submit'), 'submit');
    
    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();

        $values['description'] = mb_trim($values['description']);
        
        // Strip HTML tags
        $values['description'] = strip_tags($values['description'], '<p><a><span><ul><li><ol><div><img><br><h1><h2><h3><b><strong><pre><code><abbr><dt><dd><dl>');
        
        $gentime = time();
        
        $genid = (float)(date('Ymd', $gentime) . '000');
        $sqltime = date('Y-m-d H:i:s', $gentime);
        
        while ($tickets->ticketIdExists($genid))
        {
          $genid++;
        }
        
        $ticket_insert = array(
          'id' => $genid,
          'customorder' => $genid,
          'priority' => 1, 
          'ticketstatus' => 1,
          'subject' => $values['subject'],
          'userid' => $this->_auth->getIdentity()->id,
          'companyid' => $values['companyid'],
          'added' => $sqltime
        );

        $reply_insert = array(
          'userid' => $this->_auth->getIdentity()->id,
          'ticketid' => $genid,
          'descr' => $values['description'],
          'usedminutes' => '0',
          'added' => $sqltime,
          'replytype' => 1,
          'statusid' => 1
        );

        $this->_db->beginTransaction();

        try
        {
          $tickets->insert($ticket_insert);
          $ticketreplies->insert($reply_insert);

          $this->_db->commit();

          return $this->_helper->redirector->gotoUrl("/ticket");

        }
        catch (Exception $e)
        {
          $this->_db->rollBack();
          var_dump($e);
        }

      } // / if is valid

    }// / is POST

    $this->view->form = $form;    
    
  }
  
  public function readAction()
  {
    $ticketid = $this->getRequest()->getParam('id', false);
    
    if ($ticketid === false)
    {
      throw new Zend_Exception("Fail.");
    }

    $VIEW_T_OPEN = new VIEW_T_OPEN();
    $VIEW_T_REPLIES = new VIEW_T_REPLIES();
    $ticketreplies = new TicketReplies();

    $td = $VIEW_T_OPEN->fetchRow("id = $ticketid")->toArray();
    
    $this->view->personname = $td['addername']; 
    $this->view->id = $td['id'];    
    $this->view->subject = $td['subject'];    
    $this->view->date = $td['added'];    
    $this->view->company = $td['companyname'];    
    $this->view->priority = $td['priority'];    
    $this->view->phone = $td['adderphone'];    
    $this->view->email = $td['adderemail'];    

    $replies = $VIEW_T_REPLIES->fetchAll("ticketid = $ticketid")->toArray();
    
    $people = $VIEW_T_REPLIES->getPeople($ticketid);
    $this->view->people = $people;
    
    $this->view->replies = $replies; 

    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . "/ticket/read/id/$ticketid");

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Add'));

    $descr = new Zend_Dojo_Form_Element_Editor('description');
    $descr->setRequired(true);
    $descr->setLabel($this->tr->_('Reply'));
    $descr->setDescription($this->tr->_('Reply'));
    $descr->addFilter('StringTrim');
    $descr->addValidator(new CRM_Validate_XML());
    $descr->addValidator('NotEmpty', true);
    $descr->addValidator('StringLength', false, array(5, 65535));
    $descr->setPlugins(array(
      'undo', 'redo', '|', 
      'cut', 'copy', 'paste', '|',
      'removeFormat', 'bold', 'italic', 'underline', '|',
      'insertOrderedList', 'insertUnorderedList', '|',
      'createLink', 'unlink', 'formatBlock'
    ));

    $form->addElement($descr);

    $form->addElement($submit);

    $form->addDisplayGroup(array('description'), 'info');
    $form->addDisplayGroup(array('submit'), 'submit');

    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();

        $insert = array(
          'userid' => $this->_auth->getIdentity()->id,
          'ticketid' => $td['id'],
          'descr' => $values['description'],
          'usedminutes' => '0',
          'added' => new Zend_Db_Expr('NOW()'),
          'replytype' => 1,
          'statusid' => 1
        );

        $this->_db->beginTransaction();

        try
        {
        
           $ticketreplies->insert($insert);

          $this->_db->commit();

          return $this->_helper->redirector->gotoUrl("/ticket/read/id/$ticketid");

        }
        catch (Exception $e)
        {
          $this->_db->rollBack();
          var_dump($e);
        }

      } // / if is valid

    }// / is POST

    $this->view->form = $form;    

  } // /function

  /**
   * Modify ticket's priority
   * directions = up, down, first, last     
   */
  public function editPriorityAction()
  {
  }

}
