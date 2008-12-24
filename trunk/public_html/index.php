<?php
define('SCRIPT_START_TIME', microtime(true), 0);

if (version_compare(phpversion(), 6) === -1)
{
  // get_magic_quotes_gpc is enabled, strip stupid slashes off
  if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc())
  {
    function stripinputslashes(&$input)
    {
      if (is_array($input))
      {
        foreach ($input as $key => $value)
        {
          $input[$key] = stripinputslashes($value);
        }
      }
      else
      {
        $input = stripslashes($input);
      }

      return true;
    }

    array_walk_recursive($_GET, 'stripinputslashes');
    array_walk_recursive($_POST, 'stripinputslashes');
    array_walk_recursive($_REQUEST, 'stripinputslashes');

  }
}

// Load CRM
require_once './../application/bootstrap.php';
