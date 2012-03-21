<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object\Attribute {
	class Enum extends \CRUDsader\Object\Attribute {

		protected function isValid()
		{
			if (true!== $error = parent::isValid())
				return $error;
			if (!isset($this->_options['choices']))
				return true;
			return isset($this->_options['choices'][$this->_value]) || in_array($this->_value, $this->_options['choices']);
		}

		public function toInput()
		{
			$html = '<select ' . $this->getHtmlAttributesToHtml() . '><option value="-1">choose</option>';

			foreach ($this->_options['choices'] as $k => $v) {
				$html.= '<option value="' . $k . '" ' . (!$this->isEmpty() && $this->_value == $k ? 'selected="selected"' : '') . '>' . \CRUDsader\Instancer::getInstance()->i18n->translate($this->_name . '.' . $v) . '</option>';
			}

			$html.='</select>';

			return $html;
		}

		public function isEmpty()
		{
			return !isset($this->_value) || $this->_value == -1;
		}
	}
}
