<?php
/**
 * Users
 */
class Users extends Zend_Db_Table_Abstract
{
  protected $_name = 'USERS';
  protected $_primary = 'id';
  protected $_dependentTables = array('Companies');

  protected $_referenceMap = array(

    'Company' => array(
      'refTableClass' => 'Companies',
      'refColumns'    => array('id'),
      'columns'       => array('companyid'),
    )

  );

  public function getUserList()
  {
    $select = $this->select();
    $select->from($this, array('id','firstname', 'lastname', 'email', 'phone'));
    $select->order(array('lastname', 'firstname', 'id'));

    return $this->fetchAll($select)->toArray();
   
  }

  public function getList()
  {
    $list = array();

    foreach($this->getUserList() as $id => $data)
    {
      $list[$data['id']] = sprintf("%s %s", $data['lastname'], $data['firstname']);
    }

    return $list;
  }
  
  public function emailExists($email)
  {
    $select = $this->select();
    $select->from($this, array('c' => 'COUNT(id)'));
    $select->where('email=?', $email);

    $result = $this->fetchAll($select)->toArray();

    return (bool) ((int)$result[0]['c'] == 1 ? true : false);
  }
  
  public function emailToID($email)
  {
    $select = $this->select();
    $select->from($this, array('id'));
    $select->where('email=?', $email);

    $result = $this->fetchAll($select)->toArray();

    return (int)$result[0]['id'];
  }
  

} // /class

/**
 * Companies
 */
class Companies extends Zend_Db_Table_Abstract
{
  protected $_name = 'COMPANIES';
  protected $_primary = 'id';
  protected $_dependentTables = array('Companies', 'Users');

  protected $_referenceMap = array(

    'Reseller' => array(
      'refTableClass' => 'Companies',
      'refColumns'    => array('id'),
      'columns'       => array('resellerid'),
    ),

    'MainContact' => array(
      'refTableClass' => 'Users',
      'refColumns'    => array('id'),
      'columns'       => array('contactid'),
    )

  );

  public function isRootCompany($id)
  {
    $select = $this->select();
    $select->from($this, array('c' => 'COUNT(id)'));
    $select->where('(resellerid IS NULL) OR (resellerid=id)');
    $select->where('id=?', $id);

    $result = $this->fetchAll($select)->toArray();

    return (bool) ((int)$result[0]['c'] == 1 ? true : false);
  }

  public function getParentID($id)
  {
    $select = $this->select();
    $select->from($this, array('resellerid'));
    $select->where('id=?', $id);

    $result = $this->fetchAll($select)->toArray();

    return $result[0]['resellerid'];
  }


  public function getCompanyRootList($id = -1)
  {
    $select = $this->select();
    $select->from($this, array('id', 'name'));
    $select->where('(resellerid IS NULL) OR (resellerid=id) OR (id=?)', $id);
    $select->order(array('name', 'id'));
    
    return $this->fetchAll($select)->toArray();
  }

  public function getCompanyCustomerList($id)
  {
    $select = $this->select();
    $select->from($this, array('id', 'name', 'streetaddress', 'postnumber', 'postoffice'));
    $select->where('resellerid=?', $id);
    $select->order(array('name', 'id'));
    
    return $this->fetchAll($select)->toArray();
  }


  public function getCompanyList()
  {
    $select = $this->select();
    $select->from($this, array('id','name'));
    $select->order(array('name', 'id'));
    
    return $this->fetchAll($select)->toArray();
  }

  public function getList()
  {
    $list = array();

    foreach($this->getCompanyList() as $id => $data)
    {
      $list[$data['id']] = sprintf("%s", $data['name']);
    }
    
    return $list;
  }
  
  public function getName($id)
  {
    $select = $this->select();
    $select->from($this, array('name'));
    $select->where('id=?', $id);
    $res = $this->fetchAll($select)->toArray();

    return $res[0]['name'];
  }

  public function getAddress($id)
  {
    $select = $this->select();
    $select->from($this, array('streetaddress', 'postnumber', 'postoffice'));
    $select->where('id=?', $id);
    $res = $this->fetchAll($select)->toArray();

    return $res[0];
  }

  public function addressExists($streetaddress, $postnumber, $postoffice)
  {
    $select = $this->select();
    $select->from($this, array('c' => 'COUNT(id)'));
    $select->where('streetaddress=?', $streetaddress);
    $select->where('postnumber=?', $postnumber);
    $select->where('postoffice=?', $postoffice);

    $result = $this->fetchAll($select)->toArray();

    return (bool) ((int)$result[0]['c'] > 0 ? true : false);
  }

} // /class

