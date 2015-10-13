<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Controller;

use Home\Controller\ControllerBase;
use Home\Model\DateBase;
use Home\Filter\HTMLPurifier;
use Zend\Db\Sql\Predicate\NotIn;


class ApiController extends ControllerBase
{

    /**
     * API để tạo lead, gắn vào các nguồn tạo lead như đăng kí trên website
     * param:
            token
            companyId
            name
            mobile
            mobile2
            phone
            phone2
            email
            cityId
            districtId
            address
            website
            nhanhStoreId
            nhanhStoreName
            service
            note
            title
            source
     */
    public function addleadAction(){
        //@TODO fix cung nguoi dc yeu cau laf Thanh Lan
        $relatedUserId = 21;

        if($this->getRequest()->isPost()){
            $formValidate = new \System\Form\Api\LeadValidate($this->getServiceLocator());
            $dataToPopulate = $this->getRequest()->getPost();
            //@TODO: fix cứng cho nhanh đã
            $dataToPopulate['companyId'] = 1;
            $formValidate->setData($dataToPopulate);
            if($formValidate->isValid()){
                $formData = $formValidate->getData();
                $isNew = true;
                $accountId = null;
                $campaignId = null;

                //Tạo mới thông tin nếu chưa có
                $lead = new \Crm\Model\Lead();
                $lead->exchangeArray($formData);
                $lead->setSource(\Crm\Model\Lead::SOURCE_WEB);
                if($lead->getSourceReference()){
                	$lead->setSourceReference(substr($lead->getSourceReference(), 0, 220));
                }
                $lead->setCreatedById(1);
                $lead->setCreatedDate(DateBase::getCurrentDate());
                $lead->setCreatedDateTime(DateBase::getCurrentDateTime());
                $lead->setDescription('Khách hàng tự đăng kí dùng thử trên nhanh.vn');
                $leadMapper = $this->getServiceLocator()->get('\Crm\Model\LeadMapper');
                if(!$leadMapper->isExisted($lead)){
                    $referSource = [];
                    $content = [];
                    $isVaild = false;
                    if(isset($formData['utm_source']) && $formData['utm_source']){
                        $referSource[] = $formData['utm_source'];
                        $content[] = '<b>utm_source: </b>'.$formData['utm_source'];
                        $isVaild = true;
                    } else {
                        $referSource[] = '';
                        $content[] = '<b>utm_source: </b>';
                    }
                    if(isset($formData['utm_medium']) && $formData['utm_medium']){
                        $referSource[] = $formData['utm_medium'];
                        $content[] = '<b>utm_medium: </b>'.$formData['utm_medium'];
                        $isVaild = true;
                    } else {
                        $referSource[] = '';
                        $content[] = '<b>utm_medium: </b>';
                    }
                    if(isset($formData['utm_campaign']) && $formData['utm_campaign']){
                        $referSource[] = $formData['utm_campaign'];
                        $content[] = '<b>utm_campaign: </b>'.$formData['utm_campaign'];
                        $isVaild = true;
                    } else {
                        $referSource[] = '';
                        $content[] = '<b>utm_campaign: </b>';
                    }

                    if(isset($formData['utm_term']) && $formData['utm_term']){
                        $content[] = '<b>utm_term: </b>'.$formData['utm_term'];
                    } else {
                        $content[] = '<b>utm_term: </b>';
                    }
                    if(isset($formData['utm_content']) && $formData['utm_content']){
                        $content[] = '<b>utm_content: </b>'.$formData['utm_content'];
                    } else {
                        $content[] = '<b>utm_content: </b>';
                    }

                    if($isVaild){
                        $campaign = new \Crm\Model\Campaign();
                        $campaign->setCode(implode('_', $referSource));
                        $campaign->setCompanyId($lead->getCompanyId());

                        $campaignMapper = $this->getServiceLocator()->get('\Crm\Model\CampaignMapper');
                        if($campaignMapper->isExistedCode($campaign) === false){
                            $campaign->setStartDate(DateBase::getCurrentDate());
                            $today = new \DateTime();
                            $today->add(new \DateInterval('P12M'));
                            $campaign->setEndDate($today->format(DateBase::COMMON_DATE_FORMAT));
                            $campaign->setName($campaign->getCode());
                            $campaign->setContent(implode('<br/>', $content));
                            $campaign->setCreatedById(1);
                            $campaign->setCreatedDateTime(DateBase::getCurrentDateTime());
                            $campaignMapper->save($campaign);

                        }
                        $campaignId = $campaign->getId();
                        $lead->setSource(\Crm\Model\Lead::SOURCE_MARKETING_CAMPAIGN);
                        $lead->setDescription(implode('<br/>', $content));
                        $lead->setCampaignId($campaignId);

                    }
                    $leadMapper->save($lead);
                    $leadMapper->updateinformation($lead);
                } else {
                    $isNew = false;
                    if($lead->getOption('tableExisted') == 'account'){
                        $accountId = $lead->getOption('accountId');
                    }
                }

                //Update nguồn cho leadCompany
                $leadCompany = new \Crm\Model\Lead\Company();
                $leadCompany->setCompanyId($lead->getCompanyId());
                $leadCompany->setLeadId($lead->getId());
                $leadCompany->setAccountId($accountId);
                if($campaignId){
                    $leadCompany->setSource(\Crm\Model\Lead::SOURCE_MARKETING_CAMPAIGN);
                    $leadCompany->setSourceCampaignId($campaignId);
                    $leadCompany->setSourceReference($lead->getSourceReference());
                } elseif ($isNew) {
                    $leadCompany->setSource(\Crm\Model\Lead::SOURCE_WEB);
                    if($lead->getSourceReference()){
                        $leadCompany->setSourceReference($lead->getSourceReference());
                    } else {
                        $leadCompany->setSourceReference('nhanh.vn');
                    }
                }
                $leadCompany->setStatus(\Crm\Model\Lead\Company::STATUS_FREE);
                $leadCompanyMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\CompanyMapper');
                //Nếu leadCompany đã tồn tại (thông tin đã được sử dụng với cty đang check) thì chỉ update lastActivityDateTime
                $leadCompanyMapper->isExisted($leadCompany);
                $leadCompany->setLastActivityDateTime(DateBase::getCurrentDateTime());
                $leadCompanyMapper->save($leadCompany);

                //Log hành động yêu cầu dùng thử của khách hàng
                $activity = new \Crm\Model\Activity();
                $activity->setLeadId($lead->getId());
                $activity->setAccountId($accountId);
                $activity->setCompanyId($lead->getCompanyId());
                $activity->setType(\Crm\Model\Activity::TYPE_REGISTER_FOR_TRIAL);
                $activity->setStatus(\Crm\Model\Activity::STATUS_SUCCESS);
//$activity->setTitle('Đăng kí dùng thử '.$formData['service']) ;
                $activity->setTitle($formData['title']?:'Đăng kí dùng thử '.$formData['service']) ;
                $activity->setContent($formData['note']?:null);
                $activity->setCreatedById(1);
                $activity->setCreatedDate(DateBase::getCurrentDate());
                $activity->setCreatedDateTime(DateBase::getCurrentDateTime());
                $activityMapper = $this->getServiceLocator()->get('\Crm\Model\ActivityMapper');
                $activityMapper->save($activity);
                $activityMapper->updateLeadId($activity);

                //Check lại 1 lần nữa trạng thái của leadCompany là thả nổi hay đã có KD
                $leadCompanyMapper->updateStatus($leadCompany);
                //Nếu là thả nổi (tức là vừa dc tạo hoặc đang thả nổi) thì tạo 1 yêu cầu gọi cho chăm sóc mặc định và gắn cho cham sóc mặc định
                if($leadCompany->getStatus() == \Crm\Model\Lead\Company::STATUS_FREE){
                	$activity = new \Crm\Model\Activity();
                	$activity->setLeadId($lead->getId());
                	$activity->setAccountId($accountId);
                	$activity->setCompanyId($lead->getCompanyId());
                	$activity->setType(\Crm\Model\Activity::TYPE_REQUEST_PHONECALL);
                	$activity->setStatus(\Crm\Model\Activity::STATUS_SUCCESS);
                	$activity->setTitle('Gọi xác nhận yêu cầu dùng thử');
                	$activity->setCreatedById(1);
                	$activity->setCreatedDate(DateBase::getCurrentDate());
                	$activity->setCreatedDateTime(DateBase::getCurrentDateTime());
                	$activity->setRelatedUserId($relatedUserId);
                	$activityMapper->save($activity);

                	$leadUser = new \Crm\Model\Lead\User();
                	$leadUser->setLeadId($lead->getId());
                	$leadUser->setAccountId($accountId);
                	$leadUser->setCompanyId($lead->getCompanyId());
                	$leadUser->setType(\Crm\Model\Lead\User::TYPE_SALE);
                	$leadUser->setUserId($relatedUserId);
                	$leadUser->setCreatedById(1);
                	$leadUser->setCreatedDateTime(DateBase::getCurrentDateTime());
                	$leadUserMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\UserMapper');
                	if(!$leadUserMapper->isExisted($leadUser)){
                		$leadUserMapper->save($leadUser);
                	}
                	$leadCompanyMapper->updateColumns(['status' => \Crm\Model\Lead\Company::STATUS_BELONG], $leadCompany);
                }

                return $this->getJsonModel()->setVariable('code', 1);
            }
            return $this->getJsonModel()->setVariables([
	           'code' => 0,
                'messages' => [$formValidate->getMessages()],
            ]);
        }
        return $this->getJsonModel()->setVariables([
            'code' => 0,
            'messages' => ['']
            ]);
    }

