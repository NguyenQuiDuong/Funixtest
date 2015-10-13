<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Controller;

use Home\Controller\ControllerBase;
use Home\Model\DateBase;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Db\Sql\Predicate\IsNull;
use Zend\Db\Sql\Predicate\Operator;

class AutoController extends ControllerBase{
	public function indexAction(){

	}


	/**
	 * Thả nổi các lead sau 1 số ngày nhất định mà ko có ghi nhận activity nào
	 * @return \Zend\View\Model\ViewModel
	 */
	public function freeleadAction(){
	    // tạm ko sử dụng nữa
	    echo 'Tạm không hỗ trợ nữa';
	    die;

	    set_time_limit(300);
	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
	    /* @var $dbAdapter \Zend\Db\Adapter\Adapter */
	    $dbSql = $this->getServiceLocator()->get('dbSql');
	    /* @var $dbSql \Zend\Db\Sql\Sql */
	    $select = $dbSql->select(array('lu' => \Crm\Model\Lead\UserMapper::TABLE_NAME));
	    $select->join(['lc'=> \Crm\Model\Lead\CompanyMapper::TABLE_NAME],
	        new Expression('lu.leadId = lc.leadId AND lu.companyId=lc.companyId'),
	        ['lastActivityDateTime', 'releaseDateTime', 'lc.id' => 'id'], $select::JOIN_LEFT);

	    $select->where(['lu.type' => \Crm\Model\Lead\User::TYPE_SALE]);
	    $select->where(['lc.lastActivityDateTime IS NOT NULL']);
        $select->where(['lc.accountId IS NULL']);
	    $date = new \DateTime();
	    $date->setTime('0', 0, 0);
	    $today = $date->format(DateBase::COMMON_DATETIME_FORMAT);
	    $date->sub(new \DateInterval('P'.\Crm\Model\Lead::MAXIMUM_KEEP_DAY. 'D'));

	    $lastActDate = $date->format(DateBase::COMMON_DATETIME_FORMAT);
	    $select->where(['lc.lastActivityDateTime <= ?' => $lastActDate]);

	    $predicateSet = new PredicateSet();
	    $predicateSet->addPredicate(new IsNull('lc.releaseDateTime'), $predicateSet::OP_OR);
	    $predicateSet->addPredicate(new Operator('lc.releaseDateTime', '<=', $today), $predicateSet::OP_OR);
        $select->where($predicateSet);

        $select->limit(300);
        $query = $dbSql->buildSqlString($select);
        echo $query;
        echo '<br/>';
        $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        $leadUserMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\UserMapper');
        $activityMapper = $this->getServiceLocator()->get('\Crm\Model\ActivityMapper');
        $leadCompanyMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\CompanyMapper');
        $lcIds = [];
        if($rows->count()) {
            foreach ($rows as $row){
                $row = (array) $row;
                $leadUser = new \Crm\Model\Lead\User($row);
                $leadUserMapper->delete($leadUser);

                $activity = new \Crm\Model\Activity();
                $activity->setType(\Crm\Model\Activity::TYPE_AUTO_FREE);
                $activity->setCompanyId($leadUser->getCompanyId());
                $activity->setLeadId($leadUser->getLeadId());
                $activity->setAccountId($leadUser->getAccountId());
                $activity->setTitle('Tự giải phóng sau '.\Crm\Model\Lead::MAXIMUM_KEEP_DAY. ' ngày');
                $activity->setRelatedUserId($leadUser->getUserId());
                $activity->setCreatedById(1);
                $activity->setCreatedDate(DateBase::getCurrentDate());
                $activity->setCreatedDateTime(DateBase::getCurrentDateTime());
                $activity->setStatus(\Crm\Model\Activity::STATUS_SUCCESS);

                $activityMapper->save($activity);
                $lcIds[$row['lc.id']] = $row['lc.id'];
            }
            if(count($lcIds)){
                $select = $dbSql->select(array('lc' => \Crm\Model\Lead\CompanyMapper::TABLE_NAME));
                $select->join(['lu' => \Crm\Model\Lead\UserMapper::TABLE_NAME],
                    new Expression('lu.leadId = lc.leadId AND lu.companyId=lc.companyId AND lu.type='.\Crm\Model\Lead\User::TYPE_SALE),
                    [], $select::JOIN_LEFT);
                $select->where(['lc.id' => $lcIds]);
                $select->where(['lu.id IS NULL']);
                $relatedLcIds = [];

                $query = $dbSql->buildSqlString($select);
                echo $query;
                $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                if($rows->count()){
                    $currentDatetime= DateBase::getCurrentDateTime();
                    foreach ($rows as $row){
                        $row = (array) $row;
                        $leadCompany = new \Crm\Model\Lead\Company($row);
                        $leadCompanyMapper->updateColumns(['lastActivityDateTime' => $currentDatetime], $leadCompany);
                    }
                }

            }


            $updateQuery = 'UPDATE
		    crm_leads_companies lc
		    LEFT JOIN crm_leads_users lu
		      ON lu.companyId=lc.companyId AND lu.leadId=lc.leadId AND lu.type='.\Crm\Model\Lead\User::TYPE_SALE.'
		    SET lc.status='.\Crm\Model\Lead\Company::STATUS_FREE.'
		    WHERE lc.accountId IS NULL AND lu.id IS NULL';

            $updateQuery2 = 'UPDATE crm_leads_companies lc
            INNER JOIN crm_leads_users lu ON lc.leadId=lu.leadId AND lc.companyId=lu.companyId AND lu.type= '.\Crm\Model\Lead\User::TYPE_SALE.
            ' SET
                lc.status = '.\Crm\Model\Lead\Company::STATUS_BELONG.
             ' WHERE
            lc.leadId IS NOT NULL AND lc.companyId IS NOT NULL';

            echo '<br/>';
            echo '<br/>';
            echo $updateQuery;
            $results = $dbAdapter->query($updateQuery, $dbAdapter::QUERY_MODE_EXECUTE);
            echo '<br/>';
            echo '<br/>';
            echo $updateQuery2;
            $results = $dbAdapter->query($updateQuery2, $dbAdapter::QUERY_MODE_EXECUTE);
            echo '<br/>';
            echo '<br/>';
            echo 'DONE!';

            \Home\Service\Uri::autoLink('/system/auto/freelead');
        }

        die;
	}

