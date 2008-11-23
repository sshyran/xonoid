<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Column_Renderer_Action
 *
 * It class to provide a Grid column widget for rendering action grid cells
 *
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_Column_Renderer
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */


class Core_DataGrid_Column_Renderer_Action extends Core_DataGrid_Column_Renderer_Text
{

	/**
	 * Renders column
	 *
	 * @param Core_Object $row
	 * @return string
	 */
	public function render($row)
	{
		$actions = $this->getColumn()->getActions();
		if ( empty($actions) || !is_array($actions) ) {
			return '&nbsp';
		}

		return $this->_toLinkHtml($actions, $row);
	}

	/**
	 * Render single action as link html
	 *
	 * @param array $action
	 * @param Core_Object $row
	 * @return string
	 */
	protected function _toLinkHtml($action, $row)
	{
		$actionCaption = '';
		$this->_transformActionData($action, $actionCaption, $row);

		if(isset($action['confirm'])) {
			$action['onclick'] = 'return window.confirm(\''
			. addslashes($this->htmlEscape($action['confirm']))
			. '\')';
			unset($action['confirm']);
		}
		
		if(isset($action['image'])) {
			$actionCaption = "<img src=\"".$action['image']."\" alt=\"".$actionCaption."\" title=\"".$actionCaption."\" />";
		}
		unset($action['image']);

		return '<a ' . $this->buildLink($action) . '>' . $actionCaption . '</a>';
	}

	/**
	 * Prepares action data for html render
	 *
	 * @param array $action
	 * @param string $actionCaption
	 * @param Core_Object $row
	 * @return Core_DataGrid_Column_Renderer_Action
	 */
	protected function _transformActionData(&$action, &$actionCaption, $row)
	{
		foreach ( $action as $attibute => $value ) {
			if(isset($action[$attibute]) && !is_array($action[$attibute])) {
				$this->getColumn()->setFormat($action[$attibute]);
				$action[$attibute] = parent::render($row);
			} else {
				$this->getColumn()->setFormat(null);
			}

			switch ($attibute) {
				case 'caption':
					$actionCaption = $action['caption'];
					unset($action['caption']);
					break;
				case 'url':
					$action['href'] = $action['url'];
					unset($action['url']);
					break;
				case 'popup':
					$action['onclick'] = 'popWin(this.href, \'windth=800,height=700,resizable=1,scrollbars=1\');return false;';
					break;
			}
		}
		return $this;
	}
	
    public function buildLink($action, $attributes = array(), $valueSeparator='=', $fieldSeparator=' ', $quote='"')
    {
        $res  = '';
        $data = array();
        if (empty($attributes)) {
            $attributes = array_keys($action);
        }

        foreach ($action as $key => $value) {
            if (in_array($key, $attributes)) {
                $data[] = $key.$valueSeparator.$quote.$value.$quote;
            }
        }
        $res = implode($fieldSeparator, $data);
        return $res;
    }
}