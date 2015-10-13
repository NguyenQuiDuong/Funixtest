<?php
/**
 * @author KienNN
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 * class sử dụng để tạo và duyệt cấu trúc cây cho danh mục, project... Tất tần tật các thứ có parentId
 */
namespace Home\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Adapter;
use Home\Model\BaseMapper;

class Tree extends BaseMapper
{
	const ALGORITHM_ROOT_BUILD = 'root_build';
	const ALGORITHM_BRANCH_BUILD = 'branch_build';

	/**
	 * @author KienNN
	 * @param unknown $items
	 * @return multitype:|Ambigous <multitype:, number, multitype:Ambigous <> >
	 * Build cây danh mục với gốc không xác định
	 * Thuật toán chia làm các cành nhỏ chỉ 2 lv, rồi duyệt dần và ghép lại
	 */
	public function branchRecusive($items){
		if(!$items || !count($items)){
			return [];
		}
		$arrayIndexed = [];
		foreach ($items as $item){
			$arrayIndexed[$item->getId()] = $item;
		}
		// phân tích thành các nhánh 2 lv
		//    o     o     o
		//   /\           |
		//  o  o          o

		$lv1Arr = [];
		foreach ($arrayIndexed as $parent){
			$lv1Arr[$parent->getId()]['obj'] = $parent;
			$lv1Arr[$parent->getId()]['ord'] = 0;
			$childs = [];
			foreach ($arrayIndexed as $item){
				if($parent->getId() == $item->getParentId()){
					$childs[$item->getId()]['obj'] = $item;
					$childs[$item->getId()]['ord'] = 1;
				}
			}
			if(count($childs)){
				$lv1Arr[$parent->getId()]['childs'] = $childs;
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

	/**
	 * @author KienNN
	 * @param Array $items
	 * @return multitype:
	 * @note: chỉ dùng để build tree vưới root = 0
	 */
	public function rootRecusive($items){
		if(!$items || !count($items)){
			return [];
		}
		$arrayIndexed = [];
		foreach ($items as $item){
			$arrayIndexed[$item->getId()] = $item;
		}
		$result = $this->recursiveObject($arrayIndexed);
		return $result;
	}

	/**
	 * @author KieNNN
	 * @param Array $items
	 * must be indexed
	 * @param number $parentId
	 * @param number $ord
	 * @return multitype:multitype:number unknown multitype:multitype:number unknown NULL
	 * @note: chỉ sử dụng để build tree với gốc 0
	 */
	private function recursiveObject($items, $parentId = 0, $ord = 0){
		$result = array();
		if($items && count($items)){
			foreach ($items as $item) {
				/* var $item \Work\Model\Project */
				$current_parent = $item->getParentId()?:0;
				if($current_parent == $parentId) {
					unset($items[$item->getId()]);
					$result[] =	array(
						'ord' => $ord,
						'obj' => $item,
						'childs' => $this->recursiveObject($items, $item->getId(), $ord+1),
					);
				}
			}
		}
		return $result;
	}

	/**
	 * @author KienNN
	 * @param recursivedTree $items
	 * @return multitype:\Work\Model\TaskCategory
	 */
	private function travel($items){
		$result = array();
		if($items && count($items)){
			foreach ($items as $node) {
				$item = $node['obj'];
				/*@var $item \Work\Model\TaskCategory */
				$item->addOption('ord', $node['ord']);
				$result[$item->getId()] = $item;
				if(isset($node['childs']) && count($node['childs'])){
					$result += $this->travel($node['childs']);
				}
			}
		}
		return $result;
	}

	/**
	 * @author KienNN
	 *
	 */
	public function toSelectBoxArray($items, $prefix = '--' ,$algorithm = self::ALGORITHM_ROOT_BUILD){
		if(!$items || !is_array($items) || !count($items)){
			return [];
		}
		$arrayRecusived = [];
		switch ($algorithm){
			case self::ALGORITHM_BRANCH_BUILD:
				$arrayRecusived = $this->travel($this->branchRecusive($items));
				break;
			case self::ALGORITHM_ROOT_BUILD:
			default:
				$arrayRecusived = $this->travel($this->rootRecusive($items));
				break;
		}
		$result = [];
		if(count($arrayRecusived)){
			foreach ($arrayRecusived as $item){
				$result[$item->getId()] = str_repeat($prefix, $item->getOption('ord')?:0).$item->getName();
			}
		}
		return $result;
	}

	/**
	 * @author KienNN
	 * @param unknown $items
	 * @param unknown $algorithm
	 * @return NULL
	 */
	public function toArrayRecusived($items, $algorithm = self::ALGORITHM_ROOT_BUILD){
		if(!$items || !is_array($items) || !count($items)){
			return null;
		}
		switch ($algorithm){
			case self::ALGORITHM_BRANCH_BUILD:
				return $this->travel($this->branchRecusive($items));
			case self::ALGORITHM_ROOT_BUILD:
			default:
				return $this->travel($this->rootRecusive($items));
		}
	}

	//==============Hàm phục vụ update allChildIds và allParentIds cho company và project=================
	/**
	 * @author KienNN
	 * @param unknown $items
	 * @param unknown $allParentIds
	 * @param \Home\Model\BaseMapper $modelMapper
	 * @return multitype:Ambigous <\Work\Model\the, number>
	 */
	private function travelForAllChilds($items, $allParentIds, $modelMapper){
		$result = array();
		$allParentIds = $allParentIds ?: [];
		if($items && count($items)){
			foreach ($items as $node) {
				$item = $node['obj'];
				$result[$item->getId()] = $item->getId();
				$allChildIds = [];
				$allChildIds[$item->getId()] = $item->getId();
				$allParentIds[$item->getId()] = $item->getId();
				if(isset($node['childs']) && count($node['childs'])){
					$allChildIds += $this->travelForAllChilds($node['childs'], $allParentIds, $modelMapper);
				}
				$result += $allChildIds;
				if(isset($allChildIds[$item->getId()])){
					unset($allChildIds[$item->getId()]);
				}
				if(isset($allParentIds[$item->getId()])){
					unset($allParentIds[$item->getId()]);
				}
				$modelMapper->updateColumns(array(
					'allChildIds' => count($allChildIds) ? implode(',', $allChildIds) : null,
					'allParentIds' =>count($allParentIds) ? implode(',', array_reverse($allParentIds)) : null
				), $item);
			}
		}
		return $result;
	}

	/**
	 * @author KienNN
	 * @param unknown $items
	 */
	public function updateAllParent($items, $modelMapper){
		if(!$items || !is_array($items) || !count($items)){
			return null;
		}
		return $this->travelForAllChilds($this->rootRecusive($items), [], $modelMapper);
	}

	/**
	 * @author KienNN
	 * @param Array $parentIds
	 * @param String $tableName
	 */
	public function fetchAllChildIds($parentIds, $tableName){
	    if(!$parentIds || !is_array($parentIds) || !count($parentIds) || !$tableName){
	        return null;
	    }
	    $result = [];
	    foreach ($parentIds as $parentId){
	        $result[$parentId] = $parentId;
	    }
	    $select = $this->getDbSql()->select($tableName);
	    $select->columns(array(
	        'id' => new Expression('id')
	    ));
	    $select->where(['parentId' => $parentIds]);
	    $query   = $this->getDbSql()->buildSqlString($select);
	    $rows = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
	    $childIds = [];
	    if($rows->count()){
	        foreach ($rows as $row){
	            $childIds[$row['id']] = $row['id'];
	        }
	    }
	    if(count($childIds)){
	        $result += $this->fetchAllChildIds($childIds, $tableName);
	    }
	    return $result;
	}
}