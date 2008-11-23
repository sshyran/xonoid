<?php
/**
 * 
 */
class Zend_View_Helper_Humanize
{
  public function humanize($bytes)
  {
    $orig = $bytes;
    $ext = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $unitCount = 0;

    for(; $bytes > 1024; $unitCount++) $bytes /= 1024;

    $human1 = sprintf("%s %s", round($bytes, 2), $ext[$unitCount]);
    $human2 = sprintf("%s %s", $orig, $ext[0]);
    
    return $human1 == $human2 ? $human1 : sprintf("%s (%s)", $human1, $human2);  
  }
}
