<?php
define('SCRIPT_START_TIME', microtime(true), 0);

function stripslashes_deep ($value = null)
{
  return is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value); 
}

// get_magic_quotes_gpc is enabled, strip stupid slashes off
if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc())
{
  if (isset($_POST) && !empty($_POST))
  {
    $_POST = stripslashes_deep($_POST);
  }
}

// Load CRM
require_once './../application/bootstrap.php';
