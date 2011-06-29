<?php
class Art_Data_Format_Date {
    //d/m/y => y-m-d
    public static function formatForDatabase($value){
        if(strpos($value,'/')!==false){
            $ex=explode('/',$value);
            return $ex[2].'-'.$ex[1].'-'.$ex[0];
        }
        if($value=='00/00/0000' || $value=='0000-00-00' || empty($value))return new Art_Database_Expression('NULL');
        return $value;
    }

    // y-m-d => m-d-y
    public static function formatFromDatabase($value,$options=false){
         if(strpos($value,'-')!==false){
            $ex=explode('-',$value);
            return $ex[2].'/'.$ex[1].'/'.$ex[0];
        }
        return $value=='00/00/0000'?'':$value;
    }
}
?>
