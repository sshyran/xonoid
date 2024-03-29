<?php
class TicketController extends crmController 
{ 
  public function preDispatch()
  {
    if (!$this->_auth->hasIdentity())
    {
      return $this->_redirect('/index/login');
    }
  }

  /**
   * List tickets  
   */
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

    $ticket_url = $this->view->baseUrl() . '/ticket/read/id/$id/subject/$subject';
    $tid = new Core_DataGrid_Column('link', $this->tr->_('Id'), 1, 'left');
    $tid->setLinks($ticket_url);
    $grid->addColumn('id', $tid);

    $grid->addColumn('added', new Core_DataGrid_Column('text', $this->tr->_('Date added'), 1, 'left'));

    $subject = new Core_DataGrid_Column('link', $this->tr->_('Subject'), null , 'left');
    $subject->setLinks($ticket_url);
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
  
  /**
   * Add new ticket  
   */
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
      if (isset($_POST['description']))
      {
        $_POST['description'] = html_trim($_POST['description']);
      }


      if ($form->isValid($_POST))
      {
        $values = $form->getValues();

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
  
  /**
   * Read and reply to ticket  
   */
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
    $tickets = new Tickets();
    $companies = new Companies();

    if (!$tickets->ticketIdExists($ticketid))
    {
      throw new Zend_Exception("Invalid ticket ID");
    }
    
    $this->view->message = null;
    
    $reply_session = new Zend_Session_Namespace('ticket_reply');
    if (!isset($reply_session->ok) || !$this->getRequest()->isPost())
    {
      $reply_session->ok = false;
    }

    $td = $VIEW_T_OPEN->fetchRow("id = $ticketid")->toArray();

    // User is in reseller company so he can 
    // - Set minutes and hours used for reply
    // - Close ticket
    $user_is_in_reseller_company = $companies->isRootCompany($this->_auth->getIdentity()->companyid);
    
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

    // Reply form
    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . "/ticket/read/id/$ticketid#replyform");

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Add'));
    $submit->setOrder(1000);

    $descr = new Zend_Dojo_Form_Element_Editor('description');
    $descr->setRequired(true);
    $descr->setLabel($this->tr->_('My reply'));
    $descr->setDescription($this->tr->_('My reply'));
    $descr->addFilter('StringTrim');
    $descr->addValidator('NotEmpty', true);
    $descr->addValidator('StringLength', false, array(5, 65535));
    $descr->addValidator(new CRM_Validate_XML(), false);

    $descr->setPlugins(array(
      'undo', 'redo', '|', 
      'cut', 'copy', 'paste', '|',
      'removeFormat', 'bold', 'italic', 'underline', '|',
      'insertOrderedList', 'insertUnorderedList', '|',
      'createLink', 'unlink', 'formatBlock'
    ));

    if ($user_is_in_reseller_company)
    {
      $hours_list = range(0,24);
  
      $used_hours = new Zend_Form_Element_Select('usedhours');
      $used_hours->setRequired(true);
      $used_hours->setLabel($this->tr->_('Used hours:'));
      $used_hours->addMultiOptions($hours_list);
  
      // Load default value
      if (!$this->getRequest()->isPost())
      {
        $used_hours->setValue(0);
      }
  
      $mins_list = range(0,59,5);
  
      $used_mins = new Zend_Form_Element_Select('usedmins');
      $used_mins->setRequired(true);
      $used_mins->setLabel($this->tr->_('Used minutes:'));
      $used_mins->addMultiOptions($mins_list);
  
      // Load default value
      if (!$this->getRequest()->isPost())
      {
        $used_mins->setValue(0);
      }
  
      $form->addElement($used_hours);
      $form->addElement($used_mins);
    } // /if

    $form->addElement($descr);

    $confirmed = new Zend_Form_Element_Checkbox('confirmed');
    $confirmed->setLabel($this->tr->_('Confirm'));

    if ($user_is_in_reseller_company)
    {
      // Used time
      $form->addDisplayGroup(array('usedhours', 'usedmins'), 'usedtime', array('legend' => $this->tr->_('Time used'), 'class' => 'used-time'));
    }

    $form->addDisplayGroup(array('description'), 'info');

