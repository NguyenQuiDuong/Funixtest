<?php
/**
 * @category       Shop99 library
 * @copyright      http://shop99.vn
 * @license        http://shop99.vn/license
 */

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Home\Model\Consts;
use Zend\Log\Filter\Validator;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter;
use Zend\Validator\File\ImageSize;
use User\Controller\gifresizer;
use User\Model\User;
use Home\Model\DateBase;
class ProfileController extends AbstractActionController
{
    public function indexAction()
    {
        $viewModel = new ViewModel();
        /* @var $userService \User\Service\User */
        $userService = $this->getServiceLocator()->get('User\Service\User');
        $user = $userService->getUser();
//         $form = $this->getServiceLocator()->get('User\Form\ChangePassword');
        $avatar= new \User\Form\ProfileFile('upload-file');
        $translator = $this->getServiceLocator()->get('translator');
        if (!file_exists(MEDIA_PATH.'/user')){
            mkdir(MEDIA_PATH.'/user');
            chmod(MEDIA_PATH.'/user', 0777);
        }
        if (!file_exists(MEDIA_PATH.'/user/avatar')){
            mkdir(MEDIA_PATH.'/user/avatar');
            chmod(MEDIA_PATH.'/user/avatar', 0777);
        }
        $files = scandir(MEDIA_PATH.'/user/avatar');
        $fileavatar='';
        foreach ($files as $f){
            if ($this->user()->getIdentity() == explode('.', $f)[0]){
                $fileavatar = $f;
            }
        }
        $message = '';
        $viewModel->setVariables([
        		'userService' => $userService,
        		'user' => $user,
//         		'form' => $form,
                'fileavatar'=>$fileavatar,
                'avatar' =>$avatar,
        		'message' => $message,
					]);
        return $viewModel;
    }
	public function loadgenderAction(){
	$arr = array(
			Consts::GENDER_FEMALE => 'Nữ',
			Consts::GENDER_MALE	  => 'Nam',
	);
	return new JsonModel($arr);
	}
    public function editAction()
    {
    	$sl = $this->getServiceLocator();
    	$userService = $sl->get('User\Service\User');
    	/* @var $userService \User\Service\User */
    	$user = $userService->getUser();
    	$userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
    	$data = $this->getRequest()->getPost()->toArray();
    	$json = new JsonModel();
    	if ($data['name'] == 'fullName') {
    		$fullName = strip_tags($data['value']);
    		$user->setFullName(trim($fullName))	;
    		$userMapper->updateUser($user);
    	}
    	if ($data['name'] == 'birthdate') {
    		$brithday  = strip_tags($data['value']);
    		$validator  = new \Zend\Validator\Date();
    		if ($validator->isValid($brithday)) {
    			$user->setBirthdate($brithday);
    			$userMapper->updateUser($user);
    		}else $json->setVariables([
						'code' => 1,
						'messages' => ['Sai định dạng ngày tháng file.']
				]);

    	}
    	if ($data['name'] == 'gender') {
    		$user->setGender(strip_tags($data['value']))	;
    		$userMapper->updateUser($user);
    	}
    	if ($data['name'] == 'cityId') {
    		return $json->setVariables([
				'code' => 1,
    			'messages' => strip_tags($data['value']),
    		]);
    	}
    	if ($data['name'] == 'districId') {
    		$user->setDistrictId(strip_tags($data['value']));
    		if (!empty($data['city'])) {
    			$user->setCityId(strip_tags($data['city']));
    		}
    		$userMapper->updateUser($user);
    	}
    	if ($data['name'] == 'address') {
    		$user->setAddress(strip_tags($data['value']))	;
    		$userMapper->updateUser($user);
    	}
    	if ($data['name'] == 'mobile') {
    	    $validator = new \Zend\Validator\Digits();
    	    if ($validator->isValid($data['value'])){
    		  $user->setMobile(strip_tags($data['value']))	;
    		  $userMapper->updateUser($user);
    	    }
    	}
    	if ($data['name'] == 'email') {
    	    $validator = new \Zend\Validator\EmailAddress();
    	    if ($validator->isValid($data['value'])){
    	       $user->setEmail(strip_tags($data['value']))	;
    	       $userMapper->updateUser($user);
    	      }
    	}
    	return '';
    }
    /**
     * easy image resize function
     * @param  $file - file name to resize
     * @param  $string - The image data, as a string
     * @param  $width - new image width
     * @param  $height - new image height
     * @param  $proportional - keep image proportional, default is no
     * @param  $output - name of the new file (include path if needed)
     * @param  $delete_original - if true the original image will be deleted
     * @param  $use_linux_commands - if set to true will use "rm" to delete the image, if false will use PHP unlink
     * @param  $quality - enter 1-100 (100 is best quality) default is 100
     * @return boolean|resource
     */
    function smart_resize_image($file,
        $string             = null,
        $width              = 0,
        $height             = 0,
        $proportional       = false,
        $output             = 'file',
        $delete_original    = true,
        $use_linux_commands = false,
        $quality = 100
    ) {
    
        if ( $height <= 0 && $width <= 0 ) return false;
        if ( $file === null && $string === null ) return false;
    
        # Setting defaults and meta
        $info                         = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
        $image                        = '';
        $final_width                  = 0;
        $final_height                 = 0;
        list($width_old, $height_old) = $info;
        $cropHeight = $cropWidth = 0;
    
        # Calculating proportionality
        if ($proportional) {
        if      ($width  == 0)  $factor = $height/$height_old;
        elseif  ($height == 0)  $factor = $width/$width_old;
        else                    $factor = min( $width / $width_old, $height / $height_old );
    
        $final_width  = round( $width_old * $factor );
        $final_height = round( $height_old * $factor );
        }
        else {
        $final_width = ( $width <= 0 ) ? $width_old : $width;
        $final_height = ( $height <= 0 ) ? $height_old : $height;
        $widthX = $width_old / $width;
        $heightX = $height_old / $height;
    
        $x = min($widthX, $heightX);
        $cropWidth = ($width_old - $width * $x) / 2;
        $cropHeight = ($height_old - $height * $x) / 2;
        }
//         /* Attempt to open */
//         $im = @imagecreatefromjpeg($file);
        
//         /* See if it failed */
//         if(!$im)
//         {
//             /* Create a black image */
//             $im  = imagecreatetruecolor(150, 30);
//             $bgc = imagecolorallocate($im, 255, 255, 255);
//             $tc  = imagecolorallocate($im, 0, 0, 0);
        
//             imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
        
//             /* Output an error message */
//             imagestring($im, 1, 5, 5, 'Error loading ' . $file, $tc);
//             return $im;
//         }
        
        
        # Loading image to memory according to type
        switch ( $info[2] ) {
        case IMAGETYPE_JPEG:  $file != null ? $image = @imagecreatefromjpeg($file) : $image = @imagecreatefromstring($string);
                    if(!$image){
                        return false;
                    }
        break;
        case IMAGETYPE_GIF:   $file != null ? $image = @imagecreatefromgif($file)  : $image = @imagecreatefromstring($string); 
                    if(!$image){
                        return false;
                    }
        break;
        case IMAGETYPE_PNG:   $file != null ? $image = @imagecreatefrompng($file)  : $image = @imagecreatefromstring($string);
                    if(!$image){
                        return false;
                    }      
        
        break;
        default: return false;
        }
        
        
        # This is the resizing/resampling/transparency-preserving magic
        $image_resized = imagecreatetruecolor( $final_width, $final_height );
        if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
        $transparency = imagecolortransparent($image);
        $palletsize = imagecolorstotal($image);
    
        if ($transparency >= 0 && $transparency < $palletsize) {
        $transparent_color  = imagecolorsforindex($image, $transparency);
        $transparency       = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
        imagefill($image_resized, 0, 0, $transparency);
        imagecolortransparent($image_resized, $transparency);
        }
        elseif ($info[2] == IMAGETYPE_PNG) {
        imagealphablending($image_resized, false);
        $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
        imagefill($image_resized, 0, 0, $color);
        imagesavealpha($image_resized, true);
        }
        }
        imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);
    
        # Taking care of original, if needed
        if ( $delete_original ) {
        if ( $use_linux_commands ) exec('rm '.$file);
        else @unlink($file);
    }
    
    # Preparing a method of providing result
        switch ( strtolower($output) ) {
        case 'browser':
        $mime = image_type_to_mime_type($info[2]);
        header("Content-type: $mime");
        $output = NULL;
        break;
        case 'file':
        $output = $file;
        break;
        case 'return':
        return $image_resized;
        break;
        default:
            break;
        }
    
        # Writing image according to type to the output destination and image quality
        switch ( $info[2] ) {
        case IMAGETYPE_GIF:   imagegif($image_resized, $output);    break;
        case IMAGETYPE_JPEG:  imagejpeg($image_resized, $output, $quality);   break;
        case IMAGETYPE_PNG:
        $quality = 9 - (int)((0.9*$quality)/10.0);
        imagepng($image_resized, $output, $quality);
        break;
        default: return false;
        }
    
        return true;
        }
        /** 
         * @author DuongNq
         * @return \Zend\View\Model\JsonModel
         */
		public function avatarAction(){
		$formData = $this->getRequest()->getPost()->toArray();
		
		$user = new User();
		$userMapper= $this->getServiceLocator()->get('User\Model\UserMapper');
		$user = $userMapper->get($this->user()->getIdentity());
		
		$uri = new \Home\Service\Uri();
		$datePath = DateBase::toFormat($user->getCreatedDateTime(), 'Ymd');
		
		if (!file_exists(MEDIA_PATH.'/user/'.$datePath.'/')){
		    mkdir(MEDIA_PATH.'/user/'.$datePath.'/');
		    chmod(MEDIA_PATH.'/user/'.$datePath.'/', 0777);
		}
		
		if (!file_exists($uri->getSavePath($user))){
		    mkdir($uri->getSavePath($user));
		    chmod($uri->getSavePath($user), 0777);
		}
		
		$avatarFiles = scandir($uri->getSavePath($user));
		for($i=2;$i<count($avatarFiles);$i++){
		    if($this->user()->getIdentity() == explode('.', $avatarFiles[$i])[0]){
		        unlink(MEDIA_PATH.'/user/avatar/'.$avatarFiles[$i]);
		    }
		}
		
		$file = $this->params()->fromFiles();
		$form = new \User\Form\ProfileFile('fileUpload');
		$form->addInputFilter($user);
		
		$tempFile = null;
			foreach ($file as $key => $fileInfo) {
			    $fileInfo['name'] = time().rand(1, 1000).'.'.pathinfo($fileInfo['name'],PATHINFO_EXTENSION);
			    $formData['fileUpload'] = $fileInfo;
				if (getimagesize($fileInfo['tmp_name'])[0] != 119 && getimagesize($fileInfo['tmp_name'])[0] != 119){
				    if (!$this->smart_resize_image($fileInfo['tmp_name'],null,119,119)){
				        $json = new JsonModel();
				        $json->setVariables([
				            'code' => 1,
				            'messages' => 'Ảnh bị lỗi',
				        ]);
				        return $json;
				    }
			    }
				$form->setData($formData);
				if ($form->isValid()) {
					$data = $form->getData();
					$user->setAvatar($data['fileUpload']['name']);
					$userMapper->save($user);
				} else {
					// Form not valid, but file uploads might be valid...
					// Get the temporary file information to show the user in the view
					$fileErrors = $form->get('fileUpload')->getMessages();
					$json = new JsonModel($fileErrors);
					return $json;

					if (empty($fileErrors)) {
						$tempFile = $form->get('fileUpload')->getValue();
					}
				}
			}
		$variables = array(
		        'code' => 0,
				'path' => $uri->getViewPath($user),
		);
		$json = new JsonModel($variables);
		return $json;
		}
