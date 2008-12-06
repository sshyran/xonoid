<?php
/**
 * Joker.COM DMAPI Client
 * Supports caching 
 */
class jokerComClient
{
  public $url = 'https://dmapi.joker.com/request/';

  protected $username;
  protected $password;
  
  protected $session = null;
  
  protected $cachedir = null;

  public function __construct($username, $password)
  {
    $this->cachedir = dirname(__FILE__) . '/../cache';

    $this->username = trim($username);
    $this->password = trim($password);

    $filename = "$this->cachedir/joker_auth_cache_" . md5($this->username) . '.cache';

    if (file_exists($filename))
    {
      $this->setSession(unserialize(file_get_contents($filename)));
    }

  } // /function

  public function parseResponse($data)
  {
    $splitter = "\n\n";
    $split_pos = strpos($data, $splitter);

    $header = substr($data, 0, $split_pos);
    $body = trim(substr($data, $split_pos + strlen($splitter)));
    
    $headers = array();

    foreach(explode("\n", $header) as $hline)
    {
      list($key, $value) = explode(":", $hline, 2);
      $headers[strtolower(trim($key))] = trim($value);
    }
    
    return array('headers' => $headers, 'body' => $body);
  } // /function

  public function setSession ($session)
  {
    $this->session = $session;
  } // /function

  public function getSession()
  {
    return $this->session;
  } // /function

  public function login()
  {
    // Session id is not set
    if ($this->getSession() == null)
    {
      $client = new Zend_Http_Client($this->url . 'login');
      $client->request(strtoupper(Zend_Form::METHOD_POST));
      $client->setEncType('application/x-www-form-urlencoded');
      $client->setParameterPost('username', $this->username);
      $client->setParameterPost('password', $this->password);

      $response = $client->request();
      $body = $response->getRawBody();

      $data = $this->parseResponse($body);

      $headers = $data['headers'];

      if (isset($headers['auth-sid']))
      {

        $this->setSession($headers['auth-sid']);

        $filename = "$this->cachedir/joker_auth_cache_" . md5($this->username) . '.cache';

        file_put_contents($filename, serialize($headers['auth-sid']));
        @chmod($filename, 0777);

        return true;
      }

      return false;

    }
    else
    {
      return true;
    }

  } // /function
  
  public function getDomainList($search = '*')
  {
    $tries = 0;

    $filename = "$this->cachedir/joker_domainlist_cache_" . md5($this->username . $search) . '.cache';

    if (file_exists($filename))
    {
      return unserialize(file_get_contents($filename));
    }

    $client = new Zend_Http_Client($this->url . 'query-domain-list');
    $client->request(strtoupper(Zend_Form::METHOD_POST));
    $client->setEncType('application/x-www-form-urlencoded');
    $client->setParameterPost('auth-sid', $this->getSession());
    $client->setParameterPost('pattern', $search);

    $response = $client->request();
    $data = $this->parseResponse($response->getRawBody());
    
    $headers = $data['headers'];

    if((int)$headers['status-code'] === 0)
    {
      $list = array();

      foreach(explode("\n", $data['body']) as $line)
      {
        list($key, $value) = explode(" ", $line, 2);
        $list[strtolower(trim($key))] = trim($value);
      }

      file_put_contents($filename, serialize($list));
      @chmod($filename, 0777);

      return $list;
    }
    else
    {
      if (file_exists($filename))
      {
        return unserialize(file_get_contents($filename));
      }

      throw new Zend_Exception($headers['error'], $headers['status-code']);
    }

  } // /function

} // /class
