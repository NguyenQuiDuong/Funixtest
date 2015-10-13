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
use Home\Service\Uri;
use Work\Model\TaskMapper;
use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Db\Sql\Predicate\IsNull;
use Zend\Db\Sql\Predicate\In;
use Home\Model\Format;
use Zend\Db\Sql\Predicate\NotIn;
use Asset\Model\AssetItem;
use Zend\Db\Adapter\Adapter;
use Zend\Validator\Explode;
use Zend\Crypt\PublicKey\Rsa\PrivateKey;

class ToolController extends ControllerBase
{

    public function indexAction()
    {}

    /**
     * @author VanCK
     */
    public function resetopcacheAction()
    {
        echo opcache_reset();
    }

    public function updatelastactivityAction()
    {
        $companyId = $this->getRequest()->getQuery('companyId');
        $leadIds = $this->getRequest()->getQuery('leadIds');
        $totalLeads = $this->getRequest()->getQuery('totalLeads', 0);
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        /* @var $dbSql \Zend\Db\Sql\Sql */
        $dbSql = $this->getServiceLocator()->get('dbSql');

        $select = $dbSql->select(array(
            'a' => \Crm\Model\ActivityMapper::TABLE_NAME
        ));

        if ($companyId) {
            $select->where([
                'a.companyId' => $companyId
            ]);
        }
        if ($leadIds) {
            $select->where([
                'a.leadId' => explode(',', $leadIds)
            ]);
        }
        $select->where([
            'a.leadId IS NOT NULL'
        ]);
        $select->order([
            'a.leadId',
            'a.createdDateTime' => 'DESC'
        ]);
        $select->group([
            'a.leadId'
        ]);

        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);

        $paginator->setItemCountPerPage(200);
        $page = $this->getRequest()->getQuery('page', 1);
        $paginator->setCurrentPageNumber($page);

