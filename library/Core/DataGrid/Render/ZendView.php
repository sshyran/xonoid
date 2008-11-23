<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Render_ZendView
 *
 * It class to provide a Zend View Render Implementation
 *
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_Render
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */

class Core_DataGrid_Render_ZendView extends Core_DataGrid_Render_Abstract implements Core_DataGrid_Render_Interface
{
	protected $_template = null;

	public function init()
	{
		$this->setTemplate('grid.phtml');
	}

	public function setTemplate($templateName)
	{
		$this->_template = $templateName;
		return $this;
	}

	public function getTemplate()
	{
		return $this->_template;
	}

	public function render()
	{
		if (!$templateName = $this->getTemplate()) {
			return '';
		}

		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$view = clone $viewRenderer->view;
		$view->clearVars();
		$view->grid = $this->getGrid();
		$view->baseUrl = Zend_Controller_Front::getInstance()->getRequest()->getBaseUrl();
		//$view->addScriptPath(Core::getBaseDir() . DIRECTORY_SEPARATOR . 'library/Core/DataGrid/skins');

		$view->pager = $this->getGrid()->renderPager();

		return $view->render($templateName);
	}
}