<?php

namespace My\Models;

class Keyword extends ModelAbstract {

    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageKeyword($dbAdapter);
    }

    public function __construct() {
        $this->setTmpKeyCache('tmpKeyword');
        parent::__construct();
    }

    public function getList($arrCondition = array()) {
        return $this->getParentTable()->getList($arrCondition);
    }

    public function getFirstDataNull() {
        $keyCaching = 'getFirstDataNullKeyword:';
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $condition) {
                $keyCaching .= $k . ':' . $condition . ':';
            }
        }
        $keyCaching .= 'tmp:' . $this->cache->read($this->tmpKeyCache);
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getFirstDataNull();
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getDataNull() {
        $keyCaching = 'getDataNullKeyword:';
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $condition) {
                $keyCaching .= $k . ':' . $condition . ':';
            }
        }
        $keyCaching .= 'tmp:' . $this->cache->read($this->tmpKeyCache);
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getDataNull();
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function add($p_arrParams = array()) {
        //$p_arrParams = $this->filter($p_arrParams);
        $result = $this->getParentTable()->add($p_arrParams);
        if ($result) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $result;
    }

    public function edit($p_arrParams, $intKeywordID) {
        //$p_arrParams = $this->filter($p_arrParams);
        $result = $this->getParentTable()->edit($p_arrParams, $intKeywordID);
        if ($result) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $result;
    }

    public function getDetail($arrCondition) {
        $keyCaching = 'getDetailKeyword:';
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

    public function getLimit($arrCondition, $intPage, $intLimit, $strOrder, $colSelect = []) {
        $keyCaching = 'getLimitKeyword:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }

        if (count($colSelect) > 0) {
            foreach ($colSelect as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }

        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getLimit($arrCondition, $intPage, $intLimit, $strOrder, $colSelect);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getLimitNoMemcached($arrCondition, $intPage, $intLimit, $strOrder) {
        $arrResult = $this->getParentTable()->getLimitNoMemcached($arrCondition, $intPage, $intLimit, $strOrder);
        return $arrResult;
    }

//    public function getTotal($arrCondition) {
//        return $this->getParentTable()->getTotal($arrCondition);
//    }
    public function getMax($arrCondition) {
        return $this->getParentTable()->getMax($arrCondition);
    }

    public function AddList($arrKeyword = array()) {
        $this->cache->increase($this->tmpKeyCache, 1);
        return $this->getParentTable()->AddList($arrKeyword);
    }

    public function getListLimit($arrCondition = [], $intPage = 1, $intLimit = 15, $strOrder = 'key_id ASC', $arrCol = array('*')) {
        $keyCaching = 'getListLimitKeyword:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        if (count($arrCol) > 0) {
            foreach ($arrCol as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimit($arrCondition, $intPage, $intLimit, $strOrder, $arrCol);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getTotal($arrCondition) {
        return $this->getParentTable()->getTotal($arrCondition);
    }

}