//     public function editAction()
//     {
//         $sl = $this->getServiceLocator();
//         $userService = $sl->get('User\Service\User');
//         /* @var $userService \User\Service\User */
//         $user = $userService->getUser();
//         vdump($user);
//         die;
//         /* @var $cityMapper \Address\Model\CityMapper */
//         $cityMapper = $sl->get('Address\Model\CityMapper');
//         /* @var $districtMapper \Address\Model\DistrictMapper */
//         $districtMapper = $sl->get('Address\Model\DistrictMapper');
//         $translator = $sl->get('translator');
//         $dt = new \Address\Model\District();

//         $dt->setCityId($user->getCityId());
//         $cities = $cityMapper->fetchAll();
//         $districts = $districtMapper->fetchAll($dt);
//         $message = '';
//         if ($this->getRequest()->isPost()) {
//             $us = new \User\Model\User();
//             $postData = (array)$this->getRequest()->getPost();
//             $us->exchangeArray($postData);
//             $us->setId($user->getId());
//             if (isset($postData['newpassword'])) {
//                 $us->setPassword($postData['newpassword']);
//             }
//             if ($userService->updateUser($us)) {
//                 $message = $translator->translate('Cập nhập thông tin thành công');
//             } else {
//                 $message = $translator->translate('Có lỗi xảy ra, cập nhập thông tin thành viên thất bại');
//             }
//         }
//         return new ViewModel(
//             array('user'      => $userService->getUser(),
//                   'cities'    => $cities,
//                   'districts' => $districts,
//                   'message'   => $message
//             )
//         );