/**
 * Companies branch offices
 */
class CompanyBranchOffices extends Zend_Db_Table_Abstract
{
  protected $_name = 'BRANCHES';
  protected $_primary = 'id';
  protected $_dependentTables = array('Companies', 'Users');

  protected $_referenceMap = array(

    'Company' => array(
      'refTableClass' => 'Companies',
      'refColumns'    => array('id'),
      'columns'       => array('companyid'),
    ),

    'ContactPerson' => array(
      'refTableClass' => 'Users',
      'refColumns'    => array('id'),
      'columns'       => array('contactid'),
    )

  );

  public function getBranchesList($id)
  {
    $select = $this->select();
    $select->from($this, array('id','name', 'streetaddress', 'postnumber', 'postoffice'));
    $select->order(array('name', 'id'));
    $select->where('companyid=?', $id);
    
    return $this->fetchAll($select)->toArray();
  }

  public function getList($id)
  {
    $list = array();

    foreach($this->getBranchesList($id) as $id => $data)
    {
      $list[$data['id']] = sprintf("%s", $data['name']);
    }
    
    return $list;
  }
  
  public function getCompanyID($id)
  {
    $select = $this->select();
    $select->from($this, array('companyid'));
    $select->where('id=?', $id);
    
    $res = $this->fetchAll($select)->toArray();
    return $res[0]['companyid'];

  }

  public function getName($id)
  {
    $select = $this->select();
    $select->from($this, array('name'));
    $select->where('id=?', $id);
    $res = $this->fetchAll($select)->toArray();

    return $res[0]['name'];
  }

  public function addressExists($streetaddress, $postnumber, $postoffice)
  {
    $select = $this->select();
    $select->from($this, array('c' => 'COUNT(id)'));
    $select->where('streetaddress=?', $streetaddress);
    $select->where('postnumber=?', $postnumber);
    $select->where('postoffice=?', $postoffice);

    $result = $this->fetchAll($select)->toArray();

    return (bool) ((int)$result[0]['c'] > 0 ? true : false);
  }

} // /class

/**
 * Network devices in branch offices
 */
class NetworkDevices extends Zend_Db_Table_Abstract
{
  protected $_name = 'NETWORK_UNITS';
  protected $_primary = 'id';
  protected $_dependentTables = array('Companies', 'Users', 'CompanyBranchOffices');

  protected $_referenceMap = array(

    'Branch' => array(
      'refTableClass' => 'CompanyBranchOffices',
      'refColumns'    => array('id'),
      'columns'       => array('branchid'),
    ),

    'Company' => array(
      'refTableClass' => 'Companies',
      'refColumns'    => array('id'),
      'columns'       => array('companyid'),
    ),

    'ContactPerson' => array(
      'refTableClass' => 'Users',
      'refColumns'    => array('id'),
      'columns'       => array('contactid'),
    )

  );

  public function getNetworkDevicesList($id)
  {
    $select = $this->select();
    $select->from($this, array('id','name', 'usize'));
    $select->order(array('name', 'id'));
    $select->where('branchid=?', $id);
    
    return $this->fetchAll($select)->toArray();
  }

  public function getList($id)
  {
    $list = array();

    foreach($this->getNetworkDevicesList($id) as $id => $data)
    {
      $list[$data['id']] = sprintf("%s", $data['name']);
    }
    
    return $list;
  }

  public function getBranchID($id)
  {
    $select = $this->select();
    $select->from($this, array('branchid'));
    $select->where('id=?', $id);
    
    $res = $this->fetchAll($select)->toArray();
    return $res[0]['branchid'];

  }

  public function getName($id)
  {
    $select = $this->select();
    $select->from($this, array('name'));
    $select->where('id=?', $id);
    $res = $this->fetchAll($select)->toArray();

    return $res[0]['name'];
  }

} // /class

/**
 * Ports in network devices
 */
class NetworkDevicePorts extends Zend_Db_Table_Abstract
{
  protected $_name = 'NETWORK_UNIT_PORTS';
  protected $_primary = 'id';
  protected $_dependentTables = array('NetworkDevices', 'Ports');

  protected $_referenceMap = array(

    'Ports' => array(
      'refTableClass' => 'Ports',
      'refColumns'    => array('porttypeid'),
      'columns'       => array('id'),
    ),

    'NetworkDevices' => array(
      'refTableClass' => 'NetworkDevices',
      'refColumns'    => array('id'),
      'columns'       => array('networkunitid'),
    )

  );

