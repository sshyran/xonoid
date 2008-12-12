<?php
require_once 'tcpdf/config/lang/eng.php'; 
require_once 'tcpdf/tcpdf.php';

class crmPDF extends TCPDF
{
  public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8')
  {
    parent::__construct($orientation, $unit, $format, $unicode, $encoding);

    $this->SetCreator(PDF_CREATOR);
    $this->SetAuthor("XoNoiD CRM");
    $this->SetTitle("XoNoiD CRM");
    $this->SetSubject("XoNoiD CRM");
    $this->SetKeywords("XoNoiD CRM");

    $this->SetFont("dejavusans", "", 9);
  } // /function

  // Page header
  public function Header()
  {
    $this->writeHTML("<table><tr><td>" . sprintf("%s (%s)", $this->PageNo(), $this->getAliasNbPages()) . "</td></tr></table>");
  }
  
  // Page footer
  public function Footer()
  {
    $this->SetY(-15); 
    $this->writeHTML("<table><tr><td>XoNoiD CRM</td></tr></table>");
  }
  
}
