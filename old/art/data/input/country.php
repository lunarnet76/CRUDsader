<select name="<?php echo $this->_input['name']; ?>" id="<?php echo $this->_input['id']; ?>" class="<?php echo $this->_input['css'] . (!empty($this->_options['css']) ? ' ' . $this->_options['css'] : '') . ' data' . $this->_type; ?>">
    <?php
    $options=Model_Country::getAll($this->_options['countries']);
   
    foreach ($options as $k => $v) {
        echo '<option value="' . $k . '" ' . ($k == $this->_value ? 'selected="selected"' : '') . '>' . $v . '</option>';
    }
    ?>
</select>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('#<?php echo $this->_input['id']; ?>').live('change',function(event){
            // for ireland hide the postcode and display a select for the towns
            if(event.target.value == 248){
                jQuery('#<?php echo $this->_options['cityDomId'];?>').val('');
                jQuery('#<?php echo $this->_options['cityDomId'];?>').parent().parent().hide();

                jQuery('#<?php echo $this->_options['postCodeDomId'];?>').val('EIRE');
                jQuery('#<?php echo $this->_options['postCodeDomId'];?>').parents('.container').hide();
                jQuery('#address_postcodeaddressfinder').hide();

                jQuery('#<?php echo $this->_options['countyDomId'];?>').val('');
                jQuery('#<?php echo $this->_options['irelandCityDomId'];?>').parents('.container').show();
            }
            else
            {
                jQuery('#<?php echo $this->_options['cityDomId'];?>').parent().parent().show();
                jQuery('#<?php echo $this->_options['postCodeDomId'];?>').parents('.container').show();
                jQuery('#<?php echo $this->_options['postCodeDomId'];?>').val('');
                jQuery('#<?php echo $this->_options['irelandCityDomId'];?>').parents('.container').hide();
            }
        });
    })
</script>