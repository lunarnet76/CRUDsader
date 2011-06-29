<?php
class Art_Database_Expression{
    protected $_expression;
    public function __construct($expression){
        $this->_expression=$expression;
    }
    public function get(){
        return $this->_expression;
    }

    public function __toString(){
        return $this->_expression=='NULL'?'':($this->_expression=='NOW()'?date('d/m/Y'):$this->_expression);
    }
}
?>
