<input type="text" name="<?php echo $this->_requestName;?>" id="<?php echo $this->_requestName;?>" class="<?php echo $this->_css.(!empty($this->_options['css'])?' '.$this->_options['css']:'').' data'.$this->_type;?>" value="<?php echo $this->_value;?>" validator="<?php echo $this->javascriptValidator();?>" <?php echo $this->_required?'required="required"':'';?> />
<input type="button" id="<?php echo $this->_requestName;?>_finder" class="postcodefinder <?php echo $this->_css.(!empty($this->_options['css'])?' '.$this->_options['css']:'').' data'.$this->_type;?>" value="<?php echo isset($this->_options['finderText'])?$this->_options['finderText']:false;?>" validator="false" iconvalidator="false"/>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('#<?php echo $this->_requestName;?>_finder').live('click',function(){
        findAddress(jQuery('#<?php echo $this->_requestName;?>').val(),'<?php echo $this->_options['selectAddressDomId'];?>','<?php echo $this->_options['addressDomId'];?>','<?php echo $this->_options['cityDomId'];?>','<?php echo $this->_options['countyDomId'];?>');
    });
})
</script>