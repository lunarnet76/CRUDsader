<?php
require 'bootstrap.php';

$form = sl()->form();

$form2 = sl()->form('ge');

$input = $form->add(sl()->{'form.component'},'i1',true);

if($form->ok()){
    
    
    
    $input2 = $form2->add(sl()->{'form.component'},'i2');
    echo 'FORM 1 recu<br>';
    
    
    
    
}

echo $form;

if($form2->ok()){
        echo 'FORM 2 recu<br>';
        $form->inputReset();
    }
    else echo $form2;


pre($_SESSION);