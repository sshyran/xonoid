<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Column_Renderer_Text
 *
 * It class to provide a grid item renderer text/string
 *
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_Column_Renderer
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */


class Core_DataGrid_Column_Renderer_Text extends Core_DataGrid_Column_Renderer_Abstract
{
    /**
     * Format variables pattern
     *
     * @var string
     */
    protected $_variablePattern = '/\\$([a-z0-9_]+)/i';

    /**
     * Renders grid column
     *
     * @param Core_Object $row
     * @return mixed
     */
    public function _getValue($row)
    {
        $format = $this->getColumn()->getFormat();
        $defaultValue = $this->getColumn()->getDefault();
        if (is_null($format)) {
            // If no format and it column not filtered specified return data as is.
            $data = parent::_getValue($row);
            $string = is_null($data) ? $defaultValue : $data;
            return htmlspecialchars($string);
        }
        elseif (preg_match_all($this->_variablePattern, $format, $matches)) {
        	
            // Parsing of format string
            $formatedString = $format;
            foreach ($matches[0] as $matchIndex=>$match) {
                $value = $row[$matches[1][$matchIndex]];
                $formatedString = str_replace($match, $value, $formatedString);
            }
            return $formatedString;
        } else {
            return htmlspecialchars($format);
        }
    }
}