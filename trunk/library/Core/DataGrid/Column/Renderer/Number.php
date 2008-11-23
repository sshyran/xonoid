<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Column_Renderer_Number
 *
 * It class to provide a grid item renderer number
 *
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_Column_Renderer
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */


class Core_DataGrid_Column_Renderer_Number extends Core_DataGrid_Column_Renderer_Abstract
{

    protected function _getValue($row)
    {
        $data = parent::_getValue($row);
        if (!is_null($data)) {
            $value = $data * 1;
        	return $value ? $value: '0'; // fixed for showing zero in grid
        }
        return $this->getColumn()->getDefault();
    }

    public function renderProperty()
    {
        $out = parent::renderProperty();
        if ($this->getColumn()->getGrid()->getFilterVisibility()) {
            $out.= ' width="100px" ';
        }
        return $out;
    }

    public function renderCss()
    {
        return parent::renderCss() . ' a-right';
    }

}