//     }
//     public function changepasswordAction()
//         {
//         	$sl = $this->getServiceLocator();
//         	/*@var $form \User\Form\ChangePassword */
//         	$userService = $sl->get('User\Service\User');
//         	/*@var $userService \User\Service\User */
//         	$user = $userService->getUser();
//         	$translator = $sl->get('translator');
//         	$json = new JsonModel();
//         	$message = '';
//         	if ($this->getRequest()->isPost()) {
//         		$oldpass = $_POST['oldpass'];
//         		$newpass = $_POST['newpass'];
//         			$us = new \User\Model\User();
//         			$us->setId($user->getId());
//         			$us->setPassword($oldpass);
//         			if (!$userService->validateChangeInfo($us)) {
//         				$json->setVariables([
//     		        					'code' => 1,
//     		    						'data' => 'Mật khẩu cũ không chính xác',
//     		    				]);
//         			} else {
//         				$us->setId($user->getId());
//         				$us->setPassword($newpass);
//         				$userService->updateUser($us);
//         				$json->setVariables([
//     		        			'code' => 2,
//     		    				'data' => 'Đổi mật khẩu thành công',
//     		    				]);
//         			}

//         	}
//         	return $json;
//         }


    public function changepasswordAction()
    {
        $sl = $this->getServiceLocator();
        /*@var $form \User\Form\ChangePassword */
        $userService = $sl->get('User\Service\User');
        /*@var $userService \User\Service\User */
        $user = $userService->getUser();
        $form = $sl->get('User\Form\ChangePassword');
        $translator = $sl->get('translator');
        $message = 0;
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $postData = (array)$this->getRequest()->getPost();
                $us = new \User\Model\User();
                $us->setId($user->getId());
                $us->setPassword($postData['oldpassword']);
                if (!$userService->validateChangeInfo($us)) {
                    $message = 1;
//                     '<p class="error">' . $translator->translate('Mật khẩu cũ nhập không chính xác') . '</p>';
//                     return new ViewModel(array('form'    => $form,
//                     		'message' => $message));
                } else {
                    $us->setId($user->getId());
                    $us->setPassword($postData['newpassword']);
                    $userService->updateUser($us);
                    $message = 2;
 //                      $translator->translate('Đổi mật khẩu tài khoản thành công');
//                     return new ViewModel(array(
//                     		'message' => $message));
                }
            }
        }
        return new ViewModel(array('form'    => $form,
                                   'message' => $message));
    }

}
