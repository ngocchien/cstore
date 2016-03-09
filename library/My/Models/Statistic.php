<?php

namespace My\Models;

class Statistic extends ModelAbstract {

    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageStatistic($dbAdapter);
    }

    public function __construct() {
        $this->setTmpKeyCache('tmpStatistic');
        parent::__construct();
    }

    public function getList($arrCondition = array()) {
        return $this->getParentTable()->getList($arrCondition);
    }

    public function getRevenue($arrCondition) {       
        $keyCaching = 'getRevenue:';
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $condition) {
                $keyCaching .= $k . ':' . $condition . ':';
            }
        }
        $keyCaching .= 'tmp:' . $this->cache->read($this->tmpKeyCache);
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getRevenue($arrCondition);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }
    public function getShip() {        
        $keyCaching = 'getShip:';
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $condition) {
                $keyCaching .= $k . ':' . $condition . ':';
            }
        }
        $keyCaching .= 'tmp:' . $this->cache->read($this->tmpKeyCache);
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getShip();
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }
    public function getStatus($arrCondition) {
        $keyCaching = 'getStatus:';
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $condition) {
                $keyCaching .= $k . ':' . $condition . ':';
            }
        }
        $keyCaching .= 'tmp:' . $this->cache->read($this->tmpKeyCache);
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getStatus($arrCondition);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }
    public function getTotal($arrCondition) {
        $keyCaching = 'getTotal:';
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $condition) {
                $keyCaching .= $k . ':' . $condition . ':';
            }
        }
        $keyCaching .= 'tmp:' . $this->cache->read($this->tmpKeyCache);
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getTotal($arrCondition);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

//    public function getListLimitProdOrder($arrCondition, $intPage, $intLimit) {
//        $keyCaching = 'getListLimitProductOrder:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
//        if (count($arrCondition) > 0) {
//            foreach ($arrCondition as $k => $val) {
//                $keyCaching .= $k . ':' . $val . ':';
//            }
//        }
//        $keyCaching = crc32($keyCaching);
//        $arrResult = $this->cache->read($keyCaching);
//        if (empty($arrResult)) {
//            $arrResult = $this->getParentTable()->getListLimitProdOrder($arrCondition, $intPage, $intLimit);
//            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
//        }
//        return $arrResult;
//    }
    public function getListProdOrder($arrCondition) {
        $keyCaching = 'getListProductOrder:' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListProdOrder($arrCondition);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }
    public function getListLimitProdOrder($arrCondition, $intPage = 1, $intLimit = 15, $strOrder = 'T1.quantity DESC') {
        //p($intPage);die;
        $keyCaching = 'getListLimitProductOrder:' . $intPage . ':' . $intLimit . ':'  . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimitProdOrder($arrCondition, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }
    public function getTotalProdOrder($arrCondition) {
        $keyCaching = 'getTotalProductOrder:' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getTotalProdOrder($arrCondition);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }
    public function getReturn($arrCondition) {
        $keyCaching = 'getReturn:';
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $condition) {
                $keyCaching .= $k . ':' . $condition . ':';
            }
        }
        $keyCaching .= 'tmp:' . $this->cache->read($this->tmpKeyCache);
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getReturn($arrCondition);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }
}
