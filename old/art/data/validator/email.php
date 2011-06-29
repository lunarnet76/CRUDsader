<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of string
 *
 * @author jb
 */
class Art_Data_Validator_Email{

    /**
     * Wether or not the value is empty
     * 	@acces public
     * 	@return bool
     */
    public static function isEmpty($value) {
        $value=trim((string)$value);
        return empty($value);
    }

    public static function isValid($value, $dataOptions=array()) {
        $valid=true;
            // length max
            if(strlen($value)>320)
                    $valid= 'email_error_too_long';

            // @ position
            $positionOfSeparator=strpos($value,'@');
            if($positionOfSeparator===false)
                    $valid= 'email_error_no_@';
            if($positionOfSeparator>65)
                    $valid= 'email_error_local_part';
            $localPart=substr($value,0,$positionOfSeparator);
            $domain=substr($value,$positionOfSeparator+1);

            // longueur
            if(strlen($localPart)==0)
                return 'email_local_part_too_short';

            // mask
            $firstPartMask=$lastPartMask='[[:alnum:][:digit:]!$%&\'*+-/=?^_`{|}~#]';
            $localPartMask='[[:alnum:][:digit:]!$%&\'*+-/=?^_`.{|}~#]*';
            if(!@ereg('^'.$firstPartMask.$localPartMask.$lastPartMask,$localPart.'$',$localPart))
                    $valid= 'email_error_mask';

            // contact host => cut cause no internet ....
            if(isset($dataOptions['checkDNS']) && gethostbyname($domain)==$domain && gethostbyname('www.'.$domain)==$domain)
                    $valid= 'email_error_cant_reach_domain';

            // final response
            return $valid===true?true:Art_I18n::getInstance()->get($valid);
    }

     public static function javascriptValid(){
         return 'function(jObj){
            $val=jObj.val();
            return /(.)*@(.*).(.*)/.test($val)?true:\''.Art_I18n::getInstance()->get('email_valid_must_be_valid').'\';
        }
        ';
    }
}
?>