<?php
class euVatNoValidatorException extends Exception
{
  protected $message;
  private $string;
  protected $code;
  protected $file;
  protected $line;
  private $trace;

  const UNKNOWN = 0;
  const INVALID_INPUT = 1;
  const SERVICE_UNAVAILABLE = 2;
  const MS_UNAVAILABLE = 3;
  const TIMEOUT = 4;
  const SERVER_BUSY = 5;

  public $errorcodes = array();
  
  public function initializeErrorCodes()
  {
    $this->errorcodes = array(
      _("Unknown error. Try again later"),
      _("The provided CountryCode is invalid or the VAT number is empty"),
      _("The SOAP service is unavailable, try again later"),
      _("The Member State service is unavailable, try again later or with another Member State"),
      _("The Member State service could not be reach in time, try again later or with another Member State"),
      _("The service can't process your request. Try again later.")
    );
  }
  public function __construct($message = null, $code = 0)
  {
    $this->message = $message;
    $this->code = $code;

    $this->initializeErrorCodes();
  }
  
  public function __toString()
  {
    return $this->errorcodes[$this->code];
  
  }

}

/**
 * European Union VAT Number validator
 */
class euVatNoValidator
{
  public $url = 'http://ec.europa.eu/taxation_customs/vies/api/checkVatPort?wsdl';
  public $validator = null;
  public $parameters = null;

  public function __construct($country, $vat)
  {
    $this->validator = new SoapClient($this->url);
    $this->parameters = array('countryCode' => $country, 'vatNumber' => $vat);
  }
  
  public function isValid()
  {
    try
    {
      $response = $this->validator->checkVat($this->parameters);

      return $response;
    }
    catch(SoapFault $e)
    {
      $fault = trim($e->getMessage());

      if(preg_match("/{ '([A-Z_]+)' }/", $fault, $m))
      {
        list(, $fault_string) = $m;

        $define = "euVatNoValidatorException::$fault_string";

        if (defined($define))
        {
          throw new euVatNoValidatorException("EU VAT Validator: " . $fault, constant($define));
        }
        else
        {
          throw new euVatNoValidatorException("EU VAT Validator: " . $fault, euVatNoValidatorException::UNKNOWN);
        }
        
      }
    }
    catch(Exception $e)
    {
      throw new Zend_Exception("Unknown SOAP Exception");
    }

  } // /function

} // /class
