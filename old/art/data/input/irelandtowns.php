<select name="<?php echo $this->_input['name']; ?>" id="<?php echo $this->_input['id']; ?>" class="<?php echo $this->_input['css'] . (!empty($this->_options['css']) ? ' ' . $this->_options['css'] : '') . ' data' . $this->_type; ?>">
    <?php
    $options=Model_Country::getIrelandTowns();
    foreach ($options as $county => $towns) {
        $county=ucfirst(strtolower($county));
        foreach($towns as $v)
            echo '<option county="'.$county.'" value="' . $v . '" ' . ($v == $this->_value ? 'selected="selected"' : '') . '>' . ucfirst(strtolower($v)).', '.$county . '</option>';
    }
    ?>
</select>
<?php
if(!empty($this->_options['countyDomId'])){
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('#<?php echo $this->_input['id']; ?>').live('change',function(event){
            $val=jQuery(this).val();
            jQuery(this).children('option').each(function(){
                if(jQuery(this).val()==$val)
                    jQuery('#<?php echo $this->_options['countyDomId'];?>').val(jQuery(this).attr('county').substr(7));
            }) 
        });
    })
</script>
    <?php
}
?>