<?php
class crmDefaultForm extends Zend_Dojo_Form
{
}

class crmForm extends crmDefaultForm
{

  public function addElement($element, $name = null, $options = null)
  {
    parent::addElement($element, $name = null, $options = null);

    if ($element instanceof Zend_Form_Element)
    {
      if($element->isRequired())
      {
        $element->setLabel("* " . $element->getLabel());
      }
    }

    if ($element instanceof Zend_Form_Element_Submit)
    {
      $submit_decorator = array(
        array('ViewHelper'),
        array('Description'),
        array('HtmlTag', 
          array('tag' => 'dd', 'class' => 'form-dd-submit')
        )
      );

      $element->setDecorators($submit_decorator);

    }
    
    if ($element instanceof Zend_Form_Element_Textarea)
    {
      $submit_decorator = array(
        array('ViewHelper'),
        array('Description'),
        array('HtmlTag', 
          array('tag' => 'dd', 'class' => 'form-dd-textarea')
        )
      );

      $element->setDecorators($submit_decorator);

    }
    
  }
} 