        foreach ($paginator as $row) {
            $row = (array) $row;
            $update = $dbSql->update(\Crm\Model\Lead\CompanyMapper::TABLE_NAME);
            $update->where([
                'id' => $row['leadId'],
                'companyId' => $row['companyId']
            ]);
            $update->set([
                'lastActivityDateTime' => $row['createdDateTime'] ?  : null
            ]);
            $query = $dbSql->buildSqlString($update);
            $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

            $totalLeads ++;
        }

        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalPages', $paginator->count());
        if ($page <= $paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri', '/system/tool/updatelastactivity?page=' . ($page + 1) . '&companyId=' . $companyId . '&leadIds=' . $leadIds . '&totalLeads=' . $totalLeads);
        }
        $this->getViewModel()->setVariable('defaultRedirectUri', '/system/tool/updatelastactivity');
        $this->getViewModel()->setVariable('totalLeads', $totalLeads);
        return $this->getViewModel();
    }

    public function getduplicateleadAction()
    {
        set_time_limit(300);
        $column = $this->getRequest()->getQuery('column');

        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $select = $dbSql->select([
            'l1' => \Crm\Model\LeadMapper::TABLE_NAME
        ]);
        switch ($column) {
            case 'mobile':
                $select->join([
                    'l2' => \Crm\Model\LeadMapper::TABLE_NAME
                ], 'l1.mobile=l2.mobile', []);
                break;
            case 'mobile2':
                $select->join([
                    'l2' => \Crm\Model\LeadMapper::TABLE_NAME
                ], 'l1.mobile=l2.mobile2', []);
                break;
            case 'phone':
                $select->join([
                    'l2' => \Crm\Model\LeadMapper::TABLE_NAME
                ], 'l1.mobile=l2.phone', []);
                break;
            case 'phone2':
                $select->join([
                    'l2' => \Crm\Model\LeadMapper::TABLE_NAME
                ], 'l1.mobile=l2.phone2', []);
                break;
            default:
                $select->join([
                    'l2' => \Crm\Model\LeadMapper::TABLE_NAME
                ], 'l1.mobile=l2.mobile', []);
                break;
        }

        $select->columns(array(
            'l1.id' => new Expression('l1.id'),
            'l2.id' => new Expression('l2.id'),
            'mobile' => new Expression('l1.mobile')
        ));
        $select->where([
            'l1.id != l2.id'
        ]);
        $select->where([
            'l1.mobile IS NOT NULL'
        ]);
        $select->where([
            'l1.companyId = l2.companyId'
        ]);
        $select->group(([
            'l1.mobile'
        ]));
        echo $select->getSqlString($dbAdapter->getPlatform());
        die();
        $query = $dbSql->buildSqlString($select);
        $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        if ($rows->count()) {
            $index = 1;
            foreach ($rows as $row) {
                $row = (array) $row;
                echo ($index ++) . ' - ' . $row['l1.id'] . ' - ' . $row['l2.id'] . ' - ' . $row['mobile'] . '<br/>';
            }
        }
        die();
    }

    public function deleteleadAction()
    {
        $id = $this->getRequest()->getQuery('id');
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        if (! $id) {
            echo 'no id';
            return;
        }
        $activity = new \Crm\Model\Activity();
        $activity->setLeadId($id);
        $activityMapper = $this->getServiceLocator()->get('\Crm\Model\ActivityMapper');
        $acts = $activityMapper->fetchAll($activity);
        $actsIds = [];
        if ($acts && count($acts)) {
            foreach ($acts as $activity) {
                $actsIds[$activity->getId()] = $activity->getId();
            }
            // delete meeting
            $deleteMeeting = $dbSql->delete(\Crm\Model\Activity\MeetingMapper::TABLE_NAME);
            $deleteMeeting->where([
                'activityId' => $actsIds
            ]);
            $query = $dbSql->buildSqlString($deleteMeeting);
            $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

            // delete phonecall
            $deletePhonecall = $dbSql->delete(\Crm\Model\Activity\PhonecallMapper::TABLE_NAME);
            $deletePhonecall->where([
                'activityId' => $actsIds
            ]);
            $query = $dbSql->buildSqlString($deletePhonecall);
            $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

            // delete reason
            $deleteReason = $dbSql->delete(\Crm\Model\Activity\ReasonMapper::TABLE_NAME);
            $deleteReason->where([
                'activityId' => $actsIds
            ]);
            $query = $dbSql->buildSqlString($deleteReason);
            $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

            // delete activity
            $deleteActivity = $dbSql->delete(\Crm\Model\ActivityMapper::TABLE_NAME);
            $deleteActivity->where([
                'id' => $actsIds
            ]);
            $query = $dbSql->buildSqlString($deleteActivity);
            $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
            echo 'Delete activity <br/>';
        }

        $deleteLead = $dbSql->delete(\Crm\Model\LeadMapper::TABLE_NAME);
        $deleteLead->where([
            'id' => $id
        ]);
        $query = $dbSql->buildSqlString($deleteLead);
        $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        echo 'Delete lead <br/>';
        die();
    }

    public function createleadsAction()
    {
        set_time_limit(300);
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $select = $dbSql->select([
            'a' => \Crm\Model\AccountMapper::TABLE_NAME
        ]);
        $select->join([
            'l' => \Crm\Model\LeadMapper::TABLE_NAME
        ], 'a.id=l.accountId', [], $select::JOIN_LEFT);
        $select->where([
            'l.id IS NULL'
        ]);
        $select->order([
            'a.id'
        ]);

        $query = $dbSql->buildSqlString($select);
        echo $query;
        echo '<br/>';
        $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        if ($rows->count()) {
            $currentDate = DateBase::getCurrentDate();
            $currentDateTime = DateBase::getCurrentDateTime();
            $userId = $this->user()->getIdentity();

            $leadMapper = $this->getServiceLocator()->get('\Crm\Model\LeadMapper');
            $result = [];
            $companyMapper = $this->getServiceLocator()->get('\Company\Model\CompanyMapper');
            foreach ($rows as $row) {
                $row = (array) $row;
                $lead = new \Crm\Model\Lead();
                $lead->exchangeArray($row);
                $lead->setId(null);


                $companyIdsInGroup = $companyMapper->getAllCompanyIdsInGroup(1);
                $lead->addOption('companyIds', $companyIdsInGroup);
                if (! $leadMapper->isExisted($lead)) {
                    $lead->setCreatedById($userId);
                    $lead->setCreatedDate($currentDate);
                    $lead->setCreatedDateTime($currentDateTime);

                    $lead->setAccountId($row['id']);
                    $lead->setIsConverted(1);
                    $leadMapper->save($lead);
                } elseif (!$lead->getAccountId()){
                    $lead->setAccountId($row['id']);
                    $leadMapper->save($lead);
                }

                $leadCompany = new \Crm\Model\Lead\Company();
                $leadCompany->setCompanyId($lead->getCompanyId());
                $leadCompany->setLeadId($lead->getId());
                $leadCompanyMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\CompanyMapper');
                $leadCompanyMapper->isExisted($leadCompany);
                $leadCompany->setAccountId($lead->getAccountId());
                $leadCompany->setOpportunityId($lead->getOpportunityId());
                $leadCompany->setLastActivityDateTime($row['createdDateTime']);
                $leadCompanyMapper->save($leadCompany);

                $result[$row['id']] = $lead->getId();
            }
            $this->getViewModel()->setVariable('result', $result);
        }

        return $this->getViewModel();
    }

    /**
     * Trình tự: tìm các account ko có leadCompany -> xem nếu có lead gắn với account đó thì tạo
     * lead company ko thôi, nếu ko có thì tạo lead rồi gắn với lead company
     */
    public function createleadcompanyAction(){
        set_time_limit(300);
        $page = $this->getRequest()->getQuery('page', 1);

        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $select = $dbSql->select(array('a' => \Crm\Model\AccountMapper::TABLE_NAME));
        $select->join(['lc' => \Crm\Model\Lead\CompanyMapper::TABLE_NAME], 'lc.accountId=a.id', [], $select::JOIN_LEFT);
        $select->where(['lc.accountId IS NULL']);
        $query = $dbSql->buildSqlString($select);
        echo $query;
        echo '<br/>';
        $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        echo 'Tìm thấy: '.$rows->count().' result <br/>';
        $leadCompanyMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\CompanyMapper');
        $leadMapper = $this->getServiceLocator()->get('\Crm\Model\LeadMapper');
        if($rows->count()){
            foreach ($rows as $row){
                $row =  (array) $row;
                $account = new \Crm\Model\Account();
                $account->exchangeArray($row);
                if($account->getLeadId()){
                    $leadCompany = new \Crm\Model\Lead\Company();

                    $leadCompany->setLeadId($account->getLeadId());
                    $leadCompany->setCompanyId($account->getCompanyId());
                    if($leadCompanyMapper->isExisted($leadCompany)){
                        echo 'update lead_company.id '.$leadCompany->getId().'<br/>';
                        $leadCompany->setAccountId($account->getId());
                    } else {
                        echo 'insert lead_company.id '.$leadCompany->getId().'<br/>';
                        $leadCompany->setAccountId($account->getId());
                        $leadCompany->setLastActivityDateTime($account->getCreatedDateTime());
                    }
                    $leadCompanyMapper->save($leadCompany);
                } else {
                    $select = $dbSql->select(array('l' => \Crm\Model\LeadMapper::TABLE_NAME));
                    $select->where(['accountId' => $account->getId()]);
                    $select->limit(1);
                    $query = $dbSql->buildSqlString($select);
                    $rows2 = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                    if($rows2->count()){
                        $row2 = (array) $rows2->current();
                        $leadCompany = new \Crm\Model\Lead\Company();

                        $leadCompany->setLeadId($row2['id']);
                        $leadCompany->setCompanyId($account->getCompanyId());
                        if($leadCompanyMapper->isExisted($leadCompany)){
                            echo 'update lead_company.id '.$leadCompany->getId().'<br/>';
                            $leadCompany->setAccountId($account->getId());
                        } else {
                            $leadCompany->setAccountId($account->getId());
                            $leadCompany->setLastActivityDateTime($account->getCreatedDateTime());
                            echo 'insert lead_company.id '.$leadCompany->getId().'<br/>';
                        }
                        $leadCompanyMapper->save($leadCompany);
                    } else {
                        $leadId = null;
                        if($account->getMobile()){
                            $select = $dbSql->select(array('l' => \Crm\Model\LeadMapper::TABLE_NAME));
                            $predicate = new \Zend\Db\Sql\Predicate\PredicateSet();
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('mobile', '=',
                                $account->getMobile(), $predicate::OP_OR));
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('mobile2', '=',
                                $account->getMobile(), $predicate::OP_OR));
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('phone', '=',
                                $account->getMobile(), $predicate::OP_OR));
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('phone2', '=',
                                $account->getMobile(), $predicate::OP_OR));
                            $select->where($predicate);
                            $select->where(['companyId' => $this->company()->getCompanyIdsInGroup()]);
                            $select->limit(1);
                            $query = $dbSql->buildSqlString($select);
                            $rowLead = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                            if($rowLead->count()){
                                $rowLead = (array) $rowLead->current();
                                $leadId = $rowLead['id'];
                            }
                        }
                        if(!$leadId && $account->getMobile2()){
                            $select = $dbSql->select(array('l' => \Crm\Model\LeadMapper::TABLE_NAME));
                            $predicate = new \Zend\Db\Sql\Predicate\PredicateSet();
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('mobile', '=',
                                $account->getMobile2(), $predicate::OP_OR));
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('mobile2', '=',
                                $account->getMobile2(), $predicate::OP_OR));
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('phone', '=',
                                $account->getMobile2(), $predicate::OP_OR));
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('phone2', '=',
                                $account->getMobile2(), $predicate::OP_OR));
                            $select->where($predicate);
                            $select->where(['companyId' => $this->company()->getCompanyIdsInGroup()]);
                            $select->limit(1);
                            $query = $dbSql->buildSqlString($select);
                            $rowLead = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                            if($rowLead->count()){
                                $rowLead = (array) $rowLead->current();
                                $leadId = $rowLead['id'];
                            }
                        }
                        if(!$leadId && $account->getPhone()){
                            $select = $dbSql->select(array('l' => \Crm\Model\LeadMapper::TABLE_NAME));
                            $predicate = new \Zend\Db\Sql\Predicate\PredicateSet();
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('mobile', '=',
                                $account->getPhone(), $predicate::OP_OR));
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('mobile2', '=',
                                $account->getPhone(), $predicate::OP_OR));
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('phone', '=',
                                $account->getPhone(), $predicate::OP_OR));
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('phone2', '=',
                                $account->getPhone(), $predicate::OP_OR));
                            $select->where($predicate);
                            $select->where(['companyId' => $this->company()->getCompanyIdsInGroup()]);
                            $select->limit(1);
                            $query = $dbSql->buildSqlString($select);
                            $rowLead = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                            if($rowLead->count()){
                                $rowLead = (array) $rowLead->current();
                                $leadId = $rowLead['id'];
                            }
                        }
                        if(!$leadId && $account->getPhone2()){
                            $select = $dbSql->select(array('l' => \Crm\Model\LeadMapper::TABLE_NAME));
                            $predicate = new \Zend\Db\Sql\Predicate\PredicateSet();
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('mobile', '=',
                                $account->getPhone(), $predicate::OP_OR));
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('mobile2', '=',
                                $account->getPhone(), $predicate::OP_OR));
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('phone', '=',
                                $account->getPhone(), $predicate::OP_OR));
                            $predicate->addPredicate(new \Zend\Db\Sql\Predicate\Operator('phone2', '=',
                                $account->getPhone(), $predicate::OP_OR));
                            $select->where($predicate);
                            $select->where(['companyId' => $this->company()->getCompanyIdsInGroup()]);
                            $select->limit(1);
                            $query = $dbSql->buildSqlString($select);
                            $rowLead = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                            if($rowLead->count()){
                                $rowLead = (array) $rowLead->current();
                                $leadId = $rowLead['id'];
                            }
                        }
                        if(!$leadId && $account->getEmail()){
                            $select = $dbSql->select(array('l' => \Crm\Model\LeadMapper::TABLE_NAME));
                            $select->where(['email' => $account->getEmail()]);
                            $select->where(['companyId' => $this->company()->getCompanyIdsInGroup()]);
                            $select->limit(1);
                            $query = $dbSql->buildSqlString($select);
                            $rowLead = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                            if($rowLead->count()){
                                $rowLead = (array) $rowLead->current();
                                $leadId = $rowLead['id'];
                            }
                        }
                        if(!$leadId && $account->getWebsite()){
                            $select = $dbSql->select(array('l' => \Crm\Model\LeadMapper::TABLE_NAME));
                            $select->where(['website' => $account->getWebsite()]);
                            $select->where(['companyId' => $this->company()->getCompanyIdsInGroup()]);
                            $select->limit(1);
                            $query = $dbSql->buildSqlString($select);
                            $rowLead = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                            if($rowLead->count()){
                                $rowLead = (array) $rowLead->current();
                                $leadId = $rowLead['id'];
                            }
                        }
                        if(!$leadId){
                            $lead = new \Crm\Model\Lead();
                            $lead->setMobile($account->getMobile());
                            $lead->setPhone($account->getPhone());
                            $lead->setMobile2($account->getMobile2());
                            $lead->setPhone2($account->getPhone2());
                            $lead->setEmail($account->getEmail());
                            $lead->setWebsite($account->getWebsite());
                            $lead->setAddress($account->getAddress());
                            $lead->setCityId($account->getCityId());
                            $lead->setDistrictId($account->getDistrictId());
                            $lead->setCompanyId($account->getCompanyId());
                            $lead->setCreatedById($this->user()->getIdentity());
                            $lead->setCreatedDate(DateBase::getCurrentDate());
                            $lead->setCreatedDateTime(DateBase::getCurrentDateTime());
                            $leadMapper->save($lead);
                            echo 'Tạo mới lead '.$lead->getId().'<br/>';

                            $leadCompany = new \Crm\Model\Lead\Company();
                            $leadCompany->setLeadId($lead->getId());
                            $leadCompany->setCompanyId($account->getCompanyId());
                            $leadCompany->setAccountId($account->getId());
                            $leadCompany->setLastActivityDateTime($account->getCreatedDateTime());
                            $leadCompanyMapper->save($leadCompany);
                            echo 'insert lead_company.id '.$leadCompany->getId().'<br/>';
                        } else {
                            $leadCompany = new \Crm\Model\Lead\Company();
                            $leadCompany->setLeadId($leadId);
                            $leadCompany->setCompanyId($account->getCompanyId());
                            $leadCompanyMapper->isExisted($leadCompany);
                            $leadCompany->setAccountId($account->getId());
                            $leadCompany->setLastActivityDateTime($leadCompany->getLastActivityDateTime()?:
                                $account->getCreatedDateTime());
                            $leadCompanyMapper->save($leadCompany);
                            echo 'update lead_company.id '.$leadCompany->getId().'<br/>';
                        }


                    }
                }

            }
        }
        die;
    }

    public function revertleadassigntoidAction()
    {
        $page = $this->getRequest()->getQuery('page', 1);
        $currentItem = $this->getRequest()->getQuery('currentItem', 0);

        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $select = $dbSql->select(array(
            'l' => \Crm\Model\LeadMapper::TABLE_NAME
        ));
        $select->where([
            'assignedToId IS NOT NULL'
        ]);
        $select->order([
            'id ASC'
        ]);

        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);

        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber($page);

        $leadUserMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\UserMapper');
        $leadMapper = $this->getServiceLocator()->get('\Crm\Model\LeadMapper');
        $currentDateTime = DateBase::getCurrentDateTime();
        foreach ($paginator as $row) {
            $row = (array) $row;
            $leadUser = new \Crm\Model\Lead\User();
            $leadUser->setLeadId($row['id']);
            $leadUser->setType(\Crm\Model\Lead\User::TYPE_SALE);
            $leadUser->setUserId($row['assignedToId']);
            $leadUser->setCreatedById($this->user()
                ->getIdentity());
            $leadUser->setCreatedDateTime($currentDateTime);
            if (! $leadUserMapper->isExisted($leadUser)) {
                $leadUserMapper->save($leadUser);
                $currentItem ++;

                $lead = new \Crm\Model\Lead();
                $lead->exchangeArray($row);
                $lead->setStatus(\Crm\Model\Lead::STATUS_ASSIGNTED);
                $leadMapper->save($lead);
            }
        }
        if ($paginator->getCurrentPageNumber() >= ($paginator->getTotalItemCount() / $paginator->getItemCountPerPage())) {
            $this->getViewModel()->setVariable('defaultRedirectUri', '/system/tool/revertleadassigntoid');
            $this->getViewModel()->setVariable('page', $currentItem);
            $this->getViewModel()->setVariable('totalPages', $paginator->getTotalItemCount());
            return $this->getViewModel();
        }

        $this->getViewModel()->setVariable('redirectUri', '/system/tool/revertleadassigntoid?page=' . ($page + 1) . '&currentItem=' . $currentItem);
        $this->getViewModel()->setVariable('defaultRedirectUri', '/system/tool/revertleadassigntoid');
        $this->getViewModel()->setVariable('page', $currentItem);
        $this->getViewModel()->setVariable('totalPages', $paginator->getTotalItemCount());
        return $this->getViewModel();
    }

    public function revertsupportleadAction()
    {
        $page = $this->getRequest()->getQuery('page', 1);
        $currentItem = $this->getRequest()->getQuery('currentItem', 0);

        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $select = $dbSql->select(array(
            'a' => \Crm\Model\ActivityMapper::TABLE_NAME
        ));
        $select->join([
            'ca' => \Calendar\Model\Calendar\AttendanceMapper::TABLE_NAME
        ], 'ca.calendarId=a.calendarId', [
            'userId' => 'userId'
        ]);
        $select->columns([
            'leadId' => 'leadId'
        ]);
        $select->where([
            '(a.type = ? OR a.result = ?)' => [
                \Crm\Model\Activity::TYPE_REQUEST_PHONECALL,
                \Crm\Model\Activity::RESULT_PHONECALL
            ]
        ]);

        $select->where([
            'leadId IS NOT NULL'
        ]);
        $select->order([
            'a.id ASC',
            'ca.userId ASC'
        ]);
        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);

        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber($page);
        $leadUserMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\UserMapper');
        foreach ($paginator as $row) {
            $row = (array) $row;
            $leadUser = new \Crm\Model\Lead\User();
            $leadUser->setLeadId($row['leadId']);
            $leadUser->setUserId($row['userId']);

            $leadUser->setType(\Crm\Model\Lead\User::TYPE_TELESALE);
            if (! $leadUserMapper->isExisted($leadUser)) {
                $leadUser->setCreatedById($this->user()
                    ->getIdentity());
                $leadUser->setCreatedDateTime(DateBase::getCurrentDateTime());

                $leadUserMapper->save($leadUser);
                $currentItem ++;
            }
        }
        if ($paginator->getCurrentPageNumber() >= ($paginator->getTotalItemCount() / $paginator->getItemCountPerPage())) {
            $this->getViewModel()->setVariable('defaultRedirectUri', '/system/tool/revertsupportlead');
            $this->getViewModel()->setVariable('page', $currentItem);
            $this->getViewModel()->setVariable('totalPages', $paginator->getTotalItemCount());
            return $this->getViewModel();
        }

        $this->getViewModel()->setVariable('redirectUri', '/system/tool/revertsupportlead?page=' . ($page + 1) . '&currentItem=' . $currentItem);
        $this->getViewModel()->setVariable('defaultRedirectUri', '/system/tool/revertsupportlead');
        $this->getViewModel()->setVariable('page', $currentItem);
        $this->getViewModel()->setVariable('totalPages', $paginator->getTotalItemCount());
        return $this->getViewModel();
    }

    /**
     *
     * @author DuongNQ
     */
    public function filesizeAction()
    {
        $taskfile = new \Work\Model\TaskFile();
        $path = Uri::getSavePath($taskfile);
        $taskfileMapper = $this->getServiceLocator()->get('Work\Model\TaskFileMapper');
        $taskFiles = $taskfileMapper->fetchAll(null, 'filesize');
        if (count($taskFiles)) {
            foreach ($taskFiles as $tf) {
                $createdFolder = DateBase::createFromFormat(DateBase::COMMON_DATETIME_FORMAT, $tf->getCreatedDateTime())->format('Ymd');
                $pathSave = Uri::getSavePath($tf) . '/' . $tf->getFileName();
                if (file_exists($pathSave)) {
                    $filesize = filesize($pathSave);
                    $tf->setFileSize($filesize);
                    try {
                        $taskfileMapper->save($tf);
                    } catch (\Exception $er) {
                        echo $er;
                        exit();
                    }
                }
            }
            vdump('Đã xong task file');
        }
        $meetingfile = new \Work\Model\MeetingFile();
        $path = Uri::getSavePath($meetingfile);
        $meetingfileMapper = $this->getServiceLocator()->get('Work\Model\MeetingFileMapper');
        $meetingFiles = $meetingfileMapper->fetchAll($meetingfile, 'filesize');
        if (count($meetingFiles)) {
            foreach ($meetingFiles as $mf) {
                $createdFolder = DateBase::createFromFormat(DateBase::COMMON_DATETIME_FORMAT, $mf->getCreatedDateTime())->format('Ymd');
                $oldname = $path . '/' . $createdFolder . '/' . $mf->getFileName();
                if (! file_exists($oldname)) {
                    $oldname = $path . '/' . $mf->getMeetingId() . '/' . $mf->getFileName();
                }
                vdump($oldname);
                $newname = $path . '/' . $createdFolder . '/' . $mf->getMeetingId();
                if (! file_exists($newname)) {
                    $oldmask = umask(0);
                    mkdir($newname, 0777, true);
                    umask($oldmask);
                }
                $newname = $path . '/' . $createdFolder . '/' . $mf->getMeetingId() . '/' . $mf->getFileName();
                if (! file_exists($newname)) {
                    vdump($newname);
                    rename($oldname, $newname);
                }
                $pathSave = $path . '/' . $createdFolder . '/' . $mf->getMeetingId() . '/' . $mf->getFileName();
                if (file_exists($pathSave)) {
                    $filesize = filesize($pathSave);
                    $mf->setFileSize($filesize);
                    try {
                        $meetingfileMapper->save($mf);
                    } catch (\Exception $er) {
                        echo $er;
                        exit();
                    }
                }
            }
            vdump('Đã xong meetingfile');
        }
        $docfile = new \Document\Model\DocumentFile();
        $path = Uri::getSavePath($docfile);
        $docfileMapper = $this->getServiceLocator()->get('Document\Model\DocumentFileMapper');
        $docFiles = $docfileMapper->fetchAll($docfile, 'filesize');
        if (count($docFiles)) {
            foreach ($docFiles as $df) {
                $createdFolder = DateBase::createFromFormat(DateBase::COMMON_DATETIME_FORMAT, $df->getCreatedDateTime())->format('Ymd');
                $pathSave = $path . '/' . $createdFolder . '/' . $df->getDocumentId() . '/' . $df->getFileName();
                if (file_exists($pathSave)) {
                    $filesize = filesize($pathSave);
                    $df->setFileSize($filesize);
                    vdump($df);
                    try {
                        $docfileMapper->save($df);
                    } catch (\Exception $er) {
                        echo $er;
                        exit();
                    }
                }
            }
            vdump('Đã xong document file');
        }
        $confile = new \Crm\Model\Contract\File();
        $path = BASE_PATH . '/public/media/contracts';
        $confileMapper = $this->getServiceLocator()->get('Crm\Model\Contract\FileMapper');
        $conFiles = $confileMapper->fetchAll($confile, 'filesize');
        if (count($conFiles)) {
            foreach ($conFiles as $cf) {
                $pathSave = $path . '/' . $cf->getContractId() . '/' . $cf->getFileName();
                if (file_exists($pathSave)) {
                    $filesize = filesize($pathSave);
                    $cf->setFileSize($filesize);
                    vdump($cf);
                    try {
                        $confileMapper->save($cf);
                    } catch (\Exception $er) {
                        echo $er;
                        exit();
                    }
                }
            }
            vdump('Đã xong contract file');
        }

        die('done');
    }

    /**
     *
     * @author KienNN
     */
    public function updatecontractvaluepaidAction()
    {
        $companyId = $this->getRequest()->getQuery('companyId');
        $ids = $this->getRequest()->getQuery('ids');
        $page = $this->getRequest()->getQuery('page');
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $select = $dbSql->select(array(
            'c' => \Crm\Model\ContractMapper::TABLE_NAME
        ));

        if ($companyId) {
            $select->where([
                'companyId' => $companyId
            ]);
        }
        if ($ids) {
            $select->where([
                'id' => explode(',', $ids)
            ]);
        }
        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);

        $paginator->setItemCountPerPage(100);
        $page = $this->getRequest()->getQuery('page', 1);
        $paginator->setCurrentPageNumber($page);
        $contractMapper = $this->getServiceLocator()->get('\Crm\Model\ContractMapper');
        foreach ($paginator as $row) {
            $row = (array) $row;
            $contract = new \Crm\Model\Contract();
            $contract->setId($row['id']);
            $contractMapper->updatePaid2($contract);
            $contractMapper->updateValue($contract);
            unset($contract);
        }

        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalPages', $paginator->count());
        if ($page <= $paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri', '/system/tool/updatecontractvaluepaid?page=' . ($page + 1) . '&companyId=' . $companyId . '&ids=' . $ids);
        }
        $this->getViewModel()->setVariable('defaultRedirectUri', '/system/tool/updatecontractvaluepaid');
        return $this->getViewModel();
    }

    public function updateaccountvaluepaidAction(){
        $companyId = $this->getRequest()->getQuery('companyId');
        $ids = $this->getRequest()->getQuery('ids');
        $page = $this->getRequest()->getQuery('page');

        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $select = $dbSql->select(array(
            'a' => \Crm\Model\AccountMapper::TABLE_NAME
        ));
        if ($companyId) {
            $select->where([
                'companyId' => $companyId
                ]);
        }
        if ($ids) {
            $select->where([
                'id' => explode(',', $ids)
                ]);
        }
        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);

        $paginator->setItemCountPerPage(100);
        $page = $this->getRequest()->getQuery('page', 1);
        $paginator->setCurrentPageNumber($page);
        $accountMapper = $this->getServiceLocator()->get('\Crm\Model\AccountMapper');
        foreach ($paginator as $row) {
            $row = (array) $row;
            $account = new \Crm\Model\Account();
            $account->setId($row['id']);
            $accountMapper->updateContractPaid($account);
            $accountMapper->updateContractValue($account);
        }
        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalPages', $paginator->count());
        if ($page <= $paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri', '/system/tool/updateaccountvaluepaid?page=' . ($page + 1) . '&companyId=' . $companyId . '&ids=' . $ids);
        }
        $this->getViewModel()->setVariable('defaultRedirectUri', '/system/tool/updateaccountvaluepaid');
        return $this->getViewModel();
    }

    public function updatecontractapprovedatetimeAction()
    {
        $ids = $this->getRequest()->getQuery('ids');
        $page = $this->getRequest()->getQuery('page');
        $totalUpdated = $this->getRequest()->getQuery('totalUpdated') ?  : 0;
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $select = $dbSql->select(array(
            'c' => \Crm\Model\ContractMapper::TABLE_NAME
        ));
        $select->where([
            'DATE(c.createdDateTime) <= ?' => '2014-11-30'
        ]);
        // $select->where(['approvedDateTime IS NULL']);
        $select->where([
            'approvedById IS NULL'
        ]);

        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);

        $paginator->setItemCountPerPage(100);
        $page = $this->getRequest()->getQuery('page', 1);
        $paginator->setCurrentPageNumber($page);

        $contractMapper = $this->getServiceLocator()->get('\Crm\Model\ContractMapper');
        foreach ($paginator as $pageRow) {
            $pageRow = (array) $pageRow;
            $contract = new \Crm\Model\Contract();
            $contract->exchangeArray($pageRow);
            $select = $dbSql->select(array(
                'c' => \Crm\Model\Contract\PaymentMapper::TABLE_NAME
            ));
            $select->where([
                'contractId' => $pageRow['id']
            ]);
            $select->order([
                'createdDateTime ASC'
            ]);
            $select->limit(1);
            $query = $dbSql->buildSqlString($select);
            $row = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
            if ($row->count()) {
                $row = (array) $row->current();

                $contractMapper->updateColumns([
                    'approvedDateTime' => $row['createdDateTime']
                ], $contract);
                echo 'update ' . $contract->getId() . ' => ' . $row['createdDateTime'] . '<br/>';
                $totalUpdated ++;
            }
        }
        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalUpdated', $totalUpdated);
        $this->getViewModel()->setVariable('totalPages', $paginator->count());
        if ($page <= $paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri', '/system/tool/updatecontractapprovedatetime?page=' . ($page + 1) . '&totalUpdated=' . $totalUpdated . '&ids=' . $ids);
        }
        $this->getViewModel()->setVariable('defaultRedirectUri', '/system/tool/updatecontractapprovedatetime');
        return $this->getViewModel();
    }

    public function cloneleadcompanyAction()
    {
        set_time_limit(300);
        $page = $this->getRequest()->getQuery('page');

        $totalInserted = $this->getRequest()->getQuery('totalInserted') ?  : 0;
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $select = $dbSql->select(array(
            'l' => \Crm\Model\LeadMapper::TABLE_NAME
        ));
        $select->join([
            'lc' => \Crm\Model\Lead\CompanyMapper::TABLE_NAME
        ], 'l.id=lc.leadId', [], $select::JOIN_LEFT);
        $select->where([
            'lc.id IS NULL'
        ]);
        $select->order([
            'l.id ASC'
        ]);
        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);

        $paginator->setItemCountPerPage(200);
        // $page = $this->getRequest()->getQuery('page', 1);
        $paginator->setCurrentPageNumber(1);

        $leadCompanyMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\CompanyMapper');
        foreach ($paginator as $pageRow) {
            $pageRow = (array) $pageRow;
            $leadCompany = new \Crm\Model\Lead\Company();
            $leadCompany->setCompanyId($pageRow['companyId']);
            $leadCompany->setLeadId($pageRow['id']);

            $leadCompanyMapper->isExisted($leadCompany);
            $leadCompany->setAccountId($pageRow['accountId']);
            $leadCompany->setOpportunityId($pageRow['opportunityId']);
            $leadCompany->setLastActivityDateTime($pageRow['lastActivityDateTime']);
            $leadCompany->setSource($pageRow['source']);
            $leadCompany->setSourceReference($pageRow['sourceReference']);

            $leadCompanyMapper->save($leadCompany);
            $totalInserted ++;
        }
        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalInserted', $totalInserted);
        $this->getViewModel()->setVariable('totalPages', $paginator->getTotalItemCount());
        if ($paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri', '/system/tool/cloneleadcompany?page=' . ($page + 1) . '&totalInserted=' . $totalInserted);
        }
        $this->getViewModel()->setVariable('defaultRedirectUri', '/system/tool/cloneleadcompany');
        return $this->getViewModel();
    }
    /*
     * $author DuongNq add thêm history của hành động thêm task và up file từ trước đến giờ
     */
    public function addnewtaskthAction()
    {
        die('Tính năng đã chạy rồi, ko chạy lại');

        $totalTasks = $this->params()->fromQuery('totalTask', 0);
        /* @var $dbSql \Zend\Db\Sql\Sql */
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $select = $dbSql->select(array(
            't' => TaskMapper::TABLE_NAME
        ));
        $select->columns(array(
            'id',
            'createdDate',
            'createdDateTime',
            'createdById'
        ));
        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage(50);
        $page = $this->getRequest()->getQuery('page', 1);
        $paginator->setCurrentPageNumber($page);
        $taskHistory = new \Work\Model\TaskHistory();
        $taskhistoryMapper = new \Work\Model\TaskHistoryMapper();

        foreach ($paginator as $task) {
            $task = (array) $task;
            $insert = $dbSql->insert(\Work\Model\TaskHistoryMapper::TABLE_NAME);
            $insert->values([
                'taskId' => $task['id'],
                'field' => \Work\Model\TaskHistory::USER_NEW,
                'oldData' => null,
                'newData' => null,
                'createdById' => $task['createdById'],
                'createdDate' => $task['createdDate'],
                'createdDateTime' => $task['createdDateTime']
            ]);
            $query = $dbSql->buildSqlString($insert);
            $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
            $totalTasks ++;
        }
        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
        if ($page <= $paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri', '/system/tool/addnewtaskth?page=' . ($page + 1) . '&totalTask=' . $totalTasks);
        }
        $this->getViewModel()->setVariable('totalTasks', $totalTasks);
        return $this->getViewModel();
    }

    public function adduploadfilethAction()
    {
        die('Tính năng đã chạy rồi, ko chạy lại');

        $totalFiles = $this->params()->fromQuery('totalFiles', 0);
        /* @var $dbSql \Zend\Db\Sql\Sql */
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $select = $dbSql->select(array(
            't' => \Work\Model\TaskFileMapper::TABLE_NAME
        ));
        $select->columns(array(
            'taskId',
            'fileName',
            'createdDateTime',
            'createdById'
        ));
        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage(50);
        $page = $this->getRequest()->getQuery('page', 1);
        $paginator->setCurrentPageNumber($page);
        $taskhistoryMapper = new \Work\Model\TaskHistoryMapper();
        foreach ($paginator as $taskfile) {
            $taskfile = (array) $taskfile;
            $insert = $dbSql->insert(\Work\Model\TaskHistoryMapper::TABLE_NAME);
            $insert->values([
                'taskId' => $taskfile['taskId'],
                'field' => \Work\Model\TaskHistory::USER_UPFILE,
                'oldData' => null,
                'newData' => json_encode('File: ' . $taskfile['fileName']) ?  : null,
                'createdById' => $taskfile['createdById'],
                'createdDate' => DateBase::toFormat($taskfile['createdDateTime'], DateBase::COMMON_DATE_FORMAT, DateBase::COMMON_DATETIME_FORMAT),
                'createdDateTime' => $taskfile['createdDateTime']
            ]);
            $query = $dbSql->buildSqlString($insert);
            $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
            $totalFiles ++;
        }

        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
        if ($page <= $paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri', '/system/tool/adduploadfileth?page=' . ($page + 1) . '&totalFiles=' . $totalFiles);
        }
        $this->getViewModel()->setVariable('totalFiles', $totalFiles);
        return $this->getViewModel();
    }

    public function mergerleadAction(){
        $digitFilter = new \Zend\Filter\Digits();
        $fromId = $digitFilter->filter($this->getRequest()->getQuery('fromId'));
        $toId = $digitFilter->filter($this->getRequest()->getQuery('toId'));
        if(!$fromId || !$toId){
            echo 'du lieu khong hop le';
            die;
        }
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $delete = $dbSql->delete(\Crm\Model\Lead\CompanyMapper::TABLE_NAME);
        $delete->where(['leadId' => $fromId]);
        $query = $dbSql->buildSqlString($delete);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        echo $query;
        echo '<br/>====================</br/>';

        $delete = $dbSql->delete(\Crm\Model\Lead\UserMapper::TABLE_NAME);
        $delete->where(['leadId' => $fromId]);
        $query = $dbSql->buildSqlString($delete);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        echo $query;
        echo '<br/>====================</br/>';

        $update = $dbSql->update(\Crm\Model\ActivityMapper::TABLE_NAME);
        $update->set(['leadId' => $toId]);
        $update->where(['leadId' => $fromId]);
        $query = $dbSql->buildSqlString($update);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        echo $query;

        $update = $dbSql->update(\Crm\Model\AccountMapper::TABLE_NAME);
        $update->set(['leadId' => $toId]);
        $update->where(['leadId' => $fromId]);
        $query = $dbSql->buildSqlString($update);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        echo $query;
        echo '<br/>====================</br/>';

        $update = $dbSql->update(\Crm\Model\OpportunityMapper::TABLE_NAME);
        $update->set(['leadId' => $toId]);
        $update->where(['leadId' => $fromId]);
        $query = $dbSql->buildSqlString($update);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        echo $query;
        echo '<br/>====================</br/>';

        $delete = $dbSql->delete(\Crm\Model\LeadMapper::TABLE_NAME);
        $delete->where(['id' => $fromId]);
        $query = $dbSql->buildSqlString($delete);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        echo $query;
        echo '<br/>====================</br/>';
        die;
    }

    public function updateleadcompanyAction(){
        $updateQuery = 'UPDATE crm_leads_companies lc INNER JOIN crm_leads l ON lc.leadId=l.id AND lc.companyId=l.companyId SET lc.accountId=l.accountId WHERE l.accountId IS NOT NULL AND lc.accountId IS NULL';
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        //$query = $dbSql->buildSqlString($update);
        $results = $dbAdapter->query($updateQuery, $dbAdapter::QUERY_MODE_EXECUTE);

        $select = $dbSql->select(['a' => \Crm\Model\AccountMapper::TABLE_NAME]);
        $select->join(['lc' => \Crm\Model\Lead\CompanyMapper::TABLE_NAME], 'a.id=lc.accountId', [], $select::JOIN_LEFT);
        $select->where(['lc.id IS NULL']);
        $query = $dbSql->buildSqlString($select);
        $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        $accountIds = [];
        if($rows->count()){
            foreach ($rows as $row){
                $row = (array) $row;
                $accountIds[$row['id']] = $row['id'];
            }
        }
        $totalUpdate = 0;
        if(count($accountIds)){
             $select = $dbSql->select(['l' => \Crm\Model\LeadMapper::TABLE_NAME]);
             $select->join(['a' => \Crm\Model\AccountMapper::TABLE_NAME], 'l.accountId = a.id', ['a.companyId' => 'companyId']);
             $select->where(['accountId' => $accountIds]);
             $query = $dbSql->buildSqlString($select);
             $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

             $leadCompanyMapper=  $this->getServiceLocator()->get('\Crm\Model\Lead\CompanyMapper');
             if($rows->count()){
                 foreach ($rows as $row){
                     $leadCompany = new \Crm\Model\Lead\Company();
                     $leadCompany->setLeadId($row['id']);
                     $leadCompany->setCompanyId($row['a.companyId']);
                     $leadCompanyMapper->isExisted($leadCompany);
                     $leadCompany->setAccountId($row['accountId']);
                     $leadCompany->setOpportunityId($row['opportunityId']);
                     $leadCompany->setSource($row['source']);
                     $leadCompany->setSourceReference($row['sourceReference']);
                     $leadCompanyMapper->save($leadCompany);
                     $totalUpdate++;
                 }
             }
        }
        echo 'Update '.$totalUpdate.' record';
        die;
    }

    /**
     * @author hungpx
     * update lai cac hop dong chua co status
     */
    public function updatestatuscontractAction(){
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $update = $dbSql->update(\Crm\Model\ContractMapper::TABLE_NAME);
        $update->set(['status' => \Crm\Model\Contract::STATUS_CHECKED]);
        $update->where(['approvedById IS NOT NULL']);
        $update->where(['status IS NULL']);
        $query = $dbSql->buildSqlString($update);
        $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        echo 'Đã update <b style="color:red;">'. $rows->count().'</b> hợp đồng chưa  có status' ;
        echo '<br/>====================<br/>';
        echo 'Done';
        die;
    }

    /**
     * @author hungpx
     * update hợp đồng những nhân viên đã nghỉ việc về trạng thái kết thúc
     */
    public function updateemployeecontractAction(){
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $updateQuery = '
            UPDATE employee_contracts ec INNER JOIN employees e ON ec.employeeId = e.id
               SET ec.status = '.\Hrm\Model\Employee\Contract::STATUS_INACTIVE.'
                   WHERE e.quitDate IS NOT NULL
        ';
        $rows = $dbAdapter->query($updateQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        echo 'Đã update <b style="color:red;">'. $rows->count().'</b> hợp đồng về trạng thái kết thúc' ;
        echo '<br/>====================<br/>';
        echo 'Done';

        $updateEmployee = 'UPDATE employees e SET e.workingStatus = '.\Hrm\Model\Employee::WORKING_STATUS_RETIRED.'
                            WHERE e.quitDate IS NOT NULL
                        ';
        $rows = $dbAdapter->query($updateEmployee, $dbAdapter::QUERY_MODE_EXECUTE);
        echo 'Đã update <b style="color:red;">'. $rows->count().'</b> nhân sự về trạng thái đã nghỉ việc' ;
        echo '<br/>====================<br/>';
        echo 'Done';

        die;
    }

    /**
     * @author hungpx
     * update bảng lunchs về đúng vị trí ngồi của nhân viên
     */
    public function updatelunchAction(){
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $select = $dbSql->select(['l' => \Hrm\Model\Employee\LunchMapper::TABLE_NAME]);
        $select->join(['e' => \Hrm\Model\EmployeeMapper::TABLE_NAME], 'l.employeeId = e.id', []);
        $select->where(['e.sittingPositionId <> ? l.sittingPositionId' ]);
        $select->where(['l.applyDate >= ?' => \Home\Model\DateBase::getCurrentDate() ]);
        $query = $dbSql->buildSqlString($select);
        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage(200);
        $page = $this->getRequest()->getQuery('page', 1);
        $paginator->setCurrentPageNumber($page);
        foreach ($paginator as $row) {
        	$employee = new \Hrm\Model\Employee();
        	$employee->setId($row['employeeId']);
        	$employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');
        	if ($employeeMapper->getEmployee($employee)){
        		$lunch = new \Hrm\Model\Employee\Lunch();
        		$lunch->exchangeArray((array) $row);
        		$lunch->setSittingPositionId($employee->getSittingPositionId());
        		$lunchMapper =  $this->getServiceLocator()->get('\Hrm\Model\Employee\LunchMapper');
        		$lunchMapper->save($lunch);
        	}
        }
        echo 'Đã update <b style="color:red;">'. $paginator->count().'</b> nhân sự đặt cơm về đúng vị trí ngồi' ;
        echo '<br/>====================<br/>';
        echo 'Done';

        die;

    }

    /**
     * B1: update tất cả status về 99
     * B2: update những lead có lead_user.type = sale về status belong
     * B3: update những lead có status 99 về free
     */
    public function updateleadcompanystatusAction(){
        $page = $this->getRequest()->getQuery('page', 1);
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        $update = $dbSql->update(\Crm\Model\Lead\CompanyMapper::TABLE_NAME);
        $update->set(['status' => 99]);
        //$update->where(['1']);
        $query = $dbSql->buildSqlString($update);
        $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        $updateQuery =
        'UPDATE crm_leads_companies lc
            INNER JOIN crm_leads_users lu ON lc.leadId=lu.leadId AND lc.companyId=lu.companyId
         SET
            lc.status = '.\Crm\Model\Lead\Company::STATUS_BELONG.'
         WHERE
            lc.leadId IS NOT NULL AND lc.companyId IS NOT NULL AND lu.type= '.\Crm\Model\Lead\User::TYPE_SALE;
        $rows = $dbAdapter->query($updateQuery, $dbAdapter::QUERY_MODE_EXECUTE);

        $update = $dbSql->update(\Crm\Model\Lead\CompanyMapper::TABLE_NAME);
        $update->set(['status' => \Crm\Model\Lead\Company::STATUS_FREE]);
        $update->where(['status' => 99]);
        $query = $dbSql->buildSqlString($update);
        $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        echo 'done';
        die;
    }

    public function createaccountproductAction(){
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        $select = $dbSql->select(['cp' => \Crm\Model\Contract\ProductMapper::TABLE_NAME]);
        $select->join(['c' => \Crm\Model\ContractMapper::TABLE_NAME], 'c.id=cp.contractId', ['accountId']);
        $select->join(['p' => \Company\Model\ProductMapper::TABLE_NAME], 'cp.productId=p.id', ['p.type' => 'type']);

        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage(200);
        $page = $this->getRequest()->getQuery('page', 1);
        $paginator->setCurrentPageNumber($page);

        $accountProductMapper = $this->getServiceLocator()->get('\Crm\Model\Account\ProductMapper');

        $totalItem = $this->getRequest()->getQuery('totalItem', 0);
        foreach ($paginator as $row) {
            $row = (array) $row;
            $accountProduct = new \Crm\Model\Account\Product();
            $accountProduct->setAccountId($row['accountId']);
            $accountProduct->setProductId($row['productId']);
            if($accountProductMapper->isExisted($accountProduct)){
                if($row['p.type'] == \Company\Model\Product::TYPE_SERVICE_FIXED_FEE){
                    $accountProduct->setExpiredDate(null);
                    $accountProduct->setStatus(\Crm\Model\Account\Product::STATUS_USING);
                } else {
                    $expriteDate = DateBase::createFromFormat(DateBase::COMMON_DATE_FORMAT, $row['expirationDate']);
                    $currentExpried = DateBase::createFromFormat(
                        DateBase::COMMON_DATE_FORMAT, $accountProduct->getExpiredDate());
                    if($expriteDate > $currentExpried){
                        $accountProduct->setExpiredDate($expriteDate->format(DateBase::COMMON_DATE_FORMAT));
                        $accountProduct->setStatus(\Crm\Model\Account\Product::STATUS_USING);
                    }
                }
            } else {
                if($row['p.type'] == \Company\Model\Product::TYPE_SERVICE_FIXED_FEE){
                    $accountProduct->setExpiredDate(null);
                    $accountProduct->setStatus(\Crm\Model\Account\Product::STATUS_USING);
                } else {
                    $accountProduct->setExpiredDate($row['expirationDate']);
                    $expriteDate = DateBase::createFromFormat(DateBase::COMMON_DATE_FORMAT, $row['expirationDate']);
                    $currentExpried = DateBase::createFromFormat(
                        DateBase::COMMON_DATE_FORMAT, DateBase::getCurrentDate());
                    if($expriteDate > $currentExpried){
                        $accountProduct->setStatus(\Crm\Model\Account\Product::STATUS_USING);
                    } else {
                        $accountProduct->setStatus(\Crm\Model\Account\Product::STATUS_EXPRIRED);
                    }
                }

            }

            $accountProduct->setLastUpdatedDateTime(DateBase::getCurrentDateTime());
            $accountProductMapper->save($accountProduct);
            $totalItem++;
        }
        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
        if ($page <= $paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri',
                '/system/tool/createaccountproduct?page=' . ($page + 1) . '&totalItem=' . $totalItem);
        }
        $this->getViewModel()->setVariable('totalItem', $totalItem);
        return $this->getViewModel();
    }

    public function addtoprojectAction(){
        $createdById = $this->getRequest()->getQuery('createdById');
        $role = $this->getRequest()->getQuery('level', \Work\Model\ProjectUser::ACCESS_LEVEL_MANAGER);
        if(!$createdById){
            echo 'phải điền param createdById';die;
        }
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        $select = $dbSql->select(['p' => \Work\Model\ProjectMapper::TABLE_NAME]);
        $select->where(['createdById' => $createdById]);

        $query = $dbSql->buildSqlString($select);
        $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        if($rows->count()){
            $projectUserMapper = $this->getServiceLocator()->get('\Work\Model\ProjectUserMapper');
            foreach ($rows as $row){
                $row = (array) $row;
                $projectUser = new \Work\Model\ProjectUser();
                $projectUser->setUserId($createdById);
                $projectUser->setProjectId($row['id']);
                if(!$projectUserMapper->isExisted($projectUser)){
                    $projectUser->setAccessLevel($role);
                    $projectUser->setStatus(\Work\Model\ProjectUser::STATUS_ACTIVE);
                    $projectUser->setCreatedById($this->user()->getIdentity());
                    $projectUser->setCreatedDateTime(DateBase::getCurrentDateTime());
                    $projectUserMapper->save($projectUser);

                    echo '<b>Đã thêm vào dự án: </b>'.$row['name'].'<br/>';
                }
            }
        }
        die;

    }
    public function moveavatarAction(){
         $oldAvatarfiles = scandir(MEDIA_PATH.'/user/avatar/');
         $userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
         $uri = new \Home\Service\Uri();
         foreach ($oldAvatarfiles as $avatarFile){
             if ($avatarFile != '.' && $avatarFile != '..'){
                $id = explode('.', $avatarFile)[0];
                $digitValidator = new \Zend\Validator\Digits();
                if ($digitValidator->isValid($id)){
                    $user = new \User\Model\User();
                    $user = $userMapper->get($id);
                    $user->setAvatar($avatarFile);
                    copy(MEDIA_PATH.'/user/avatar/'.$avatarFile, $uri->getSavePath($user).$avatarFile);
                    echo '<b>OLD: </b>'.MEDIA_PATH.'/user/avatar/'.$avatarFile.'<br>';
                    echo '<b style="color:red">NEW: </b>'.$uri->getSavePath($user).$avatarFile.'<br>';
                    $userMapper->save($user);
                }
             }

         }die('Xong');
    }

    public function updateactivitiesleadidAction(){
        set_time_limit(500);
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        $select = $dbSql->select(['act' => \Crm\Model\ActivityMapper::TABLE_NAME]);
        $select->where(['leadId IS NULL']);
        $select->group(['accountId', 'opportunityId']);
        $query = $dbSql->buildSqlString($select);
        $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        $activityMapper = $this->getServiceLocator()->get('\Crm\Model\ActivityMapper');
        if($rows->count()){
            foreach ($rows as $row){
                $activity = new \Crm\Model\Activity();
                $activity->exchangeArray((array) $row);
                $activityMapper->updateLeadId($activity);
            }
        }
        die('Xong');
    }

    public function revertleaduserAction(){
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        $select = $dbSql->select(['lc' => \Crm\Model\Lead\CompanyMapper::TABLE_NAME]);
        $select->join(['a' => \Crm\Model\ActivityMapper::TABLE_NAME],
            new Expression('a.companyId=lc.companyId AND a.leadId=lc.leadId'), ['relatedUserId' ,'a.createdDateTime' => 'createdDateTime']);
        //$select->where(['lc.accountId IS NOT NULL']);
        $select->where(['a.type' => \Crm\Model\Activity::TYPE_AUTO_FREE]);
        $select->where(['a.createdDate' => '2015-05-29']);
        $select->order(['a.id DESC']);
        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage(300);
        $page = $this->getRequest()->getQuery('page', 1);
        $paginator->setCurrentPageNumber($page);

        $leadUserMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\UserMapper');
        $leadCompanyMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\CompanyMapper');
        $currentDateTime = DateBase::getCurrentDateTime();
        $totalUser = $this->getRequest()->getQuery('totalUser')?:0;
        // Xóa các người dùng dc gắn sau thời điểm lỗi
        $result = [];
        foreach ($paginator as $row) {
            $row = (array) $row;
            $leadCompany = new \Crm\Model\Lead\Company($row);
            $delete = $dbSql->delete(\Crm\Model\Lead\UserMapper::TABLE_NAME);
            $delete->where(['createdDateTime > ?' => $row['a.createdDateTime']]);
            $delete->where(['companyId' => $leadCompany->getCompanyId()]);
            $delete->where(['leadId' => $leadCompany->getLeadId()]);
            $query = $dbSql->buildSqlString($delete);
            $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
            $result[] = $row;
        }
        foreach ($result as $row) {
            $leadCompany = new \Crm\Model\Lead\Company($row);

            $totalUser++;
            //update laij last activities về trước khi xóa
            $select = $dbSql->select(['a' => \Crm\Model\ActivityMapper::TABLE_NAME]);
            $select->where(['companyId' => $leadCompany->getCompanyId()]);
            $select->where(['leadId' => $leadCompany->getLeadId()]);
            $select->where(['createdDateTime < ?' => $row['a.createdDateTime']]);
            $select->order(['a.createdDateTime DESC']);
            $select->limit(1);
            $query = $dbSql->buildSqlString($select);
            $row2 = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
            if($row2->count()){
                $row2 = (array) $row2->current();
                $leadCompanyMapper->updateColumns([
                    'status' => \Crm\Model\Lead\Company::STATUS_BELONG,
                    'lastActivityDateTime' => $row2['createdDateTime']
                ], $leadCompany);
            }

            // set lại user như trước khi xóa
            $leadUser = new \Crm\Model\Lead\User();
            $leadUser->setLeadId($leadCompany->getLeadId());
            $leadUser->setAccountId($leadCompany->getAccountId());
            $leadUser->setCompanyId($leadCompany->getCompanyId());
            $leadUser->setType(\Crm\Model\Lead\User::TYPE_SALE);
            $leadUser->setUserId($row['relatedUserId']);
            if(!$leadUserMapper->isExisted($leadUser)){
                $leadUser->setCreatedById(1);
                $leadUser->setCreatedDateTime('2015-05-29 14:00:00');
                $leadUserMapper->save($leadUser);
            }
        }

        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
        if ($page <= $paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri', '/system/tool/revertleaduser?page=' . ($page + 1) . '&totalUser=' . $totalUser);
        }
        $this->getViewModel()->setVariable('totalUser', $totalUser);
        return $this->getViewModel();
    }

    public function mergerleadcompanyAction(){
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        $delete = $dbSql->delete(\Crm\Model\Lead\CompanyMapper::TABLE_NAME);
        $delete->where(['leadId IS NULL', 'accountId IS NULL', 'opportunityId IS NULL']);
        $query = $dbSql->buildSqlString($delete);
        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        echo $query;

        echo '<br/>';
        //$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        $select = $dbSql->select(['lc' => \Crm\Model\Lead\CompanyMapper::TABLE_NAME]);
        $select->columns(['companyId', 'leadId', 'totalDuplicate' => new Expression('COUNT(id)')]);
        $select->group(['companyId', 'leadId']);
        $select->having(['totalDuplicate > 1']);
        $query = $dbSql->buildSqlString($select);
        $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        $leadCompanyMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\CompanyMapper');

        if($rows){
            foreach ($rows->toArray() as $row){
                $select = $dbSql->select(['lc' => \Crm\Model\Lead\CompanyMapper::TABLE_NAME]);
                $select->where(['companyId' => $row['companyId']]);
                if($row['leadId']){
                    $select->where(['leadId' => $row['leadId']]);
                } else {
                    $select->where(['leadId IS NULL']);
                }
                $query = $dbSql->buildSqlString($select);
                echo $query;
                echo '<br/>';
                $rows2 = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                if($rows2){

                    $leadCompany = new \Crm\Model\Lead\Company();
                    foreach ($rows2->toArray() as $row2){
                        $leadCompany->setId($row2['id']);
                        $leadCompany->setLeadId($row2['leadId']);
                        $leadCompany->setCompanyId($row2['companyId']);
                        if($row2['opportunityId']){
                            $leadCompany->setOpportunityId($row2['opportunityId']);
                        }
                        if($row2['accountId']){
                            $leadCompany->setAccountId($row2['accountId']);
                        }
                        if($row2['lastActivityDateTime']){
                            $leadCompany->setLastActivityDateTime($row2['lastActivityDateTime']);
                        }
                        if($row2['releaseDateTime']){
                            $leadCompany->setReleaseDateTime($row2['releaseDateTime']);
                        }
                        if($row2['source']){
                            $leadCompany->setSource($row2['source']);
                        }
                        if($row2['sourceReference']){
                            $leadCompany->setSourceReference($row2['sourceReference']);
                        }
                        if($row2['sourceCampaignId']){
                            $leadCompany->setSourceCampaignId($row2['sourceCampaignId']);
                        }
                        if($row2['sourceAccountId']){
                            $leadCompany->setSourceAccountId($row2['sourceAccountId']);
                        }
                        if($row2['sourceContactId']){
                            $leadCompany->setSourceContactId($row2['sourceContactId']);
                        }
                        if($row2['sourceEmployeeId']){
                            $leadCompany->setSourceEmployeeId($row2['sourceEmployeeId']);
                        }
                        if($row2['status']){
                            $leadCompany->setStatus($row2['status']);
                        }
                    }
                    $leadCompanyMapper->save($leadCompany);
                    vdump(array(
                        'id' => $leadCompany->getId(),
                        'companyId' => $leadCompany->getCompanyId()?:null,
                        'leadId' => $leadCompany->getLeadId()?:null,
                        'accountId' => $leadCompany->getAccountId()?:null,
                        'opportunityId' => $leadCompany->getOpportunityId() ?: null,
                        'releaseDateTime' => $leadCompany->getReleaseDateTime()?:null,
                        'lastActivityDateTime' => $leadCompany->getLastActivityDateTime() ?: null,
                        'source' => $leadCompany->getSource() ?: null,
                        'sourceReference' => $leadCompany->getSourceReference() ?: null,
                        'sourceCampaignId' => $leadCompany->getSourceCampaignId() ?: null,
                        'sourceAccountId' => $leadCompany->getSourceAccountId() ?: null,
                        'sourceContactId' => $leadCompany->getSourceContactId() ?: null,
                        'sourceEmployeeId' => $leadCompany->getSourceEmployeeId() ?: null,
                        'status' => $leadCompany->getStatus() ?: null,
                    ));
                    $delete = $dbSql->delete(\Crm\Model\Lead\CompanyMapper::TABLE_NAME);
                    $delete->where(['companyId' => $leadCompany->getCompanyId()]);
                    $delete->where(['leadId' => $leadCompany->getLeadId()]);
                    $delete->where(['id != ?' => $leadCompany->getId()]);
                    $query = $dbSql->buildSqlString($delete);
                    echo $query;
                    $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                    echo '<br/>';
                    echo '================';
                    echo '<br/>';
                    unset($leadCompany);
                }
            }
        }
        die;
    }


    public function deleteaccountAction(){
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        //Lấy các account chưa có hợp đồng hoặc hợp đồng chưa có phiếu thu được duyệt
        $select = $dbSql->select(['a' =>\Crm\Model\AccountMapper::TABLE_NAME]);
        $select->join(['c' => \Crm\Model\ContractMapper::TABLE_NAME], 'c.accountId=a.id', [], $select::JOIN_LEFT);
        $select->join(['cp' => \Crm\Model\Contract\PaymentMapper::TABLE_NAME], 'cp.contractId=c.id', [], $select::JOIN_LEFT);
        $predicateSet = new PredicateSet();
        $predicateSet->addPredicate(new IsNull('cp.id'), $predicateSet::OP_OR);
        $predicateSet->addPredicate(new In('cp.status', [\Crm\Model\Contract\Payment::STATUS_DELETED, \Crm\Model\Contract\Payment::STATUS_UNCHECKED]), $predicateSet::OP_OR);
        $select->where($predicateSet);
        $select->group(['a.id']);
        $query = $dbSql->buildSqlString($select);
        echo $query;
        $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        $accountIds = [];
        if($rows){
            foreach ($rows->toArray() as $row){
                $accountIds[$row['id']] = $row['id'];
            }
        }
        // Loại bỏ các account trong danh sách đã có hợp đồng có phiếu thu dc duyệt
        $select = $dbSql->select(['c' =>\Crm\Model\ContractMapper::TABLE_NAME]);
        $select->join(['cp' => \Crm\Model\Contract\PaymentMapper::TABLE_NAME], 'cp.contractId=c.id', []);
        $select->columns(['accountId']);
        $select->where(['cp.status' => \Crm\Model\Contract\Payment::STATUS_CHECKED]);
        $select->where(['c.accountId' => $accountIds]);
        $select->group(['c.accountId']);
        $query = $dbSql->buildSqlString($select);
        $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        if($rows){
            foreach ($rows->toArray() as $row){
                unset($accountIds[$row['accountId']]);
            }
        }

        // Loại bỏ các account có liên kết đặc biệt trong 1 số bảng
        $fks = array(
        	array(
        	   'table' => \Contact\Model\ContactMapper::TABLE_NAME,
        	   'column' => 'crmAccountId'
            ),
            array(
                'table' => \Crm\Model\Account\ProductMapper::TABLE_NAME,
                'column' => 'accountId'
            ),
            array(
                'table' => \Crm\Model\ContactMapper::TABLE_NAME,
                'column' => 'accountId'
            ),
            array(
                'table' => \Crm\Model\ContractMapper::TABLE_NAME,
                'column' => 'refererAccountId'
            ),
            array(
                'table' => \Crm\Model\Lead\CompanyMapper::TABLE_NAME,
                'column' => 'sourceAccountId'
            ),
            array(
                'table' => \Idea\Model\IdeaMapper::TABLE_NAME,
                'column' => 'crmAccountId'
            ),
            array(
                'table' => \Work\Model\MeetingMapper::TABLE_NAME,
                'column' => 'crmAccountId'
            ),
            array(
                'table' => \Work\Model\PlanMapper::TABLE_NAME,
                'column' => 'crmAccountId'
            ),
            array(
                'table' => \Work\Model\ProjectUserMapper::TABLE_NAME,
                'column' => 'crmAccountId'
            ),
            array(
                'table' => \Work\Model\Task\RequirementMapper::TABLE_NAME,
                'column' => 'accountId'
            ),
            array(
                'table' => \Work\Model\TaskMapper::TABLE_NAME,
                'column' => 'crmAccountId'
            ),
        );
        foreach ($fks as $fk){
            if(count($accountIds)){
                $select = $dbSql->select($fk['table']);
                $select->where([$fk['column'] => $accountIds]);
                $select->columns([$fk['column']]);
                $select->group([$fk['column']]);
                $query = $dbSql->buildSqlString($select);
                $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                if($rows){
                    foreach ($rows->toArray() as $row){
                        unset($accountIds[$row[$fk['column']]]);
                    }
                }
            }

        }

        if(count($accountIds)){
            $select = $dbSql->select(['a' => \Crm\Model\AccountMapper::TABLE_NAME]);
            $select->join(['lc' =>\Crm\Model\Lead\CompanyMapper::TABLE_NAME], 'lc.accountId=a.id', [
        	   'accountId', 'companyId', 'leadId'
            ], $select::JOIN_LEFT);
            $select->columns([]);
            $select->where(['a.id' => $accountIds]);
            $select->where(['lc.leadId IS NOT NULL']);
            $select->group(['a.id']);
            $query = $dbSql->buildSqlString($select);
            echo $query;
            echo '<br/>=================</br/>';
            $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
            if($rows){
                $leadCompanyMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\CompanyMapper');
                $contractMapper = $this->getServiceLocator()->get('\Crm\Model\ContractMapper');
                foreach ($rows->toArray() as $row){
                    // update lead user
                    $update = $dbSql->update(\Crm\Model\Lead\UserMapper::TABLE_NAME);
                    $update->set([
                        'leadId' => $row['leadId'],
                        'accountId' => null
                    ]);
                    $update->where(['accountId' => $row['accountId']]);
                    $query = $dbSql->buildSqlString($update);
                    $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                    echo $query;
                    echo '<br/>';

                    // update activity
                    $update = $dbSql->update(\Crm\Model\ActivityMapper::TABLE_NAME);
                    $update->set([
                        'leadId' => $row['leadId'],
                        'accountId' => null
                        ]);
                    $update->where(['accountId' => $row['accountId']]);
                    $query = $dbSql->buildSqlString($update);
                    $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                    echo $query;
                    echo '<br/>';

                    // update contract
                    $update = $dbSql->update(\Crm\Model\ContractMapper::TABLE_NAME);
                    $update->set([
                        'leadId' => $row['leadId'],
                        'accountId' => null
                        ]);
                    $update->where(['accountId' => $row['accountId']]);
                    $query = $dbSql->buildSqlString($update);
                    $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                    echo $query;
                    echo '<br/>';

                    // update lead company
                    $update = $dbSql->update(\Crm\Model\Lead\CompanyMapper::TABLE_NAME);
                    $update->set([
                        'leadId' => $row['leadId'],
                        'accountId' => null
                        ]);
                    $update->where(['accountId' => $row['accountId']]);
                    $query = $dbSql->buildSqlString($update);
                    $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                    echo $query;
                    echo '<br/>';

                    // update lead
                    $update = $dbSql->update(\Crm\Model\LeadMapper::TABLE_NAME);
                    $update->set([
                        'accountId' => null
                        ]);
                    $update->where(['accountId' => $row['accountId']]);
                    $query = $dbSql->buildSqlString($update);
                    $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                    echo $query;
                    echo '<br/>';

                    // xoa account
                    $delete = $dbSql->delete(\Crm\Model\AccountMapper::TABLE_NAME);
                    $delete->where(['id' => $row['accountId']]);
                    $query = $dbSql->buildSqlString($delete);
                    $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                    echo $query;
                    echo '<br/>';
                    echo '<br/>========================<br/>';

                    unset($accountIds[$row['accountId']]);
                }
            }

        }
        if(count($accountIds)){
            $select = $dbSql->select(['a' => \Crm\Model\AccountMapper::TABLE_NAME]);
            $select->where(['id' => $accountIds]);
            $query = $dbSql->buildSqlString($select);
            $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
            if($rows){
                foreach ($rows->toArray() as $row){
                    $account = new \Crm\Model\Account();
                    $account->exchangeArray($row);
                    if($account->getLeadId()){
                        // update lead user
                        $update = $dbSql->update(\Crm\Model\Lead\UserMapper::TABLE_NAME);
                        $update->set([
                            'leadId' => $account->getLeadId(),
                            'accountId' => null
                            ]);
                        $update->where(['accountId' => $account->getId()]);
                        $query = $dbSql->buildSqlString($update);
                        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                        echo $query;
                        echo '<br/>';

                        // update activity
                        $update = $dbSql->update(\Crm\Model\ActivityMapper::TABLE_NAME);
                        $update->set([
                            'leadId' => $account->getLeadId(),
                            'accountId' => null
                            ]);
                        $update->where(['accountId' => $account->getId()]);
                        $query = $dbSql->buildSqlString($update);
                        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                        echo $query;
                        echo '<br/>';

                        // update contract
                        $update = $dbSql->update(\Crm\Model\ContractMapper::TABLE_NAME);
                        $update->set([
                            'leadId' => $account->getLeadId(),
                            'accountId' => null
                            ]);
                        $update->where(['accountId' => $account->getId()]);
                        $query = $dbSql->buildSqlString($update);
                        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                        echo $query;
                        echo '<br/>';

                        // update lead company
                        $update = $dbSql->update(\Crm\Model\Lead\CompanyMapper::TABLE_NAME);
                        $update->set([
                            'leadId' => $account->getLeadId(),
                            'accountId' => null
                            ]);
                        $update->where(['accountId' => $account->getId()]);
                        $query = $dbSql->buildSqlString($update);
                        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                        echo $query;
                        echo '<br/>';

                        // update lead
                        $update = $dbSql->update(\Crm\Model\LeadMapper::TABLE_NAME);
                        $update->set([
                            'accountId' => null
                            ]);
                        $update->where(['accountId' => $account->getId()]);
                        $query = $dbSql->buildSqlString($update);
                        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                        echo $query;
                        echo '<br/>';

                        // xoa account
                        $delete = $dbSql->delete(\Crm\Model\AccountMapper::TABLE_NAME);
                        $delete->where(['id' => $account->getId()]);
                        $query = $dbSql->buildSqlString($delete);
                        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                        echo $query;
                        echo '<br/>';
                        echo '<br/>========================<br/>';

                        unset($accountIds[$account->getId()]);
                    } else {
                        // update lead user
                        $update = $dbSql->update(\Crm\Model\Lead\UserMapper::TABLE_NAME);
                        $update->set([
                            'accountId' => null
                            ]);
                        $update->where(['accountId' => $account->getId()]);
                        $query = $dbSql->buildSqlString($update);
                        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                        echo $query;
                        echo '<br/>';

                        // update activity
                        $update = $dbSql->update(\Crm\Model\ActivityMapper::TABLE_NAME);
                        $update->set([
                            'accountId' => null
                            ]);
                        $update->where(['accountId' => $account->getId()]);
                        $query = $dbSql->buildSqlString($update);
                        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                        echo $query;
                        echo '<br/>';

                        // update contract
                        $update = $dbSql->update(\Crm\Model\ContractMapper::TABLE_NAME);
                        $update->set([
                            'accountId' => null
                            ]);
                        $update->where(['accountId' => $account->getId()]);
                        $query = $dbSql->buildSqlString($update);
                        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                        echo $query;
                        echo '<br/>';

                        // update lead company
                        $update = $dbSql->update(\Crm\Model\Lead\CompanyMapper::TABLE_NAME);
                        $update->set([
                            'accountId' => null
                            ]);
                        $update->where(['accountId' => $account->getId()]);
                        $query = $dbSql->buildSqlString($update);
                        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                        echo $query;
                        echo '<br/>';

                        // update lead
                        $update = $dbSql->update(\Crm\Model\LeadMapper::TABLE_NAME);
                        $update->set([
                            'accountId' => null
                            ]);
                        $update->where(['accountId' => $account->getId()]);
                        $query = $dbSql->buildSqlString($update);
                        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                        echo $query;
                        echo '<br/>';

                        // xoa account
                        $delete = $dbSql->delete(\Crm\Model\AccountMapper::TABLE_NAME);
                        $delete->where(['id' => $account->getId()]);
                        $query = $dbSql->buildSqlString($delete);
                        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                        echo $query;
                        echo '<br/>';
                        echo '<br/>========================<br/>';

                        unset($accountIds[$account->getId()]);
                    }

                }
            }
        }

        die;
    }

    public function deleteopportunitiesAction(){
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        $update1 = $dbSql->update(\Crm\Model\Lead\CompanyMapper::TABLE_NAME);
        $update1->set(['opportunityId' => null]);
        $query = $dbSql->buildSqlString($update1);
        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        echo $query;
        echo '<br/>';

        $update1 = $dbSql->update(\Crm\Model\ActivityMapper::TABLE_NAME);
        $update1->set(['opportunityId' => null]);
        $query = $dbSql->buildSqlString($update1);
        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        echo $query;
        echo '<br/>';
        die;
    }