	/**
	 * Giải phóng các lead được tạo 3 ngày chưa có activities
	 */
	public function freelead2Action(){
	    //tạm ko dùng nữa
	    echo 'Tạm không dùng nữa';
	    die;
	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
	    /* @var $dbAdapter \Zend\Db\Adapter\Adapter */
	    $dbSql = $this->getServiceLocator()->get('dbSql');
	    /* @var $dbSql \Zend\Db\Sql\Sql */
	    $date = new \DateTime();
	    $date->setTime(0, 0, 0);
	    $date->sub(new \DateInterval('P'.\Crm\Model\Lead::MAXIMUM_CREATER_BEONG_DAY.'D'));
	    $date = $date->format(DateBase::COMMON_DATETIME_FORMAT);

	    $select = $dbSql->select(['lu' => \Crm\Model\Lead\UserMapper::TABLE_NAME]);
	    $select->join(['a' => \Crm\Model\ActivityMapper::TABLE_NAME],
	        new Expression('lu.companyId=a.companyId AND lu.leadId=a.leadId'), [], $select::JOIN_LEFT);
	    $select->where(['a.id IS NULL']);
	    $select->where(['lu.accountId IS NULL']);
	    $select->where(['lu.createdDateTime <= ?' => $date]);
	    $query = $dbSql->buildSqlString($select);
	    $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

	    $leadUserMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\UserMapper');
	    $activityMapper = $this->getServiceLocator()->get('\Crm\Model\ActivityMapper');
	    $leadCompanyMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\CompanyMapper');
	    $index = 0;
	    if($rows->count()){
	        foreach ($rows as $row){
	            $row = (array) $row;
	            $leadUser = new \Crm\Model\Lead\User($row);

	            $activity = new \Crm\Model\Activity();
	            $activity->setType(\Crm\Model\Activity::TYPE_AUTO_FREE);
	            $activity->setCompanyId($leadUser->getCompanyId());
	            $activity->setLeadId($leadUser->getLeadId());
	            $activity->setAccountId($leadUser->getAccountId());
	            $activity->setTitle('Tự giải phóng sau '.\Crm\Model\Lead::MAXIMUM_CREATER_BEONG_DAY. ' ngày tạo mà không có hoạt động');
	            $activity->setRelatedUserId($leadUser->getUserId());
	            $activity->setCreatedById(1);
	            $activity->setCreatedDate(DateBase::getCurrentDate());
	            $activity->setCreatedDateTime(DateBase::getCurrentDateTime());
	            $activity->setStatus(\Crm\Model\Activity::STATUS_SUCCESS);
	            $activityMapper->save($activity);

	            $leadUserMapper->delete($leadUser);

	            $leadCompany = new \Crm\Model\Lead\Company();
	            $leadCompany->setCompanyId($leadUser->getCompanyId());
	            $leadCompany->setLeadId($leadUser->getLeadId());
	            $leadCompanyMapper->updateStatus($leadCompany);
	            $index++;
	        }
	    }
	    echo $query;
	    echo '<br/>';
	    echo 'Đã giải phóng '.$index .' người dùng';
	    die;
	}

