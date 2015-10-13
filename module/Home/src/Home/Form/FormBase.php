<?php

namespace Home\Form;

use Zend\Form\FormInterface;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Validator\IsInstanceOf;
use Zend\Form\Annotation\Validator;
use Home\Model\Format;

class FormBase extends Form implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @author VanCK
     * @see \Zend\Form\Form::getData()
     */
    public function getData($flag = FormInterface::VALUES_NORMALIZED)
    {
		$data = parent::getData($flag);
		// populate default values
		foreach($this->getElements() as $element) {
			/* @var $element \Zend\Form\Element */
			if((!count($this->data) || !array_key_exists($element->getName(), $this->data))
			&& $element->getValue()) {
				$data[$element->getName()] = $element->getValue();
			}
		}
		return $data;
    }

    /**
     * load cities for select box
     * @param $element \Zend\Form\Element|string instance of Zend\Form\Element
     * 				or element name
     * @param $options array
     */
    public function loadCities($element, $options = null)
    {
		/** @var $cityMapper \Address\Model\CityMapper */
		$cityMapper = $this->getServiceLocator()->get('Address\Model\CityMapper');
		$cities = $cityMapper->fetchAll();

    	$arr = ['' => '- Thành phố -'];
    	if(is_array($cities)) {
    		foreach ($cities as $city) {
    			/** @var $city \Address\Model\City */
    			$arr[$city->getId()] = $city->getName();
    		}
    	}
    	if($element instanceof \Zend\Form\Element) {
    		$element->setValueOptions($arr);
    	} else {
    		$this->get($element)->setValueOptions($arr);
    	}
    	return $this;
    }

    /**
     * load districts for select box
     * @param $element \Zend\Form\Element|string instance of Zend\Form\Element
     * 				or element name
     * @param $options array
     */
    public function loadDistricts($element, $cityElement, $options = null)
    {
    	/** @var $request \Zend\Http\Request */
    	$request = $this->getServiceLocator()->get('Request');
    	if($request->isPost()) {
    		$cityId = $request->getPost($cityElement->getName());
    	} else {
    		$cityId = $request->getQuery($cityElement->getName());
    	}

    	if(!$cityId && isset($options['cityId'])) {
    		$cityId = $options['cityId'];
    	}

    	if(!$cityId) {
    		return;
    	}

    	$district = new \Address\Model\District();
    	$district->setCityId($cityId);

		/** @var $districtMapper \Address\Model\DistrictMapper */
		$districtMapper = $this->getServiceLocator()->get('Address\Model\DistrictMapper');
		$districts = $districtMapper->fetchAll($district);

    	$arr = ['' => '- Quận huyện -'];
    	if(is_array($districts)) {
    		foreach ($districts as $d) {
    			/** @var $d \Address\Model\District */
    			$arr[$d->getId()] = $d->getName();
    		}
    	}
    	if($element instanceof \Zend\Form\Element) {
    		$element->setValueOptions($arr);
    	} else {
    		$this->get($element)->setValueOptions($arr);
    	}
    	return $this;
    }  


    /**
     * (non-PHPdoc)
     * @see \Zend\Form\Form::setData()
     */
    public function setData($data){
    	if($this->has('companyId') && isset($data['companyId']) && $data['companyId']){
    		$companyId = $this->get('companyId');
    		if($companyId instanceof \Zend\Form\Element\Hidden && $this->has('companyIdSuggest')){
				if(!isset($data['companyIdSuggest']) || !$data['companyIdSuggest']){
					$company = new \Company\Model\Company();
					$company->setId($data['companyId']);
					$companyMapper = $this->getServiceLocator()->get('\Company\Model\CompanyMapper');
					if($companyMapper->get($company)){
						$data['companyIdSuggest'] = $company->getName();
					}
				}
    		}
    	}
    	return parent::setData($data);
    }



    /**
     * load categories for select box
     * @param $element \Zend\Form\Element|string instance of Zend\Form\Element
     * 				or element name
     * @param $options array
     */

    public function loadCategories($element, $options = null)
    {
        /** @var $categoryMapper \Subject\Model\Subject\CategoryMapper */
		$categoryMapper = $this->getServiceLocator()->get('Subject\Model\Subject\CategoryMapper');
        $categories = $categoryMapper->fetchAll();

        $arr = ['' => '- Danh mục -'];
        if(is_array($categories)) {
            foreach ($categories as $cate) {
                /** @var $cate \Subject\Model\Subject\Category */
                $arr[$cate->getId()] = $cate->getName();
            }
        }
        if($element instanceof \Zend\Form\Element) {
            $element->setValueOptions($arr);
        } else {
            $this->get($element)->setValueOptions($arr);
        }
        return $this;
    }


    /**
     * load user for select box
     * @param $element \Zend\Form\Element|string instance of Zend\Form\Element
     * 				or element name
     * @param $options array
     */
    public function loadUsers($element, $options = null)
    {
    	/** @var $UserMapper \User\Model\UserMapper */
    	$userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
    	$users = $userMapper->fetchAll();

		if($options['firstOptions']) {
			$arr = ['' => $options['firstOptions']];
		} else if ($options['User']) {
    		$arr = ['' => '- User -'];
		} else {
			$arr = ['' => '- User -'];
		}
    	if(is_array($users)) {
    		foreach ($users as $user) {
    			/** @var $city \User\Model\UserMapper */
    			$arr[$user->getId()] = $user->getFullName();
    		}
    	}
    	if($element instanceof \Zend\Form\Element) {
    		$element->setValueOptions($arr);
    	} else {
    		$this->get($element)->setValueOptions($arr);
    	}
    	return $this;
    }

    /**
     * get list of all error messages in only 1 level array
     * @return multitype:unknown |NULL
     */
    public function getErrorMessagesList(){
    	$errors = $this->getMessages();
    	if(count($errors)){
    		$result = [];
    		foreach ($errors as $elementName => $elementErrors){
    			foreach ($elementErrors as $errorMsg){
    				$result[] = $errorMsg;
    			}
    		}
    		return $result;
    	}
    	return null;
    }

}