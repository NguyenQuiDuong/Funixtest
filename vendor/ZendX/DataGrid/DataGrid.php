<?php

/**
 * @author 		VanCK
 * @category   	ZendX library
 * @copyright  	http://nhanh.vn
 * @license    	http://nhanh.vn/license
 */
namespace ZendX\DataGrid;

use Zend\View\Helper\EscapeHtml;

class DataGrid
{

    const SHOW_PAGING = 'showPaging';

    const SHOW_SORTING = 'showSorting';

    /**
     *
     * @var Zend\View\Helper\EscapeHtml
     */
    protected $escapeHtml;

    /**
     *
     * @var array
     */
    protected $dataSource;

    /**
     *
     * @var array
     */
    protected $headers;

    /**
     *
     * @var array
     */
    protected $bodyAttributes;

    /**
     *
     * @var array
     */
    protected $rows;

    /**
     *
     * @var array
     */
    protected $attributes = [
        'cellspacing' => '0',
        'cellpadding' => '0'
    ];

    /**
     *
     * @var array
     */
    protected $options = [
        'showPaging' => true, // flag to determine we should show or hide the paginator control
        'showSorting' => true // flag to determine we should show or hide the sorting icons
    ];

    /**
     *
     * @var array
     */
    protected $formats;

    /**
     *
     * @return the $dataSource
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     *
     * @param array|Zend_Paginator $dataSource
     */
    public function setDataSource($dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     *
     * @param
     *            array
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     *
     * @return the $bodyAttributes
     */
    public function getBodyAttributes()
    {
        return $this->bodyAttributes;
    }

    /**
     *
     * @param multitype: $bodyAttributes
     */
    public function setBodyAttributes($bodyAttributes)
    {
        $this->bodyAttributes = $bodyAttributes;
    }

    /**
     *
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     *
     * @param
     *            array
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    /**
     *
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     *
     * @param array $formats
     */
    public function setFormats($formats)
    {
        $this->formats = $formats;
    }

    /**
     *
     * @param string $key
     * @return string
     */
    public function getAttribute($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }

    /**
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     *
     * @param
     *            array
     */
    public function addAttributes($attributes)
    {
        if (! is_array($this->attributes)) {
            throw new \Exception('attributes must be an array');
        }
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    /**
     *
     * @param
     *            array
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     *
     * @return array
     */
    public function getOption($key, $default = null)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        if ($default) {
            return $default;
        }
        return null;
    }

    /**
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * constructor
     */
    public function __construct($options = null)
    {
        if (isset($options['dataSource'])) {
            $this->setDataSource($options['dataSource']);
        }
        if (isset($options['attributes'])) {
            $this->setAttributes(array_merge($this->getAttributes(), $options['attributes']));
        }
        if (isset($options['options'])) {
            if (! is_array($this->getOptions())) {
                $this->options = array();
            }
            $this->setOptions(array_merge($this->getOptions(), $options['options']));
        }
        $this->init();
    }

    /**
     * initialize datagrid
     */
    public function init()
    {}

    /**
     *
     * @var array $rows
     */
    public function addHeaders($rows)
    {
        if (! is_array($rows)) {
            throw new \Exception("formats must be an array");
        }
        foreach ($rows as $row) {
            $this->addHeader($row);
        }
    }

    /**
     *
     * @param Row|array $row
     * @throws Exception
     */
    public function addHeader($row)
    {
        if ($row instanceof Row) {
            $row->setDataGrid($this);
            $row->setType(Row::TYPE_HEADER);
            $this->headers[] = $row;
            return $row;
        } else
            if (is_array($row)) {
                $r = new Row($row);
                $r->setDataGrid($this);
                $r->setType(Row::TYPE_HEADER);
                $this->headers[] = $r;
                return $r;
            }
        throw new \Exception("row must be instantce of Row or an array");
    }

    /**
     *
     * @var array $rows
     */
    public function addRows($rows)
    {
        if (! is_array($rows)) {
            throw new \Exception("formats must be an array");
        }
        foreach ($rows as $row) {
            $this->addRow($row);
        }
    }

    /**
     *
     * @param Row|array $row
     * @throws Exception
     */
    public function addRow($row)
    {
        if ($row instanceof Row) {
            $row->setDataGrid($this);
            $this->rows[] = $row;
            return $row;
        } else
            if (is_array($row)) {
                $r = new Row($row);
                $r->setDataGrid($this);
                $this->rows[] = $r;
                return $r;
            } else {
                throw new \Exception("row must be instantce of Row or an array");
            }
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * escape html
     *
     * @author VanCK
     * @return string
     */
    public function escapeHtml($value)
    {
        if (! $this->escapeHtml) {
            $this->escapeHtml = new EscapeHtml();
        }
        return $this->escapeHtml->__invoke($value);
    }
}