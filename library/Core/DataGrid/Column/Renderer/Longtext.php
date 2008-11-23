<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Column_Renderer_Longtext
 *
 * It class to provide a grid item renderer long text
 *
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_Column_Renderer
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */


class Core_DataGrid_Column_Renderer_Longtext extends Core_DataGrid_Column_Renderer_Abstract
{
    public function render($row)
    {
        $maxLenght = ( $this->getColumn()->getStringLimit() ) ? $this->getColumn()->getStringLimit() : 250;
        $text = parent::_getValue($row);
        $suffix = ( $this->getColumn()->getSuffix() ) ? $this->getColumn()->getSuffix() : '...';

        if( strlen($text) > $maxLenght ) {
            return substr($text, 0, $maxLenght) . $suffix;
        } else {
            return $text;
        }
    }
}