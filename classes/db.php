<?php
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
    $select->from($this, array('id','firstname', 'lastname'));
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

} // /class

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

}

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

}

class NetworkDevicePorts extends Zend_Db_Table_Abstract
{
  protected $_name = 'NETWORK_UNIT_PORTS';
  protected $_primary = 'id';
  protected $_dependentTables = array('NetworkDevices', 'Ports');

  protected $_referenceMap = array(

    'PortType' => array(
      'refTableClass' => 'Ports',
      'refColumns'    => array('id'),
      'columns'       => array('porttypeid'),
    ),

    'Device' => array(
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

}
class Ports extends Zend_Db_Table_Abstract
{
  protected $_name = 'PORT_TYPES';
  protected $_primary = 'id';
  protected $_dependentTables = array('NetworkDevicePorts');

  protected $_referenceMap = array(
    'DevicePort' => array(
      'refTableClass' => 'NetworkDevices',
      'refColumns'    => array('id'),
      'columns'       => array('porttypeid'),
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

}