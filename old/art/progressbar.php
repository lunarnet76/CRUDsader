<?php

class Art_Progressbar {

    protected $_totalNumberOfElements = 100;
    protected $_percent = 1;
    protected $_position = 0;
    protected $_design = 'default';
    protected $_title = '';
    protected $_evaluatedTime;
    public static $on = true;

    /**
     */
    public function __construct($totalNumberOfElements=100, $title='', $barSizePercent=4) {
        $this->_totalNumberOfElements = $totalNumberOfElements;
        $this->_percent = $this->_totalNumberOfElements / 100; // 250
        $this->_barSizePercent = $barSizePercent;
        $this->_title = $title;
        if (ob_get_level() == 0) {
            ob_start();
        }
        Art_Debug::chrono_start('progressBar', false);
    }

    public function increase($number) {
        if (!self::$on
            )return;
        $percent = round($number / $this->_percent, 2);
        if ($number == 0)
            $evaluation = false;
        else {
            $evaluation = (Art_Debug::chrono_time('progressBar', false) / $number) * ($this->_totalNumberOfElements - $number);
            $seconds = $evaluation % 60;
            $min = ceil($evaluation / 60) - 1;
            $evaluation = $min . 'min ' . $seconds . 's';
        }
        echo $this->getOutput($percent, $evaluation);
        flush();
        ob_flush();
    }

    public function setNumberOfElements($totalNumberOfElements) {
        $this->_totalNumberOfElements = $totalNumberOfElements;
        $this->_percent = $this->_totalNumberOfElements / 100; // 250
    }

    public function setTitle($title) {
        $this->_title = $title;
    }

    public function end() {
        if (!self::$on
            )return;
        echo $this->getOutput(100);
        flush();
        ob_flush();
        if (ob_get_level() == 0)
            ob_end_flush();
    }

    public function getOutput($percent, $evaluation=false) {
        return '<div style="margin: 1px;height: 20px;background-color:white;position:absolute; border:1px solid black;width:' . ($this->_barSizePercent * 100) . 'px;
 z-index:12;
 left: 10px;
 top: 16px;
 text-align: center;"><table style="display:inline;margin-bottom:10px"><tr><td><b>' . $this->_title . '</b></td><td>' . $percent . '%</td><td>' . $evaluation . '</td></tr></table></div>
     <div style="border: 1px solid #CCC;background-color:white;
 margin: 1px;
 height: 20px;
 position:absolute;
 width:' . ($this->_barSizePercent * 100) . 'px;
 z-index:10;
 left: 10px;
 top: 38px;
 text-align: center;"><div style="width:' . ($this->_barSizePercent * $percent) . 'px; background-color:#ae1414; height:20px">&nbsp;</div></div>';
    }

}
?>