    public function addcustomerrequirementAction(){
        //@TODO fix cung nguoi dc yeu cau laf Thanh Lan
        $relatedUserId = 21;

        if($this->getRequest()->isPost()){
            $formValidate = new \System\Form\Api\LeadValidate($this->getServiceLocator());
            $dataToPopulate = $this->getRequest()->getPost();
            //@TODO: fix cứng cho nhanh đã
            $dataToPopulate['companyId'] = 1;
            $formValidate->setData($dataToPopulate);
            if($formValidate->isValid()){
                $formData = $formValidate->getData();
                $isNew = true;
                $accountId = null;
                $lead = new \Crm\Model\Lead();
                $lead->exchangeArray($formData);
                $lead->setSource(\Crm\Model\Lead::SOURCE_WEB);
                if($lead->getSourceReference()){
                	$lead->setSourceReference(substr($lead->getSourceReference(), 0, 220));
                }
                $lead->setCreatedById(1);
                $lead->setCreatedDate(DateBase::getCurrentDate());
                $lead->setCreatedDateTime(DateBase::getCurrentDateTime());
                $lead->setDescription('Khách hàng tự đăng kí dùng thử trên nhanh.vn');
                $leadMapper = $this->getServiceLocator()->get('\Crm\Model\LeadMapper');
                if(!$leadMapper->isExisted($lead)){
                    $referSource = [];
                    $content = [];
                    $isVaild = false;
                    if(isset($formData['utm_source']) && $formData['utm_source']){
                    	$referSource[] = $formData['utm_source'];
                    	$content[] = '<b>utm_source: </b>'.$formData['utm_source'];
                    	$isVaild = true;
                    } else {
                    	$referSource[] = '';
                    	$content[] = '<b>utm_source: </b>';
                    }
                    if(isset($formData['utm_medium']) && $formData['utm_medium']){
                    	$referSource[] = $formData['utm_medium'];
                    	$content[] = '<b>utm_medium: </b>'.$formData['utm_medium'];
                    	$isVaild = true;
                    } else {
                    	$referSource[] = '';
                    	$content[] = '<b>utm_medium: </b>';
                    }
                    if(isset($formData['utm_campaign']) && $formData['utm_campaign']){
                    	$referSource[] = $formData['utm_campaign'];
                    	$content[] = '<b>utm_campaign: </b>'.$formData['utm_campaign'];
                    	$isVaild = true;
                    } else {
                    	$referSource[] = '';
                    	$content[] = '<b>utm_campaign: </b>';
                    }

                    if(isset($formData['utm_term']) && $formData['utm_term']){
                    	$content[] = '<b>utm_term: </b>'.$formData['utm_term'];
                    } else {
                    	$content[] = '<b>utm_term: </b>';
                    }
                    if(isset($formData['utm_content']) && $formData['utm_content']){
                    	$content[] = '<b>utm_content: </b>'.$formData['utm_content'];
                    } else {
                    	$content[] = '<b>utm_content: </b>';
                    }

                    if($isVaild){
                    	$campaign = new \Crm\Model\Campaign();
                    	$campaign->setCode(implode('_', $referSource));
                    	$campaign->setCompanyId($lead->getCompanyId());

                    	$campaignMapper = $this->getServiceLocator()->get('\Crm\Model\CampaignMapper');
                    	if($campaignMapper->isExistedCode($campaign) === false){
                    		$campaign->setStartDate(DateBase::getCurrentDate());
                    		$today = new \DateTime();
                    		$today->add(new \DateInterval('P12M'));
                    		$campaign->setEndDate($today->format(DateBase::COMMON_DATE_FORMAT));
                    		$campaign->setName($campaign->getCode());
                    		$campaign->setContent(implode('<br/>', $content));
                    		$campaign->setCreatedById(1);
                    		$campaign->setCreatedDateTime(DateBase::getCurrentDateTime());
                    		$campaignMapper->save($campaign);

                    	}
                    	$campaignId = $campaign->getId();

                    	$lead->setSource(\Crm\Model\Lead::SOURCE_MARKETING_CAMPAIGN);
                    	$lead->setDescription(implode('<br/>', $content));
                    	$lead->setCampaignId($campaignId);
                    }
                    $leadMapper->save($lead);
                } else {
                    $isNew = false;
                    if($lead->getOption('tableExisted') == 'account'){
                        $accountId = $lead->getOption('accountId');
                    }
                }

                $leadCompany = new \Crm\Model\Lead\Company();
                $leadCompany->setCompanyId($lead->getCompanyId());
                $leadCompany->setLeadId($lead->getId());
                $leadCompany->setAccountId($accountId);
                $leadCompany->setSource(\Crm\Model\Lead::SOURCE_WEB);
                if(isset($formData['sourceReference']) && $formData['sourceReference']){
                    $leadCompany->setSourceReference($formData['sourceReference']);
                } else {
                    $leadCompany->setSourceReference('nhanh.vn');
                }

                $leadCompany->setStatus(\Crm\Model\Lead\Company::STATUS_FREE);
                $leadCompanyMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\CompanyMapper');
                $leadCompanyMapper->isExisted($leadCompany);
                $leadCompany->setLastActivityDateTime(DateBase::getCurrentDateTime());
                $leadCompanyMapper->save($leadCompany);

                $activity = new \Crm\Model\Activity();
                $activity->setLeadId($lead->getId());
                $activity->setAccountId($accountId);
                $activity->setCompanyId($lead->getCompanyId());
                $activity->setType(\Crm\Model\Activity::TYPE_CUSTOMER_REQUEST);
                $activity->setStatus(\Crm\Model\Activity::STATUS_SUCCESS);
                $activity->setTitle($formData['title']) ;
                $activity->setContent($formData['note']);
                $activity->setCreatedById(1);
                $activity->setCreatedDate(DateBase::getCurrentDate());
                $activity->setCreatedDateTime(DateBase::getCurrentDateTime());
                $activityMapper = $this->getServiceLocator()->get('\Crm\Model\ActivityMapper');
                $activityMapper->save($activity);

                //Check lại 1 lần nữa trạng thái của leadCompany là thả nổi hay đã có KD
                $leadCompanyMapper->updateStatus($leadCompany);
                //Nếu là thả nổi (tức là vừa dc tạo hoặc đang thả nổi) thì tạo 1 yêu cầu gọi cho chăm sóc mặc định và gắn cho cham sóc mặc định
                if($leadCompany->getStatus() == \Crm\Model\Lead\Company::STATUS_FREE){
                    $activity = new \Crm\Model\Activity();
                    $activity->setLeadId($lead->getId());
                    $activity->setAccountId($accountId);
                    $activity->setCompanyId($lead->getCompanyId());
                    $activity->setType(\Crm\Model\Activity::TYPE_REQUEST_PHONECALL);
                    $activity->setStatus(\Crm\Model\Activity::STATUS_SUCCESS);
                    $activity->setTitle('Gọi xác nhận khách hàng');
                    $activity->setCreatedById(1);
                    $activity->setCreatedDate(DateBase::getCurrentDate());
                    $activity->setCreatedDateTime(DateBase::getCurrentDateTime());
                    $activity->setRelatedUserId($relatedUserId);
                    $activityMapper->save($activity);

                    $leadUser = new \Crm\Model\Lead\User();
                    $leadUser->setLeadId($lead->getId());
                    $leadUser->setAccountId($accountId);
                    $leadUser->setCompanyId($lead->getCompanyId());
                    $leadUser->setType(\Crm\Model\Lead\User::TYPE_SALE);
                    $leadUser->setUserId($relatedUserId);
                    $leadUser->setCreatedById(1);
                    $leadUser->setCreatedDateTime(DateBase::getCurrentDateTime());
                    $leadUserMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\UserMapper');
                    if(!$leadUserMapper->isExisted($leadUser)){
                        $leadUserMapper->save($leadUser);
                    }

                    $leadCompanyMapper->updateColumns(['status' => \Crm\Model\Lead\Company::STATUS_BELONG], $leadCompany);
                }
                $activityMapper->updateLeadId($activity);
                return $this->getJsonModel()->setVariable('code', 1);
            } else {
                return $this->getJsonModel()->setVariables([
                    'code' => 0,
                    'messages' => [$formValidate->getErrorMessagesList()]
                    ]);
            }
        }
        return $this->getJsonModel()->setVariables([
            'code' => 0,
            'messages' => ['']
            ]);
    }

