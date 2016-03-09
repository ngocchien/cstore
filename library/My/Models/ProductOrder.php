<?php

namespace My\Models;

class ProductOrder extends ModelAbstract {

    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageProductOrder($dbAdapter);
    }

    public function __construct() {
        $this->setTmpKeyCache('tmpProductOrder');
        parent::__construct();
    }

    public function getList($arrCondition = array()) {
        return $this->getParentTable()->getList($arrCondition);
    }

    public function getListLimit($arrCondition, $intPage, $intLimit, $strOrder) {
        $keyCaching = 'getListLimitProductOrder:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimit($arrCondition, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getListLimitGroup($arrCondition, $intPage, $intLimit, $strOrder) {
        $keyCaching = 'getListLimitGroup:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimitGroup($arrCondition, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getGroupSum($arrCondition) {
        $keyCaching = 'getGroupSum:' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getGroupSum($arrCondition, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getQuantityProduct($arrCondition, $intPage, $intLimit, $strOrder) {
        $keyCaching = 'getQuantityProduct:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getQuantityProduct($arrCondition, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getTotalQuantityProduct($arrCondition) {
        return $this->getParentTable()->getTotalQuantityProduct($arrCondition);
    }

    public function getTotal($arrCondition) {
        return $this->getParentTable()->getTotal($arrCondition);
    }

    public function getTotalGroup($arrCondition) {
        return $this->getParentTable()->getTotalGroup($arrCondition);
    }

    public function getDetail($arrCondition) {
        $keyCaching = 'getDetailCategory:';
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $condition) {
                $keyCaching .= $k . ':' . $condition . ':';
            }
        }
        $keyCaching .= 'tmp:' . $this->cache->read($this->tmpKeyCache);
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getDetail($arrCondition);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function add($p_arrParams) {
        $intResult = $this->getParentTable()->add($p_arrParams);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

    public function edit($p_arrParams, $intCateID) {
        $intResult = $this->getParentTable()->edit($p_arrParams, $intCateID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

    public function editByProdId($p_arrParams, $intCateID) {
        $intResult = $this->getParentTable()->editByProdId($p_arrParams, $intCateID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

    public function editToWhere($p_arrParams, $arrCondition) {
        $intResult = $this->getParentTable()->editToWhere($p_arrParams, $arrCondition);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

    public function del($intProdOderID) {
//        $ttl = 60 * 60 * 24 * 7;
        $intResult = $this->getParentTable()->del($intProdOderID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

    public function delOrder($intProdOderID) {
//        $ttl = 60 * 60 * 24 * 7;
        $intResult = $this->getParentTable()->delOrder($intProdOderID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

//    public function getDetailCategoryList($arrCondition) {
//        $keyCaching = 'getDetailCategoryList:';
//        if (count($arrCondition) > 0) {
//            foreach ($arrCondition as $k => $condition) {
//                $keyCaching .= $k . ':' . $condition . ':';
//            }
//        }
//        $keyCaching .= 'tmp:' . $this->cache->read($this->tmpKeyCache);
//        $keyCaching = crc32($keyCaching);
//        $arrResult = $this->cache->read($keyCaching);
//        if (empty($arrResult)) {
//            $arrResult = $this->getParentTable()->getDetailCategoryList($arrCondition);
//            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
//        }
//        return $arrResult;
//    }
}
