<select name="<?php echo $this->_requestName;?>" id="<?php echo $this->_requestName;?>" class="<?php echo $this->_css.(!empty($this->_options['css'])?' '.$this->_options['css']:'');?>" value="<?php echo $this->_value;?>" validator="<?php echo $this->javascriptValidator();?>" <?php echo $this->_required?'isrequired="isrequired"':'';?>>
    <?php
    if(isset($this->_options['selection']) && $this->_options['selection'])echo '<option value="">'.Art_I18n::getInstance()->get('select').'</option>';
    if(isset($this->_options['options']))
        foreach($this->_options['options'] as $k=>$v){
            if(isset($this->_options['useIndexAsValue']) && !$this->_options['useIndexAsValue'])
                    $k=$v;
            echo '<option value="'.$k.'" '.($k==$this->_value?'selected="selected"':'').'>'.$v.'</option>';
        }
    ?>
</select>