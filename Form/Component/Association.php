<?php
namespace CRUDsader\Form\Component {
    class Association extends \CRUDsader\Form\Component {

        public function toInput(){
            $query=new \CRUDsader\Query('FROM '.$this->_options['class'].(\CRUDsader\Instancer::getInstance()->map->classHasParent($this->_options['class'])?',parent':''));
            $html='<select '.$this->getHtmlAttributesToHtml().' ><option value="">Select</option>';
            foreach($query->fetchAll() as $object){
                $html.='<option value="'.$object->isPersisted().'" '.(isset($this->_value) && $this->_value==$object->isPersisted()?'selected="selected"':'').'>'.$object.'</option>';
            }
            return $html.'</select>';
        }
    }
}