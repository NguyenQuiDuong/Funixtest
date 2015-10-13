<?php
namespace Admin\Form\Subject;

use Home\Form\FormBase;
use Zend\Form\Element\Text;

class CategoryFilter extends FormBase
{

    /**
     *
     * @param null|string $name
     */
    public function __construct($serviceLocator)
    {
        parent::__construct('ideaFilter');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'GET');

        $filter = $this->getInputFilter();

        $name = new Text('name');
        $name->setAttributes([
            'maxlength' => 255,
            'placeholder' => 'Tên danh mục'
        ]);
        $this->add($name);
        $filter->add(array(
            'name' => 'name',
            'required' => false,
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                )
            )
        ));


        $this->add(array(
            'name' => 'submit',
            'options' => array(),
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Lọc',
                'id' => 'btnFilterCompanyContact',
                'class' => 'btn btn-primary'
            )
        ));
    }
}

?>