	/**
	 * Giải phóng các lead quá 3 ngày mà chưa hẹn gặp dc
	 */
	public function freelead3Action(){
	    //tạm ko dùng nữa
	    echo 'Tạm không dùng nữa';
	    die;

	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
	    /* @var $dbAdapter \Zend\Db\Adapter\Adapter */
	    $dbSql = $this->getServiceLocator()->get('dbSql');
	    /* @var $dbSql \Zend\Db\Sql\Sql */
	    $date = new \DateTime();
	    $date->setTime(0, 0, 0);
	    $date->sub(new \DateInterval('P'.\Crm\Model\Lead::MAXIMUM_NOT_MEETING_KEEP_DAY.'D'));
	    $date = $date->format(DateBase::COMMON_DATETIME_FORMAT);

	    $select = $dbSql->select(['lu' => \Crm\Model\Lead\UserMapper::TABLE_NAME]);
	    $select->join(['a' => \Crm\Model\ActivityMapper::TABLE_NAME],
	        new Expression('lu.companyId=a.companyId AND lu.leadId=a.leadId AND a.type='.\Crm\Model\Activity::TYPE_MEETING),
	        [], $select::JOIN_LEFT);
	    $select->where(['a.id IS NULL']);
	    $select->where(['lu.accountId IS NULL']);
	    $select->where(['lu.createdDateTime <= ?' => $date]);
	    $query = $dbSql->buildSqlString($select);
	    $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

	    $leadUserMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\UserMapper');
	    $activityMapper = $this->getServiceLocator()->get('\Crm\Model\ActivityMapper');
	    $leadCompanyMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\CompanyMapper');
	    if($rows->count()){
	        foreach ($rows as $row){
	            $row = (array) $row;
	            $leadUser = new \Crm\Model\Lead\User($row);

	            $activity = new \Crm\Model\Activity();
	            $activity->setType(\Crm\Model\Activity::TYPE_AUTO_FREE);
	            $activity->setCompanyId($leadUser->getCompanyId());
	            $activity->setLeadId($leadUser->getLeadId());
	            $activity->setAccountId($leadUser->getAccountId());
	            $activity->setTitle('Tự giải phóng sau '.\Crm\Model\Lead::MAXIMUM_NOT_MEETING_KEEP_DAY. ' ngày bàn giao mà không có được cuộc gặp');
	            $activity->setRelatedUserId($leadUser->getUserId());
	            $activity->setCreatedById(1);
	            $activity->setCreatedDate(DateBase::getCurrentDate());
	            $activity->setCreatedDateTime(DateBase::getCurrentDateTime());
	            $activity->setStatus(\Crm\Model\Activity::STATUS_SUCCESS);
	            $activityMapper->save($activity);

	            $leadUserMapper->delete($leadUser);

	            $leadCompany = new \Crm\Model\Lead\Company();
	            $leadCompany->setCompanyId($leadUser->getCompanyId());
	            $leadCompany->setLeadId($leadUser->getLeadId());
	            $leadCompanyMapper->updateStatus($leadCompany);
	        }
	    }
	    echo $query;die;
	}

	public function updateprojectcacheidsAction(){
		set_time_limit(300);
		$project = new \Work\Model\Project();
		$project->setStatus(\Work\Model\Project::STATUS_ACTIVE);
		$projectMapper = $this->getServiceLocator()->get('\Work\Model\ProjectMapper');

		$tree = new \Home\Model\Tree();
		$tree->updateAllParent($projectMapper->fetchAll($project), $projectMapper);
		$projectMapper->resetInactiveAllChildIds();
		echo 'Done!';
		die;
	}