    public function fixaddlead20150630Action(){
		$fromDateTime = '2015-06-26 00:00:00';
		$toDateTime = '2015-06-29 00:00:00';
    	//@TODO fix cung nguoi dc yeu cau laf Thanh Lan
    	$relatedUserId = 21;

    	if($this->getRequest()->isPost()){
    		$formValidate = new \System\Form\Api\LeadValidate($this->getServiceLocator());
    		$dataToPopulate = $this->getRequest()->getPost();
    		//@TODO: fix cứng cho nhanh đã
    		$dataToPopulate['companyId'] = 1;
    		$formValidate->setData($dataToPopulate);
    		if($formValidate->isValid()){
    			$formData = $formValidate->getData();
    			$isNew = true;
    			$accountId = null;
    			$campaignId = null;
    			$createdDateTime = $this->getRequest()->getPost('createdDateTime');

    			//Tạo mới thông tin nếu chưa có
    			$lead = new \Crm\Model\Lead();
    			$lead->exchangeArray($formData);
    			$lead->setSource(\Crm\Model\Lead::SOURCE_WEB);
    			if($lead->getSourceReference()){
    				$lead->setSourceReference(substr($lead->getSourceReference(), 0, 220));
    			}
    			$lead->setCreatedById(1);
    			$lead->setCreatedDate(DateBase::getCurrentDate());
    			$lead->setCreatedDateTime(DateBase::getCurrentDateTime());
    			$lead->setDescription('Khách hàng tự đăng kí dùng thử trên nhanh.vn');
    			$leadMapper = $this->getServiceLocator()->get('\Crm\Model\LeadMapper');
    			if(!$leadMapper->isExisted($lead)){
    				$referSource = [];
    				$content = [];
    				$isVaild = false;
    				if(isset($formData['utm_source']) && $formData['utm_source']){
    					$referSource[] = $formData['utm_source'];
    					$content[] = '<b>utm_source: </b>'.$formData['utm_source'];
    					$isVaild = true;
    				} else {
    					$referSource[] = '';
    					$content[] = '<b>utm_source: </b>';
    				}
    				if(isset($formData['utm_medium']) && $formData['utm_medium']){
    					$referSource[] = $formData['utm_medium'];
    					$content[] = '<b>utm_medium: </b>'.$formData['utm_medium'];
    					$isVaild = true;
    				} else {
    					$referSource[] = '';
    					$content[] = '<b>utm_medium: </b>';
    				}
    				if(isset($formData['utm_campaign']) && $formData['utm_campaign']){
    					$referSource[] = $formData['utm_campaign'];
    					$content[] = '<b>utm_campaign: </b>'.$formData['utm_campaign'];
    					$isVaild = true;
    				} else {
    					$referSource[] = '';
    					$content[] = '<b>utm_campaign: </b>';
    				}

    				if(isset($formData['utm_term']) && $formData['utm_term']){
    					$content[] = '<b>utm_term: </b>'.$formData['utm_term'];
    				} else {
    					$content[] = '<b>utm_term: </b>';
    				}
    				if(isset($formData['utm_content']) && $formData['utm_content']){
    					$content[] = '<b>utm_content: </b>'.$formData['utm_content'];
    				} else {
    					$content[] = '<b>utm_content: </b>';
    				}

    				if($isVaild){
    					$campaign = new \Crm\Model\Campaign();
    					$campaign->setCode(implode('_', $referSource));
    					$campaign->setCompanyId($lead->getCompanyId());

    					$campaignMapper = $this->getServiceLocator()->get('\Crm\Model\CampaignMapper');
    					if($campaignMapper->isExistedCode($campaign) === false){
    						$campaign->setStartDate(DateBase::getCurrentDate());
    						$today = new \DateTime();
    						$today->add(new \DateInterval('P12M'));
    						$campaign->setEndDate($today->format(DateBase::COMMON_DATE_FORMAT));
    						$campaign->setName($campaign->getCode());
    						$campaign->setContent(implode('<br/>', $content));
    						$campaign->setCreatedById(1);
    						$campaign->setCreatedDateTime(DateBase::getCurrentDateTime());
    						$campaignMapper->save($campaign);

    					}
    					$campaignId = $campaign->getId();
    					$lead->setSource(\Crm\Model\Lead::SOURCE_MARKETING_CAMPAIGN);
    					$lead->setDescription(implode('<br/>', $content));
    					$lead->setCampaignId($campaignId);

    				}
    				$leadMapper->save($lead);
    			} else {

    				if($lead->getOption('tableExisted') == 'lead'){
    					$dbAdapter = $this->getServiceLocator()->get('dbAdapter');
    					/* @var $dbSql \Zend\Db\Sql\Sql */
    					$dbSql = $this->getServiceLocator()->get('dbSql');
    					// check nếu đã tồn tại yêu cầu dùng thử trong khoảng thời gian cần check thì sẽ kiểm tra lại
    					// nếu có hành động bàn giao cho người khác Lan nhưng ko có bất kì hành động j sau đó thì xóa đi
    					$select = $dbSql->select(['a' => \Crm\Model\ActivityMapper::TABLE_NAME]);
    					$select->where(['leadId' => $lead->getId()]);
    					$select->where(['companyId' => $lead->getCompanyId()]);
    					$select->where(['type' => [\Crm\Model\Activity::TYPE_CUSTOMER_REQUEST, \Crm\Model\Activity::TYPE_REGISTER_FOR_TRIAL]]);
    					$select->where(['createdDateTime >= ?' => $fromDateTime]);
    					$select->where(['createdDateTime >= ?' => $createdDateTime]);
    					//$select->where(['createdDateTime <= ?' => $toDateTime]);
    					$query = $dbSql->buildSqlString($select);
    					$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
    					if($rows->count()){
    						return $this->getJsonModel()->setVariables([
								'code' => 1,
    							'messages' => 'sended'
    						]);
    					}

    					//check thông tin có dc ai nhận sau thời điểm bắn về ko
    					$select = $dbSql->select(['a' => \Crm\Model\ActivityMapper::TABLE_NAME]);
    					$select->where(['leadId' => $lead->getId()]);
    					$select->where(['companyId' => $lead->getCompanyId()]);
    					$select->where(['type' => [\Crm\Model\Activity::TYPE_ASSIGN_LEAD, \Crm\Model\Activity::TYPE_SELF_ASSIGN_LEAD]]);
    					$select->where(['createdById != ?' => $relatedUserId]);
    					$select->where(['createdDateTime >= ?' => $fromDateTime]);
    					$select->where(['createdDateTime >= ?' => $createdDateTime]);
    					//$select->where(['createdDateTime <= ?' => $toDateTime]);
    					$select->order(['createdDateTime ASC']);
    					$select->limit(1);
    					$query = $dbSql->buildSqlString($select);
    					$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
    					if($rows->count()){
    						// nếu có, check tiếp có hành động nào sau đấy ko
    						$row = (array) $rows->current();
    						$select = $dbSql->select(['a' => \Crm\Model\ActivityMapper::TABLE_NAME]);
    						$select->where(['leadId' => $lead->getId()]);
    						$select->where(['companyId' => $lead->getCompanyId()]);
    						$select->where(new NotIn('type', ['\Crm\Model\Activity::TYPE_ASSIGN_LEAD, \Crm\Model\Activity::TYPE_SELF_ASSIGN_LEAD']));
    						$select->where(['createdDateTime > ?' => $row['createdDateTime']]);
    						$select->limit(1);
    						$query = $dbSql->buildSqlString($select);
    						$row2 = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
    						if(!$row2->count()){
    							// nếu chỉ nhận về mà ko có bất kì hành động j,
    							// Xóa các lead user dc tạo trong khoảng thời gian từ lúc bắn về đến hết thời điểm check
    							$delete = $dbSql->delete(\Crm\Model\Lead\UserMapper::TABLE_NAME);
    							$delete->where(['leadId' => $lead->getId()]);
    							$delete->where(['companyId' => $lead->getCompanyId()]);
    							$delete->where(['userId != ?' => $relatedUserId]);
    							$delete->where(['createdDateTime > ?' => $createdDateTime]);
    							$delete->where(['createdDateTime <= ?' => $toDateTime]);
    							$query = $dbSql->buildSqlString($delete);
    							$dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

    							// xóa các activities nhận chăm sóc
    							$delete = $dbSql->delete(\Crm\Model\ActivityMapper::TABLE_NAME);
    							$delete->where(['leadId' => $lead->getId()]);
    							$delete->where(['companyId' => $lead->getCompanyId()]);
    							$delete->where(['createdById != ?' => $relatedUserId]);
    							$delete->where(['type' => [\Crm\Model\Activity::TYPE_ASSIGN_LEAD, \Crm\Model\Activity::TYPE_SELF_ASSIGN_LEAD]]);
    							$delete->where(['createdDateTime > ?' => $createdDateTime]);
    							$delete->where(['createdDateTime <= ?' => $toDateTime]);
    							$query = $dbSql->buildSqlString($delete);
    							$dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

    						}
    					}
    				}
    				$isNew = false;
    				if($lead->getOption('tableExisted') == 'account'){
    					$accountId = $lead->getOption('accountId');
    				}
    			}

    			//Update nguồn cho leadCompany
    			$leadCompany = new \Crm\Model\Lead\Company();
    			$leadCompany->setCompanyId($lead->getCompanyId());
    			$leadCompany->setLeadId($lead->getId());
    			$leadCompany->setAccountId($accountId);
    			if($campaignId){
    				$leadCompany->setSource(\Crm\Model\Lead::SOURCE_MARKETING_CAMPAIGN);
    				$leadCompany->setSourceCampaignId($campaignId);
    				$leadCompany->setSourceReference($lead->getSourceReference());
    			} elseif ($isNew) {
    				$leadCompany->setSource(\Crm\Model\Lead::SOURCE_WEB);
    				if($lead->getSourceReference()){
    					$leadCompany->setSourceReference($lead->getSourceReference());
    				} else {
    					$leadCompany->setSourceReference('nhanh.vn');
    				}
    			}
    			$leadCompany->setStatus(\Crm\Model\Lead\Company::STATUS_FREE);
    			$leadCompanyMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\CompanyMapper');
    			//Nếu leadCompany đã tồn tại (thông tin đã được sử dụng với cty đang check) thì chỉ update lastActivityDateTime
    			$leadCompanyMapper->isExisted($leadCompany);
    			$leadCompany->setLastActivityDateTime(DateBase::getCurrentDateTime());
    			$leadCompanyMapper->save($leadCompany);

    			//Log hành động yêu cầu dùng thử của khách hàng
    			$activity = new \Crm\Model\Activity();
    			$activity->setLeadId($lead->getId());
    			$activity->setAccountId($accountId);
    			$activity->setCompanyId($lead->getCompanyId());
    			$activity->setType(\Crm\Model\Activity::TYPE_REGISTER_FOR_TRIAL);
    			$activity->setStatus(\Crm\Model\Activity::STATUS_SUCCESS);
    			//$activity->setTitle('Đăng kí dùng thử '.$formData['service']) ;
    			$activity->setTitle($formData['title']?:'Đăng kí dùng thử '.$formData['service']) ;
    			$activity->setContent($formData['note']?:null);
    			$activity->setCreatedById(1);
    			$activity->setCreatedDate(DateBase::toFormat($createdDateTime, DateBase::COMMON_DATE_FORMAT));
    			$activity->setCreatedDateTime($createdDateTime);
    			$activityMapper = $this->getServiceLocator()->get('\Crm\Model\ActivityMapper');
    			$activityMapper->save($activity);
    			$activityMapper->updateLeadId($activity);

    			//Check lại 1 lần nữa trạng thái của leadCompany là thả nổi hay đã có KD
    			$leadCompanyMapper->updateStatus($leadCompany);
    			//Nếu là thả nổi (tức là vừa dc tạo hoặc đang thả nổi) thì tạo 1 yêu cầu gọi cho chăm sóc mặc định và gắn cho cham sóc mặc định
    			if($leadCompany->getStatus() == \Crm\Model\Lead\Company::STATUS_FREE){
    				$activity = new \Crm\Model\Activity();
    				$activity->setLeadId($lead->getId());
    				$activity->setAccountId($accountId);
    				$activity->setCompanyId($lead->getCompanyId());
    				$activity->setType(\Crm\Model\Activity::TYPE_REQUEST_PHONECALL);
    				$activity->setStatus(\Crm\Model\Activity::STATUS_SUCCESS);
    				$activity->setTitle('Gọi xác nhận yêu cầu dùng thử');
    				$activity->setCreatedById(1);
    				$activity->setCreatedDate(DateBase::toFormat($createdDateTime, DateBase::COMMON_DATE_FORMAT));
    				$activity->setCreatedDateTime($createdDateTime);
    				$activity->setRelatedUserId($relatedUserId);
    				$activityMapper->save($activity);

    				$leadUser = new \Crm\Model\Lead\User();
    				$leadUser->setLeadId($lead->getId());
    				$leadUser->setAccountId($accountId);
    				$leadUser->setCompanyId($lead->getCompanyId());
    				$leadUser->setType(\Crm\Model\Lead\User::TYPE_SALE);
    				$leadUser->setUserId($relatedUserId);
    				$leadUser->setCreatedById(1);
    				$leadUser->setCreatedDateTime(DateBase::getCurrentDateTime());
    				$leadUserMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\UserMapper');
    				if(!$leadUserMapper->isExisted($leadUser)){
    					$leadUserMapper->save($leadUser);
    				}
    				$leadCompanyMapper->updateColumns(['status' => \Crm\Model\Lead\Company::STATUS_BELONG], $leadCompany);
    			}

    			return $this->getJsonModel()->setVariable('code', 1);
    		}
    		return $this->getJsonModel()->setVariables([
    				'code' => 0,
    				'messages' => [$formValidate->getMessages()],
    				]);
    	}
    	return $this->getJsonModel()->setVariables([
    			'code' => 0,
    			'messages' => ['']
    			]);

    }

}