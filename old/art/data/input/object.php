<select name="<?php echo $this->_requestName; ?>" id="<?php echo $this->_requestName; ?>" class="<?php echo $this->_css . (!empty($this->_options['css']) ? ' ' . $this->_options['css'] : ''); ?>" value="<?php echo $this->_value; ?>" validator="<?php echo $this->javascriptValidator(); ?>" <?php echo $this->_required ? 'isrequired="isrequired"' : ''; ?>>
    <option value=""><?php echo Art_I18n::getInstance()->get('select');?></option>
    <?php
    $q=Art_Mapper::getInstance()->paginate(array('oql'=>'SELECT *'.(Art_Mapper::getInstance()->classHasParent($this->_options['class'])?',parent.*':'').' FROM '.$this->_options['class']));
    foreach ($q as $id => $o) {
        echo '<option value="' . $id . '">' . $o . '</option>';
    }
    ?>
</select>