    if ($user_is_in_reseller_company)
    {
      // Unique IDs!
      $ticket_statuses = array(
              0 => '------------------------------------------------',
          10000 => $this->tr->_('Waits customer reply'),
        1000000 =>$this->tr->_('Closed')
      );

      $ticketstatus = new Zend_Form_Element_Select('setstatus');
      $ticketstatus->setLabel($this->tr->_('Set ticket status to'));
      $ticketstatus->addMultiOptions($ticket_statuses);

      $form->addElement($ticketstatus);
      $form->addDisplayGroup(array('setstatus'), 'set-status');

    }

    if ($user_is_in_reseller_company && $reply_session->ok)
    {
      // Add 'All fields confirmed'

      $form->addElement($confirmed);
      $form->addDisplayGroup(array('confirmed'), 'confirmed');
    }

    $form->addElement($submit);
    $form->addDisplayGroup(array('submit'), 'submit');

    // Reply Form POSTed
    if ($this->getRequest()->isPost())
    {
    
      if (isset($_POST['description']) && !empty($_POST['description']))
      {
        $_POST['description'] = html_trim($_POST['description']);
      }
    
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();

        $values['description'] = mb_trim($values['description']);

        // Strip HTML tags
        $values['description'] = strip_tags($values['description'], '<p><a><span><ul><li><ol><div><img><br><h1><h2><h3><b><strong><pre><code><abbr><dt><dd><dl>');
        
        $used_minutes = 0;

        $field_is_confirmed = false;
        
        if ($user_is_in_reseller_company)
        {
          // Reseller
          if (!isset($values['confirmed']) || (int)$values['confirmed'] != 1)
          {
            $reply_session->ok = true;
            $field_is_confirmed = false;
          }
          else
          {
            if ($reply_session->ok)
            {
              $field_is_confirmed = true;
            }
          }
        
          $used_minutes += $values['usedhours'] * 60;
          $used_minutes += $values['usedmins'];

        } // /if
        else
        {
          // Normal user
          $field_is_confirmed = true;
        }
        
        if ($field_is_confirmed)
        {
          $insert = array(
            'userid' => $this->_auth->getIdentity()->id,
            'ticketid' => $td['id'],
            'descr' => $values['description'],
            'usedminutes' => $used_minutes,
            'added' => new Zend_Db_Expr('NOW()'),
            'replytype' => 1,
            'statusid' => 1
          );
  
          $this->_db->beginTransaction();
  
          try
          {
            $ticketreplies->insert($insert);
            $this->_db->commit();
  
            $reply_session->ok = false;

            return $this->_helper->redirector->gotoUrl("/ticket/read/id/$ticketid");
          }
          catch (Exception $e)
          {
            $this->_db->rollBack();
            var_dump($e);
          }

        }
        else
        {
          if ($user_is_in_reseller_company && $reply_session->ok)
          {
            $this->view->message = $this->tr->_('Confirm all fields');
            $form->markAsError();

            $form->addElement($confirmed);
            $form->addDisplayGroup(array('confirmed'), 'confirmed');
          }
        }

      } // / if is valid
      else
      {
        $reply_session->ok = false;
        $form->removeElement($confirmed);
      }

    }// / is POST

    $this->view->form = $form;

  } // /function

  /**
   * Modify ticket's priority
   * directions = up, down, first, last     
   */
  public function editPriorityAction()
  {
    // @TODO
    $ticketid = $this->getRequest()->getParam('id', false);
    
    if ($ticketid === false)
    {
      throw new Zend_Exception("Fail.");
    }

    $direction = $this->getRequest()->getParam('direction', false);
    
    if ($direction === false)
    {
      throw new Zend_Exception("Fail.");
    }

    $this->_helper->layout->disableLayout();
    $this->getHelper('viewRenderer')->setNoRender();

    return $this->_helper->redirector->gotoUrl("/ticket");
  } // /function
  
  /**
   * Search tickets  
   */
  public function searchAction()
  {
    $layout = (string)$this->getRequest()->getParam('layout', 'enabled');

    if ($layout === 'disabled')
    {
      $this->_helper->layout->disableLayout();
    }
    
    $this->view->layout = $layout; 

    $ticketreplies = new TicketReplies();
    $tickets = new Tickets();
  
    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . "/ticket/search/layout/$layout");

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Search'));
    $submit->setOrder(1000);

    $query = new Zend_Form_Element_Text('query');
    $query->setRequired(false);
    $query->setLabel($this->tr->_('Search Query'));
    $query->addFilter('StringTrim');
    $query->addValidator('StringLength', false, array(1, 100));
    
    $form->addElement($query);
    $form->addElement($submit);

    $form->addDisplayGroup(array('query', 'submit'), 'search-query', array('legend' => $this->tr->_('Search tickets'), 'class' => 'ticket-search'));

    $this->view->search = null;

    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();
        $q = '%' . $values['query'] . '%';

        $found_ids = array();

        // Search from tickets

        $select = $tickets->select();
        $select->from($tickets, array('id'));

        if (ctype_digit($values['query']))
        {
          $select->where('id = ?', $values['query']);
        }

        $select->where('subject LIKE ?', $q);
        $select->group(array('id'));

        $found = $tickets->fetchAll($select)->toArray();

        unset($select);

        foreach($found as $key => $val)
        {
          $found_ids[] = $val['id'];
        }

        // Search from replies

        $select = $ticketreplies->select();
        $select->from($ticketreplies, array('ticketid'));

        if (count($found_ids) > 0)
        {
          $select->Where('ticketid NOT IN (?)', new Zend_Db_Expr(join(',', $found_ids)));
        }

        $select->where('descr LIKE ?', $q);

        $select->group(array('ticketid'));

        $found = $ticketreplies->fetchAll($select)->toArray();

        unset($select);

        foreach($found as $key => $val)
        {
          $found_ids[] = $val['ticketid'];
        }
        
        // Find ticket details

        if (count($found_ids) > 0)
        {
          $select = $tickets->select();
          $select->from($tickets, array('id', 'subject', 'added'));

          $select->Where('id IN (?)', new Zend_Db_Expr(join(',', $found_ids)));

          $ticket_list = $tickets->fetchAll($select)->toArray();

          unset($select);
        }
        else
        {
          $ticket_list = array();
        }

        // Create grid

        $grid = new Core_DataGrid(new Core_DataGrid_DataSource_Array($ticket_list), 50);
        $grid->setDefaultSort(array('added' => 'asc'));
    
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
    
        $ticket_url = $this->view->baseUrl() . '/ticket/read/id/$id/subject/$subject';
        $tid = new Core_DataGrid_Column('link', $this->tr->_('Id'), 1, 'left');
        $tid->setLinks($ticket_url);
        $grid->addColumn('id', $tid);
    
        $grid->addColumn('added', new Core_DataGrid_Column('text', $this->tr->_('Date added'), 1, 'left'));
    
        $subject = new Core_DataGrid_Column('link', $this->tr->_('Subject'), null , 'left');
        $subject->setLinks($ticket_url);
        $grid->addColumn('subject', $subject);
    
        $this->view->search = $grid;
        
      }
    }
    
    $this->view->form = $form;
    
  }
  
  public function addReferenceAction()
  {
    $this->_helper->layout->disableLayout();

    $id = $this->getRequest()->getParam('id', false);

    if ($id === false)
    {
      throw new Exception("Fail.");
    }

    // Reference form
    $form = new crmForm();
    $form->setMethod(Zend_Form::METHOD_POST);
    $form->setAction($this->_request->getBaseUrl() . "/ticket/add-reference/id/$id");

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel($this->tr->_('Add'));
    $submit->setOrder(1000);

    $reference = new Zend_Form_Element_Text('refid');
    $reference->setRequired(false);
    $reference->setLabel($this->tr->_('Ticket ID'));
    $reference->addFilter('StringTrim');
    $reference->addValidator('StringLength', false, array(1, 100));
    $reference->addValidator('Digits');
    
    $form->addElement($reference);
    $form->addElement($submit);

    $form->addDisplayGroup(array('refid', 'submit'), 'reference', array('legend' => $this->tr->_('Add ticket reference'), 'class' => 'ticket-reference'));

    // Form POSTed
    if ($this->getRequest()->isPost())
    {
      if ($form->isValid($_POST))
      {
        $values = $form->getValues();
        
        // @TODO
        
        return $this->_helper->redirector->gotoUrl("/ticket/add-reference/id/$id");
      }
    }
    
    $this->view->form = $form;

  }

} // /class
