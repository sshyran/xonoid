<?php
function html_trim($data)
{
  $data = mb_trim($data);
  $data = trim($data);

  $data = str_ireplace('<br>', '<br />', $data);
  $data = str_ireplace('<br/>', '<br />', $data);
  $data = str_ireplace('<br >', '<br />', $data);

  // Remove first <br /> tags
  $data = preg_replace("@^(?:<br />)+@i", '', $data);

  // Remove last <br /> tags
  $data = preg_replace("@(?:<br />)+$@i", '', $data);

  return $data;
}
