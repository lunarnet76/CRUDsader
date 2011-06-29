<?php
interface Art_Policy{
    public function hasPolicy($name);
    public function usePolicy($name);
}
?>
