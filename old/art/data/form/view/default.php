<?php echo $this->htmlStart();?>
<div class="title<?php if($this->_parent)echo ' sub';?>"><?php echo $this->_title.($this->_mandatory?'<span class="mandatory">*</span>':'');?></div>
<div class="content<?php if($this->_parent)echo ' sub';?>"><?php
foreach($this->_objects as $object){
    if($object['data'] instanceof Art_Data)
        echo '<div class="container"><div class="label">'.$object['name'].($object['data']->isMandatory()?'<span class="mandatory">*</span>':'').'&nbsp;</div><div class="input">'.$this->htmlInput($object).'&nbsp;</div><div class="error">'.$object['data']->getError().'&nbsp;</div></div>';
    else
        echo $object['data'];
}
?></div><?php if(!$this->_parent){?>
<div class="container"><?php echo $this->htmlSubmitButton();?></div>
<?php }echo $this->htmlStop();echo $this->javascriptValidator();?>
