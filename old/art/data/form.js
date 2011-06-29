;
var R=function(){
    var tmp=new Array();
    this.s=function($k,$v){
        tmp[$k]=$v;
    }
    this.g=function($k){
        return tmp[$k];
    }
};
R.set=function($k,$v){
    if(!R.instance)
        R.instance=new R();
    return R.instance.s($k,$v);
}
R.get=function($k,$v){
    if(!R.instance)
        R.instance=new R();
    return R.instance.g($k);
}

function d($v){
    console.log($v);
}
(function($) {


var $popup;
function popError($domObject,$message){
    if(!$popup)
        $popup=$('body').append($('<table id="error-popup" cellspacing="0" cellpadding="0"><tbody><tr><td id="topleft" class="corner"></td><td class="top"></td><td id="topright" class="corner"></td></tr><tr><td class="left"></td><td class="content">&nbsp;gewg weg we gwe &nbsp;</td><td class="right"></td></tr><tr><td class="corner" id="bottomleft"></td><td class="bottom"><div id="tail"></div></td><td id="bottomright" class="corner"></td></tr></tbody></table>'));
    if($message==false || $message==true){
        $('#error-popup').hide();
    }else{
        $('#error-popup').show();
        $('#error-popup .content').html($message);
        $('#error-popup').css('top',($domObject).offset().top);
        $('#error-popup').css('left',($domObject).offset().left);
    }
}
$(document).ready(function(){
    (function( $ ){
        // param:$error[emptyAndRequired,errorTextOrFalse]
        $.fn.showError = function($error) {
            // remove fields that cannot possibly have errors
            if($(this).css('display')=='none' || $(this).attr('type')=='hidden' || $(this).attr('type')=='submit' || $(this).attr('type')=='button')return;
            $iconValidator=$(this).parents('form:first').attr('iconvalidator')==1 && (!$(this).attr('iconvalidator') || $(this).attr('iconvalidator')!='false');
            
            if($error[0] || $error[1]){
                if($iconValidator && ($error[0] || $error[1]))
                    $(this).popValidationIcon(true);
                if($error[0])popError(this,$translations['required']);
                else popError(this,$error[1]);
            }else{
                popError(this,false);
                // $(this).removeClass('error');
                if($iconValidator)
                    if($(this).attr('value'))
                        $(this).popValidationIcon(false);
                    else
                        $(this).parent().children('div.validation-icon').hide();
            }
        }
        $.fn.popValidationIcon=function($error){
            if(!$(this).parent().children('div.validation-icon').length)
                $(this).parent().append('<div class="validation-icon">&nbsp;</div>');
            if($error==false)
                $(this).parent().children('div.validation-icon').removeClass('invalid').addClass('valid').show();
            else if($error==true)
                $(this).parent().children('div.validation-icon').removeClass('valid').addClass('invalid').show()
            else
                $(this).parent().children('div.validation-icon').hide()
        }
        // return [emptyAndRequired,errorTextOrFalse]
        $.fn.validateInput = function(){
            // remove fields that cannot possibly have errors
            if($(this).css('display')=='none' || $(this).attr('type')=='hidden' || $(this).attr('type')=='submit' || $(this).attr('type')=='button')return;
            $(this).trigger('validateInput');
            
            // has the field got a validator?
            $validator=false;
            if($(this).attr('validator') && $(this).attr('type')!='password'){
                if($(this).attr('validator')=='false')return [false,false];
                eval('$validator='+$(this).attr('validator'));
            }
            // is the field required ?
            $required=$(this).attr('isrequired')=='isrequired';
            // is it empty?
            if($(this).val())
                $empty=$(this).val().length==0;
            else
                $empty=true;
            
            // validation, validator return true or error string
            $error=false;
            if(!$empty && $.isFunction($validator)){
                $valid=$validator(this);
                $error=$valid==true?false:($valid==false?true:$valid);
            }
            d(this)
            d([$empty && $required,$error])
            return [$empty && $required,$error]
        }

        $.fn.validateInputGroup = function($fieldset) {
            R.set('empty',true);
            // is the fieldset empty
            $(this).each(function() {
                if((!$(this).attr('type') || $(this).attr('type')!='button') && $(this).attr('name').length!=0 && $(this).val()  && $(this).val().length!=0)
                    R.set('empty',false);
            });
            var $fieldSetRequired=$fieldset.attr('isrequired') && $fieldset.attr('isrequired')=='isrequired';
            if(R.get('empty')){
                // if fieldset required and not empty => send to the first field else return false
                if($fieldSetRequired)
                    return $fieldset.find(':input').get(0);
                return false;
            }else{
                // check errors
                R.set('error',false);
                this.each(function() {
                    $errors=$(this).validateInput();
                    if($errors && (($errors[0] && $fieldSetRequired) || ($errors[1]!=true && $errors[1]!=false))){
                        if(!R.get('error',false))
                            R.set('error',this);
                    }
                });
                return R.get('error');
            }
        };
    })($);
    $intervalCheck=false;

    $('input').live('click',function(){
        if(!$(this).hasClass('autocompleted'))
             $(this).showError($(this).validateInput());
    });

    $(':input').live('focus',function(){
        if(!$(this).hasClass('autocompleted'))
            $(this).showError($(this).validateInput());
    });

    $(':input').live('change',function(){
        $(this).showError($(this).validateInput());
    });

    $(':input').live('keyup',function(e){
        if(e.keyCode>=37 && e.keyCode<=40)return;
        if(!$(this).hasClass('autocompleted'))
            $(this).showError($(this).validateInput());
    });

    $('#error-popup').live('click',function(){
        $(this).hide();
    });

    $('form').find('div.error').each(function(){
        if($(this).html().length>2){
            $fieldset=$(this).find('+ fieldset').find(':input').first().focus();
            return false;
        }
    });

    $('form').live('submit',function(){
        R.set('inputWithError',false);
        $fieldsets=$(this).find('fieldset');
        if($fieldsets.length){
            $fieldsets.each(function(){
                $error=$(this).find(':input').validateInputGroup($(this));
                if($error!=false){
                    if(!R.get('inputWithError'))
                        R.set('inputWithError',$error);
                }
            });
        }else{
            $(this).attr('isrequired','isrequired');
            $error=$(this).find(':input').validateInputGroup($(this));
            if($error!=false)
                if(!R.get('inputWithError'))
                    R.set('inputWithError',$error);
        }
        if(R.get('inputWithError')){
            $input=R.get('inputWithError');
            $($input).focus();
            $($input).showError($($input).validateInput());
        }
        return !R.get('inputWithError');
    });
    })
})(jQuery);