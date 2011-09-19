<?php
namespace Art\Object\Collection {
    class Association extends \Art\Object\Collection implements \SplObserver {
        protected $_definition;

        public function __construct($definition, $fromClass) {
            $this->_definition = $definition;
            $this->_fromClass = $fromClass;
            $this->_class = $definition['to'];
        }

        public function getForm($oql=false, $alias=false) {
            if (false === $alias)
                $alias = $this->_class;
            $form = new \Art\Form();
            $max = $this->_definition['max'] == '*' ? 3 : $this->_definition['max'];
            $this->rewind();
            for ($i = 0; $i < $max; $i++) {
                if (!$this->valid()) {
                    $object = $this->newObject();
                } else {
                    $object = $this->current();
                    $this->next();
                }
                if ($this->_definition['composition']) {
                    $form->add($object->getForm($oql, $alias));
                } else {
                    $component = $form->add(new \Art\Form\Component\Composition(), $i, false);
                    $component->setLabel($i == 0 ? $alias : ' ');
                    $component->setExtra('compositionIndex', $this->_iterator);
                    $component->attach($this);
                }
            }
            return $form;
        }

        public function update(\SplSubject $component) {
            if (!$component->isEmpty() && $component->hasExtra('compositionIndex') && isset($this[$component->getExtra('compositionIndex')])) {
                // replace actual by proxy
                $index=$component->getExtra('compositionIndex');
                $target=$this[$index];
                $id=$target->isPersisted();
                if($id){
                    if(isset($this->$id))
                        unset($this->_objectIndexes[$id]);
                }
                unset($this->_objects[$index]);
                $this->_objects[$index]=new \Art\Object\Proxy($this->_class,$component->getValue());
                pre($this->toArray(true));
            }
        }
    }
}