//======================Tool đồng bộ ofice====================

    private function createOfficeAdapter()
    {
       /*  if (getenv('APPLICATION_ENV') == 'development') {
        	$officeAdapter = new \Zend\Db\Adapter\Adapter(array(
            	'driver' => 'Pdo_Mysql',
            	   'driver_options' => array(
            	   \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
            	),
            	'dsn'             => 'mysql:dbname=db_officevatgia;host=localhost:3306',
            	'username'        => 'sql_erp_dev',
            	'password'        => '1stdy456d7uyAgs_eqwer7tiykmnbdvsaerygj'
        	));
        } else { */
//         	$officeAdapter = new \Zend\Db\Adapter\Adapter(array(
//             	'driver' => 'Pdo_Mysql',
//             	   'driver_options' => array(
//             	   \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
//             	),
//             	'dsn'             => 'mysql:dbname=officevatgia;host=10.1.4.1:3306',
//             	'username'        => 'nhanhvn',
//             	'password'        => '9yxET6d7MLhcpzpW'
//         	));
        	$officeAdapter = new \Zend\Db\Adapter\Adapter(array(
            	'driver' => 'Pdo_Mysql',
            	   'driver_options' => array(
            	   \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
            	),
            	'dsn'             => 'mysql:dbname=officevatgia;host=192.168.1.200:3306',
            	'username'        => 'sql_officevg',
            	'password'        => '34w6ygwetgsdft_436ygbsvseB12433'
        	));
        //}
    	$officeAdapter->setProfiler(new \Zend\Db\Adapter\Profiler\Profiler());
    	$officeSql = new \Zend\Db\Sql\Sql($officeAdapter);
    	return array($officeAdapter, $officeSql);

    }

    private function createOfficeBNCAdapter(){
        $officeAdapter = new \Zend\Db\Adapter\Adapter(array(
            'driver' => 'Pdo_Mysql',
            'driver_options' => array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
            ),
            'dsn'             => 'mysql:dbname=officebnc;host=192.168.1.200:3306',
            'username'        => 'sql_officevg',
            'password'        => '34w6ygwetgsdft_436ygbsvseB12433'
        ));
        $officeAdapter->setProfiler(new \Zend\Db\Adapter\Profiler\Profiler());
        $officeSql = new \Zend\Db\Sql\Sql($officeAdapter);
        return array($officeAdapter, $officeSql);
    }

    /**
     * @author VanCK
     */
    public function testofficedbAction()
    {
        echo "testofficedbAction<br><br>";

		list($officeAdapter, $officeSql) = $this->createOfficeAdapter();
		$select = $officeSql->select(['l' => 'locations']);
		$query = $officeSql->buildSqlString($select);
		$rows = $officeAdapter->query($query, $officeAdapter::QUERY_MODE_EXECUTE);

		if($rows->count()) {
		    foreach($rows as $row) {
                var_dump((array)$row);
		    }
		} else {
		    echo $query . '<br>';
			echo "No Rows";
		}
		die();
    }

    /**
     *
     * @param unknown $placeId
     * @param boolean $getAsId
     * fasle => text
     * true => array(country, cityId, districtId)
     *
     */
    private function mathPlace($placeId, $getAsId = false){
        if(!$placeId){
            if($getAsId){
    			return array(
    				'countryId' => null,
    				'cityId' => null,
    				'districtId' => null
    			);
    		} else {
    			return null;
    		}
        }
		list($officeAdapter, $officeSql) = $this->createOfficeAdapter();
		/*@var $officeAdapter \Zend\Db\Adapter\Adapter */
		/*@var $officeSql \Zend\Db\Sql\Sql */

		$select = $officeSql->select(['l' => 'locations']);
		$select->where(['ID' => $placeId]);
		$query = $officeSql->buildSqlString($select);
		$row = $officeAdapter->query($query, $officeAdapter::QUERY_MODE_EXECUTE);
		if($row->count()){
			$row = (array) $row->current();
			// nếu chỉ lấy text
			if(!$getAsId){
				if($row['type'] == 3){ // nếu địa điểm là quận huyện, lấy ngược lại parent để lấy thêm thành phố
					$select = $officeSql->select(['l' => 'locations']);
					$select->where(['ID' => $row['parent_id']]);
					$query = $officeSql->buildSqlString($select);
					$row2 = $officeAdapter->query($query, $officeAdapter::QUERY_MODE_EXECUTE);

					if($row2->count()){
						$row2 = (array) $row2->current();
						return Format::displaySetItems([$row['title'], $row2['title']], ', ');
					} else {
						return $row['title'];
					}
				} else {
					return $row['title'];
				}
			} else {
			//nếu phải lấy ra dữ liệu khớp tỉnh thành
				$districtId = null;
				$cityId =  null;
				$countryId= 243;

				switch ($row['type']){
					case 3: // quận huyện
						$districtName = $row['title'];
						$cityName = null;
						// lấy ra tên thành phố
						$select = $officeSql->select(['l' => 'locations']);
						$select->where(['ID' => $row['parent_id']]);
						$query = $officeSql->buildSqlString($select);
						$rowCity = $officeAdapter->query($query, $officeAdapter::QUERY_MODE_EXECUTE);
						if($rowCity->count()){
							$rowCity = (array) $rowCity->current();
							$cityName = $rowCity['title'];
						}

						// khớp với tỉnh thành bên mình
						$city = new \Address\Model\City();
						$city->setNativeName($cityName);
						$cityMapper = $this->getServiceLocator()->get('\Address\Model\CityMapper');
						if($cityMapper->isExisted($city)){
							$cityId = $city->getId();
						}

						$district = new \Address\Model\District();
						$district->setCityId($cityId);
						$district->setName($districtName);
						$districtMapper = $this->getServiceLocator()->get('\Address\Model\DistrictMapper');
						if($districtMapper->isExisted($district)){
							$districtId = $district->getId();
						}
						break;
					case 2: // thành phố
						$city = new \Address\Model\City();
						$city->setNativeName($row['title']);
						$cityMapper = $this->getServiceLocator()->get('\Address\Model\CityMapper');
						if($cityMapper->isExisted($city)){
							$cityId = $city->getId();
						}
						break;
					case 1:
					default:break;
				}
				return array(
					'countryId' => $countryId,
					'cityId' => $cityId,
					'districtId' => $districtId
				);
			}
		}
		if($getAsId){
			return array(
				'countryId' => null,
				'cityId' => null,
				'districtId' => null
			);
		} else {
			return null;
		}
    }

    private function matchDepartment($officeDepartmentId, $privateSource = \Home\Model\Consts::PRIVATE_SOURCE_OFFICEVG){
    	list($officeAdapter, $officeSql) = $this->createOfficeAdapter();

    	$dbSql = $this->getServiceLocator()->get('dbSql');
    	$dbAdapter = $this->getServiceLocator()->get('dbAdapter');
    	//tìm trong bảng department
		$select = $dbSql->select(['d' => \Company\Model\DepartmentMapper::TABLE_NAME]);
		$select->where(['oneofficeId' => $officeDepartmentId]);
		$select->where(['privateSource' => $privateSource]);
		$select->limit(1);
		$query = $dbSql->buildSqlString($select);
		$row = $dbAdapter->query($query, Adapter::QUERY_MODE_EXECUTE);
		if($row->count()){
			$row = (array) $row->current();
			return array(
				'companyId' => $row['companyId'],
				'departmentId' => $row['id']
			);
		} else {
			$select = $dbSql->select(['c' => \Company\Model\CompanyMapper::TABLE_NAME]);
			$select->where(['oneofficeId' => $officeDepartmentId]);
			$select->where(['privateSource' => $privateSource]);
			$select->limit(1);
			$query = $dbSql->buildSqlString($select);
			$row = $dbAdapter->query($query, Adapter::QUERY_MODE_EXECUTE);
			if($row->count()){
				$row = (array) $row->current();
				return array(
					'companyId' => $row['id'],
					'departmentId' => null
				);
			}
		}
		return array(
			'companyId' => null,
			'departmentId' => null
		);
    }

    public function officecompanyAction(){
        list($adapter, $sql) = $this->createOfficeBNCAdapter();
        $rows = $adapter->query('SELECT * FROM `departments` WHERE 1', $adapter::QUERY_MODE_EXECUTE);
        $arrs = $this->travel($this->recursiveObject($rows->toArray()));

        $this->getViewModel()->setVariable('items', $arrs);
        return $this->getViewModel();
    }

    private function recursiveObject($items, $parentId = 0, $ord = 0){
        $result = array();
        if($items && count($items)){
            foreach ($items as $item) {

                $current_parent = $item['parent_id']?:0;
                if($current_parent == $parentId) {
                    unset($items[$item['ID']]);
                    $result[] =	array(
                        'ord' => $ord,
                        'obj' => $item,
                        'childs' => $this->recursiveObject($items, $item['ID'], $ord+1),
                    );
                }
            }
        }
        return $result;
    }

    private function travel($items){
        $result = array();
        if($items && count($items)){
            foreach ($items as $node) {
                $item = $node['obj'];
                $item['ord'] = $node['ord'];
                $result[$item['ID']] = $item;
                if(isset($node['childs']) && count($node['childs'])){
                    $result += $this->travel($node['childs']);
                }
            }
        }
        return $result;
    }

    /**
     * update trực tiếp oneofficeId cho 1 số doanh nghiệp cơ bản như VNP, TNHH Nhanh.vn, kd đa dịch vụ, cty vatgia
     *
     */
    public function officeupdatebasiccompanyAction(){
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        //update các công ty cơ bản
        $update = $dbSql->update(\Company\Model\CompanyMapper::TABLE_NAME);
        $update->set(['oneofficeId' => 186, 'name' => 'VNP group']);
        $update->where(['id' => 10]);
        $query = $dbSql->buildSqlString($update);
        echo $query;
        echo '<br/>';
        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        $update = $dbSql->update(\Company\Model\CompanyMapper::TABLE_NAME);
        $update->set(['oneofficeId' => 649, 'name' => 'Khối kinh doanh đa dịch vụ VNP group']);
        $update->where(['id' => 305]);
        $query = $dbSql->buildSqlString($update);
        echo $query;
        echo '<br/>';
        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        $update = $dbSql->update(\Company\Model\CompanyMapper::TABLE_NAME);
        $update->set(['oneofficeId' => 141, 'name' => 'Nhanh.vn']);
        $update->where(['id' => 1]);
        $query = $dbSql->buildSqlString($update);
        echo $query;
        echo '<br/>';
        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        $update = $dbSql->update(\Company\Model\CompanyMapper::TABLE_NAME);
        $update->set(['oneofficeId' => 25, 'name' => 'Công ty cổ phần Vật Giá Việt Nam']);
        $update->where(['id' => 2]);
        $query = $dbSql->buildSqlString($update);
        echo $query;
        echo '<br/>';
        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        die;
    }

    /**
     * Chuẩn bị 1 số dữ liệu cơ bản cho BNC
     * - update privateSource cho các bảng company, department, employee, user

     */
    public function officeprepairbncdataAction(){
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        $update = $dbSql->update(\Company\Model\CompanyMapper::TABLE_NAME);
        $update->set(['privateSource' => \Home\Model\Consts::PRIVATE_SOURCE_OFFICEVG]);
        $update->where(['privateSource IS NULL']);
        $update->where(['oneofficeId IS NOT NULL']);
        $query = $dbSql->buildSqlString($update);
        echo $query;
        echo '<br/>';
        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        $update = $dbSql->update(\Company\Model\DepartmentMapper::TABLE_NAME);
        $update->set(['privateSource' => \Home\Model\Consts::PRIVATE_SOURCE_OFFICEVG]);
        $update->where(['privateSource IS NULL']);
        $update->where(['oneofficeId IS NOT NULL']);
        $query = $dbSql->buildSqlString($update);
        echo $query;
        echo '<br/>';
        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        $update = $dbSql->update(\Hrm\Model\EmployeeMapper::TABLE_NAME);
        $update->set(['privateSource' => \Home\Model\Consts::PRIVATE_SOURCE_OFFICEVG]);
        $update->where(['privateSource IS NULL']);
        $update->where(['oneofficeId IS NOT NULL']);
        $query = $dbSql->buildSqlString($update);
        echo $query;
        echo '<br/>';
        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        $update = $dbSql->update(\User\Model\UserMapper::TABLE_NAME);
        $update->set(['privateSource' => \Home\Model\Consts::PRIVATE_SOURCE_OFFICEVG]);
        $update->where(['privateSource IS NULL']);
        $update->where(['oneofficeId IS NOT NULL']);
        $query = $dbSql->buildSqlString($update);
        echo $query;
        echo '<br/>';
        $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        die;
    }

    public function officebncclonecompanyAction(){
        list($adapter, $sql) = $this->createOfficeBNCAdapter();
        $rows = $adapter->query('SELECT * FROM `departments` WHERE 1', $adapter::QUERY_MODE_EXECUTE);
        $arrs = $this->recursiveObject($rows->toArray());
        $this->travelCreateDepartment($arrs, 10, null, \Home\Model\Consts::PRIVATE_SOURCE_OFFICEBNC);
        echo 'done';
        die;
    }

    /**
     * Tạo ra cây doanh nghiệp + phòng ban cho các cty thuộc vnp như oneoffice.
     * Các phòng ban cũ vẫn dc giữ nguyên
     */
    public function officeclonecompanyAction(){
       list($adapter, $sql) = $this->createOfficeAdapter();

       $rows = $adapter->query('SELECT * FROM `departments` WHERE 1', $adapter::QUERY_MODE_EXECUTE);
       $arrs = $this->recursiveObject($rows->toArray());
       $this->travelCreateDepartment($arrs[0]['childs'], 10);
       echo 'done';
       die;
   }

   private function travelCreateDepartment($items, $companyId, $parentId = null, $privateSource = \Home\Model\Consts::PRIVATE_SOURCE_OFFICEVG){
       $result = array();
       if($items && count($items)){
           $companyMapper = $this->getServiceLocator()->get('\Company\Model\CompanyMapper');
           $departmentMapper = $this->getServiceLocator()->get('\Company\Model\DepartmentMapper');
           foreach ($items as $node) {
               $item = $node['obj'];
               if($item['is_company'] == '1'){
                   //nếu dc đanh dấu là company, sẽ kiểm tra nếu company chưa tồn tại thì tạo mới company.
                   //Sau đó chạy đệ quy với companyId mới
                   $company = new \Company\Model\Company();
                   $company->setOneofficeId($item['ID']);
                   $company->setPrivateSource($privateSource);
                   if(!$companyMapper->isExistedOfficeId($company)){
                       $company->setName($item['title']);
                       $company->setParentId($companyId);
                       if(!$companyMapper->isExisted($company)){
                           $company->setCreatedById(1);
                           $company->setCreatedDateTime(DateBase::getCurrentDateTime());
                           $company->setHeadquartersCityId(2);
                           $company->setHeadquartersDistrictId(6);
                           $company->setHeadquartersAddress('51 Lê Đại Hành');
                       } else {
                           $company->setParentId($companyId);
                       }

                   } else {
                       $company->setParentId($companyId);
                   }
                   $companyMapper->save($company);



                   if(isset($node['childs']) && count($node['childs'])){
                       $this->travelCreateDepartment($node['childs'], $company->getId(), null, $privateSource);
                   }
               } else {
                   $department = new \Company\Model\Department();
                   $department->setOneofficeId($item['ID']);
                   $department->setPrivateSource($privateSource);
                   if(!$departmentMapper->isExistedOfficeId($department)){
                       $department->setName($item['title']);
                       $department->setParentId($parentId);
                       $department->setCompanyId($companyId);
                       if(!$departmentMapper->isExisted($department)){
                           $department->setStatus(\Company\Model\Department::STATUS_ACTIVE);
                           $department->setCreatedById(1);
                           $department->setCreatedDateTime(DateBase::getCurrentDateTime());
                       }
                   } else {
                       $department->setParentId($parentId);
                       $department->setCompanyId($companyId);
                   }
                   $departmentMapper->save($department);
                   if(isset($node['childs']) && count($node['childs'])){
                       $this->travelCreateDepartment($node['childs'], $companyId, $department->getId(), $privateSource);
                   }
               }
           }
       }
   }



   /**
    * Lấy dữ liệu danh mục tài sản từ oneoffice
    */
   public function officeassettypeAction(){
       list($adapter, $sql) = $this->createOfficeAdapter();
       $rows = $adapter->query('SELECT * FROM `asset_types` WHERE 1', $adapter::QUERY_MODE_EXECUTE);
       $arrs = $this->recursiveObject($rows->toArray());
       $this->travelCreateAssetCategory($arrs);
        echo 'done';
       die;
   }
   /**
    * Cho action officeassettype
    * @param unknown $items
    * @param string $parentId
    */
   private function travelCreateAssetCategory($items, $parentId=null){
       $result = array();
       if($items && count($items)){
           $categoryMapper = $this->getServiceLocator()->get('\Asset\Model\AssetCategoryMapper');
           foreach ($items as $node) {
               $item = $node['obj'];
               $category = new \Asset\Model\AssetCategory();
               $category->setCompanyId(10);
               $category->setName($item['title']);
               $category->setParentId($parentId);
               if(!$categoryMapper->isExisted($category)){
                   $category->setCreatedById($this->user()->getIdentity());
                   $category->setCreatedDateTime(DateBase::getCurrentDateTime());

               }
               $category->setCode('ONEOFFICE_'.$item['ID']);
               $category->setParentId($parentId);
               $categoryMapper->save($category);
               if(isset($node['childs']) && count($node['childs'])){
                   $this->travelCreateAssetCategory($node['childs'], $category->getId());
               }
           }
       }
   }

    public function officeassetAction(){
        set_time_limit(300);
        $totalAssetItem = $this->getRequest()->getQuery('totalAssetItem', 0);
        $totalAsset = $this->getRequest()->getQuery('totalAsset', 0);
        $deviceStatusMath = array(
        	'1' => \Asset\Model\Asset::DEVICE_STATUS_USING,
            '2' => \Asset\Model\Asset::DEVICE_STATUS_REPAIR,
            '3' => \Asset\Model\Asset::DEVICE_STATUS_MAINTAIN,
            '4' => \Asset\Model\Asset::DEVICE_STATUS_LIQUIDATION,
            '5' => \Asset\Model\Asset::DEVICE_STATUS_WARRANTY,
            '7' => \Asset\Model\Asset::DEVICE_STATUS_ERROR,
            '8' => \Asset\Model\Asset::DEVICE_STATUS_FULLBOX,
            '10' => \Asset\Model\Asset::DEVICE_STATUS_WELL,
        );

        $statusMath = array(
        	'0' => \Asset\Model\Asset::STATUS_STORAGE,
            '1' => \Asset\Model\Asset::STATUS_ALLOCATED,
        );

        list($adapter, $sql) = $this->createOfficeAdapter();

        $select = $sql->select(['a' => 'assets']);
        $select->order(['date_created ASC']);

        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $adapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage(200);
        $page = $this->getRequest()->getQuery('page', 1);
        $paginator->setCurrentPageNumber($page);

        $assetItemMapper = $this->getServiceLocator()->get('\Asset\Model\AssetItemMapper');
        $assetCateMapper = $this->getServiceLocator()->get('\Asset\Model\AssetCategoryMapper');
        $assetMapper = $this->getServiceLocator()->get('\Asset\Model\AssetMapper');
        foreach ($paginator as $row){
            $row = (array) $row;

            // lấy ra categoryId của asset
            $categoryId = null;
            if($row['parent_id']){
                $assetCate = new \Asset\Model\AssetCategory();
                $assetCate->setCompanyId(10);
                $assetCate->setCode('ONEOFFICE_'.$row['parent_id']);
                $assetCateMapper->isExistedCode($assetCate);
                $categoryId = $assetCate->getId();
            }


            //Lấy ra asset_item Id của asset
            $assetItem = new \Asset\Model\AssetItem();
            $assetItem->setName($row['title']);
            $assetItem->setCompanyId(10);
            if(!$assetItemMapper->isExisted($assetItem)){
                $assetItem->setCreatedDateTime($row['date_created']);
                $assetItem->setCreatedById($this->user()->getIdentity());
                $assetItem->setCategoryId($categoryId);
                $assetItemMapper->save($assetItem);
                $totalAssetItem++;
            }

            $asset = new \Asset\Model\Asset();
            $asset->setItemId($assetItem->getId());
            $asset->setSerialNumber($row['model']);
            $asset->setOneofficeId($row['ID']);
            if($row['status_id'] !== null && isset($statusMath[$row['status_id']])){
                $asset->setStatus($statusMath[$row['status_id']]);
            } else {
                $asset->setStatus(\Asset\Model\Asset::STATUS_STORAGE);
            }
            if($row['use_status_id'] !== null && isset($deviceStatusMath[$row['use_status_id']])){
                $asset->setDeviceStatus($deviceStatusMath[$row['use_status_id']]);
            } else {
                $asset->setDeviceStatus(\Asset\Model\Asset::DEVICE_STATUS_WELL);
            }
            if($row['date_created']){
                $createdDateTime = new \DateTime($row['date_created']);
                $asset->setCreatedDate($createdDateTime->format(DateBase::COMMON_DATE_FORMAT));
                $asset->setCreatedTime($createdDateTime->format(DateBase::COMMON_TIME_FORMAT));
            }
            $asset->setCreatedById($this->user()->getIdentity());
            if($row['time_warranty']){
                $asset->setWarrantyMonths($row['time_warranty']);
            }
            if($row['date_bought']){
                $asset->setBoughtDate($row['date_bought']);
            } else {
                $asset->setBoughtDate(DateBase::toFormat($row['date_created'], DateBase::COMMON_DATE_FORMAT));
            }
            if($assetMapper->isExistedOneofficeId($asset) === false){
                $assetMapper->save($asset);
                $totalAsset++;
            }

        }

        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalAssetItem', $totalAssetItem);
        $this->getViewModel()->setVariable('totalAsset', $totalAsset);
        $this->getViewModel()->setVariable('paginator', $paginator);
        if ($page <= $paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri', Uri::build('/system/tool/officeasset', [
        	    'page' => $page+1,
                'totalAssetItem' => $totalAssetItem,
                'totalAsset' => $totalAsset
            ]));
        }
        $this->getViewModel()->setVariable('defaultRedirectUri', '/system/tool/officeasset');
        return $this->getViewModel();
    }

    public function officedocumentcompanycategoryAction(){
        list($adapter, $sql) = $this->createOfficeAdapter();

        $select = $sql->select(['dc' => 'documents']);
        $select->where([
            'folder' => 'COMPANY',
            'type' => 'FOLDER',
            'is_deleted' => 'no'
        ]);
        $query = $sql->buildSqlString($select);
        $rows = $adapter->query($query, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $arrayRecusived = $this->branchRecusive($rows->toArray());
        $this->travelToCreateCompanyDocumentCategory($arrayRecusived);
        /* $arrs = $this->travel($arrayRecusived);
        foreach ($arrs as $arr){
            echo $arr['ID'].'---- '.str_repeat('---- ', $arr['ord']).$arr['title'].($arr['is_deleted'] == 'yes'?'<span style="color:red;">***</span>':'');
            echo '<br/>';
        } */
        echo 'done!!!!';
        //vdump($arrs);
        die;
    }

    private function mergerTreeBranch($items, &$referentResultArr, $referentArr, $ord=0){
        $result = [];
        if($items && count($items)){
            foreach ($items as $key=>$node){
                $result[$key] = $node;
                $result[$key]['ord'] = $ord;
                unset($referentResultArr[$key]);
                $childs = [];
                if(isset($node['childs']) && count($node['childs'])){
                    foreach ($node['childs'] as $keyChild=>$child){
                        $childs[$keyChild] = $referentArr[$keyChild];
                        unset($referentResultArr[$keyChild]);
                    }
                }
                if(count($childs)){
                    $childs = $this->mergerTreeBranch($childs, $referentResultArr, $referentArr, $ord+1);
                    $result[$key]['childs'] = $childs;
                }
            }
        }
        return $result;
    }

    private function branchRecusive($items){
        if(!$items || !count($items)){
            return [];
        }
        $arrayIndexed = [];
        foreach ($items as $item){
            $arrayIndexed[$item['ID']] = $item;
        }
        // phân tích thành các nhánh 2 lv
        //    o     o     o
        //   /\           |
        //  o  o          o

        $lv1Arr = [];
        foreach ($arrayIndexed as $parent){
            $lv1Arr[$parent['ID']]['obj'] = $parent;
            $lv1Arr[$parent['ID']]['ord'] = 0;
            $childs = [];
            foreach ($arrayIndexed as $item){
                if($parent['ID'] == $item['parent_id']){
                    $childs[$item['ID']]['obj'] = $item;
                    $childs[$item['ID']]['ord'] = 1;
                }
            }
            if(count($childs)){
                $lv1Arr[$parent['ID']]['childs'] = $childs;
            }
        }

        // từ cây lv2 duyệt từng note rồi ghép nhánh
        $result = $lv1Arr;
        foreach ($lv1Arr as $key=>$node){
            if(!isset($result[$key])){
                continue;
            }
            if(isset($node['childs']) && count($node['childs'])){
                $childs = [];
                foreach ($node['childs'] as $keyChild=>$child){
                    $childs[$keyChild] = $lv1Arr[$keyChild];
                    unset($result[$keyChild]);
                }
                if(count($childs)){
                    $childs = $this->mergerTreeBranch($childs, $result, $lv1Arr, 1);
                    $result[$key]['childs'] = $childs;
                }
            }
        }
        return $result;
    }

    private function travelToCreateCompanyDocumentCategory($items, $parentId=null){
       $result = array();
       if($items && count($items)){
           $categoryMapper = $this->getServiceLocator()->get('\Document\Model\DocumentCategoryMapper');
           foreach ($items as $node) {
               $item = $node['obj'];
               $category = new \Document\Model\DocumentCategory();
               $category->setCompanyId(10);
               $category->setName($item['title']);
               $category->setParentId($parentId);
               if(!$categoryMapper->isExisted($category)){
                   $category->setCreatedById(1);
                   $category->setCreatedDateTime($item['date_created']);
                   $category->setType(\Document\Model\Document::OWNERTYPE_COMPANY);
               }
               $category->setOneofficeId($item['ID']);
               $categoryMapper->save($category);
               if(isset($node['childs']) && count($node['childs'])){
                   $this->travelToCreateCompanyDocumentCategory($node['childs'], $category->getId());
               }
           }
       }
    }


    public function officecreatecompanydocumentAction(){
       list($adapter, $sql) = $this->createOfficeAdapter();

       $select = $sql->select(['d' => 'documents']);
       $select->where([
            'folder' => 'COMPANY',
            'type' => 'FILE',
            'is_deleted' => 'no'
        ]);
       $query = $sql->buildSqlString($select);
       echo $query;
       $rows = $adapter->query($query, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

       $categoryMapper = $this->getServiceLocator()->get('\Document\Model\DocumentCategoryMapper');
       $documentMapper = $this->getServiceLocator()->get('\Document\Model\DocumentMapper');
       $matchOneOfficeIds = [];
       $total = 0;
       foreach ($rows->toArray() as $row){
           // math category với office parent_id
           if(!isset($matchOneOfficeIds[$row['parent_id']])){
                $category = new \Document\Model\DocumentCategory();
                $category->setOneofficeId($row['parent_id']);
                if($categoryMapper->isExistedOneofficeCode($category)){
                    $matchOneOfficeIds[$category->getOneofficeId()] = $category->getId();
                } else {
                    echo 'parentId: '.$row['parent_id'].' ID: '.$row['ID'].'<br/>';
                }
           }

           //tạo bản ghi document mới

           $document = new \Document\Model\Document();
           $document->setOneofficeId($row['ID']);
           if($documentMapper->isExistedOneofficeCode($document) === false){
           		$document->setCompanyId(10);
               $document->setOwnerType(\Document\Model\Document::OWNERTYPE_COMPANY);
               $document->setType(\Document\Model\Document::TYPE_DOCUMENT);
               $document->setName($row['title']);
               $document->setContent($row['desc']);
               $document->setCategoryId($matchOneOfficeIds[$row['parent_id']]);
               $document->setCreatedById(1);
               $document->setCreatedDateTime($row['date_created']);
               if(!$documentMapper->isExisted($document)){
                   $documentMapper->save($document);
               }

               echo '<b>category: </b>' . $document->getCategoryId() . '<b>document:</b> '.$document->getName();
               echo '<br/>';
               $total++;
           }
       }
       echo '<b>Tổng: </b>'.$total;
       die;
    }

    public function officeupdatecompanydocumentfileAction(){
    	$officeDirector = BASE_PATH.'/data/oneOfficeFiles';

        list($adapter, $sql) = $this->createOfficeAdapter();

        $select = $sql->select(['df' => 'document_files']);
        $select->join(['d' => 'documents'], 'd.ID=df.document_id', ['created_by_id']);
        $select->where([
            'd.folder' => 'COMPANY',
            'd.type' => 'FILE',
            'd.is_deleted' => 'no'
        ]);
        $select->order(['ID ASC']);

        $query = $sql->buildSqlString($select);
        $rows = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);


        $documentMapper = $this->getServiceLocator()->get('\Document\Model\DocumentMapper');
        $documentCategoryMapper = $this->getServiceLocator()->get('\Document\Model\DocumentCategoryMapper');
        $documentFileMapper = $this->getServiceLocator()->get('\Document\Model\DocumentFileMapper');
        $index =0;
        $totalSize = 0;
        foreach ($rows->toArray() as $row) {
        	$officeUri = $officeDirector.'/document/company/'.$row['created_by_id'].'/'.$row['filename'];
        	if(!file_exists($officeUri)){
        		echo '<b>File: </b>'.$officeUri.'<br/>';
        		echo '<b style="color:red;">File not found!!!</b>';
        		echo '<br/><br/>=============================================<br/>';
        		continue;
        	}

			$document = new \Document\Model\Document();
			$document->setOneofficeId($row['document_id']);
			// check nếu đã có bản ghi trong document thì coi nó là 1 file đính kèm của document
			if($documentMapper->isExistedOneofficeCode($document)){
				$documentFile = new \Document\Model\DocumentFile();
				$documentFile->setDocumentId($document->getId());
				$documentFile->setFileName($row['filename']);
				$documentFile->setCreatedDateTime($document->getCreatedDateTime());
				$uri = Uri::getSavePath($documentFile);

				echo '<b>docfile: </b>'.$document->getName();
				echo '<br/>';
				echo $row['ID'].' --- '.$row['document_id'];
				echo '<br/>';
				echo '<b>Từ: </b>'.$officeUri;
				echo '<br/>';
				echo '<b>Đến: </b>'.$uri.'/'.$documentFile->getFileName();
				echo '<br/>';
				if(is_file($officeUri)){
					$filesize = filesize($officeUri);
					echo '<b>Filesize: </b>'.$filesize;
					$totalSize += $filesize;

					$documentFile->setFileSize($filesize);
				}
				$documentFile->setCreatedById(1);
				if(!$documentFileMapper->isExisted($documentFile)){
					$documentFileMapper->save($documentFile);
					if (!file_exists($uri)) {
						$oldmask = umask(0);
						mkdir($uri, 0777, true);
						umask($oldmask);
					}

					@copy($officeUri, $uri.'/'.$documentFile->getFileName());
					$index++;
					echo '<br/><span style="color:green">Đã chuyển</span>';
				}
				echo '<br/><br/>=============================================<br/>';
			} else {
				// nếu ko thì nghĩa laà file gắn với danh mục, tạo mới 1 document rồi gắn file với document đó
				// nhưng kiểm tra dữ liệu ko có case này nên ko viết
				$documentCategory = new \Document\Model\DocumentCategory();
				$documentCategory->setOneofficeId($row['document_id']);
				if($documentCategoryMapper->isExistedOneofficeCode($documentCategory)){
					echo '<span style="color:blue;">Tạo mới theo dnah mục</span>';
					echo '<br/><br/>=============================================<br/>';
				} else {
					echo '<span style="color:red;">Ko dữ liệu </span>';
					echo $row['ID'].' --- '.$row['document_id'];
					echo '<br/><br/>=============================================<br/>';
				}
			}

        }
        echo '<b>Tổng: </b>'.$index;
		echo '<br/><b>Tổng dung lượng: </b>'.$totalSize;
		die;
   }

	public function officeannoucementAction(){
		$mathType = array(
			'NOTICE' => \Company\Model\Announcement::TYPE_ANNOUNCEMENT,
			'DIPLOMA' => \Company\Model\Announcement::TYPE_DECISION
		);
		$officeDirector = BASE_PATH.'/data/oneOfficeFiles';

		list($adapter, $sql) = $this->createOfficeAdapter();

		$select = $sql->select(['sp' => 'social_posts']);
		$select->where(['type' => ['NOTICE', 'DIPLOMA']]);
		$select->order(['ID ASC']);

		$paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $adapter);
		$paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
		$paginator->setItemCountPerPage(50);
		$page = $this->getRequest()->getQuery('page', 1);
		$totalFile = $this->getRequest()->getQuery('totalFile', 0);
		$totalRecord = $this->getRequest()->getQuery('totalRecord', 0);
		$paginator->setCurrentPageNumber($page);

		$annoucementMapper = $this->getServiceLocator()->get('\Company\Model\AnnouncementMapper');
		$annoucementFileMapper = $this->getServiceLocator()->get('\Company\Model\AnnouncementFileMapper');
		foreach ($paginator as $row){
			$row = (array) $row;

			$annoucement = new \Company\Model\Announcement();
			$annoucement->setCompanyId(10);
			$annoucement->setType($mathType[$row['type']]);
			$annoucement->setTitle($row['title']);
			$annoucement->setContent($row['content']);
			$annoucement->setOneofficeId($row['ID']);
			$annoucement->setCreatedDateTime($row['date_created']);
			$annoucement->setCreatedById(1);
			if($annoucementMapper->isExistedOneofficeCode($annoucement) === false){
				$annoucementMapper->save($annoucement);
				$totalRecord++;
			}

			$select = $sql->select(['sf' => 'social_files']);
			$select->where(['post_id' => $row['ID']]);
			$query = $sql->buildSqlString($select);
			$rows = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
			if($rows->count()){
				foreach ($rows->toArray() as $rowFile){
					$officeUri = $officeDirector.'/social/group';

					if(file_exists($officeUri.'/'.$rowFile['filename'])){
						$annoucementFile  = new \Company\Model\AnnouncementFile();
						$annoucementFile->setAnnouncementId($annoucement->getId());
						$annoucementFile->setCreatedDateTime($annoucement->getCreatedDateTime());
						$filePath = DateBase::toFormat($annoucementFile->getCreatedDateTime(), DateBase::FILEPATH_DATE_FORMAT);
						$annoucementFile->setFilePath($filePath);
						$annoucementFile->setFileName($rowFile['filename']);
						$annoucementFile->setCreatedById(1);
						if($annoucementFileMapper->isExisted($annoucementFile) === false){
							$targetFolder = Uri::getSavePath($annoucementFile);

							if (!file_exists($targetFolder)) {
								$oldmask = umask(0);
								mkdir($targetFolder, 0777, true);
								umask($oldmask);
							}
							@copy($officeUri.'/'.$rowFile['filename'], $targetFolder.'/'.$annoucementFile->getFileName());
							$annoucementFileMapper->save($annoucementFile);
							$totalFile++;
						}

					}
				}
			}

		}
		$this->getViewModel()->setTerminal(true);
		$this->getViewModel()->setVariable('page', $page);
		$this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
		$this->getViewModel()->setVariable('totalRecord', $totalRecord);
		if ($page <= $paginator->count()) {
			$this->getViewModel()->setVariable('redirectUri',
					Uri::build('/system/tool/officeannoucement', [
							'page' => $page+1,
							'totalFile' => $totalFile,
							'totalRecord' => $totalRecord
							]));
		}
		$this->getViewModel()->setVariable('totalFile', $totalFile);
		return $this->getViewModel();
	}

	public function testAction(){
	    list($officeAdapter, $officeSql) = $this->createOfficeBNCAdapter();

	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
	    $dbSql = $this->getServiceLocator()->get('dbSql');

	    $rows = $officeAdapter->query('SELECT * FROM `position_titles`', Adapter::QUERY_MODE_EXECUTE);
	    vdump($rows->toArray());


	    die;
	}

	public function officebncemployeeAction(){
	    list($adapter, $sql) = $this->createOfficeBNCAdapter();
	    $mathGender = array(
	        '1' => \Home\Model\Consts::GENDER_MALE,
	        '2' => \Home\Model\Consts::GENDER_FEMALE
	    );
	    $mathMaritalStatus = array(
	        '4' => \Hrm\Model\Employee::RELATIONSHIP_SINGLE,
	        '5' => \Hrm\Model\Employee::RELATIONSHIP_MARRIED,
	        '6' => \Hrm\Model\Employee::RELATIONSHIP_DIVORCED
	    );
	    $mathNation = array(
	        '1' => 'Kinh',
	        '2' => 'Dân tộc',
	        '3' => 'Tày'
	    );
	    $mathReligion = array(
    		'6' => 'Không',
    		'7' => 'Cơ đốc giáo',
    		'8' => 'Hồi giáo',
    		'9' => 'Phật giáo',
    		'10' => 'Thiên chúa giáo'
		);
	    $mathWorkingStatus = array(
	        'STOP_WORKING' => \Hrm\Model\Employee::WORKING_STATUS_RETIRED,
	        'WORKING' => \Hrm\Model\Employee::WORKING_STATUS_WORKING,
	        'NOT_WORKING' => \Hrm\Model\Employee::WORKING_STATUS_PAUSE,
	        'PRACTICE' => \Hrm\Model\Employee::WORKING_STATUS_TRIAL
	    );
	    $mathQuitReason = array(
	        '10' => \Hrm\Model\Employee::QUIT_REASON_WORK_ENVIRONMENT,
	        '11' => \Hrm\Model\Employee::QUIT_REASON_HEALTHY,
	        '12' => \Hrm\Model\Employee::QUIT_REASON_SALARY,
	        '13' => \Hrm\Model\Employee::QUIT_REASON_WORK_PRESSURE,
	        '14' => \Hrm\Model\Employee::QUIT_REASON_WORK_SUITABLE,
	        '15' => \Hrm\Model\Employee::QUIT_REASON_FORCED_TO_RESIGN,
	        '16' => \Hrm\Model\Employee::QUIT_REASON_FAMILY,
	    );
	    $mathWorkPlaces = [
	    '4' => 'Hà Nội',
	    '5' => 'Hồ Chí Minh',
	    '6' => 'Đà Nẵng'
	        ];
	    $mathWorkCityId = [
	    '4' => '2',
	    '5' => '3',
	    '6' => '65'
	        ];
	    $mathWorkPosition = [];
	    $select = $sql->select(['p' => 'positions']);
	    $rows = $adapter->query($sql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);

	    if($rows->count()){
	        foreach ($rows->toArray() as $row){
	            $mathWorkPosition[$row['ID']] =  $row['title'];
	        }
	    }

	    $mathWorkPositionTitle = [
	    '1' => 'Phó Giám Đốc',
	    '2' => 'Giám Đốc',
	    '3' => 'Cộng tác viên',
	    '4' => 'Nhân viên',
	    '5' => 'Trưởng phòng',
	    '6' => 'Trưởng nhóm',
	    '7' => 'Quản lý',
	    '8' => 'Nhân viên Partime',
	    '9' => 'Tổng giám đốc',
	    '10' => 'Phó tổng giám đốc',
	    '11' => 'Chủ tịch HĐQT',
	    ];

	    $select = $sql->select(['p' => 'personnels']);
	    $select->order(['ID ASC']);

	    $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $adapter);
	    $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
	    $paginator->setItemCountPerPage(50);
	    $page = $this->getRequest()->getQuery('page', 1);
	    $totalEmployee = $this->getRequest()->getQuery('totalEmployee', 0);
	    $totalUpdate = $this->getRequest()->getQuery('totalUpdate', 0);
	    $paginator->setCurrentPageNumber($page);

	    $employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');
	    $departmentMapper = $this->getServiceLocator()->get('\Company\Model\DepartmentMapper');
	    $companyMapper = $this->getServiceLocator()->get('\Company\Model\CompanyMapper');
	    $titleMapper = $this->getServiceLocator()->get('\Company\Model\TitleMapper');
	    $userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
	    $mobileFilter = new \Home\Filter\Mobile();
	    $mobileValidate = new \Zend\Validator\StringLength();
	    $mobileValidate->setMin(10);
	    $mobileValidate->setMax(11);
	    $companyIdsInGroup = $this->company()->getCompanyIdsInGroup();

	    $codeStart = $employeeMapper->generateCode(array('companyIds' => $this->company()->getCompanyIdsInGroup()));
	    foreach ($paginator as $row){
	        $row = (array) $row;
	        $employee = new \Hrm\Model\Employee();
	        $employee->setOneofficeId($row['ID']);
	        $employee->setPrivateSource(\Home\Model\Consts::PRIVATE_SOURCE_OFFICEBNC);
	        if(!$employeeMapper->isExistedOneofficeId($employee)){
	            $employee->setCode($codeStart++);
	        }



	        if(!$employee->getFirstName() && $row['first_name']){
	            $employee->setFirstName($row['first_name']?:'');
	        } elseif (!$employee->getFirstName()){
	            $employee->setFirstName('');
	        }
	        if(!$employee->getLastName() && $row['last_name']){
	            $employee->setLastName($row['last_name']);
	        } elseif (!$employee->getLastName()){
	            $employee->setLastName('');
	        }
	        //if(!$employee->getFullName()){
	        if($row['name']){
	            $employee->setFullName($row['name']);
	            list($lastName, $middleName, $firstName) = Format::splitFullName($employee->getFullName());
	            $employee->setFirstName($firstName);
	            $employee->setMiddleName($middleName);
	            $employee->setLastName($lastName);
	        } else {
	            $employee->setFullName(Format::displaySetItems([$employee->getFirstName(), $employee->getMiddleName(), $employee->getLastName()], ' '));
	        }

	        //}
	        if(!$employee->getGender() && isset($mathGender[$row['gender']])){
	            $employee->setGender($mathGender[$row['gender']]);
	        } else {
	            $employee->setGender(\Home\Model\Consts::GENDER_MALE);
	        }
	        if(!$employee->getMaritalStatus() && $row['marital_status'] && isset($mathMaritalStatus[$row['marital_status']])){
	            $employee->setMaritalStatus($mathMaritalStatus[$row['marital_status']]);
	        } else {
	            $employee->setMaritalStatus(\Hrm\Model\Employee::RELATIONSHIP_SINGLE);
	        }
	        if(!$employee->getBirthdate() && $row['birthday']){
	            $employee->setBirthdate($row['birthday']);
	        }
	        if(!$employee->getBirthplace() && $row['place_of_birth']){
	            $employee->setBirthplace($this->mathPlace($row['place_of_birth']));
	        }

	        if(!$employee->getIdentification() && $row['private_code']){
	            $employee->setIdentification($row['private_code']);
	        }
	        if($row['private_code_place']){
	            $employee->setIdentificationPlace($this->mathPlace($row['private_code_place']));
	        }
	        if(!$employee->getIdentificationDate() && $row['private_code_date']){
	            $employee->setIdentificationDate($row['private_code_date']);
	        }
	        // fix cứng luôn, bên kia cũng ko có nhân sự nước ngoài
	        if(!$employee->getCountryId()){
	            $employee->setCountryId(243);
	        }
	        if(!$employee->getNation() && $row['nationality'] && isset($mathNation[$row['nationality']])){
	            $employee->setNation($mathNation[$row['nationality']]);
	        }
	        if(!$employee->getReligion() && $row['religious'] && isset($mathReligion[$row['religious']])){
	            $employee->setReligion($mathReligion[$row['religious']]);
	        }
	        if(!$employee->getCreatedDateTime()){
	            $employee->setCreatedDateTime($row['date_created']);
	        }
	        //@TODO fix đã

	        /* if(!$employee->getStartedDate() && $row['job_date_join']){
	         $employee->setStartedDate($row['job_date_join']);
	        } */
	        // Ngày nhập hồ sơ
	        if($row['job_date_join']){
	            $employee->setReceiveContractDate($row['job_date_join']);
	        } else {
	            $employee->setReceiveContractDate(null);
	        }
	        // ngày vào thực tế
	        if($row['job_reldate_join']){
	            $employee->setStartedDate($row['job_reldate_join']);
	        } else {
	            $employee->setStartedDate(null);
	        }
	        // Nơi làm việc
	        if($row['work_place'] && isset($mathWorkCityId[$row['work_place']])){
	            $employee->setWorkingCityId($mathWorkCityId[$row['work_place']]);
	        }
	        //  nguyên quán - quê quán
	        if($row['origin_id']){
	            $employee->setHometown($this->mathPlace($row['origin_id']));
	        }
	        if(!$employee->getDepartmentId() && $row['department_id']){
	            $deparmentInfor = $this->matchDepartment(
	                $row['department_id'], \Home\Model\Consts::PRIVATE_SOURCE_OFFICEBNC);
	            if(!$employee->getCompanyId() && isset($deparmentInfor['companyId'])){
	                $employee->setCompanyId($deparmentInfor['companyId']);
	            }
	            if(!$employee->getDepartmentId() && isset($deparmentInfor['departmentId'])){
	                $employee->setDepartmentId($deparmentInfor['departmentId']);
	            }
	        }
	        if (!$employee->getCompanyId()){
	            $employee->setCompanyId(10);
	        }
	        if($employee->getCompanyId() != 1 && $row['job_title']){
	            if(isset($mathWorkPositionTitle[$row['job_title']]))
	                $title = new \Company\Model\Title();
	            $title->setCompanyId($employee->getCompanyId());
	            $title->setName($mathWorkPositionTitle[$row['job_title']]);

	            if(!$titleMapper->isExisted($title)){
	                $title->setCreatedById($this->user()->getIdentity());
	                $title->setCreatedDateTime(DateBase::getCurrentDateTime());
	                $titleMapper->save($title);
	            }

	            $employee->setTitleId($title->getId());

	        }

	        if(!$employee->getWorkingStatus() && $row['job_status'] && isset($mathWorkingStatus[$row['job_status']])){
	            $employee->setWorkingStatus($mathWorkingStatus[$row['job_status']]);
	        }
	        if(!$employee->getTaxCode() && $row['job_tax']){
	            $employee->setTaxCode($row['job_tax']);
	        }
	        if(!$employee->getQuitDate() && $row['job_date_out']){
	            $employee->setQuitDate($row['job_date_out']);
	        }
	        if(!$employee->getQuitReason() && $row['job_out_reason'] && isset($mathQuitReason[$row['job_out_reason']])){
	            $employee->setQuitReason($mathQuitReason[$row['job_out_reason']]);
	        }

	        if(!$employee->getEmail() && $row['email'] && strlen($row['email']) < 225){
	            $employee->setEmail($row['email']);
	        }
	        if(!$employee->getYahoo() && $row['yahoo'] && strlen($row['yahoo']) < 100){
	            $employee->setYahoo($row['yahoo']);
	        }
	        if(!$employee->getSkype() && $row['skype'] && strlen($row['skype']) < 100){
	            $employee->setSkype($row['skype']);
	        }
	        if(!$employee->getFacebook() && $row['facebook'] && strlen($row['facebook']) < 100){
	            $employee->setFacebook($row['facebook']);
	        }
	        if(!$employee->getMobile() && $row['mobile']){
	            $mobile = $mobileFilter->filter($row['mobile']);
	            if($mobile  && $mobileValidate->isValid($mobile)){
	                $employee->setMobile($mobile?:null);
	            }
	        }
	        if(!$employee->getMobile() && $row['phone']){
	            $phone = $mobileFilter->filter($row['phone']);
	            if($phone && $mobileValidate->isValid($phone)){
	                $employee->setMobile($phone?:null);
	            }

	        }
	        if(!$employee->getPermanentAddress() && $row['home_address']){
	            if(!$row['home_address_state']){
	                $employee->setPermanentAddress(substr($row['home_address'], 0, 225));
	            } else {
	                $home_address_state = $this->mathPlace($row['home_address_state']);
	                $employee->setPermanentAddress(substr(Format::displaySetItems(
	                    array($row['home_address'], $home_address_state), ', '), 0, 225));
	            }
	        }

	        if(!$employee->getTemporateAddress() && $row['current_address']){
	            if(strpos($row['current_address'], '\xC3') === false){
	                if(!$row['current_address_state']){
	                    $employee->setTemporateAddress(substr($row['current_address'], 0, 225));

	                } else {
	                    $home_address_state = $this->mathPlace($row['current_address_state']);
	                    $employee->setTemporateAddress(substr(Format::displaySetItems(
	                        array($row['current_address'], $home_address_state), ', '), 0, 225));
	                }
	            }

	        }

	        if($employee->getExtraContent()){
	            $extraContent = json_decode($employee->getExtraContent(), true);
	            if($row['position_id'] && isset($mathWorkPosition[$row['position_id']])){
	                $extraContent['workPosition'] = $mathWorkPosition[$row['position_id']];
	            }
	            $employee->setExtraContent(json_encode($extraContent));
	        }

	        // check nếu là employee mới mới update các thông tin phía sau, nếu ko save lại chạy tiếp
	        if($employee->getId()){
	            $employeeMapper->save($employee);
	            $totalUpdate++;
	            continue;
	        }

	        if(!$employee->getCreatedDateTime() && $row['date_created']){
	            if($row['date_created'] != '0000-00-00 00:00:00'){
	                $employee->setCreatedDateTime($row['date_created']);
	            } else {
	                $employee->setCreatedDateTime(DateBase::getCurrentDateTime());
	            }
	        } else {
	            $employee->setCreatedDateTime(DateBase::getCurrentDateTime());
	        }
	        if(!$employee->getCreatedById()){
	            $employee->setCreatedById(1);
	        }

	        if($row['department_id']){
	            $deparmentInfor = $this->matchDepartment($row['department_id']);
	            if(!$employee->getCompanyId() && isset($deparmentInfor['companyId'])){
	                $employee->setCompanyId($deparmentInfor['companyId']);
	            }
	            if(!$employee->getDepartmentId() && isset($deparmentInfor['departmentId'])){
	                $employee->setDepartmentId($deparmentInfor['departmentId']);
	            }
	        }
	        if (!$employee->getCompanyId()){
	            $employee->setCompanyId(10);
	        }
	        //tạo mới title nếu chưa có

	        $employeeMapper->save($employee);
	        $totalEmployee++;
	    }
	    $this->getViewModel()->setTerminal(true);
	    $this->getViewModel()->setVariable('paginator', $paginator);
	    $this->getViewModel()->setVariable('page', $page);
	    $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
	    $this->getViewModel()->setVariable('totalEmployee', $totalEmployee);
	    $this->getViewModel()->setVariable('totalUpdate', $totalUpdate);
	    if ($page <= $paginator->count()) {
	        $this->getViewModel()->setVariable('redirectUri',
	            Uri::build('/system/tool/officebncemployee', [
	                'page' => $page+1,
	                'totalEmployee' => $totalEmployee,
	                'totalUpdate' => $totalUpdate
	                ]));
	    }
	    return $this->getViewModel();
	}

	/**
	 * @author KienNN
	 */
	public function officeemployeeAction(){
	    list($adapter, $sql) = $this->createOfficeAdapter();
		$mathGender = array(
		'1' => \Home\Model\Consts::GENDER_MALE,
		'2' => \Home\Model\Consts::GENDER_FEMALE
		);
		$mathMaritalStatus = array(
		'4' => \Hrm\Model\Employee::RELATIONSHIP_SINGLE,
		'5' => \Hrm\Model\Employee::RELATIONSHIP_MARRIED,
		'6' => \Hrm\Model\Employee::RELATIONSHIP_DIVORCED
		);
		$mathNation = array(
		'1' => 'Kinh',
		'2' => 'Dân tộc',
		'3' => 'Tày'
		);
		$mathReligion = array(
		'6' => 'Không',
		'7' => 'Cơ đốc giáo',
		'8' => 'Hồi giáo',
		'9' => 'Phật giáo',
		'10' => 'Thiên chúa giáo'
		);
		$mathWorkingStatus = array(
			'STOP_WORKING' => \Hrm\Model\Employee::WORKING_STATUS_RETIRED,
			'WORKING' => \Hrm\Model\Employee::WORKING_STATUS_WORKING,
			'NOT_WORKING' => \Hrm\Model\Employee::WORKING_STATUS_PAUSE,
			'PRACTICE' => \Hrm\Model\Employee::WORKING_STATUS_TRIAL
		);
		$mathQuitReason = array(
		'10' => \Hrm\Model\Employee::QUIT_REASON_WORK_ENVIRONMENT,
		'11' => \Hrm\Model\Employee::QUIT_REASON_HEALTHY,
		'12' => \Hrm\Model\Employee::QUIT_REASON_SALARY,
		'13' => \Hrm\Model\Employee::QUIT_REASON_WORK_PRESSURE,
		'14' => \Hrm\Model\Employee::QUIT_REASON_WORK_SUITABLE,
		'15' => \Hrm\Model\Employee::QUIT_REASON_FORCED_TO_RESIGN,
		'16' => \Hrm\Model\Employee::QUIT_REASON_FAMILY,
		);
		$mathWorkPlaces = [
			'4' => 'Hà Nội',
			'5' => 'Hồ Chí Minh',
			'6' => 'Đà Nẵng'
		];
		$mathWorkCityId = [
		'4' => '2',
		'5' => '3',
		'6' => '65'
		];
		$mathWorkPosition = [];
		$select = $sql->select(['p' => 'positions']);
		$rows = $adapter->query($sql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);

		if($rows->count()){
		    foreach ($rows->toArray() as $row){
		        $mathWorkPosition[$row['ID']] =  $row['title'];
		    }
		}


        $mathWorkPositionTitle = [
			'1' => 'Phó Giám Đốc',
			'2' => 'Giám Đốc',
			'3' => 'Cộng tác viên',
			'4' => 'Nhân viên',
			'5' => 'Trưởng phòng',
			'6' => 'Trưởng nhóm',
			'7' => 'Quản lý',
			'8' => 'Nhân viên Partime',
			'9' => 'Tổng giám đốc',
			'10' => 'Phó tổng giám đốc',
			'11' => 'Chủ tịch HĐQT',
        ];



		$select = $sql->select(['p' => 'personnels']);
		$select->order(['ID ASC']);

		$paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $adapter);
		$paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
		$paginator->setItemCountPerPage(50);
		$page = $this->getRequest()->getQuery('page', 1);
		$totalEmployee = $this->getRequest()->getQuery('totalEmployee', 0);
		$totalUpdate = $this->getRequest()->getQuery('totalUpdate', 0);
		$paginator->setCurrentPageNumber($page);

		$employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');
		$departmentMapper = $this->getServiceLocator()->get('\Company\Model\DepartmentMapper');
		$companyMapper = $this->getServiceLocator()->get('\Company\Model\CompanyMapper');
		$titleMapper = $this->getServiceLocator()->get('\Company\Model\TitleMapper');
		$userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
		$mobileFilter = new \Home\Filter\Mobile();
		$mobileValidate = new \Zend\Validator\StringLength();
		$mobileValidate->setMin(10);
		$mobileValidate->setMax(11);
		$companyIdsInGroup = $this->company()->getCompanyIdsInGroup();
		foreach ($paginator as $row){
			$row = (array) $row;
			$employee = new \Hrm\Model\Employee();
			$employee->setCode($row['code']);
			$employee->addOption('companyIds', $companyIdsInGroup);
			$employeeMapper->isExistedCode($employee);

			// check nếu employee đã có trong hệ thống sẽ update lại 1 số thông tin
			$employee->setOneofficeId($row['ID']);

			if(!$employee->getFirstName() && $row['first_name']){
				$employee->setFirstName($row['first_name']?:'');
			} elseif (!$employee->getFirstName()){
				$employee->setFirstName('');
			}
			if(!$employee->getLastName() && $row['last_name']){
				$employee->setLastName($row['last_name']);
			} elseif (!$employee->getLastName()){
				$employee->setLastName('');
			}
			//if(!$employee->getFullName()){
				if($row['name']){
					$employee->setFullName($row['name']);
					list($lastName, $middleName, $firstName) = Format::splitFullName($employee->getFullName());
					$employee->setFirstName($firstName);
					$employee->setMiddleName($middleName);
					$employee->setLastName($lastName);
				} else {
					$employee->setFullName(Format::displaySetItems([$employee->getFirstName(), $employee->getMiddleName(), $employee->getLastName()], ' '));
				}

			//}
			if(!$employee->getGender() && isset($mathGender[$row['gender']])){
				$employee->setGender($mathGender[$row['gender']]);
			} else {
				$employee->setGender(\Home\Model\Consts::GENDER_MALE);
			}
			if(!$employee->getMaritalStatus() && $row['marital_status'] && isset($mathMaritalStatus[$row['marital_status']])){
				$employee->setMaritalStatus($mathMaritalStatus[$row['marital_status']]);
			} else {
				$employee->setMaritalStatus(\Hrm\Model\Employee::RELATIONSHIP_SINGLE);
			}
			if(!$employee->getBirthdate() && $row['birthday']){
				$employee->setBirthdate($row['birthday']);
			}
			if(!$employee->getBirthplace() && $row['place_of_birth']){
				$employee->setBirthplace($this->mathPlace($row['place_of_birth']));
			}

			/* if(!$employee->getHometown() && $row['home_address']){
				if(!$row['home_address_state']){
					$employee->setHometown($row['home_address']);
				} else {
					$home_address_state = $this->mathPlace($row['home_address_state']);
					$employee->setHometown(Format::displaySetItems(
							array($row['home_address'], $home_address_state), ', '));
				}
			} */
			if(!$employee->getIdentification() && $row['private_code']){
				$employee->setIdentification($row['private_code']);
			}
			if($row['private_code_place']){
				$employee->setIdentificationPlace($this->mathPlace($row['private_code_place']));
			}
			if(!$employee->getIdentificationDate() && $row['private_code_date']){
				$employee->setIdentificationDate($row['private_code_date']);
			}
			// fix cứng luôn, bên kia cũng ko có nhân sự nước ngoài
			if(!$employee->getCountryId()){
				$employee->setCountryId(243);
			}
			if(!$employee->getNation() && $row['nationality'] && isset($mathNation[$row['nationality']])){
				$employee->setNation($mathNation[$row['nationality']]);
			}
			if(!$employee->getReligion() && $row['religious'] && isset($mathReligion[$row['religious']])){
				$employee->setReligion($mathReligion[$row['religious']]);
			}
			if(!$employee->getCreatedDateTime()){
				$employee->setCreatedDateTime($row['date_created']);
			}
			//@TODO fix đã

			/* if(!$employee->getStartedDate() && $row['job_date_join']){
				$employee->setStartedDate($row['job_date_join']);
			} */
			// Ngày nhập hồ sơ
			if($row['job_date_join']){
			    $employee->setReceiveContractDate($row['job_date_join']);
			} else {
			    $employee->setReceiveContractDate(null);
			}
			// ngày vào thực tế
			if($row['job_reldate_join']){
			    $employee->setStartedDate($row['job_reldate_join']);
			} else {
			    $employee->setStartedDate(null);
			}
			// Nơi làm việc
			if($row['work_place'] && isset($mathWorkCityId[$row['work_place']])){
				$employee->setWorkingCityId($mathWorkCityId[$row['work_place']]);
			}
			//  nguyên quán - quê quán
			if($row['origin_id']){
			    $employee->setHometown($this->mathPlace($row['origin_id']));
			}
			if(!$employee->getDepartmentId() && $row['department_id']){
			    $deparmentInfor = $this->matchDepartment($row['department_id']);
			    if(!$employee->getCompanyId() && isset($deparmentInfor['companyId'])){
			        $employee->setCompanyId($deparmentInfor['companyId']);
			    }
			    if(!$employee->getDepartmentId() && isset($deparmentInfor['departmentId'])){
			        $employee->setDepartmentId($deparmentInfor['departmentId']);
			    }
			}
			if (!$employee->getCompanyId()){
			    $employee->setCompanyId(10);
			}
			if($employee->getCompanyId() != 1 && $row['job_title']){
			    if(isset($mathWorkPositionTitle[$row['job_title']]))
			        $title = new \Company\Model\Title();
			        $title->setCompanyId($employee->getCompanyId());
			        $title->setName($mathWorkPositionTitle[$row['job_title']]);

			        if(!$titleMapper->isExisted($title)){
			            $title->setCreatedById($this->user()->getIdentity());
			            $title->setCreatedDateTime(DateBase::getCurrentDateTime());
			            $titleMapper->save($title);
			        }

			        $employee->setTitleId($title->getId());

			}

			if(!$employee->getWorkingStatus() && $row['job_status'] && isset($mathWorkingStatus[$row['job_status']])){
				$employee->setWorkingStatus($mathWorkingStatus[$row['job_status']]);
			}
			if(!$employee->getTaxCode() && $row['job_tax']){
				$employee->setTaxCode($row['job_tax']);
			}
			if(!$employee->getQuitDate() && $row['job_date_out']){
				$employee->setQuitDate($row['job_date_out']);
			}
			if(!$employee->getQuitReason() && $row['job_out_reason'] && isset($mathQuitReason[$row['job_out_reason']])){
				$employee->setQuitReason($mathQuitReason[$row['job_out_reason']]);
			}

			if(!$employee->getEmail() && $row['email'] && strlen($row['email']) < 225){
				$employee->setEmail($row['email']);
			}
			if(!$employee->getYahoo() && $row['yahoo'] && strlen($row['yahoo']) < 100){
				$employee->setYahoo($row['yahoo']);
			}
			if(!$employee->getSkype() && $row['skype'] && strlen($row['skype']) < 100){
				$employee->setSkype($row['skype']);
			}
			if(!$employee->getFacebook() && $row['facebook'] && strlen($row['facebook']) < 100){
				$employee->setFacebook($row['facebook']);
			}
			if(!$employee->getMobile() && $row['mobile']){
				$mobile = $mobileFilter->filter($row['mobile']);
				if($mobile  && $mobileValidate->isValid($mobile)){
					$employee->setMobile($mobile?:null);
				}
			}
			if(!$employee->getMobile() && $row['phone']){
				$phone = $mobileFilter->filter($row['phone']);
				if($phone && $mobileValidate->isValid($phone)){
					$employee->setMobile($phone?:null);
				}

			}
			if(!$employee->getPermanentAddress() && $row['home_address']){
				if(!$row['home_address_state']){
					$employee->setPermanentAddress(substr($row['home_address'], 0, 225));
				} else {
					$home_address_state = $this->mathPlace($row['home_address_state']);
					$employee->setPermanentAddress(substr(Format::displaySetItems(
							array($row['home_address'], $home_address_state), ', '), 0, 225));
				}
			}

			if(!$employee->getTemporateAddress() && $row['current_address']){
				if(!$row['current_address_state']){
					$employee->setTemporateAddress(substr($row['current_address'], 0, 225));

				} else {
					$home_address_state = $this->mathPlace($row['current_address_state']);
					$employee->setTemporateAddress(substr(Format::displaySetItems(
							array($row['current_address'], $home_address_state), ', '), 0, 225));
				}
			}

			if(!$employee->getBirthCertificate() && $row['birth_certificate']){
				$employee->setBirthCertificate(\Hrm\Model\Employee::BIRTH_CERTIFICATE);
			}

            if($employee->getExtraContent()){
                $extraContent = json_decode($employee->getExtraContent(), true);
                if($row['position_id'] && isset($mathWorkPosition[$row['position_id']])){
                    $extraContent['workPosition'] = $mathWorkPosition[$row['position_id']];
                }
                $employee->setExtraContent(json_encode($extraContent));
            }

			// check nếu là employee mới mới update các thông tin phía sau, nếu ko save lại chạy tiếp
			if($employee->getId()){
				$employeeMapper->save($employee);
				$totalUpdate++;
				continue;
			}

			if(!$employee->getCreatedDateTime() && $row['date_created']){
				if($row['date_created'] != '0000-00-00 00:00:00'){
					$employee->setCreatedDateTime($row['date_created']);
				} else {
					$employee->setCreatedDateTime(DateBase::getCurrentDateTime());
				}
			} else {
				$employee->setCreatedDateTime(DateBase::getCurrentDateTime());
			}
			if(!$employee->getCreatedById()){
				$employee->setCreatedById(1);
			}

			if($row['department_id']){
				$deparmentInfor = $this->matchDepartment($row['department_id']);
				if(!$employee->getCompanyId() && isset($deparmentInfor['companyId'])){
					$employee->setCompanyId($deparmentInfor['companyId']);
				}
				if(!$employee->getDepartmentId() && isset($deparmentInfor['departmentId'])){
					$employee->setDepartmentId($deparmentInfor['departmentId']);
				}
			}
			if (!$employee->getCompanyId()){
				$employee->setCompanyId(10);
			}
			//tạo mới title nếu chưa có

			$employeeMapper->save($employee);
			$totalEmployee++;
		}
		$this->getViewModel()->setTerminal(true);
		$this->getViewModel()->setVariable('paginator', $paginator);
		$this->getViewModel()->setVariable('page', $page);
		$this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
		$this->getViewModel()->setVariable('totalEmployee', $totalEmployee);
		$this->getViewModel()->setVariable('totalUpdate', $totalUpdate);
		if ($page <= $paginator->count()) {
			$this->getViewModel()->setVariable('redirectUri',
					Uri::build('/system/tool/officeemployee', [
							'page' => $page+1,
							'totalEmployee' => $totalEmployee,
							'totalUpdate' => $totalUpdate
							]));
		}
		return $this->getViewModel();
	}



	/**
	 * Logic:
	 * lấy danh sach user có personel_id + personel_code
	 * 		dùng personelId tham chiếu với bảng employee, lấy ra employee
	 * 		xem nếu employee đó đã có user thì continue
	 * 		nếu employee chưa gắn với user thì
	 * 			lấy thông tin email, check user đã  tồn tại chưa
	 * 			nếu đã tồn tại, check xem user đó đã gắn với employee nào chưa
	 * 				nếu chưa thì gắn luôn với employee
	 * 				nếu đã tôn tại, continue
	 * 			nếu chưa tồn tại user, tạo mới rồi gắn với employee
	 */
	public function officeuserAction(){
		list($officeAdapter, $officeSql) = $this->createOfficeAdapter();
		$dbSql = $this->getServiceLocator()->get('dbSql');
		$dbAdapter = $this->getServiceLocator()->get('dbAdapter');

		// lấy danh sach user có personel_id
		$select = $officeSql->select(['u' => 'users']);
		$select->where(['personnel_id IS NOT NULL']);
		$select->order(['id ASC']);

		$paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $officeAdapter);
		$paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
		$paginator->setItemCountPerPage(50);
		$page = $this->getRequest()->getQuery('page', 1);
		$totalUpdate = $this->getRequest()->getQuery('totalUpdate', 0);
		$totalInsert = $this->getRequest()->getQuery('totalInsert', 0);
		$paginator->setCurrentPageNumber($page);

		$employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');
		foreach ($paginator as $row){
			$row = (array) $row;
			// dùng personelId tham chiếu với bảng employee, lấy ra employee
			$employee = new \Hrm\Model\Employee();
			$employee->setOneofficeId($row['personnel_id']);
			if(!$employeeMapper->isExistedOneofficeId($employee)){
				continue;
			}
			// xem nếu employee đó đã có user thì bỏ qua
			if($employee->getUserId()){
				continue;
			}
			if(!$row['email']){
				continue;
			}
			// nếu employee chưa gắn với user thfi tạo user
			$user = new \User\Model\User();
			$userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
			// lấy thông tin email, check user đã  tồn tại chưa
			$user = $userMapper->get(null, null, $row['email']);
			if($user){
				// nếu đã tồn tại, check xem user đó đã gắn với employee nào chưa
				$select = $dbSql->select(['e' => \Hrm\Model\EmployeeMapper::TABLE_NAME]);
				$select->where(['userId' => $user->getId()]);
				$rowEmployee = $dbAdapter->query($dbSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
				// nếu đã tôn tại, continue
				if($rowEmployee->count()){
					continue;
				}
				//nếu chưa thì gắn luôn với employee
				$employee->setUserId($user->getId());
				$employeeMapper->save($employee);

				$user->setOneofficeId($row['ID']);
				$userMapper->save($user);
				$totalUpdate++;

			} else {
				// nếu chưa tồn tại user, tạo mới rồi gắn với employee
				$user = new \User\Model\User();
				$username = $this->getValidUsername($row['username']);
				$user->setUsername($username);

				$user->setFullName($employee->getFullName());
				$user->setEmail($row['email']);
				$user->setRole(\User\Model\User::ROLE_MEMBER);
				$user->setGender($employee->getGender());
				$user->setFullName($employee->getFullName());
				$user->setBirthdate($employee->getBirthdate());
				$user->setActive(1);
				$user->setSalt($user->generateSalt());
				$user->setPassword($user->createPassword('vatgia.com'));
				if($row['is_deleted'] == 'yes'){
					$user->setLocked(1);
				}
				if($row['date_created'] && $row['date_created'] != '0000-00-00 00:00:00'){
					$user->setCreatedDateTime($row['date_created']);
					$user->setCreatedDate(DateBase::toFormat($row['date_created'], DateBase::COMMON_DATE_FORMAT));
				} else {
					$user->setCreatedDateTime(DateBase::getCurrentDateTime());
					$user->setCreatedDate(DateBase::getCurrentDate());
				}

				$user->setCreatedById(1);
				$user->setEmployeeCode($employee->getCode());
				$user->setOneofficeId($row['ID']);
				$userMapper->save($user);

				$employee->setUserId($user->getId());
				$employeeMapper->save($employee);
				$totalInsert++;
			}

		}

		$this->getViewModel()->setTerminal(true);
		$this->getViewModel()->setVariable('paginator', $paginator);
		$this->getViewModel()->setVariable('page', $page);
		$this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
		$this->getViewModel()->setVariable('totalInsert', $totalInsert);
		$this->getViewModel()->setVariable('totalUpdate', $totalUpdate);
		if ($page <= $paginator->count()) {
			$this->getViewModel()->setVariable('redirectUri',
					Uri::build('/system/tool/officeuser', [
							'page' => $page+1,
							'totalInsert' => $totalInsert,
							'totalUpdate' => $totalUpdate
							]));
		}
		return $this->getViewModel();
	}

	public function officebncuserAction(){
	    list($officeAdapter, $officeSql) = $this->createOfficeBNCAdapter();
	    $dbSql = $this->getServiceLocator()->get('dbSql');
	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

	    // lấy danh sach user có personel_id
	    $select = $officeSql->select(['u' => 'users']);
	    $select->where(['personnel_id IS NOT NULL']);
	    $select->order(['id ASC']);

	    $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $officeAdapter);
	    $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
	    $paginator->setItemCountPerPage(50);
	    $page = $this->getRequest()->getQuery('page', 1);
	    $totalUpdate = $this->getRequest()->getQuery('totalUpdate', 0);
	    $totalInsert = $this->getRequest()->getQuery('totalInsert', 0);
	    $paginator->setCurrentPageNumber($page);

	    $employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');
	    foreach ($paginator as $row){
	        $row = (array) $row;
	        // dùng personelId tham chiếu với bảng employee, lấy ra employee
	        $employee = new \Hrm\Model\Employee();
	        $employee->setOneofficeId($row['personnel_id']);
	        $employee->setPrivateSource(\Home\Model\Consts::PRIVATE_SOURCE_OFFICEBNC);
	        if(!$employeeMapper->isExistedOneofficeId($employee)){
	            continue;
	        }
	        // xem nếu employee đó đã có user thì bỏ qua
	        if($employee->getUserId()){
	            continue;
	        }
	        if(!$row['email']){
	            continue;
	        }
	        // nếu employee chưa gắn với user thfi tạo user
	        $user = new \User\Model\User();
	        $userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
	        // lấy thông tin email, check user đã  tồn tại chưa
	        $user = $userMapper->get(null, null, $row['email']);
	        if($user){
	            // nếu đã tồn tại, check xem user đó đã gắn với employee nào chưa
	            $select = $dbSql->select(['e' => \Hrm\Model\EmployeeMapper::TABLE_NAME]);
	            $select->where(['userId' => $user->getId()]);
	            $rowEmployee = $dbAdapter->query($dbSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
	            // nếu đã tôn tại, continue
	            if($rowEmployee->count()){
	                continue;
	            }
	            //nếu chưa thì gắn luôn với employee
	            $employee->setUserId($user->getId());
	            $employeeMapper->save($employee);

	            $user->setOneofficeId($row['ID']);
	            $userMapper->save($user);
	            $totalUpdate++;

	        } else {
	            // nếu chưa tồn tại user, tạo mới rồi gắn với employee
	            $user = new \User\Model\User();
	            $username = $this->getValidUsername($row['username']);
	            $user->setUsername($username);

	            $user->setFullName($employee->getFullName());
	            $user->setEmail($row['email']);
	            $user->setRole(\User\Model\User::ROLE_MEMBER);
	            $user->setGender($employee->getGender());
	            $user->setFullName($employee->getFullName());
	            $user->setBirthdate($employee->getBirthdate());
	            $user->setActive(1);
	            $user->setSalt($user->generateSalt());
	            $user->setPassword($user->createPassword('vatgia.com'));
	            if($row['is_deleted'] == 'yes'){
	                $user->setLocked(1);
	            }
	            if($row['date_created'] && $row['date_created'] != '0000-00-00 00:00:00'){
	                $user->setCreatedDateTime($row['date_created']);
	                $user->setCreatedDate(DateBase::toFormat($row['date_created'], DateBase::COMMON_DATE_FORMAT));
	            } else {
	                $user->setCreatedDateTime(DateBase::getCurrentDateTime());
	                $user->setCreatedDate(DateBase::getCurrentDate());
	            }

	            $user->setCreatedById(1);
	            $user->setEmployeeCode($employee->getCode());
	            $user->setOneofficeId($row['ID']);
	            $userMapper->save($user);

	            $employee->setUserId($user->getId());
	            $employeeMapper->save($employee);
	            $totalInsert++;
	        }

	    }

	    $this->getViewModel()->setTerminal(true);
	    $this->getViewModel()->setVariable('paginator', $paginator);
	    $this->getViewModel()->setVariable('page', $page);
	    $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
	    $this->getViewModel()->setVariable('totalInsert', $totalInsert);
	    $this->getViewModel()->setVariable('totalUpdate', $totalUpdate);
	    if ($page <= $paginator->count()) {
	        $this->getViewModel()->setVariable('redirectUri',
	            Uri::build('/system/tool/officebncuser', [
	                'page' => $page+1,
	                'totalInsert' => $totalInsert,
	                'totalUpdate' => $totalUpdate
	                ]));
	    }
	    return $this->getViewModel();
	}

	/**
	 * Hàm chạy đệ quy , thêm các số phía sau username cho đến khi dc 1 cái username chưa có trong hệ thống
	 * @param unknown $username
	 * @param unknown $prefix
	 */
	private function getValidUsername($username, $prefix = 0){
		$userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
		$user = new \User\Model\User();
		$result = $username;
		if($prefix){
			$result .= $prefix;
		}
		$user->setUsername(trim($result));
		if($userMapper->isExistedUserName($user)){
			return $this->getValidUsername($username, $prefix + 1);
		}
		return $result;
	}

	/**
	 * @author KienNN
	 * convert danh mục tài liệu cá nhân
	 */
	public function officeuserdocumentcategoryAction(){
		// tạo danh mục tài liệu
		list($adapter, $sql) = $this->createOfficeAdapter();

		// tìm kiếm user trên erp tương ứng
		$select = $sql->select(['d' => 'documents']);
		$select->join(['u' => 'users'], 'd.created_by_id = u.ID', ['personnel_id']);
		$select->where(['folder' => 'USER']);
		$select->where(['type' => 'FOLDER']);
		$select->group(['d.created_by_id']);
		$query = $sql->buildSqlString($select);
		$rows = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
		$usersRefer = [];
		$companyRefer = [];
		$employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');
		if($rows->count()){
			foreach ($rows->toArray() as $row){
				if(!isset($usersRefer[$row['created_by_id']])){
					$employee = new \Hrm\Model\Employee();
					$employee->setOneofficeId($row['personnel_id']);
					$employeeMapper->isExistedOneofficeId($employee);
					if($employee->getUserId()){
						$usersRefer[$row['created_by_id']] = $employee->getUserId();
						$companyRefer[$employee->getUserId()] = $employee->getCompanyId();
					} else {
						continue;
					}
				}
			}
		}

		// xây cây danh mục tương ứng với từng user
		foreach ($usersRefer as $officeUserId => $erpUserId){
			$select = $sql->select(['d' => 'documents']);
			$select->where(['folder' => 'USER']);
			$select->where(['type' => 'FOLDER']);
			$select->where(['created_by_id' => $officeUserId]);
			$query = $sql->buildSqlString($select);
			$rows = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
			if($rows->count()){
				$this->travelToCreateUserDocumentCategory($this->branchRecusive($rows->toArray()), $erpUserId, $companyRefer[$erpUserId]);
			}
		}
		die;
	}

	private function travelToCreateUserDocumentCategory($items, $userId, $companyId, $parentId=null){
		$result = array();
		if($items && count($items)){
			$categoryMapper = $this->getServiceLocator()->get('\Document\Model\DocumentCategoryMapper');
			foreach ($items as $node) {
				$item = $node['obj'];
				$category = new \Document\Model\DocumentCategory();
				$category->setCompanyId($companyId);
				$category->setName($item['title']);
				$category->setParentId($parentId);
				if(!$categoryMapper->isExisted($category)){
					$category->setCreatedById($userId);
					$category->setCreatedDateTime($item['date_created']);
					$category->setType(\Document\Model\Document::OWNERTYPE_PERSONAL);
				}
				$category->setOneofficeId($item['ID']);
				$categoryMapper->save($category);
				echo 'tạo mới category '.$category->getId().'<br/>';
				if(isset($node['childs']) && count($node['childs'])){
					$this->travelToCreateUserDocumentCategory($node['childs'], $userId,$companyId, $category->getId());
				}
			}
		}
	}

	/**
	 * @author Kienn
	 * convert danh mục tài liệu cá nhân
	 */
	public function officeuserdocumentAction(){
		// tạo danh mục tài liệu
		list($adapter, $sql) = $this->createOfficeAdapter();

		// lấy ra người tạo tương ứng
		$select = $sql->select(['d' => 'documents']);
		$select->join(['u' => 'users'], 'd.created_by_id = u.ID', ['personnel_id']);
		$select->where(['folder' => 'USER']);
		$select->where(['type' => 'FILE']);
		$select->group(['d.created_by_id']);
		$query = $sql->buildSqlString($select);
		$rows = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
		$usersRefer = [];
		$companyRefer = [];
		$employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');
		if($rows->count()){
			foreach ($rows->toArray() as $row){
				if(!isset($usersRefer[$row['created_by_id']])){
					$employee = new \Hrm\Model\Employee();
					$employee->setOneofficeId($row['personnel_id']);
					if($employeeMapper->isExistedOneofficeId($employee) && $employee->getUserId()){
						$usersRefer[$row['created_by_id']] = $employee;
					} else {
						continue;
					}
				}
			}
		}
		$categoryMapper = $this->getServiceLocator()->get('\Document\Model\DocumentCategoryMapper');
		$documentMapper = $this->getServiceLocator()->get('\Document\Model\DocumentMapper');
		$matchCategory = [];
		foreach ($usersRefer as $officeUserId => $employee){
			$select = $sql->select(['d' => 'documents']);
			$select->where(['folder' => 'USER']);
			$select->where(['type' => 'FILE']);
			$select->where(['created_by_id' => $officeUserId]);
			$query = $sql->buildSqlString($select);
			$rows = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
			if($rows->count()){
				foreach ($rows->toArray() as $row){
					//tìm kiếm danh mục
					$categoryId = null;
					if($row['parent_id'] && !isset($matchCategory[$row['parent_id']])){
						$category = new \Document\Model\DocumentCategory();
						$category->setOneofficeId($row['parent_id']);
						if($categoryMapper->isExistedOneofficeCode($category)){
							$matchCategory[$row['parent_id']] = $category->getId();
						}
					}
					$document = new \Document\Model\Document();
					$document->setCreatedById($employee->getUserId());
					$document->setCompanyId($employee->getCompanyId());
					if($row['parent_id'] && isset($matchCategory[$row['parent_id']])){
						$document->setCategoryId($matchCategory[$row['parent_id']]);
					}

					$document->setType(\Document\Model\Document::TYPE_DOCUMENT);
					$document->setOneofficeId($row['ID']);
					$document->setOwnerType(\Document\Model\Document::OWNERTYPE_PERSONAL);
					$document->setName($row['title']);
					$document->setContent($row['desc']);
					$document->setCreatedDateTime($row['date_created']);
					if(!$documentMapper->isExisted($document)){
						$documentMapper->save($document);
						echo 'Tạo mới document '.$document->getId().'<br/>';
					}
				}
			}
		}
		echo 'done!';
		die;
	}

	public function officeconvertuserdocumentfileAction(){
		$officeDirector = BASE_PATH.'/data/oneOfficeFiles';

		list($adapter, $sql) = $this->createOfficeAdapter();

		$select = $sql->select(['df' => 'document_files']);
		$select->join(['d' => 'documents'], 'd.ID=df.document_id', ['created_by_id']);
		$select->where([
				'd.folder' => 'USER',
				'd.type' => 'FILE',
				'd.is_deleted' => 'no'
				]);
		$select->order(['ID ASC']);

		$query = $sql->buildSqlString($select);
		$rows = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);

		$documentMapper = $this->getServiceLocator()->get('\Document\Model\DocumentMapper');
		$documentCategoryMapper = $this->getServiceLocator()->get('\Document\Model\DocumentCategoryMapper');
		$documentFileMapper = $this->getServiceLocator()->get('\Document\Model\DocumentFileMapper');
		$index =0;
		$totalSize = 0;
		foreach ($rows->toArray() as $row) {
			$officeUri = $officeDirector.'/document/user/'.$row['created_by_id'].'/'.$row['filename'];
			if(!file_exists($officeUri)){
				echo '<b>File: </b>'.$officeUri.'<br/>';
				echo '<b style="color:red;">File not found!!!</b>';
				echo '<br/><br/>=============================================<br/>';
				continue;
			}

			$document = new \Document\Model\Document();
			$document->setOneofficeId($row['document_id']);
			// check nếu đã có bản ghi trong document thì coi nó là 1 file đính kèm của document
			if($documentMapper->isExistedOneofficeCode($document)){
				$documentFile = new \Document\Model\DocumentFile();
				$documentFile->setDocumentId($document->getId());
				$documentFile->setFileName($row['filename']);
				$documentFile->setCreatedDateTime($document->getCreatedDateTime());
				$uri = Uri::getSavePath($documentFile);

				echo '<b>docfile: </b>'.$document->getName();
				echo '<br/>';
				echo $row['ID'].' --- '.$row['document_id'];
				echo '<br/>';
				echo '<b>Từ: </b>'.$officeUri;
				echo '<br/>';
				echo '<b>Đến: </b>'.$uri.'/'.$documentFile->getFileName();
				echo '<br/>';
				if(is_file($officeUri)){
					$filesize = filesize($officeUri);
					echo '<b>Filesize: </b>'.$filesize;
					$totalSize += $filesize;

					$documentFile->setFileSize($filesize);
				}
				$documentFile->setCreatedById($document->getCreatedById());

				if(!$documentFileMapper->isExisted($documentFile)){
					$documentFileMapper->save($documentFile);
					if (!file_exists($uri)) {
						$oldmask = umask(0);
						mkdir($uri, 0777, true);
						umask($oldmask);
					}

					@copy($officeUri, $uri.'/'.$documentFile->getFileName());
					$index++;
					echo '<br/><span style="color:green">Đã chuyển</span>';
				}
				echo '<br/><br/>=============================================<br/>';
			} else {
				// nếu ko thì nghĩa laà file gắn với danh mục, tạo mới 1 document rồi gắn file với document đó
				// nhưng kiểm tra dữ liệu ko có case này nên ko viết
				$documentCategory = new \Document\Model\DocumentCategory();
				$documentCategory->setOneofficeId($row['document_id']);
				if($documentCategoryMapper->isExistedOneofficeCode($documentCategory)){
					echo '<span style="color:blue;">Tạo mới theo dnah mục</span>';
					echo '<br/><br/>=============================================<br/>';
				} else {
					echo '<span style="color:red;">Ko dữ liệu </span>';
					echo $row['ID'].' --- '.$row['document_id'];
					echo '<br/><br/>=============================================<br/>';
				}
			}

		}
		echo '<b>Tổng: </b>'.$index;
		echo '<br/><b>Tổng dung lượng: </b>'.$totalSize;
		die;
	}

	/**
	 * @TODO: chua xong
	 */
	public function officassethistoryAction(){
		$deviceStatusMath = array(
			'1' => \Asset\Model\Asset::DEVICE_STATUS_USING,
			'2' => \Asset\Model\Asset::DEVICE_STATUS_REPAIR,
			'3' => \Asset\Model\Asset::DEVICE_STATUS_MAINTAIN,
			'4' => \Asset\Model\Asset::DEVICE_STATUS_LIQUIDATION,
			'5' => \Asset\Model\Asset::DEVICE_STATUS_WARRANTY,
			'7' => \Asset\Model\Asset::DEVICE_STATUS_ERROR,
			'8' => \Asset\Model\Asset::DEVICE_STATUS_FULLBOX,
			'10' => \Asset\Model\Asset::DEVICE_STATUS_WELL,
		);

		list($officeAdapter, $officeSql) = $this->createOfficeAdapter();

		$dbAdapter = $this->getServiceLocator()->get('dbAdapter');
		$dbSql = $this->getServiceLocator()->get('dbSql');

		$select = $officeSql->select(['aa' => 'assets_assigns']);
		$select->order(['ID ASC']);

		$paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $officeAdapter);
		$paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
		$paginator->setItemCountPerPage(50);
		$page = $this->getRequest()->getQuery('page', 1);
		$paginator->setCurrentPageNumber($page);

		$assetMapper = $this->getServiceLocator()->get('\Asset\Model\AssetMapper');
		foreach ($paginator as $pageRow){
			$pageRow = (array) $pageRow;
			// lấy ra asset
			$asset = new \Asset\Model\Asset();
			$asset->setOneofficeId($pageRow['asset_id']);
			if($assetMapper->isExistedOneofficeId($asset)){
				//thêm lịch sử cấp
				$assetHistory = new \Asset\Model\AssetHistory();
				$assetHistory->setType(\Asset\Model\AssetHistory::TYPE_ASSIGN);
				$assetHistory->setAssetId($asset->getId());


				//nếu có người trả, thêm lịch sử thu hồi

			}


		}
	}



	public function officeinsuranceAction(){
		list($officeAdapter, $officeSql) = $this->createOfficeAdapter();

		$dbAdapter = $this->getServiceLocator()->get('dbAdapter');
		$dbSql = $this->getServiceLocator()->get('dbSql');

		$defaultAdminId = 1;
		$currentDate = DateBase::getCurrentDate();
		$currentDateTime = DateBase::getCurrentDateTime();

		$page = $this->getRequest()->getQuery('page', 1);
		$totalBook = $this->getRequest()->getQuery('totalBook', 0);
		$totalCertificate = $this->getRequest()->getQuery('totalCertificate', 0);
		// duyệt các personel có perimium_code
		$select = $officeSql->select(['p' => 'personnels']);
		$select->columns(['ID', 'premium_number', 'premium_status', 'premium_pay_status',
		'premium_key_status', 'premium_mode', 'premium_start_date']);
		$select->where(['premium_number IS NOT NULL']);
		$select->order(['p.ID']);

		$paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $officeAdapter);
		$paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
		$paginator->setItemCountPerPage(100);

		$paginator->setCurrentPageNumber($page);

		$bookMapper = $this->getServiceLocator()->get('\Hrm\Model\Insurance\BookMapper');
		$employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');
		$certificateMapper = $this->getServiceLocator()->get('\Hrm\Model\Insurance\CertificateMapper');
		foreach ($paginator as $row){
		    $row = (array) $row;
		    // đối chiếu employee
		    $employee = new \Hrm\Model\Employee();
		    $employee->setOneofficeId($row['ID']);
            if(!$employeeMapper->isExistedOneofficeId($employee)){
                continue;
            }
            if($employee->getInsuranceBookId()){
                continue;
            }

		    // đối chiếu với insurance book, nếu đã tồn tại thì continue
		    $book = new \Hrm\Model\Insurance\Book();
		    $book->setCode($row['premium_number']);
            if($bookMapper->isExistedCode($book)){
                continue;
            }
            // nếu chưa có tạo mới
            $book->setCreatedById(1);
            $book->setCreatedDateTime(DateBase::getCurrentDateTime());

            if($row['premium_pay_status']){
                $book->setStatus(\Hrm\Model\Insurance\Book::STATUS_RETURNED);
                $book->setStorageStatus(\Hrm\Model\Insurance\Book::STORAGE_STATUS_UNAVAILABLE);
            } elseif ($row['premium_key_status']){
                $book->setStatus(\Hrm\Model\Insurance\Book::STATUS_END);
                $book->setStorageStatus(\Hrm\Model\Insurance\Book::STORAGE_STATUS_AVAILABLE);
            } elseif ($row['premium_status']) {
                $book->setStatus(\Hrm\Model\Insurance\Book::STATUS_USING);
                $book->setStorageStatus(\Hrm\Model\Insurance\Book::STORAGE_STATUS_AVAILABLE);
            } else{
                $book->setStatus(\Hrm\Model\Insurance\Book::STATUS_USING);
                $book->setStorageStatus(\Hrm\Model\Insurance\Book::STORAGE_STATUS_UNAVAILABLE);
            }
            $bookMapper->save($book);
            // gắn cùng employee
            $employee->setInsuranceBookId($book->getId());
            $employeeMapper->save($employee);

		    // tạo mới cùng cẻtificate

            if($row['premium_status']){
                $certificate = new \Hrm\Model\Insurance\Certificate();
                $certificate->setBookId($book->getId());
                $certificate->setCompanyId($employee->getCompanyId());
                if($row['premium_pay_status']){
                    $certificate->setStatus(\Hrm\Model\Insurance\Certificate::STATUS_END);
                } elseif ($row['premium_key_status']){
                    $certificate->setStatus(\Hrm\Model\Insurance\Certificate::STATUS_END);
                } else {
                    $certificate->setStatus(\Hrm\Model\Insurance\Certificate::STATUS_USING);
                }

                $certificateMapper->save($certificate);
                $totalCertificate++;
            }
            $totalBook++;
		}
		$this->getViewModel()->setTerminal(true);
		$this->getViewModel()->setVariable('paginator', $paginator);
		$this->getViewModel()->setVariable('page', $page);
		$this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
		$this->getViewModel()->setVariable('totalCertificate', $totalCertificate);
		$this->getViewModel()->setVariable('totalBook', $totalBook);
		if ($page <= $paginator->count()) {
		    $this->getViewModel()->setVariable('redirectUri',
		        Uri::build('/system/tool/officeinsurance', [
		            'page' => $page+1,
		            'totalBook' => $totalBook,
		            'totalCertificate' => $totalCertificate,
		            ]));
		}
		return $this->getViewModel();
	}

	public function officebncinsuranceAction(){

	    list($officeAdapter, $officeSql) = $this->createOfficeBNCAdapter();

	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
	    $dbSql = $this->getServiceLocator()->get('dbSql');

	    $defaultAdminId = 1;
	    $currentDate = DateBase::getCurrentDate();
	    $currentDateTime = DateBase::getCurrentDateTime();

	    $page = $this->getRequest()->getQuery('page', 1);
	    $totalBook = $this->getRequest()->getQuery('totalBook', 0);
	    $totalCertificate = $this->getRequest()->getQuery('totalCertificate', 0);
	    // duyệt các personel có perimium_code
	    $select = $officeSql->select(['p' => 'personnels']);
	    $select->columns(['ID', 'premium_number', 'premium_status', 'premium_pay_status',
	        'premium_key_status', 'premium_mode', 'premium_start_date']);
	    $select->where(['premium_number IS NOT NULL']);
	    $select->order(['p.ID']);

	    $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $officeAdapter);
	    $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
	    $paginator->setItemCountPerPage(100);

	    $paginator->setCurrentPageNumber($page);

	    $bookMapper = $this->getServiceLocator()->get('\Hrm\Model\Insurance\BookMapper');
	    $employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');
	    $certificateMapper = $this->getServiceLocator()->get('\Hrm\Model\Insurance\CertificateMapper');
	    foreach ($paginator as $row){
	        $row = (array) $row;
	        // đối chiếu employee
	        $employee = new \Hrm\Model\Employee();
	        $employee->setOneofficeId($row['ID']);
	        $employee->setPrivateSource(\Home\Model\Consts::PRIVATE_SOURCE_OFFICEBNC);
	        if(!$employeeMapper->isExistedOneofficeId($employee)){
	            continue;
	        }
	        if($employee->getInsuranceBookId()){
	            continue;
	        }

	        // đối chiếu với insurance book, nếu đã tồn tại thì continue
	        $book = new \Hrm\Model\Insurance\Book();
	        $book->setCode($row['premium_number']);
	        if($bookMapper->isExistedCode($book)){
	            continue;
	        }
	        // nếu chưa có tạo mới
	        $book->setCreatedById(1);
	        $book->setCreatedDateTime(DateBase::getCurrentDateTime());

	        if($row['premium_pay_status']){
	            $book->setStatus(\Hrm\Model\Insurance\Book::STATUS_RETURNED);
	            $book->setStorageStatus(\Hrm\Model\Insurance\Book::STORAGE_STATUS_UNAVAILABLE);
	        } elseif ($row['premium_key_status']){
	            $book->setStatus(\Hrm\Model\Insurance\Book::STATUS_END);
	            $book->setStorageStatus(\Hrm\Model\Insurance\Book::STORAGE_STATUS_AVAILABLE);
	        } elseif ($row['premium_status']) {
	            $book->setStatus(\Hrm\Model\Insurance\Book::STATUS_USING);
	            $book->setStorageStatus(\Hrm\Model\Insurance\Book::STORAGE_STATUS_AVAILABLE);
	        } else{
	            $book->setStatus(\Hrm\Model\Insurance\Book::STATUS_USING);
	            $book->setStorageStatus(\Hrm\Model\Insurance\Book::STORAGE_STATUS_UNAVAILABLE);
	        }
	        $bookMapper->save($book);
	        // gắn cùng employee
	        $employee->setInsuranceBookId($book->getId());
	        $employeeMapper->save($employee);

	        // tạo mới cùng cẻtificate

	        if($row['premium_status']){
	            $certificate = new \Hrm\Model\Insurance\Certificate();
	            $certificate->setBookId($book->getId());
	            $certificate->setCompanyId($employee->getCompanyId());
	            if($row['premium_pay_status']){
	                $certificate->setStatus(\Hrm\Model\Insurance\Certificate::STATUS_END);
	            } elseif ($row['premium_key_status']){
	                $certificate->setStatus(\Hrm\Model\Insurance\Certificate::STATUS_END);
	            } else {
	                $certificate->setStatus(\Hrm\Model\Insurance\Certificate::STATUS_USING);
	            }

	            $certificateMapper->save($certificate);
	            $totalCertificate++;
	        }
	        $totalBook++;
	    }
	    $this->getViewModel()->setTerminal(true);
	    $this->getViewModel()->setVariable('paginator', $paginator);
	    $this->getViewModel()->setVariable('page', $page);
	    $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
	    $this->getViewModel()->setVariable('totalCertificate', $totalCertificate);
	    $this->getViewModel()->setVariable('totalBook', $totalBook);
	    if ($page <= $paginator->count()) {
	        $this->getViewModel()->setVariable('redirectUri',
	            Uri::build('/system/tool/officebncinsurance', [
	                'page' => $page+1,
	                'totalBook' => $totalBook,
	                'totalCertificate' => $totalCertificate,
	                ]));
	    }
	    return $this->getViewModel();

	}

	/**
	 *
	 */
	public function officeemployeecontractAction(){
		list($officeAdapter, $officeSql) = $this->createOfficeAdapter();

		$dbAdapter = $this->getServiceLocator()->get('dbAdapter');
		$dbSql = $this->getServiceLocator()->get('dbSql');

		$mathType = array(
		    '12' => \Hrm\Model\Employee\Contract::TYPE_COLLABORATOR,
		    '14' => \Hrm\Model\Employee\Contract::TYPE_TRIAL_1_MONTH,
		    '8' => \Hrm\Model\Employee\Contract::TYPE_UNDER_3_MONTH,
		    '15' => \Hrm\Model\Employee\Contract::TYPE_TRIAL_2_MONTH,
		    '11' => \Hrm\Model\Employee\Contract::TYPE_UNDER_2_MONTH,
		    '4' => \Hrm\Model\Employee\Contract::TYPE_FIRST_1_YEAR,
		    '13' => \Hrm\Model\Employee\Contract::TYPE_SECOND_1_YEAR,
		    '6' => \Hrm\Model\Employee\Contract::TYPE_UNLIMITED,
		    '9' => \Hrm\Model\Employee\Contract::TYPE_TRIAL_2_MONTH_OLD,
		    '10' => \Hrm\Model\Employee\Contract::TYPE_2_MONTH_OLD,
		);
		$mathWorkingType = array(
			'1' => \Hrm\Model\Employee\Contract::WORKING_TYPE_FULLTIME,
		    '2' => \Hrm\Model\Employee\Contract::WORKING_TYPE_PARTTIME,
		    '3' => \Hrm\Model\Employee\Contract::WORKING_TYPE_COLLABORATOR,
		    '4' => \Hrm\Model\Employee\Contract::WORKING_TYPE_ADVISER,
		);
		$mathSalaryType = array(
			'3' => \Hrm\Model\Employee\Contract::SALARY_TYPE_PRODUCT,
		    '4' => \Hrm\Model\Employee\Contract::SALARY_TYPE_SALE,
		    '5' => \Hrm\Model\Employee\Contract::SALARY_TYPE_HANDOVER,
		    '6' => \Hrm\Model\Employee\Contract::SALARY_TYPE_DATE,
		    '7' => \Hrm\Model\Employee\Contract::SALARY_TYPE_COLLABORATOR,
		);

		$totalInsert = $this->getRequest()->getQuery('totalInsert', 0);
		$page = $this->getRequest()->getQuery('page', 1);
		// lấy ra danh sách hợp đồng của office
		$select = $dbSql->select(['pc' => 'personnel_contracts']);
		$select->order(['ID ASC']);

		$paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $officeAdapter);
		$paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
		$paginator->setItemCountPerPage(100);
		$paginator->setCurrentPageNumber($page);

		$employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');
		$contractMapper = $this->getServiceLocator()->get('\Hrm\Model\Employee\ContractMapper');
		$currentDate = new \DateTime();
		foreach ($paginator as $row){
		    $row = (array) $row;
		    if(!$row['personnel_id']){
		        continue;
		    }
		    // rà soát lấy ra employee
		    $employee = new \Hrm\Model\Employee();
		    $employee->setOneofficeId($row['personnel_id']);
            if(!$employeeMapper->isExistedOneofficeId($employee)){
                continue;
            }
		    // nạp dữ liệu cho hợp đồng
		    $contract = new \Hrm\Model\Employee\Contract();
		    $contract->setCode($row['code']);
            $matchDepartment = $this->matchDepartment($row['department_id']);
            $contract->setCompanyId($matchDepartment['companyId']);
            $contract->setDepartmentId($matchDepartment['departmentId']);
            if($contractMapper->isExisted($contract) !== false){
                continue;
            }
            $contract->setEmployeeId($employee->getId());
            if(isset($mathType[$row['type']])){
                $contract->setType($mathType[$row['type']]);
            } else{
               $contract->setType(\Hrm\Model\Employee\Contract::TYPE_UNKNOWN);
            }
            if(isset($mathWorkingType[$row['work_type']])){
                $contract->setWorkingType($mathWorkingType[$row['work_type']]);
            }
            $contract->setRegisteredDate($row['date_reg']);
            $contract->setStartDate($row['date_start']);
            $contract->setEndDate($row['date_finish']);
            $contract->setSalary($row['salary']);
            $contract->setSalaryPremium($row['salary_premium']);
            $contract->setSalaryAllowances($row['salary_allowances']);
            if(isset($mathSalaryType[$row['salary_method']])){
                $contract->setSalaryType($mathSalaryType[$row['salary_method']]);
            }
            $contract->setCompanySocialInsurance($row['insurance_social_percent_company']);
            $contract->setCompanyHealthInsurance($row['insurance_health_percent_company']);
            $contract->setCompanyUnemployeeInsurance($row['insurance_unemployment_percent_company']);
            $contract->setEmployeeSocialInsurance($row['insurance_social_percent_person']);
            $contract->setEmployeeHealthInsurance($row['insurance_health_percent_person']);
            $contract->setEmployeeUnemployeeInsurance($row['insurance_unemployment_percent_person']);

            $contract->setCreatedById(1);
            if($row['date_created'] && $row['date_created'] != '0000-00-00'){
                $dateCreated = new \DateTime($row['date_created']);
                $contract->setCreatedDate($dateCreated->format(DateBase::COMMON_DATE_FORMAT));
                $contract->setCreatedTime($dateCreated->format(DateBase::COMMON_TIME_FORMAT));
            }
            // check nếu dateFinish < date hiện tại thì đánh status inactive
            // nếu ko có dateFinish hoặc dateFinish > currentDate đánh status active
            if($contract->getType() == \Hrm\Model\Employee\Contract::TYPE_UNLIMITED){
                $contract->setStatus(\Hrm\Model\Employee\Contract::STATUS_ACTIVE);
            } else {
                if($row['date_finish'] && $row['date_finish'] != '0000-00-00'){
                    $dateFisnish = new \DateTime($row['date_finish']);
                    if($dateFisnish > $currentDate){
                        $contract->setStatus(\Hrm\Model\Employee\Contract::STATUS_ACTIVE);
                    } else {
                        $contract->setStatus(\Hrm\Model\Employee\Contract::STATUS_INACTIVE);
                    }
                }
            }
            $contractMapper->save($contract);
            $totalInsert++;

		}

		$this->getViewModel()->setTerminal(true);
		$this->getViewModel()->setVariable('paginator', $paginator);
		$this->getViewModel()->setVariable('page', $page);
		$this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
		$this->getViewModel()->setVariable('totalInsert', $totalInsert);
		if ($page <= $paginator->count()) {
		    $this->getViewModel()->setVariable('redirectUri',
		        \Home\Service\Uri::build('/system/tool/officeemployeecontract', [
		            'page' => $page+1,
		            'totalInsert' => $totalInsert,
		            ]));
		}
		return $this->getViewModel();
	}

	public function officeattendancetimeAction(){
		// insert bang attendance time
		list($officeAdapter, $officeSql) = $this->createOfficeAdapter();

		$dbAdapter = $this->getServiceLocator()->get('dbAdapter');
		$dbSql = $this->getServiceLocator()->get('dbSql');

		// lấy ra danh sách check máy chấm công bên offiec
		// lấy dánh sách employeeId dựa trên employeeCode
		// insert vào bảng employee bên minh


	}

	public function officeattendanceAction(){
		list($officeAdapter, $officeSql) = $this->createOfficeAdapter();

		$dbAdapter = $this->getServiceLocator()->get('dbAdapter');
		$dbSql = $this->getServiceLocator()->get('dbSql');

		// lấy danh sách chấm công bên office
		// lấy dánh sách employeeId dựa trên employeeCode
		// insert vào bảng bên mình
	}

    public function recreatepasswordAction(){
        list($officeAdapter, $officeSql) = $this->createOfficeAdapter();

        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $dbSql = $this->getServiceLocator()->get('dbSql');

        $select = $dbSql->select(['u' => \User\Model\UserMapper::TABLE_NAME]);
        $select->where(['createdById' => 1]);
        $select->where(['oneofficeId IS NOT NULL']);
        $select->where(['salt IS NULL']);

        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage(50);
        $page = $this->getRequest()->getQuery('page', 1);
        $totalUpdate = $this->getRequest()->getQuery('totalUpdate', 0);
        $paginator->setCurrentPageNumber($page);

        $userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
        foreach ($paginator as $row){
            $row = (array) $row;
            $user = new \User\Model\User();
            $user->exchangeArray($row);
            $user->setSalt($user->generateSalt());
            $user->setPassword($user->createPassword('vatgia.com'));

            $select = $officeSql->select(['u' => 'users']);
            $select->where(['ID' => $user->getOneofficeId()]);
            $select->where(['is_deleted' => 'no']);
            $check = $officeAdapter->query($officeSql->buildSqlString($select), $officeAdapter::QUERY_MODE_EXECUTE);
            if($check->count()){
                $user->setLocked(null);
            } else {
                $user->setLocked(1);
            }

            $userMapper->save($user);
            $totalUpdate++;
        }
        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('paginator', $paginator);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
        $this->getViewModel()->setVariable('totalUpdate', $totalUpdate);
        if ($paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri',
                Uri::build('/system/tool/recreatepassword', [
                    'page' => $page+1,
                    'totalUpdate' => $totalUpdate,
                    ]));
        }
        return $this->getViewModel();
    }

    /**
     * @author KienNN
     * rà soát bảng crm_customers của office
     * duwanj vào onefficeId của lead kiểm tra lead đẫ tồn tại chưa, nếu chưa tạo mới, nếu có thì update nếu cần
     * đối chiếu bảng crm_customer_users của office để tìm kiếm và ghép các user với lead
     */
    public function officeconvertleadAction(){
        list($officeAdapter, $officeSql) = $this->createOfficeAdapter();

        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        /*@var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbSql = $this->getServiceLocator()->get('dbSql');

        $mathInformationItemType = array(
            'ADDRESS' => \Contact\Model\Contact\Information::ITEM_TYPE_ADDRESS,
            'BANK' => \Contact\Model\Contact\Information::ITEM_TYPE_BANKACCOUNT,
            'BUSINESS_ADDRESS' => \Contact\Model\Contact\Information::ITEM_TYPE_ADDRESS,
            'BUSINESS_EMAIL' => \Contact\Model\Contact\Information::ITEM_TYPE_EMAIL,
            'BUSINESS_PHONE' => \Contact\Model\Contact\Information::ITEM_TYPE_MOBILE,
            'EMAIL' => \Contact\Model\Contact\Information::ITEM_TYPE_EMAIL,
            'PERSONAL_ADDRESS' => \Contact\Model\Contact\Information::ITEM_TYPE_ADDRESS,
            'PERSONAL_EMAIL' => \Contact\Model\Contact\Information::ITEM_TYPE_EMAIL,
            'PERSONAL_PHONE' => \Contact\Model\Contact\Information::ITEM_TYPE_MOBILE,
            'PHONE' => \Contact\Model\Contact\Information::ITEM_TYPE_MOBILE,
            'WEB' => \Contact\Model\Contact\Information::ITEM_TYPE_WEBSITE,
            'WEBSITE' => \Contact\Model\Contact\Information::ITEM_TYPE_WEBSITE,
        );

        $mathLeadUserType = array(
        	'1' => \Crm\Model\Lead\User::TYPE_SALE,
            '2' => \Crm\Model\Lead\User::TYPE_TELESALE,
        );

        // rà soát bảng crm_customers của office
        $select = $officeSql->select(['cc' => 'crm_customers']);
        $select->order(['ID ASC']);

        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $officeAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage(50);
        $page = $this->getRequest()->getQuery('page', 1);
        $totalCreate = $this->getRequest()->getQuery('totalCreate', 0);
        $paginator->setCurrentPageNumber($page);


        $informationMapper = $this->getServiceLocator()->get('\Contact\Model\Contact\InformationMapper');
        $leadMapper = $this->getServiceLocator()->get('\Crm\Model\LeadMapper');
        $leadUserMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\UserMapper');
        foreach ($paginator as $row){
            $row = (array) $row;

            //làm lead
            $lead = new \Crm\Model\Lead();
            $lead->setOneofficeId($row['ID']);

            if($leadMapper->isExistedOneofficeId($lead)){
                continue;
            }
            $lead->setName($row['name']);
            $lead->setBirthdate($row['birthday']);
            $lead->exchangeArray($this->mathPlace($row['district_id']?:$row['state_id']));


            // Lấy người tạo, math để lấy ra company
            if($row['created_by_id']){
                $select = $dbSql->select(['u' => \User\Model\UserMapper::TABLE_NAME]);
                $select->join(['e' => \Hrm\Model\EmployeeMapper::TABLE_NAME],
                    'e.userId=u.id', []);
                $select->columns(array(
                    'userId' => new Expression('u.id'),
                    'companyId' => new Expression('e.companyId'),
                    'departmentId' => new Expression('e.departmentId'),
                ));
                $select->where(['u.oneofficeId' => $row['created_by_id']]);
                $select->limit(1);
                $rowUser = $dbAdapter->query($dbSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
                if($rowUser->count()){
                    $rowUser = (array) $rowUser->current();
                    $lead->setCompanyId($rowUser['companyId']);
                } else {
                    // nếu không thể xác định company thì để company = VNP
                    $lead->setCompanyId(10);
                }
            } else {
                // nếu không thể xác định company thì để company = VNP
                $lead->setCompanyId(10);
            }

            $lead->setCreatedById(1);
            $lead->setCreatedDate(DateBase::getCurrentDate());
            $lead->setCreatedDateTime(DateBase::getCurrentDateTime());
            $lead->setLastActivityById(1);
            $lead->setLastActivityDateTime(DateBase::getCurrentDateTime());
            $lead->setDescription($row['desc']);
            $leadMapper->save($lead);

            if($row['fax']){
                $information = new \Contact\Model\Contact\Information();
                $information->setCompanyId($lead->getCompanyId());
                $information->setType(\Contact\Model\Contact\Information::TYPE_CRM_LEAD);
                $information->setItemId($lead->getId());
                $information->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_FAX);
                $information->setContent($row['fax']);
                if(!$informationMapper->isExisted($information)){
                    $informationMapper->save($information);
                }
            }
            if($row['private_code']){
                $information = new \Contact\Model\Contact\Information();
                $information->setCompanyId($lead->getCompanyId());
                $information->setType(\Contact\Model\Contact\Information::TYPE_CRM_LEAD);
                $information->setItemId($lead->getId());
                $information->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_IDENTIFICATION);
                $information->setContent($row['private_code']);
                $information->setExtraContent(json_encode(array(
                	'identificationPlace' => $row['private_place'],
                    'identificationDate' => $row['private_date']
                )));
                if(!$informationMapper->isExisted($information)){
                    $informationMapper->save($information);
                }
            }
            if($row['tax']){
                $information = new \Contact\Model\Contact\Information();
                $information->setCompanyId($lead->getCompanyId());
                $information->setType(\Contact\Model\Contact\Information::TYPE_CRM_LEAD);
                $information->setItemId($lead->getId());
                $information->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_TAXCODE);
                $information->setContent($row['tax']);
                if(!$informationMapper->isExisted($information)){
                    $informationMapper->save($information);
                }
            }


            // lấy ra các properties
            $select = $officeSql->select(['ccp' => 'crm_customer_properties']);
            $select->where(['customer_id' => $row['ID']]);
            $rowProperties = $officeAdapter->query($officeSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
            if($rowProperties->count()){
                foreach ($rowProperties->toArray() as $rowProperty){
                    $information = new \Contact\Model\Contact\Information();
                    $information->setCompanyId($lead->getCompanyId());
                    $information->setType(\Contact\Model\Contact\Information::TYPE_CRM_LEAD);
                    $information->setItemId($lead->getId());
                    if(isset($mathInformationItemType[$rowProperty['type']])){
                        $information->setItemType($mathInformationItemType[$rowProperty['type']]);
                    } else {
                        continue;
                    }
                    $information->setContent($rowProperty['title']);
                    $information->setExtraContent($rowProperty['params']?:null);

                    if(!$informationMapper->isExisted($information)){
                        $informationMapper->save($information);
                    }
                }
            }

            // lấy ra user
            $select = $officeSql->select(['ccu' => 'crm_customer_users']);
            $select->where(['customer_id' => $row['ID']]);
            $rowCustomerUsers = $officeAdapter->query($officeSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
            if($rowCustomerUsers->count()){
                foreach ($rowCustomerUsers->toArray() as $rowCustomerUser){
                    $select = $dbSql->select(['u' => \User\Model\UserMapper::TABLE_NAME]);
                    $select->join(['e' => \Hrm\Model\EmployeeMapper::TABLE_NAME],
                        'e.userId=u.id', []);
                    $select->columns(array(
                        'userId' => new Expression('u.id'),
                        'companyId' => new Expression('e.companyId'),
                        'departmentId' => new Expression('e.departmentId'),
                    ));
                    $select->where(['u.oneofficeId' => $rowCustomerUser['user_id']]);
                    $select->limit(1);
                    $rowUser = $dbAdapter->query($dbSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
                    if($rowUser->count()){
                        $rowUser = (array) $rowUser->current();
                        $leadUser = new \Crm\Model\Lead\User();
                        $leadUser->setUserId($rowUser['userId']);
                        $leadUser->setLeadId($lead->getId());
                        $leadUser->setCompanyId($rowUser['companyId']?:$lead->getCompanyId());
                        if(isset($mathLeadUserType[$rowCustomerUser['role_id']])){
                            $leadUser->setType($mathLeadUserType[$rowCustomerUser['role_id']]);
                        } else {
                            $leadUser->setType(\Crm\Model\Lead\User::TYPE_SALE);
                        }

                        if(!$leadUserMapper->isExisted($leadUser)){
                            $leadUser->setCreatedById(1);
                            $leadUser->setCreatedDateTime(DateBase::getCurrentDateTime());
                            $leadUserMapper->save($leadUser);
                        }

                    }
                }
            }
            $totalCreate++;
        }
        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('paginator', $paginator);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
        $this->getViewModel()->setVariable('totalCreate', $totalCreate);
        if ($paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri',
                Uri::build('/system/tool/officeconvertlead', [
                    'page' => $page+1,
                    'totalCreate' => $totalCreate,
                    ]));
        }
        return $this->getViewModel();
    }

    /**
     * Rà soát bảng crm_contracts của office, với status = 1, lấy ra customer_id
     * Nếu account có officeId tương ứng thì bỏ qua
     * Đối soát lại bảng lead dựa theo customer_id
     * Nếu lead đã có accountId, bỏ qua
     * Nếu không, lấy thông tin của lead rà soát xem đã tồn tại account như vậy chưa
     *      Nếu đã tồn tại account, update 1 số thông tin cho account
     *      Nếu không tạo mới account
     * Update accountId cho leadUser và lead
     */
    public function officeconvertaccountAction(){
        list($officeAdapter, $officeSql) = $this->createOfficeAdapter();

        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        /*@var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbSql = $this->getServiceLocator()->get('dbSql');

        $company = new \Company\Model\Company();
        $companyMapper = $this->getServiceLocator()->get('\Company\Model\CompanyMapper');
        $groupCompanyIds = $companyMapper->getAllCompanyIdsInGroup(10);

        $select = $officeSql->select(['cc' => 'crm_contracts']);
        $select->where(['customer_id IS NOT NULL']);
        $select->where(['status' => '1']);
        $select->group(['customer_id']);

        $select->order(['customer_id']);
        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $officeAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage(100);
        $page = $this->getRequest()->getQuery('page', 1);
        $totalCreate = $this->getRequest()->getQuery('totalCreate', 0);
        $totalUpdate = $this->getRequest()->getQuery('totalUpdate', 0);
        $paginator->setCurrentPageNumber($page);

        $leadMapper = $this->getServiceLocator()->get('\Crm\Model\LeadMapper');
        $informationMapper = $this->getServiceLocator()->get('\Contact\Model\Contact\InformationMapper');
        $accountMapper = $this->getServiceLocator()->get('\Crm\Model\AccountMapper');
        foreach ($paginator as $contractRow){
            $contractRow = (array) $contractRow;
            $oneofficeId= $contractRow['customer_id'];

            //Đối soát lại bảng lead dựa theo customer_id
            $lead = new \Crm\Model\Lead();
            $lead->setOneofficeId($oneofficeId);

            if(!$leadMapper->isExistedOneofficeId($lead)){
                continue;
            }
            //Nếu lead đã có accountId, bỏ qua
            if($lead->getAccountId()){
                continue;
            }
            //Nếu không, lấy thông tin của lead rà soát xem đã tồn tại account như vậy chưa
            $information = new \Contact\Model\Contact\Information();
            $information->setItemId($lead->getId());
            $information->setType(\Contact\Model\Contact\Information::TYPE_CRM_LEAD);

            $informations = $informationMapper->fetchAll($information);
            $accountId = null;
            if(count($informations)){
                foreach ($informations as $information){
                    $checkInfo = new \Contact\Model\Contact\Information();
                    $checkInfo->setContent($information->getContent());
                    $checkInfo->addOption('companyIds', $groupCompanyIds);
                    $checkInfo->setType(\Contact\Model\Contact\Information::TYPE_CRM_ACCOUNT);
                    $checkInfo->setItemType($information->getItemType());
                    if($informationMapper->isExisted($checkInfo)){
                        $accountId = $checkInfo->getItemId();
                        break;
                    }
                }
            }
            //Nếu như chưa tồn tại account thì tạo mới dựa trên lead
            if(!$accountId){
                $account = new \Crm\Model\Account();
                $account->setCompanyId($lead->getCompanyId());
                $account->setCityId($lead->getCityId());
                $account->exchangeArray(array(
                    'companyId' => $lead->getCompanyId(),
                    'type' => \Crm\Model\Account::TYPE_PERSONAL,
                    'name' => $lead->getName() ?  : $lead->getCompanyName(),
                    'cityId' => $lead->getCityId() ?  : 2,
                    'districtId' => $lead->getDistrictId() ?  : 1,
                    'address' => $lead->getAddress() ?  : '',
                    'nhanhStoreId' => $lead->getNhanhStoreId(),
                    'nhanhStoreName' => $lead->getNhanhStoreName()
                ));
                $account->setCreatedById($this->user()
                    ->getIdentity());
                $account->setCreatedDate(DateBase::getCurrentDate());
                $account->setCreatedDateTime(DateBase::getCurrentDateTime());
                $accountMapper->save($account);

            } else {
                $account = new \Crm\Model\Account();
                $account->setId($accountId);
                if(!$accountMapper->get($account)){
                    continue;
                }
            }
            // update information cho account
            $infor1 = new \Contact\Model\Contact\Information();
            $infor1->setType(\Contact\Model\Contact\Information::TYPE_CRM_LEAD);
            $infor1->setItemId($lead->getId());

            $infor2 = new \Contact\Model\Contact\Information();
            $infor2->setType(\Contact\Model\Contact\Information::TYPE_CRM_ACCOUNT);
            $infor2->setItemId($account->getId());

            $infoDiffs = $informationMapper->compare($infor1, $infor2);
            if (count($infoDiffs)) {
                foreach ($infoDiffs as $infoData) {
                    $infor = new \Contact\Model\Contact\Information();
                    $infor->exchangeArray($infoData);
                    $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_ACCOUNT);
                    $infor->setItemId($account->getId());
                    $infor->setCompanyId($account->getCompanyId());

                    if (! $informationMapper->isExisted($infor)) {
                        $informationMapper->save($infor);
                    }
                }
            }

            // update accountId cho leadUser và lead
            $lead->setAccountId($account->getId());
            $leadMapper->save($lead);

            $leadUser = new \Crm\Model\Lead\User();
            $leadUserMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\UserMapper');
            $leadUserMapper->updateColumns([
            	'accountId' => $account->getId()
            ], [
                'leadId' => $lead->getId(),
                'accountId IS NULL'
            ]);
        }
        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('paginator', $paginator);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);
        $this->getViewModel()->setVariable('totalCreate', $totalCreate);
        $this->getViewModel()->setVariable('totalUpdate', $totalUpdate);
        if ($paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri',
                Uri::build('/system/tool/officeconvertaccount', [
                    'page' => $page+1,
                    'totalCreate' => $totalCreate,
                    'totalUpdate' => $totalUpdate
                    ]));
        }
        return $this->getViewModel();

    }

    /**
     * rà soát bảng crm_contract của office
     * Tham chiếu bảng contract, nếu đã tồn tại thì bỏ qua
     * Nếu chưa tồn tại thì nạp dữ liệu vào chuỗi officeContractIds để lấy thêm các thông tin khác
     * Lấy ra hết contract_services của officeContractIds (=contract_products)
     * Lấy ra hết crm_income_services của officeContractIds (=commission)
     * Lấy ra hết finance_externals của officeContractIds (=payment.type=chi)
     * Lấy ra hết finance_internals của officeContractIds (=payment.type=thu)
     *
     *
     */
    public function officegetcontractAction(){

    }
//=================================end tool đồng bộ office=============


	/**
	 * @author KienNN
	 * @return \Zend\View\Model\ViewModel
	 * chuyển các file của thông báo về đúng thư mục của nó
	 */
	public function changeanoucementfilelocationAction(){
		$dbSql = $this->getServiceLocator()->get('dbSql');
		$dbAdapter = $this->getServiceLocator()->get('dbAdapter');

		$select = $dbSql->select(['af' => \Company\Model\AnnouncementFileMapper::TABLE_NAME]);
		$select->join(['a' => \Company\Model\AnnouncementMapper::TABLE_NAME], 'af.announcementId=a.id', ['companyId']);
		$select->where(['filePath IS NULL']);
		$select->order(['id asc']);

		$paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
		$paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
		$paginator->setItemCountPerPage(200);
		$page = $this->getRequest()->getQuery('page', 1);
		$totalFile = $this->getRequest()->getQuery('totalFile', 0);
		$paginator->setCurrentPageNumber($page);

		$fileMapper = $this->getServiceLocator()->get('\Company\Model\AnnouncementFileMapper');
		foreach ($paginator as $row){
			$row = (array) $row;
			$fileModel = new \Company\Model\AnnouncementFile();
			$fileModel->exchangeArray($row);

			$filePath = DateBase::toFormat($fileModel->getCreatedDateTime(), 'Ymd');
			$fileModel->setFilePath($filePath);

			$oldSavePath = MEDIA_PATH.'/company/announcement/'.$row['companyId'].'/'
					.$fileModel->getAnnouncementId().'/'.$fileModel->getFileName();

			$tempPath = MEDIA_PATH.'/announcement/temp/'.$fileModel->getAnnouncementId().'/'.$fileModel->getFileName();
			if(file_exists($oldSavePath)){
				$newSavePath = Uri::getSavePath($fileModel);
				if (!file_exists($newSavePath)) {
					$oldmask = umask(0);
					mkdir($newSavePath, 0777, true);
					umask($oldmask);
				}
				@copy($oldSavePath, $newSavePath.'/'.$fileModel->getFileName());
				$fileMapper->save($fileModel);
				$totalFile++;
			} elseif (file_exists($tempPath)){
				$newSavePath = Uri::getSavePath($fileModel);
				if (!file_exists($newSavePath)) {
					$oldmask = umask(0);
					mkdir($newSavePath, 0777, true);
					umask($oldmask);
				}
				@copy($oldSavePath, $newSavePath.'/'.$fileModel->getFileName());
				$fileMapper->save($fileModel);
				$totalFile++;
			}
		}

		$this->getViewModel()->setTerminal(true);
		$this->getViewModel()->setVariable('page', $page);
		$this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);

		if ($page <= $paginator->count()) {
			$this->getViewModel()->setVariable('redirectUri',
				Uri::build('/system/tool/changeanoucementfilelocation', [
					'page' => $page+1,
					'totalFile' => $totalFile
			]));
		}
		$this->getViewModel()->setVariable('totalFile', $totalFile);
		return $this->getViewModel();
	}


	public function fixapierror20150626Action(){
		set_time_limit(300);
		$dbSql = $this->getServiceLocator()->get('dbSql');
		$dbAdapter = $this->getServiceLocator()->get('dbAdapter');
		/*@var $dbAdapter \Zend\Db\Adapter\Adapter */
		$fromDate = '2015-06-26 00:00:00';
		$toDate = '2015-06-30 00:00:00';
		$defaultSupporter = 21;

		// lấy ra các lead do api tạo thành trong khoảng từ 26/6 -> 29/6 (arr1)
		$select = $dbSql->select(['l' => \Crm\Model\LeadMapper::TABLE_NAME]);
		$select->where(['createdById' => 1]);
		$select->where(['createdDateTime >= ?' => $fromDate]);
		$select->where(['createdDateTime <= ?' => $toDate]);
		$query = $dbSql->buildSqlString($select);
		echo $query;
		echo '<br/>';
		echo '<br/>';
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		$leadIds = [];
		$leads = [];
		if($rows->count()){
			foreach ($rows->toArray() as $row){
				$leadIds[$row['id']] = $row['id'];
			}
		}
		unset($select);
		unset($rows);

		// thêm ra các lead có yêu cầu tính năng hoặc đăng kí dùng thử trong đoạn thời gian này
		$select = $dbSql->select(['a' => \Crm\Model\ActivityMapper::TABLE_NAME]);
		$select->columns(['leadId']);
		$select->where(['accountId IS NULL']);
		$select->where(['createdById' => 1]);
		$select->where(['type' => [
				\Crm\Model\Activity::TYPE_CUSTOMER_REQUEST,
				\Crm\Model\Activity::TYPE_REGISTER_FOR_TRIAL,]]);
		$select->where(['createdDateTime >= ?' => $fromDate]);
		$select->where(['createdDateTime <= ?' => $toDate]);
		$query = $dbSql->buildSqlString($select);
		echo $query;
		echo '<br/>';
		echo '<br/>';
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($rows->count()){
			foreach ($rows->toArray() as $row){
				if(!isset($leadIds[$row['leadId']])){
					$leadIds[$row['leadId']] = $row['leadId'];
				}
			}
		}
		unset($select);
		unset($rows);

		// loại trừ trong số lead này các lead  mà lan đang năm giữ (arr2)
		$select = $dbSql->select(['l' => \Crm\Model\LeadMapper::TABLE_NAME]);
		$select->join(['lu' => \Crm\Model\Lead\UserMapper::TABLE_NAME], 'l.id=lu.leadId', []);
		$select->columns(['id']);
		$select->where(['leadId' => $leadIds]);
		$select->where(['lu.userId' => $defaultSupporter]);
		$select->group(['l.id']);
		$query = $dbSql->buildSqlString($select);
		echo $query;
		echo '<br/>';
		echo '<br/>';
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($rows->count()){
			foreach ($rows->toArray() as $row){
				if(isset($leadIds[$row['id']])){
					unset($leadIds[$row['id']]);
				}

			}
		}
		unset($select);
		unset($rows);

		// loaij trừ các leadId trong số đó mà có bất cứ hành động nào ko thuộc (nhận, bàn giao, yêu cầu tính năng, tự đăng kí, yêu cầu gọi)
		$select = $dbSql->select(['a' => \Crm\Model\ActivityMapper::TABLE_NAME]);
		$select->columns(['leadId']);
		$select->where(['leadId' => $leadIds]);
		$select->where(new NotIn('type', [
				\Crm\Model\Activity::TYPE_ASSIGN_LEAD,
				\Crm\Model\Activity::TYPE_SELF_ASSIGN_LEAD,
				\Crm\Model\Activity::TYPE_REQUEST_PHONECALL,
				\Crm\Model\Activity::TYPE_CUSTOMER_REQUEST,
				\Crm\Model\Activity::TYPE_REGISTER_FOR_TRIAL,
		]));
		$select->where(['companyId' => 1]);
		$select->group(['leadId']);
		$query = $dbSql->buildSqlString($select);
		echo $query;
		echo '<br/>';
		echo '<br/>';
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($rows->count()){
			foreach ($rows->toArray() as $row){
				if(isset($leadIds[$row['leadId']])){
					unset($leadIds[$row['leadId']]);
				}

			}
		}
		unset($select);
		unset($rows);

		// với mỗi leadId này, gỡ hết user ra, gắn lại cho Lan + thêm yêu cầu gọi cho cái lan
		if(count($leadIds)){
			$activityMapper = $this->getServiceLocator()->get('\Crm\Model\ActivityMapper');
			$leadMapper = $this->getServiceLocator()->get('\Crm\Model\LeadMapper');
			foreach ($leadIds as $leadId){
				// xóa action nhận - bàn giao của lead này
				$delete = $dbSql->delete(\Crm\Model\ActivityMapper::TABLE_NAME);
				$delete->where(['leadId' => $leadId]);
				$delete->where(['companyId' => 1]);
				$delete->where(['type' => [
					\Crm\Model\Activity::TYPE_ASSIGN_LEAD,
					\Crm\Model\Activity::TYPE_SELF_ASSIGN_LEAD,
				]]);
				$delete->where(['relatedUserId != ?' => $defaultSupporter]);
				$query = $dbSql->buildSqlString($delete);
				$dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

				// xóa user đang chăm sóc lead này
				$delete = $dbSql->delete(\Crm\Model\Lead\UserMapper::TABLE_NAME);
				$delete->where(['leadId' => $leadId]);
				$delete->where(['companyId' => 1]);
				$delete->where(['userId != ?' => $defaultSupporter]);
				$query = $dbSql->buildSqlString($delete);
				$dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

				// lay ra lead
				$lead = new \Crm\Model\Lead();
				$lead->setId($leadId);
				$leadMapper->get($lead);

				// thêm action yêu cầu gọi cho Lan
				$activity = new \Crm\Model\Activity();
    			$activity->setLeadId($leadId);
    			$activity->setCompanyId(1);
    			$activity->setType(\Crm\Model\Activity::TYPE_REQUEST_PHONECALL);
    			$activity->setStatus(\Crm\Model\Activity::STATUS_SUCCESS);
    			$activity->setTitle('Bổ sung xác nhận dùng thử 26/6 - 30/6');
    			$content = [];
    			if($lead->getNhanhStoreId()){
    				$content[] = '<b>nhanhStoreId: </b>'.$lead->getNhanhStoreId();
    			}
    			if($lead->getNhanhStoreName()){
    				$content[] = '<b>nhanhStoreName: </b>'.$lead->getNhanhStoreName();
    			}
    			$activity->setContent(count($content) ? implode('<br/>', $content) : null);
    			$activity->setCreatedById(1);
    			$activity->setCreatedDate(DateBase::getCurrentDate());
    			$activity->setCreatedDateTime(DateBase::getCurrentDateTime());
    			$activity->setRelatedUserId($defaultSupporter);
    			$activityMapper->save($activity);

    			//gắn cho lan
    			$leadUser = new \Crm\Model\Lead\User();
    			$leadUser->setLeadId($leadId);
    			$leadUser->setCompanyId(1);
    			$leadUser->setUserId($defaultSupporter);
    			$leadUser->setType(\Crm\Model\Lead\User::TYPE_SALE);
    			$leadUserMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\UserMapper');
    			if(!$leadUserMapper->isExisted($leadUser)){
    				$leadUser->setCreatedById(1);
    				$leadUser->setCreatedDateTime(DateBase::getCurrentDateTime());
    				$leadUserMapper->save($leadUser);
    			}

    			//update lại companyStatus + lastActivity
    			$leadCompany = new \Crm\Model\Lead\Company();
    			$leadCompany->setLeadId($leadId);
    			$leadCompany->setCompanyId(1);
    			$leadCompanyMapper = $this->getServiceLocator()->get('\Crm\Model\Lead\CompanyMapper');
    			$leadCompanyMapper->isExisted($leadCompany);
    			$leadCompany->setLastActivityDateTime(DateBase::getCurrentDateTime());
    			$leadCompany->setStatus(\Crm\Model\Lead\Company::STATUS_BELONG);
    			$leadCompanyMapper->save($leadCompany);

    			echo 'update lead : <b>'.$leadId.'</b><br/>';

			}

		}
		die;
	}

	/**
	 * @author Hungpx
	 * Update lại số lượng tài sản chính xác nếu import từ bên ngoài
	 */
	public function updatequantityassetsAction(){
	    $ids = [];
	    $dbSql = $this->getServiceLocator()->get('dbSql');
	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
	    $select = $dbSql->select(['a' => \Asset\Model\AssetItemMapper::TABLE_NAME]);
	    $select->columns(['id']);
	    $query = $dbSql->buildSqlString($select);
	    $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
	    if ($rows->count()){
	        foreach ($rows as $row){
	            $ids[] = $row['id'];
	        }
	    }
	    foreach ($ids as $id){
	    	$asset = new \Asset\Model\Asset();
	    	$asset->setItemId($id);
	    	$assetMapper = $this->getServiceLocator()->get('\Asset\Model\AssetMapper');
	    	$assets = $assetMapper->fetchAll($asset);
	    	if ($assets){
	    		$quantity = count($assets);
	    		$assetItem = new AssetItem();
	    		$assetItem->setId($id);
	    		$assetItemMapper = $this->getServiceLocator()->get('\Asset\Model\AssetItemMapper');
	    		if ($assetItemMapper->get($assetItem) && $assetItem->getQuantity() != $quantity){
	    		    $assetItem->setQuantity($quantity);
	    		    $assetItemMapper->save($assetItem);
	    		    echo 'Vừa cập nhật Tài sản id = '.$id.'<br>';
	    		}
	    	}
	    }
	    echo 'Đã cập nhật số lượng tài sản thành công';
	    die;
	}

	public function clonecompanyfeatureAction(){
	    $fromCompanyId = $this->getRequest()->getQuery('fromCompanyId');
	    $toCompanyId = $this->getRequest()->getQuery('toCompanyId');

	    $dbSql = $this->getServiceLocator()->get('dbSql');
	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

	    $select = $dbSql->select(['f' => \Company\Model\FeatureMapper::TABLE_NAME]);
	    $select->where(['companyId' => $fromCompanyId]);

	    $query = $dbSql->buildSqlString($select);
	    $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
	    $actionIds = [];
	    if($rows->count()){
	        foreach ($rows->toArray() as $row){
	            $actionIds[$row['actionId']] = $row['actionId'];
	        }
	    }

	    $toCompanyIds = explode(',', $toCompanyId);
	    $digitFilter = new \Zend\Filter\Digits();
	    $featureMapper = $this->getServiceLocator()->get('\Company\Model\FeatureMapper');
	    if(count($toCompanyIds)){
	        foreach ($toCompanyIds as $companyId){
	            $companyId = $digitFilter->filter($companyId);
	            foreach ($actionIds as $actionId){
	                $feature = new \Company\Model\Feature();
	                $feature->setCompanyId($companyId);
	                $feature->setActionId($actionId);
	                if(!$featureMapper->isExisted($feature)){
	                    $featureMapper->save($feature);
	                }
	            }
	            echo 'done '.$companyId.'<br/>';
	        }
	    }
        die;
	}

	public function clonecompanyrolefeatureAction(){
        $form = new \System\Form\Tool\Clonecompanyrolefeature($this->getServiceLocator());
        $dbSql = $this->getServiceLocator()->get('dbSql');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        if($this->getRequest()->isPost()){
            $form->setData($this->getRequest()->getPost());
            if($form->isValid()){
                $data = $form->getData();

                // láy danh sách actionId
                $select = $dbSql->select(['f' => \Company\Model\Role\FeatureMapper::TABLE_NAME]);
                $select->where(['roleId' => $data['fromRoleId']]);
                $query = $dbSql->buildSqlString($select);
                $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
                $actionIds = [];
                if($rows->count()){
                    foreach ($rows->toArray() as $row){
                        $actionIds[$row['actionId']] = $row['actionId'];
                    }
                }

                $toCompanyIds = explode(',', $data['toCompanyId']);
                $digitFilter = new \Zend\Filter\Digits();
                $companyRoleMapper = $this->getServiceLocator()->get('\Company\Model\RoleMapper');
                $roleFeatureMapper = $this->getServiceLocator()->get('\Company\Model\Role\FeatureMapper');
                foreach ($toCompanyIds as $companyId){
                    $companyId = $digitFilter->filter($companyId);
                    if(!$companyId){
                        continue;
                    }
                    // tạo role
                    $companyRole = new \Company\Model\Role();
                    $companyRole->setCompanyId($companyId);
                    $companyRole->setName($data['asRole']);
                    if(!$companyRoleMapper->isExisted($companyRole)){
                        $companyRole->setCreatedById(1);
                        $companyRole->setCreatedDateTime(DateBase::getCurrentDateTime());
                        $companyRoleMapper->save($companyRole);
                    }

                    foreach ($actionIds as $actionId){
                        $roleFeature = new \Company\Model\Role\Feature();
                        $roleFeature->setActionId($actionId);
                        $roleFeature->setRoleId($companyRole->getId());
                        if(!$roleFeatureMapper->isExisted($roleFeature)){
                            $roleFeature->setCreatedById(1);
                            $roleFeature->setCreatedDateTime(DateBase::getCurrentDateTime());
                            $roleFeatureMapper->save($roleFeature);
                        }
                    }
                    echo 'done company '.$companyId.'<br/>';
                }
            }
            die;
        }
        $this->getViewModel()->setVariable('form', $form);
        return $this->getViewModel();
	}

	public function officeemployeeroleAction(){
	    $positionId = $this->getRequest()->getQuery('positionId');

	    list($officeAdapter, $officeSql) = $this->createOfficeAdapter();

	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
	    $dbSql = $this->getServiceLocator()->get('dbSql');

	    // lấy position tương ứng
	    $select = $officeSql->select(['p' => 'positions']);
	    $select->where(['ID' => $positionId]);
	    $row = $officeAdapter->query($officeSql->buildSqlString($select), $officeAdapter::QUERY_MODE_EXECUTE);
	    $roleName = '';
	    if($row->count()){
	        $row = (array) $row->current();
	        $roleName = $row['title'];
	    }
        if(!$roleName){
            echo 'ko tim thay position!';die;
        }
	    // lấy các personel có position tương ứng
	    $select = $officeSql->select(['p' => 'personnels']);
	    $select->columns(['ID']);
	    $select->where(['position_id' => $positionId]);
	    $select->order(['ID ASC']);

	    $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $officeAdapter);
	    $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
	    $paginator->setItemCountPerPage(100);
	    $page = $this->getRequest()->getQuery('page', 1);
	    $totalUpdate = $this->getRequest()->getQuery('totalUpdate', 0);

	    $paginator->setCurrentPageNumber($page);

	    $employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');
        foreach ($paginator as $row){
            //duyệt từng personel
            $row = (array) $row;
            //tham chiếu với employee, nếu employee đã có role hoặc full quyền thì bỏ qua
            $employee = new \Hrm\Model\Employee();
            $employee->setOneofficeId($row['ID']);
            if(!$employeeMapper->isExistedOneofficeId($employee)){
                continue;
            }
            if($employee->getRole()){
                continue;
            }
            if($employee->getHasFullPrivilege()){
                continue;
            }
            if(!$employee->getUserId()){
                continue;
            }
            //tham chiếu theo tên quyền + companyId
            $companyRole = new \Company\Model\Role();
            $companyRole->setName($roleName);
            $companyRole->setCompanyId($employee->getCompanyId());
            $companyRoleMapper = $this->getServiceLocator()->get('\Company\Model\RoleMapper');
            if(!$companyRoleMapper->isExisted($companyRole)){
                continue;
            }
            //nếu tồn tại thì gắn employee đó với quyền đó
            $employee->setRole($companyRole->getId());
            $employeeMapper->save($employee);
            $totalUpdate++;
        }

        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('paginator', $paginator);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);

        $this->getViewModel()->setVariable('totalUpdate', $totalUpdate);
        if ($page <= $paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri',
                Uri::build('/system/tool/officeemployeerole', [
                    'page' => $page+1,
                    'totalUpdate' => $totalUpdate,
                    'positionId' => $positionId,
                    ]));
        }
        return $this->getViewModel();

	}

	/**
	 * Nghiepj vụ chính là ghép toEmployee với fromUser
	 */
	public function rematchemployeeAction(){
	    $fromEmployeeId = $this->getRequest()->getQuery('fromEmployeeId');
	    $toEmployeeId = $this->getRequest()->getQuery('toEmployeeId');

	    $employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');

	    $fromEmployee = new \Hrm\Model\Employee();
	    $fromEmployee->setId($fromEmployeeId);
        if(!$employeeMapper->get($fromEmployee)){
            echo 'ko tìm thấy from employee';
            die;
        }

        $toEmployee = new \Hrm\Model\Employee();
        $toEmployee->setId($toEmployeeId);
        if(!$employeeMapper->get($toEmployee)){
            echo 'ko tìm thấy to employee';
            die;
        }

        if($fromEmployee->getOneofficeId()){
            echo 'from employee là nhân sự được đồng bộ';
            die;
        }

        $userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
        if($fromEmployee->getUserId()){
            $fromUser = $userMapper->get($fromEmployee->getUserId());
            if($fromUser->getOneofficeId()){
                echo 'from employee gắn với user do đồng bộ về';
                die;
            }

            if($toEmployee->getUserId()){
                $toUser = $userMapper->get($toEmployee->getUserId());
                $fromUser->setEmployeeCode($toUser->getEmployeeCode());
                $fromUser->setOneofficeId($toUser->getOneofficeId());
                $userMapper->save($fromUser);

                $toUser->setEmployeeCode(null);
                $toUser->setOneofficeId(null);
                $userMapper->save($toUser);
            }

            $toEmployee->setUserId($fromUser->getId());
            if(!$toEmployee->getInsuranceBookId()){
                $toEmployee->setInsuranceBookId($fromEmployee->getInsuranceBookId());
            }
            $employeeMapper->save($toEmployee);

            $fromEmployee->setUserId(null);
            $fromEmployee->setInsuranceBookId(null);
            $employeeMapper->save($fromEmployee);

            $employeeMapper->updateColumns([
                'workingStatus' =>\Hrm\Model\Employee::WORKING_STATUS_DELETED,
                'userId' => null,
                'insuranceBookId' => null
		   ], $fromEmployee);

        } else {
            echo 'done'; die;
        }


        echo 'done'; die;

	}



	public function covertleadAction(){
	    set_time_limit(300);
	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
	    $dbSql = $this->getServiceLocator()->get('dbSql');

	    $totalConvert = $this->getRequest()->getQuery('totalConvert');

	    $select = $dbSql->select(['l' => \Crm\Model\LeadMapper::TABLE_NAME]);
	    $select->order(['id DESC']);

	    $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
	    $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
	    $paginator->setItemCountPerPage(300);
	    $page = $this->getRequest()->getQuery('page', 1);
	    $paginator->setCurrentPageNumber($page);

	    $inforMapper = $this->getServiceLocator()->get('\Contact\Model\Contact\InformationMapper');
	    $currentDateTime = DateBase::getCurrentDateTime();
	    foreach ($paginator as $row){
	        $row = (array) $row;
	        $leadId = $row['id'];
	        $select = $dbSql->select(['ci' => \Contact\Model\Contact\InformationMapper::TABLE_NAME]);
	        $select->where([
			    'type' => \Contact\Model\Contact\Information::TYPE_CRM_LEAD,
	            'itemId' => $row['id']
	        ]);
	        $select->limit(1);
            $rowCheck = $dbAdapter->query($dbSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
            if($rowCheck->count()){
                continue;
            }
            unset($rowCheck);

            // name


            // mobile
            if($row['mobile']){
                $infor = new \Contact\Model\Contact\Information();
                $infor->setCompanyId($row['companyId']);
                $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_LEAD);
                $infor->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_MOBILE);
                $infor->setItemId($leadId);
                $infor->setContent($row['mobile']);
                if(!$inforMapper->isExisted($infor)){
                    $infor->setCreatedById(1);
                    $infor->setCreatedDateTime($currentDateTime);
                    $inforMapper->save($infor);
                }
            }
            // mobile2
            if($row['mobile2']){
                $infor = new \Contact\Model\Contact\Information();
                $infor->setCompanyId($row['companyId']);
                $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_LEAD);
                $infor->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_MOBILE);
                $infor->setItemId($leadId);
                $infor->setContent($row['mobile2']);
                if(!$inforMapper->isExisted($infor)){
                    $infor->setCreatedById(1);
                    $infor->setCreatedDateTime($currentDateTime);
                    $inforMapper->save($infor);
                }
            }
            // phone
            if($row['phone']){
                $infor = new \Contact\Model\Contact\Information();
                $infor->setCompanyId($row['companyId']);
                $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_LEAD);
                $infor->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_MOBILE);
                $infor->setItemId($leadId);
                $infor->setContent($row['phone']);
                if(!$inforMapper->isExisted($infor)){
                    $infor->setCreatedById(1);
                    $infor->setCreatedDateTime($currentDateTime);
                    $inforMapper->save($infor);
                }
            }
            // phone2
            if($row['phone2']){
                $infor = new \Contact\Model\Contact\Information();
                $infor->setCompanyId($row['companyId']);
                $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_LEAD);
                $infor->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_MOBILE);
                $infor->setItemId($leadId);
                $infor->setContent($row['phone2']);
                if(!$inforMapper->isExisted($infor)){
                    $infor->setCreatedById(1);
                    $infor->setCreatedDateTime($currentDateTime);
                    $inforMapper->save($infor);
                }
            }
            // email
            if($row['email']){
                $infor = new \Contact\Model\Contact\Information();
                $infor->setCompanyId($row['companyId']);
                $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_LEAD);
                $infor->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_EMAIL);
                $infor->setItemId($leadId);
                $infor->setContent($row['email']);
                if(!$inforMapper->isExisted($infor)){
                    $infor->setCreatedById(1);
                    $infor->setCreatedDateTime($currentDateTime);
                    $inforMapper->save($infor);
                }
            }
            // website
            if($row['website']){
                $infor = new \Contact\Model\Contact\Information();
                $infor->setCompanyId($row['companyId']);
                $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_LEAD);
                $infor->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_WEBSITE);
                $infor->setItemId($leadId);
                $infor->setContent($row['website']);
                if(!$inforMapper->isExisted($infor)){
                    $infor->setCreatedById(1);
                    $infor->setCreatedDateTime($currentDateTime);
                    $inforMapper->save($infor);
                }
            }
            $totalConvert++;
	    }

	    $this->getViewModel()->setTerminal(true);
	    $this->getViewModel()->setVariable('paginator', $paginator);
	    $this->getViewModel()->setVariable('page', $page);
	    $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);

	    $this->getViewModel()->setVariable('totalConvert', $totalConvert);
	    if ($page <= $paginator->count()) {
	        $this->getViewModel()->setVariable('redirectUri',
	            Uri::build('/system/tool/covertlead', [
	                'page' => $page+1,
	                'totalConvert' => $totalConvert,
	                ]));
	    }
	    return $this->getViewModel();
	}

	public function covertaccountAction(){
	    set_time_limit(300);
	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
	    $dbSql = $this->getServiceLocator()->get('dbSql');

	    $totalConvert = $this->getRequest()->getQuery('totalConvert');

	    $select = $dbSql->select(['a' => \Crm\Model\AccountMapper::TABLE_NAME]);
	    $select->order(['id DESC']);

	    $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
	    $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
	    $paginator->setItemCountPerPage(100);
	    $page = $this->getRequest()->getQuery('page', 1);
	    $paginator->setCurrentPageNumber($page);

	    $inforMapper = $this->getServiceLocator()->get('\Contact\Model\Contact\InformationMapper');
	    $currentDateTime = DateBase::getCurrentDateTime();
	    foreach ($paginator as $row){
	        $row = (array) $row;
	        $accountId = $row['id'];
	        $select = $dbSql->select(['ci' => \Contact\Model\Contact\InformationMapper::TABLE_NAME]);
	        $select->where([
	            'type' => \Contact\Model\Contact\Information::TYPE_CRM_ACCOUNT,
	            'itemId' => $row['id']
	            ]);
	        $select->limit(1);
	        $rowCheck = $dbAdapter->query($dbSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
	        if($rowCheck->count()){
	            continue;
	        }
	        unset($rowCheck);

	        // mobile
	        if($row['mobile']){
	            $infor = new \Contact\Model\Contact\Information();
	            $infor->setCompanyId($row['companyId']);
	            $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_ACCOUNT);
	            $infor->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_MOBILE);
	            $infor->setItemId($accountId);
	            $infor->setContent($row['mobile']);
	            if(!$inforMapper->isExisted($infor)){
	                $infor->setCreatedById(1);
	                $infor->setCreatedDateTime($currentDateTime);
	                $inforMapper->save($infor);
	            }
	        }
	        // mobile2
	        if($row['mobile2']){
	            $infor = new \Contact\Model\Contact\Information();
	            $infor->setCompanyId($row['companyId']);
	            $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_ACCOUNT);
	            $infor->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_MOBILE);
	            $infor->setItemId($accountId);
	            $infor->setContent($row['mobile2']);
	            if(!$inforMapper->isExisted($infor)){
	                $infor->setCreatedById(1);
	                $infor->setCreatedDateTime($currentDateTime);
	                $inforMapper->save($infor);
	            }
	        }
	        // phone
	        if($row['phone']){
	            $infor = new \Contact\Model\Contact\Information();
	            $infor->setCompanyId($row['companyId']);
	            $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_ACCOUNT);
	            $infor->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_MOBILE);
	            $infor->setItemId($accountId);
	            $infor->setContent($row['phone']);
	            if(!$inforMapper->isExisted($infor)){
	                $infor->setCreatedById(1);
	                $infor->setCreatedDateTime($currentDateTime);
	                $inforMapper->save($infor);
	            }
	        }
	        // phone2
	        if($row['phone2']){
	            $infor = new \Contact\Model\Contact\Information();
	            $infor->setCompanyId($row['companyId']);
	            $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_ACCOUNT);
	            $infor->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_MOBILE);
	            $infor->setItemId($accountId);
	            $infor->setContent($row['phone2']);
	            if(!$inforMapper->isExisted($infor)){
	                $infor->setCreatedById(1);
	                $infor->setCreatedDateTime($currentDateTime);
	                $inforMapper->save($infor);
	            }
	        }
	        // alternativePhone
	        if($row['alternativePhone']){
	            $infor = new \Contact\Model\Contact\Information();
	            $infor->setCompanyId($row['companyId']);
	            $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_ACCOUNT);
	            $infor->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_MOBILE);
	            $infor->setItemId($accountId);
	            $infor->setContent($row['alternativePhone']);
	            if(!$inforMapper->isExisted($infor)){
	                $infor->setCreatedById(1);
	                $infor->setCreatedDateTime($currentDateTime);
	                $inforMapper->save($infor);
	            }
	        }
	        // email
	        if($row['email']){
	            $infor = new \Contact\Model\Contact\Information();
	            $infor->setCompanyId($row['companyId']);
	            $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_ACCOUNT);
	            $infor->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_EMAIL);
	            $infor->setItemId($accountId);
	            $infor->setContent($row['email']);
	            if(!$inforMapper->isExisted($infor)){
	                $infor->setCreatedById(1);
	                $infor->setCreatedDateTime($currentDateTime);
	                $inforMapper->save($infor);
	            }
	        }
	        // website
	        if($row['website']){
	            $infor = new \Contact\Model\Contact\Information();
	            $infor->setCompanyId($row['companyId']);
	            $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_ACCOUNT);
	            $infor->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_WEBSITE);
	            $infor->setItemId($accountId);
	            $infor->setContent($row['website']);
	            if(!$inforMapper->isExisted($infor)){
	                $infor->setCreatedById(1);
	                $infor->setCreatedDateTime($currentDateTime);
	                $inforMapper->save($infor);
	            }
	        }
	        // fax
	        if($row['fax']){
	            $infor = new \Contact\Model\Contact\Information();
	            $infor->setCompanyId($row['companyId']);
	            $infor->setType(\Contact\Model\Contact\Information::TYPE_CRM_ACCOUNT);
	            $infor->setItemType(\Contact\Model\Contact\Information::ITEM_TYPE_WEBSITE);
	            $infor->setItemId($accountId);
	            $infor->setContent($row['fax']);
	            if(!$inforMapper->isExisted($infor)){
	                $infor->setCreatedById(1);
	                $infor->setCreatedDateTime($currentDateTime);
	                $inforMapper->save($infor);
	            }
	        }
	        $totalConvert++;
	    }

	    $this->getViewModel()->setTerminal(true);
	    $this->getViewModel()->setVariable('paginator', $paginator);
	    $this->getViewModel()->setVariable('page', $page);
	    $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);

	    $this->getViewModel()->setVariable('totalConvert', $totalConvert);
	    if ($page <= $paginator->count()) {
	        $this->getViewModel()->setVariable('redirectUri',
	            Uri::build('/system/tool/covertaccount', [
	                'page' => $page+1,
	                'totalConvert' => $totalConvert,
	                ]));
	    }
	    return $this->getViewModel();
	}

	/**
	 * Nếu hợp đồng đã có mission => bỏ qua
	 * Nếu chưa, lấy salemanId, tìm ra employee, lấy commisstion là maximum của product,
	 * Tìm trong contract_product, lấy ra endDate lớn nhất, nạp cho endDate của contract
	 */
	public function convertcrmcontract06082015Action(){
	    set_time_limit(300);
	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
	    $dbSql = $this->getServiceLocator()->get('dbSql');

	    $select = $dbSql->select(['c' => \Crm\Model\ContractMapper::TABLE_NAME]);
	    $select->order(['c.id asc']);

	    $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
	    $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
	    $paginator->setItemCountPerPage(100);
	    $page = $this->getRequest()->getQuery('page', 1);
	    $totalConvert = $this->getRequest()->getQuery('totalConvert', 0);
	    $paginator->setCurrentPageNumber($page);

	    $contractMapper = $this->getServiceLocator()->get('\Crm\Model\ContractMapper');
	    $commissionMapper = $this->getServiceLocator()->get('\Crm\Model\Contract\CommissionMapper');
	    foreach ($paginator as $row){
	        $row = (array) $row;
	        $contract = new \Crm\Model\Contract();
	        $contract->exchangeArray($row);

	        $select = $dbSql->select(['cc' => \Crm\Model\Contract\CommissionMapper::TABLE_NAME]);
	        $select->where(['contractId' => $contract->getId()]);
	        $rows = $dbAdapter->query($dbSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
	        if($rows->count()){
	            continue;
	        }

	        // update startDate, endDate
	        if(!$contract->getStartDate()){
	            $select = $dbSql->select(['cp' => \Crm\Model\Contract\ProductMapper::TABLE_NAME]);
	            $select->columns([
	                'minStartDate' => new Expression('MIN(startDate)')
	                ]);
	            $select->where(['contractId' => $contract->getId()]);
	            $select->where(['startDate IS NOT NULL']);
	            $select->where(['startDate != ?' => '0000-00-00']);
	            $row = $dbAdapter->query($dbSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
	            if($row->count()){
	                $row = (array) $row->current();
	                $contract->setStartDate($row['minStartDate']);
	            }
	        }
	        if(!$contract->getEndDate()){
	            $select = $dbSql->select(['cp' => \Crm\Model\Contract\ProductMapper::TABLE_NAME]);
	            $select->columns([
	                'maxEndDate' => new Expression('MAX(endDate)')
	                ]);
	            $select->where(['contractId' => $contract->getId()]);
	            $select->where(['endDate IS NOT NULL']);
	            $select->where(['endDate != ?' => '0000-00-00']);
	            $row = $dbAdapter->query($dbSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
	            if($row->count()){
	                $row = (array) $row->current();
	                $contract->setEndDate($row['maxEndDate']);
	            }
	        }

            $contractMapper->save($contract);

            if($contract->getSalemanId()){
                $select = $dbSql->select(['e' => \Hrm\Model\EmployeeMapper::TABLE_NAME]);
                $select->where(['userId' => $contract->getSalemanId()]);
                $select->limit(1);
                $row = $dbAdapter->query($dbSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
                if($row->count()){
                    $row =(array) $row->current();
                    $employeeId = $row['id'];
                    $select = $dbSql->select(['cp' => \Crm\Model\Contract\ProductMapper::TABLE_NAME]);
                    $select->join(['p' => \Company\Model\ProductMapper::TABLE_NAME], 'cp.productId = p.id', [
                	'p.commissionValue' => 'commissionValue'
                    ]);

                    $select->where(['contractId' => $contract->getId()]);
                    $rowCps = $dbAdapter->query($dbSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
                    if($rowCps->count()){
                        foreach ($rowCps->toArray() as $rowCp){
                            $contractProduct = new \Crm\Model\Contract\Product($rowCp);
                            $productValue = $contractProduct->calculateValue();

                            $commission = new \Crm\Model\Contract\Commission();
                            if($rowCp['p.commissionValue']){
                                $commission->setCommissionPercent(100);
                            } else {
                                $commission->setCommissionPercent($rowCp['p.commissionValue']);
                            }
                            $commissionValue = round($productValue * $commission->getCommissionPercent() /100);
                            $commission->setCommission($commissionValue);
                            $commission->setProductId($rowCp['productId']);
                            $commission->setEmployeeId($employeeId);
                            $commission->setContractId($contract->getId());
                            if(!$commissionMapper->isExisted($commission)){
                                $commissionMapper->save($commission);
                            }
                        }
                    }

                }
            }
            $contractMapper->updateCommission($contract);
            $totalConvert++;
	    }

	    $this->getViewModel()->setTerminal(true);
	    $this->getViewModel()->setVariable('paginator', $paginator);
	    $this->getViewModel()->setVariable('page', $page);
	    $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);

	    $this->getViewModel()->setVariable('totalConvert', $totalConvert);
	    if ($page <= $paginator->count()) {
	        $this->getViewModel()->setVariable('redirectUri',
	            Uri::build('/system/tool/convertcrmcontract06082015', [
	                'page' => $page+1,
	                'totalConvert' => $totalConvert,
	                ]));
	    }
	    return $this->getViewModel();
	}

	/**
	 * Rà soát tất cả payment
	 * check nếu payment đã có transactionId => bỏ qua
	 * Nếu không, tạo mới transaction, transactionItem, với transaction.id=payment.id
	 * update transactionId cho payment
	 *
	 */
	public function paymenttotransactionAction(){
	    $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
	    $dbSql = $this->getServiceLocator()->get('dbSql');

	    $select = $dbSql->select(['p' => \Crm\Model\Contract\PaymentMapper::TABLE_NAME]);
	    $select->join(['c'=>\Crm\Model\ContractMapper::TABLE_NAME], 'p.contractId=c.id', ['c.companyId' => 'companyId']);
	    $select->order(['p.id asc']);

	    $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
	    $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
	    $paginator->setItemCountPerPage(200);
	    $page = $this->getRequest()->getQuery('page', 1);
	    $totalConvert = $this->getRequest()->getQuery('totalConvert', 0);
	    $paginator->setCurrentPageNumber($page);

	    $transactionItemMapper = $this->getServiceLocator()->get('\Accounting\Model\Transaction\ItemMapper');
	    $transactionMapper = $this->getServiceLocator()->get('\Accounting\Model\TransactionMapper');
	    $paymentMapper = $this->getServiceLocator()->get('\Crm\Model\Contract\PaymentMapper');
	    foreach ($paginator as $row){
            $row = (array) $row;
            $payment = new \Crm\Model\Contract\Payment();
            $payment->exchangeArray($row);
            if($row['transactionId']){
                continue;
            }
            $transaction = new \Accounting\Model\Transaction();
            $transaction->setId($payment->getId());
            if($payment->getCompanyId()){
                $transaction->setCompanyId($payment->getCompanyId());
            } else {
                $transaction->setCompanyId($row['c.companyId']);
            }

            if($payment->getPaymentType() == \Crm\Model\Contract\Payment::PAYMENT_TYPE_EXPORT){
                $transaction->setType(\Accounting\Model\Transaction::TYPE_PAY);
            } else {
                $transaction->setType(\Accounting\Model\Transaction::TYPE_RECEIPT);
            }
            $transaction->setItemType(\Accounting\Model\Transaction::ITEM_TYPE_CRM_CONTRACT);
            $transaction->setItemId($payment->getContractId());
            $transaction->setCreatedById($payment->getCreatedById());
            $createdDateTime = new \DateTime($payment->getCreatedDateTime());
            $transaction->setCreatedDate($createdDateTime->format(DateBase::COMMON_DATE_FORMAT));
            $transaction->setCreatedTime($createdDateTime->format(DateBase::COMMON_TIME_FORMAT));
            if($payment->getStatus() == \Crm\Model\Contract\Payment::STATUS_DELETED){
                $transaction->setStatus(\Accounting\Model\Transaction::STATUS_INAPPROVED);
            } elseif ($payment->getStatus() == \Crm\Model\Contract\Payment::STATUS_CHECKED){
                $transaction->setStatus(\Accounting\Model\Transaction::STATUS_ACCOUNTING);
            } else {
                $transaction->setStatus(\Accounting\Model\Transaction::STATUS_NEW);
            }
            $transaction->setAmount($payment->getAmount());
            $transaction->setDescription($payment->getDescription());
            if($payment->getCheckedDate()){
                $transaction->setApplyDate($payment->getCheckedDate());
            } else {
                $transaction->setApplyDate($createdDateTime->format(DateBase::COMMON_DATE_FORMAT));
            }
            if($payment->getCheckedById()){
                $transaction->setAccountingById($payment->getCheckedById());
            }
            if($payment->getCheckedDateTime()){
                $transaction->setAccountingDateTime($payment->getCheckedDateTime());
            }

            $data = array(
                'id'                => $transaction->getId(),
                'companyId'          =>  $transaction->getCompanyId(),
                'type'               =>  $transaction->getType(),
                'applyDate'          =>  $transaction->getApplyDate()?:null,
                'amount'             =>  $transaction->getAmount()?:null,
                'description'        =>  $transaction->getDescription()?:null,
                'status'             =>  $transaction->getStatus(),
                'createdDate'        =>  $transaction->getCreatedDate(),
                'createdById'        =>  $transaction->getCreatedById(),
                'createdTime'        =>  $transaction->getCreatedTime(),
                'approvedById'       =>  $transaction->getApprovedById()?:null,
                'approvedDateTime'   =>  $transaction->getApprovedDateTime()?:null,
                'accountingById'     =>  $transaction->getAccountingById()?:null,
                'accountingDateTime' =>  $transaction->getAccountingDateTime()?:null,
                'paymentById'        =>  $transaction->getPaymentById()?:null,
                'paymentDateTime'    =>  $transaction->getPaymentDateTime()?:null,
                'itemType'           =>  $transaction->getItemType()?:null,
                'itemId'             =>  $transaction->getItemId()?:null,
            );
            $insert = $dbSql->insert($transactionMapper::TABLE_NAME);
            $insert->values($data);
            $dbAdapter->query($dbSql->buildSqlString($insert), Adapter::QUERY_MODE_EXECUTE);

            //$transactionMapper->save($transaction);

            $payment->setTransactionId($transaction->getId());

            $paymentMapper->save($payment);

            $transactionItem = new \Accounting\Model\Transaction\Item();
            $transactionItem->setTransactionId($transaction->getId());
            $transactionItem->setDate($transaction->getApplyDate());
            $transactionItem->setAmount($transaction->getAmount());
            if($transaction->getType() == \Accounting\Model\Transaction::TYPE_RECEIPT){
                if($payment->getType() == $payment::TYPE_MONEY_TRANSFER){
                    $transactionItem->setCreditAccountId(308);
                } else {
                    $transactionItem->setCreditAccountId(303);
                }

            } else {
                if($payment->getType() == $payment::TYPE_MONEY_TRANSFER){
                    $transactionItem->setDebitAccountId(308);
                } else {
                    $transactionItem->setDebitAccountId(303);
                }

            }
            $transactionItem->setItemType($transaction->getItemType());
            $transactionItem->setItemId($transaction->getItemId());
            $transactionItem->setDescription($transaction->getDescription());
            $transactionItem->setStatus($transaction->getStatus());

            $transactionItemMapper->save($transactionItem);
            $totalConvert++;
	    }
	    $this->getViewModel()->setTerminal(true);
	    $this->getViewModel()->setVariable('paginator', $paginator);
	    $this->getViewModel()->setVariable('page', $page);
	    $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);

	    $this->getViewModel()->setVariable('totalConvert', $totalConvert);
	    if ($page <= $paginator->count()) {
	        $this->getViewModel()->setVariable('redirectUri',
	            Uri::build('/system/tool/paymenttotransaction', [
	                'page' => $page+1,
	                'totalConvert' => $totalConvert,
	                ]));
	    }
	    return $this->getViewModel();
	}

	/**
	 * Chỉ rà soát trong 1 khoảng thời gian nhất định
	 * Rà soát các transaction trong thời gian quy định
	 * Tình toán ra commission theo từng nhân viên
	 * Xóa các payment có transactionId=transaction.id
	 * Tạo mới payment
	 *
	 */
    public function recalculatepaymentAction(){
        $fromDate = '2015-08-01';
        $transactionId = $this->getRequest()->getQuery('transactionId');
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $dbSql = $this->getServiceLocator()->get('dbSql');

        $select = $dbSql->select(['t' => \Accounting\Model\TransactionMapper::TABLE_NAME]);
        $select->join(['ti' => \Accounting\Model\Transaction\ItemMapper::TABLE_NAME], 't.id=ti.transactionId', [
            'accountingAccountId' => new Expression('IFNULL(ti.creditAccountId, ti.debitAccountId)')
        ]);
        $select->where(['createdDate >= ?' => $fromDate]);
        if($transactionId){
            $select->where(['t.id' => $transactionId]);
        }

        $query = $dbSql->buildSqlString($select);
        //echo $query;die;
        $rows = $dbAdapter->query($query, Adapter::QUERY_MODE_EXECUTE);

        $commissionMapper = $this->getServiceLocator()->get('\Crm\Model\Contract\CommissionMapper');
        $productMapper = $this->getServiceLocator()->get('\Crm\Model\Contract\ProductMapper');
        $employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');
        $paymentMapper = $this->getServiceLocator()->get('\Crm\Model\Contract\PaymentMapper');
        $accountingAccountMapper = $this->getServiceLocator()->get('\Accounting\Model\AccountMapper');

        if($rows->count()){
            foreach ($rows->toArray() as $row){
                $transaction = new \Accounting\Model\Transaction();
                $transaction->exchangeArray($row);

                $accountingAccount = new \Accounting\Model\Account();
                $accountingAccount->setId($row['accountingAccountId']);

                $accountingAccountMapper->get($accountingAccount);

                /** tính toán commission */
                // lấy ra các sản phẩm trong hợp đồng
                $product = new \Crm\Model\Contract\Product();
                $product->setContractId($transaction->getItemId());
                $products = $productMapper->fetchAll($product);
                // lấy commission
                $commission = new \Crm\Model\Contract\Commission();
                $commission->setContractId($transaction->getItemId());
                $commissions = $commissionMapper->fetchAll($commission);

                $paymentAmounts = \Crm\Model\Contract\Payment::breakToCommission(
                    $transaction->getAmount(), $products, $commissions);

                /** Xóa các payment có transactionId=transaction.id */
                $delete = $dbSql->delete(\Crm\Model\Contract\PaymentMapper::TABLE_NAME);
                $delete->where(['transactionId' => $transaction->getId()]);
                $dbAdapter->query($dbSql->buildSqlString($delete), Adapter::QUERY_MODE_EXECUTE);

                /** Tạo mới payment */
                echo '<b>Tạo payment từ transaction '.$transaction->getId().'</b><br/>';
                 foreach ($paymentAmounts as $employeeId => $amount){
                        // lấy ra user của employee
                        $employee = new \Hrm\Model\Employee();
                        $employee->setId($employeeId);
                        $employeeMapper->get($employee);
                        if($employee->getUserId()){
                            $select = $dbSql->select(['p' => \Crm\Model\Contract\PaymentMapper::TABLE_NAME]);
                            $select->where(['transactionId' => $transaction->getId()]);
                            $select->where(['salemanId' => $employee->getUserId()]);
                            $select->limit(1);
                            $rowP = $dbAdapter->query($dbSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
                            if($rowP->count()){
                                $rowP = (array) $rowP->current();

                                $payment = new \Crm\Model\Contract\Payment();
                                $payment->exchangeArray($rowP);
                                $payment->setAmount($amount);
                                $paymentMapper->save($payment);

                                echo '<p style="">Update payment '.$payment->getId()
                                    .' - amount: '.$rowP['amount'].' -> '.$payment->getAmount(). '</p><br/>';

                            } else {
                                $payment = new \Crm\Model\Contract\Payment();
                                $payment->setAmount($amount);
                                $payment->setSalemanId($employee->getUserId());
                                $payment->setAccountingAccountId($accountingAccount->getId());
                                $payment->setTransactionId($transaction->getId());
                                $payment->setDescription($transaction->getDescription());
                                $payment->setCompanyId($employee->getCompanyId());
                                $payment->setDepartmentId($employee->getDepartmentId());
                                $payment->setContractId($transaction->getItemId());

                                if($accountingAccount->getType() == \Accounting\Model\Account::TYPE_CASH){
                                    $payment->setType(\Crm\Model\Contract\Payment::TYPE_CASH);
                                } else {
                                    $payment->setType(\Crm\Model\Contract\Payment::TYPE_MONEY_TRANSFER);
                                }
                                if(in_array($transaction->getStatus(), [\Accounting\Model\Transaction::STATUS_ACCOUNTING, \Accounting\Model\Transaction::STATUS_PAYMENT])){
                                    $payment->setStatus(\Crm\Model\Contract\Payment::STATUS_CHECKED);
                                } elseif (in_array($transaction->getStatus(), [\Accounting\Model\Transaction::STATUS_NEW, \Accounting\Model\Transaction::STATUS_APPROVED])){
                                    $payment->setStatus(\Crm\Model\Contract\Payment::STATUS_UNCHECKED);
                                } else {
                                    $payment->setStatus(\Crm\Model\Contract\Payment::STATUS_DELETED);
                                }

                                $payment->setCheckedById($transaction->getAccountingById());
                                $payment->setCheckedDate($transaction->getApplyDate());
                                $payment->setCheckedDateTime($transaction->getAccountingDateTime());
                                $payment->setCreatedById($transaction->getAccountingById()?:$transaction->getCreatedById());
                                $payment->setCreatedDateTime($transaction->getAccountingDateTime()?:($transaction->getCreatedDate().' '.$transaction->getCreatedTime()));
                                $paymentMapper->save($payment);

                                echo '--<span style="color:red;">Tạo mới payment '.$payment->getId().'</span></br/>';
                                echo '--------User: '.$employee->getFullName().'<br/>';
                                echo '--------Amount: '.$payment->getAmount().'/ '.$transaction->getAmount().'<br/>';

                            }
                        }
                 }
            }
        }
        die;
    }

    /**
     * lấy ra danh sách employee bị tạo sau mung 1 thang 8 mà ko phải do đồng bộ về
     * Lấy ra lịch sử tạo tương ứng của employee đó
     * Nếu có lịch sử, echange array cho employee về lịch sử đó, set code= null
     * Nếu chưa có lịch sử, tham chiếu theo user để lấy lại tên, email, các thông tin khác đánh về null hết
     */
    public function revertemployee20150818Action(){
         $fromDate = '2015-08-01 00:00:00';
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $dbSql = $this->getServiceLocator()->get('dbSql');

        $select = $dbSql->select(['e' => \Hrm\Model\EmployeeMapper::TABLE_NAME]);
        $select->where(['createdDateTime >= ?' => $fromDate]);
        $select->where(['createdById != ?' => 1]);
        $select->where(['code IS NOT NULL']);
        $select->where(['oneofficeId IS NOT NULL']);
        $rows = $dbAdapter->query($dbSql->buildSqlString($select), $dbAdapter::QUERY_MODE_EXECUTE);
        $employeeIds = [];
        $employees= [];
        foreach ($rows->toArray() as $row){
            $employeeIds[$row['id']] = $row['id'];
            $employees[$row['id']] = new \Hrm\Model\Employee($row);
        }
        if(!count($employeeIds)){
            echo 'Ko timf thaays ban ghi naof';die;
        }

        /** lấy ra danh sách employee bị tạo sau mung 1 thang 8 mà ko phải do đồng bộ về*/
        $select = $dbSql->select(['h' => \Hrm\Model\Employee\HistoryMapper::TABLE_NAME]);
        $select->where(['employeeId' => $employeeIds]);
        $select->where(['type' => 1]);
        $select->group(['employeeId']);
        $query = $dbSql->buildSqlString($select);
        $rows = $dbAdapter->query($dbSql->buildSqlString($select), $dbAdapter::QUERY_MODE_EXECUTE);
        $histories = [];
        foreach ($rows->toArray() as $row){
            $histories[$row['employeeId']] = gzuncompress($row['dataAfter']);

        }
        $this->getViewModel()->setVariable('employees', $employees);
        $this->getViewModel()->setVariable('histories', $histories);

        $employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');
        $userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
        foreach ($histories as $employeeId => $history){
            $history = json_decode($history, true);
            $employee = $employees[$employeeId];
            $userId = $employee->getUserId();
            $employee->exchangeArray($history);
            if($employee->getBirthdate()){
                $employee->setBirthdate(DateBase::toCommonDate($employee->getBirthdate()));
            }
            if($employee->getQuitDate()){
                $employee->setQuitDate(DateBase::toCommonDate($employee->getQuitDate()));
            }
            if($employee->getBirthdate()){
                $employee->setBirthdate(DateBase::toCommonDate($employee->getBirthdate()));
            }
            if($employee->getReceiveContractDate()){
                $employee->setReceiveContractDate(DateBase::toCommonDate($employee->getReceiveContractDate()));
            }
            $employee->setCode(null);
            $employee->setOneofficeId(null);
            $employee->setUserId($userId);

            $employeeMapper->save($employee);
            echo 'revert từ history employee '.$employee->getId();
            echo '<br/>';
            unset($employees[$employeeId]);
        }
        if(count($employees)){
            foreach ($employees as $employee){
                $employee->setCode(null);
                $employee->setOneofficeId(null);
                $employee->exchangeArray(array(
                    'maritalStatus' => null,
                    'birthdate' => null,
                    'birthplace' => null,
                    'hometown' =>  null,
                    'identification' =>  null,
                    'identificationPlace' => null,
                    'identificationDate' => null,
                    'nation' =>  null,
                    'countryId' => null,
                    'startedDate' => null,
                    'hasFullPrivilege' => null,
                    'oneofficeId' => null,
                    'mobile' => null,
                    'email' => null,
                    'yahoo' => null,
                    'skype' => null,
                    'facebook' => null,
                    'religion' => null,
                    'birthCertificate' => null,
                    'sittingPositionId' => null,
                    'workingCityId' => null,
                    'workingStatus' => null,
                    'insuranceBookId' => null,
                    'taxCode' => null,
                    'bankAccountName' => null,
                    'bankAccountNumber' => null,
                    'bankAccountBranch' => null,
                    'quitDate' => null,
                    'quitReason' => null,
                    'quitConfirmedById' => null,
                    'quitConfirmedDateTime' => null,
                    'temporateAddress' => null,
                    'permanentAddress' => null,
                    'receiveContractDate' =>  null,
                    'extraContent' =>null
                ));


                $user = $userMapper->get($employee->getUserId());
                if($user){
                    $employee->setFullName($user->getFullName());
                    list($lastName, $middleName, $firstName) = Format::splitFullName($employee->getFullName());
                    $employee->setLastName($lastName);
                    $employee->setMiddleName($middleName);
                    $employee->setFirstName($firstName);
                    $employee->setEmail($user->getEmail());
                }
                $employeeMapper->save($employee);
                echo 'Set lại data '.$employee->getId();
                echo '<br/>';
            }
        }
        die;
        //return $this->getViewModel();
    }


    public function findinvaildmapuserAction(){
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $dbSql = $this->getServiceLocator()->get('dbSql');

        $select = $dbSql->select(['e' => \Hrm\Model\EmployeeMapper::TABLE_NAME]);
        $select->join(['u' => \User\Model\UserMapper::TABLE_NAME], 'e.userId=u.id', []);
        $select->columns([
            'e.id' => 'id',
            'e.oneofficeId' => 'oneofficeId',
            'e.fullName' => 'fullName',
            'code' => 'code',
            'userId' => 'userId',
            'username' => new Expression('u.username'),
            'userFullName' =>  new Expression('u.fullName')
        ]);
        $select->where(['e.oneofficeId IS NOT NULL']);
        $select->where(['e.fullName != u.fullName']);

        //echo  $dbSql->buildSqlString($select);
        $rows = $dbAdapter->query($dbSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
        $results = [];
        $oneofficeIds = [];
        if($rows){
            foreach ($rows->toArray() as $row){
                $oneofficeIds[$row['e.oneofficeId']] = $row['e.oneofficeId'];
                $result[] = $row;
            }
        }
        $this->getViewModel()->setVariable('rows', $result);
        list($officeAdapter, $officeSql) = $this->createOfficeAdapter();
        $select = $officeSql->select(['u' => 'users']);
        $select->where(['personnel_id' => $oneofficeIds]);
        $select->columns(['personnel_id', 'username']);
        $rows = $officeAdapter->query($officeSql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
        $officeUserNames = [];
        if($rows){
            foreach ($rows->toArray() as $row){
                $officeUserNames[$row['personnel_id']] = $row['username'];
            }
        }
        $this->getViewModel()->setVariable('officeUserNames', $officeUserNames);

        return $this->getViewModel();
    }

    public function recalculatecontractAction(){
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $dbSql = $this->getServiceLocator()->get('dbSql');

        $select = $dbSql->select(['c' => \Crm\Model\ContractMapper::TABLE_NAME]);
        $select->order(['id DESC']);

        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $dbAdapter);
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage(200);
        $page = $this->getRequest()->getQuery('page', 1);
        $totalConvert = $this->getRequest()->getQuery('totalConvert', 0);
        $paginator->setCurrentPageNumber($page);

        $contractMapper = $this->getServiceLocator()->get('\Crm\Model\ContractMapper');
        foreach ($paginator as $row){
            $row = (array) $row;
            $contract = new \Crm\Model\Contract($row);
            $contractMapper->updatePaid2($contract);
            $contractMapper->updateValue($contract);
            $totalConvert++;
        }

        $this->getViewModel()->setTerminal(true);
        $this->getViewModel()->setVariable('paginator', $paginator);
        $this->getViewModel()->setVariable('page', $page);
        $this->getViewModel()->setVariable('totalPages', $paginator->count() + 1);

        $this->getViewModel()->setVariable('totalConvert', $totalConvert);
        if ($page <= $paginator->count()) {
            $this->getViewModel()->setVariable('redirectUri',
                Uri::build('/system/tool/recalculatecontract', [
                    'page' => $page+1,
                    'totalConvert' => $totalConvert,
                    ]));
        }
        return $this->getViewModel();
    }

}