  public function getNetworkDevicePortList($id)
  {
    $select = $this->select();
    $select->from($this, array('id','name', 'side', 'porttypeid'));
    $select->order(array('side', 'name', 'id'));
    $select->where('networkunitid=?', $id);
    
    return $this->fetchAll($select)->toArray();
  }

  public function getList($id)
  {
    $list = array();

    foreach($this->getNetworkDevicePortList($id) as $id => $data)
    {
      $list[$data['id']] = sprintf("%s", $data['name']);
    }
    
    return $list;
  }

  public function getNetworkDevice($id)
  {
    $select = $this->select();
    $select->from($this, array('networkunitid'));
    $select->order(array('side', 'name', 'id'));
    $select->where('id=?', $id);

    $res = $this->fetchAll($select)->toArray();

    return $res[0]['networkunitid'];
  }

  public function getNetworkDeviceName($id)
  {
    $select = $this->select();
    $select->from($this, array('name'));
    $select->order(array('side', 'name', 'id'));
    $select->where('id=?', $id);

    $res = $this->fetchAll($select)->toArray();

    return $res[0]['name'];
  }

} // /class

/**
 * Different port types in network devices
 */
class Ports extends Zend_Db_Table_Abstract
{
  protected $_name = 'PORT_TYPES';
  protected $_primary = 'id';
  protected $_dependentTables = array('NetworkDevicePorts');

  protected $_referenceMap = array(
    'NetworkDevicePorts' => array(
      'refTableClass' => 'NetworkDevicePorts',
      'refColumns'    => array('porttypeid'),
      'columns'       => array('id'),
    )
  );

  public function getPortsList()
  {
    $select = $this->select();
    $select->from($this, array('id','name'));
    $select->order(array('name', 'id'));
    
    return $this->fetchAll($select)->toArray();
  }

  public function getList()
  {
    $list = array();

    foreach($this->getPortsList() as $id => $data)
    {
      $list[$data['id']] = sprintf("%s", $data['name']);
    }
    
    return $list;
  }

} // /class

/**
 * Port(s) IP Address(es)
 */
class IPAddresses extends Zend_Db_Table_Abstract
{
  protected $_name = 'PORT_IP_ADDRESSES';
  protected $_primary = 'id';
} // /class

/**
 * Port(s) VLAN(s)
 */
class VLANs extends Zend_Db_Table_Abstract
{
  protected $_name = 'PORT_VLANS';
  protected $_primary = 'id';
} // /class


/**
 * 
 */
class Tickets extends Zend_Db_Table_Abstract
{
  protected $_name = 'TICKETS';
  protected $_primary = 'id';
  protected $_dependentTables = array('Users', 'Companies');
  
  public function ticketIdExists($id)
  {
    $select = $this->select();
    $select->from($this, array('c' => 'COUNT(id)'));
    $select->where('id=?', $id);

    $result = $this->fetchAll($select)->toArray();

    return (bool) ((int)$result[0]['c'] == 1 ? true : false);

  }

} // /class

/**
 * 
 */
class TicketReplies extends Zend_Db_Table_Abstract
{
  protected $_name = 'TICKET_REPLY';
  protected $_primary = 'id';
  protected $_dependentTables = array('Users', 'Companies');

} // /class


/*******************************************************************************
  VIEWS
*******************************************************************************/


/**
 * VIEW: NUP PT
 */
class VIEW_NUP_PT extends Zend_Db_Table_Abstract
{
  protected $_name = 'view_nup_pt';
  protected $_primary = 'id';
  protected $_dependentTables = array();

}

/**
 * VIEW: P IP
 */
class VIEW_P_IP extends Zend_Db_Table_Abstract
{
  protected $_name = 'view_p_ip';
  protected $_primary = 'id';
  protected $_dependentTables = array();
}

/**
 * VIEW: T OPEN
 */
class VIEW_T_OPEN extends Zend_Db_Table_Abstract
{
  protected $_name = 'view_t_open';
  protected $_primary = 'id';
  protected $_dependentTables = array();
}

/**
 * VIEW: T REPLIES
 */
class VIEW_T_REPLIES extends Zend_Db_Table_Abstract
{
  protected $_name = 'view_t_replies';
  protected $_primary = 'id';
  protected $_dependentTables = array();

  public function getPeople($id)
  {
    $select = $this->select();
    $select->from($this, array('userid', 'writer', 'writeremail'));
    $select->where('ticketid=?', $id);
    $select->group(array('userid'));
    $select->order(array('writer'));

    $result = $this->fetchAll($select)->toArray();

    return $result;

  }

}
