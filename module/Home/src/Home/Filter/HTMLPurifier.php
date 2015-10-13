<?php
/**
 * Home\Filter\HTMLPurifier
 *
 * @author      VanCK
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace Home\Filter;

require_once LIB_PATH . '/HTMLPurifier/HTMLPurifier.auto.php';

use Zend\Filter\AbstractFilter;
use \HTMLPurifier as HTMLPurifierLib;

class HTMLPurifier extends AbstractFilter
{

    protected $_htmlPurifier = null;

    /**
     * filter html value
     * @see \Zend\Filter\FilterInterface::filter()
     */
    public function filter($value)
    {
        if (! $this->_htmlPurifier) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Filter.YouTube', true);
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%'); // allow YouTube and Vimeo
            $this->_htmlPurifier = new HTMLPurifierLib($config);
        }
        return $this->_htmlPurifier->purify($value);
    }
}