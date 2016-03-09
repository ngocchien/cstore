<?php

namespace My\Models;

class Order extends ModelAbstract {

    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageOrder($dbAdapter);
    }

    public function __construct() {
        $this->setTmpKeyCache('tmpOrder');
        parent::__construct();
    }

    public function getList($arrCondition = array()) {
        return $this->getParentTable()->getList($arrCondition);
    }

    public function getListLimit($arrCondition = array(), $intPage = 1, $intLimit = 15, $strOrder = 'orde_id DESC') {
        $keyCaching = 'getListLimitOrder:';
        foreach ($arrCondition as $k => $condition) {
            $keyCaching .= $k . ':' . $condition . ':';
        }
        $keyCaching .= $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimit($arrCondition, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 12);
        }
        return $arrResult;
    }

    public function getOrderForChart($intPage = 1, $intLimit = 10) {
        $keyCaching = 'getListLimitOrderForChart:';
        foreach ($arrCondition as $k => $condition) {
            $keyCaching .= $k . ':' . $condition . ':';
        }
        $keyCaching .= $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getOrderForChart($intPage, $intLimit);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 12);
        }
        return $arrResult;
    }

    public function getTotal($arrCondition) {
        return $this->getParentTable()->getTotal($arrCondition);
    }

    public function add($p_arrParams) {
        $intResult = $this->getParentTable()->add($p_arrParams);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

    public function edit($p_arrParams, $intOrderID) {
        $intResult = $this->getParentTable()->edit($p_arrParams, $intOrderID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

    public function delOrder($intOrderID) {
//        p($intOrderID);die;
        $intResult = $this->getParentTable()->delOrder($intOrderID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

    public function getDetail($arrCondition = array()) {
        $arrResult = array();
        if ($arrCondition && is_array($arrCondition)) {
            $keyCaching = 'getDetailOrder:';
            foreach ($arrCondition as $k => $condition) {
                $keyCaching .= $k . ':' . $condition;
            }
            $keyCaching .= ':' . $this->cache->read($this->tmpKeyCache);
            $keyCaching = crc32($keyCaching);
            $arrResult = $this->cache->read($keyCaching);
            if (empty($arrResult)) {
                $arrResult = $this->getParentTable()->getDetail($arrCondition);
                $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
            }
        }
        return $arrResult;
    }

    public function getCountTotalOrder($arrCondition) {
        $arrResult = array();
        $arrResult = $this->getParentTable()->getCountTotalOrder($arrCondition);
        return $arrResult;
    }

    public function getTotalUserBought($arrCondition = array()) {
        $arrResult = array();
        $arrResult = $this->getParentTable()->getTotalUserBought($arrCondition);
        return $arrResult;
    }

}