	public function updatecompanycacheidsAction(){
		set_time_limit(300);
		$company = new \Company\Model\Company();
		$companyMapper = $this->getServiceLocator()->get('\Company\Model\CompanyMapper');

		$tree = new \Home\Model\Tree();
		$tree->updateAllParent($companyMapper->fetchAll($company), $companyMapper);

		echo 'Done!';
		die;
	}

	public function updateaccountproductAction(){
	    set_time_limit(300);
	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
	    /* @var $dbSql \Zend\Db\Sql\Sql */
	    $dbSql = $this->getServiceLocator()->get('dbSql');
	    $update = $dbSql->update(\Crm\Model\Account\ProductMapper::TABLE_NAME);
	    $update->set([
	       'status' => \Crm\Model\Account\Product::STATUS_EXPRIRED
	    ]);
	    $update->where(['expiredDate < ?' => DateBase::getCurrentDate()]);
	    $query = $dbSql->buildSqlString($update);
	    $result = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
	    echo 'done';
	    die;
	}

	public function correctcrmdataAction(){
	    set_time_limit(300);
	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
	    /* @var $dbSql \Zend\Db\Sql\Sql */
	    $dbSql = $this->getServiceLocator()->get('dbSql');

	    $update1 = 'UPDATE crm_leads_users as lu INNER JOIN employees AS e ON e.userId=lu.userId SET lu.companyId = e.companyId WHERE lu.companyId IS NULL AND e.companyId IS NOT NULL';
	    $update2 = 'UPDATE crm_leads_users AS lu INNER JOIN crm_leads_companies AS lc ON lu.companyId=lc.companyId AND lu.leadId=lc.leadId SET lu.accountId = lc.accountId WHERE lu.accountId IS NULL AND lc.accountId IS NOT NULL ';

	    $update3 = 'UPDATE crm_activities a INNER JOIN employees e ON e.userId=a.createdById SET a.companyId=e.companyId WHERE a.companyId IS NULL';
	    $update4 = 'UPDATE crm_activities a INNER JOIN crm_leads l ON a.leadId=l.id SET a.companyId=l.companyId WHERE a.companyId IS NULL';
        $update5 = 'UPDATE crm_activities a INNER JOIN crm_leads_companies lc ON a.companyId=lc.companyId AND a.leadId=lc.leadId SET a.accountId=lc.accountId WHERE lc.accountId IS NOT NULL AND a.accountId IS NULL';

        $update6 = 'UPDATE crm_leads_companies lc INNER JOIN crm_accounts a ON lc.accountId=a.id SET lc.leadId=a.leadId WHERE lc.leadId IS NULL AND a.leadId IS NOT NULL ';


        $update7 = 'UPDATE
		    crm_leads_companies lc
		    LEFT JOIN crm_leads_users lu
		      ON lu.companyId=lc.companyId AND lu.leadId=lc.leadId AND lu.type='.\Crm\Model\Lead\User::TYPE_SALE.'
		    SET lc.status='.\Crm\Model\Lead\Company::STATUS_FREE.'
		    WHERE lc.accountId IS NULL AND lu.id IS NULL';

        $update8 = 'UPDATE crm_leads_companies lc
            INNER JOIN crm_leads_users lu ON lc.leadId=lu.leadId AND lc.companyId=lu.companyId AND lu.type= '.\Crm\Model\Lead\User::TYPE_SALE.
                    ' SET
                lc.status = '.\Crm\Model\Lead\Company::STATUS_BELONG.
                        ' WHERE
            lc.leadId IS NOT NULL AND lc.companyId IS NOT NULL';

	    echo $update1;
	    echo '<br/>';
	    $result = $dbAdapter->query($update1, $dbAdapter::QUERY_MODE_EXECUTE);
	    echo $update2;
	    echo '<br/>';
	    $result = $dbAdapter->query($update2, $dbAdapter::QUERY_MODE_EXECUTE);
	    echo $update6;
	    echo '<br/>';
	    $result = $dbAdapter->query($update6, $dbAdapter::QUERY_MODE_EXECUTE);
	    echo '<br/>';
	    echo $update7;
	    $result = $dbAdapter->query($update7, $dbAdapter::QUERY_MODE_EXECUTE);
	    echo '<br/>';
	    echo $update8;
	    $result = $dbAdapter->query($update8, $dbAdapter::QUERY_MODE_EXECUTE);
	    die;
	}

	public function testAction(){
	    $curl = curl_init('https://erp.nhanh.vn/system/auto/freelead');
	    $result = curl_exec($curl);
	    echo $result;
	    curl_close($curl);
	    die;
	}
}