<?php
/**
 * HTML link
 * @author 	Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 */
namespace CRUDsader\MVC {
    class Link {
        /**
         * 	@access protected
         * 	@var string
         */
        protected $_text;

        /**
         * 	@access protected
         * 	@var string
         */
        protected $_image;

        /**
         * 	@access protected
         * 	@var string
         */
        protected $_css;

        /**
         *  whether to display the image in a table
         * 	@access protected
         * 	@var bool
         */
        protected $_table = true;

        /**
         * 	@access protected
         * 	@var string
         */
        protected $_url = false;

        /**
         * 	@access protected
         * 	@var string
         */
        protected $_target = false;

        /**
         * 	@access protected
         * 	@var string
         */
        protected $_id = false;

        /**
         * Constructor
         * @param array $params
         */
        public function __construct($params) {
            foreach ($params as $key => $value)
                $this->{'_' . $key} = $value;
        }

        /**
         * Write the HTML link, to be used on echo context
         * 	@access public
         * 	@return string
         */
        public function __toString() {
            $url = $this->_url;
            if (isset($this->_image))
                return '<a ' . (isset($this->_id) ? 'id="' . $this->_id . '"' : '') . ' href="' . $url . '"  ' . ($this->_css ? 'class="' . $this->_css . '"' : '') . ' ' . ($this->_target ? 'target="' . $this->_target . '"' : '') . '>' . ($this->_table ? '<table class="link"><tr><td class="image">' . $this->_image . '</td>' . ($this->_text ? '<td class="text">' . $this->_text . '</td>' : '') . '</tr></table>' : $this->_image . $this->_text) . '</a>';
            else
                return '<a ' . (isset($this->_id) ? 'id="' . $this->_id . '"' : '') . ' href="' . $url . '"  ' . ($this->_css ? 'class="' . $this->_css . '"' : '') . ' ' . ($this->_target ? 'target="' . $this->_target . '"' : '') . '>' . ($this->_text ? $this->_text : $url) . '</a>';
        }
    }
}