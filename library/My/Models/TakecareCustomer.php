<?php

namespace My\Models;

class TakecareCustomer extends ModelAbstract {

    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageTakecareCustomer($dbAdapter);
    }

    public function __construct() {
        $this->setTmpKeyCache('tmpTakecareCustomer');
        parent::__construct();
    }
    
    public function getList($arrCondition = array()) {
        return $this->getParentTable()->getList($arrCondition);
    }

    public function add($p_arrParams) {
        $intResult = $this->getParentTable()->add($p_arrParams);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

    public function edit($p_arrParams, $intCateID) {
        $ttl = 60 * 60 * 24 * 7;
        $intResult = $this->getParentTable()->edit($p_arrParams, $intCateID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }
    
    public function del($intProdOderID) {
        $intResult = $this->getParentTable()->del($intProdOderID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }
    
    public function getTotal($arrCondition) {
        return $this->getParentTable()->getTotal($arrCondition);
    }
    
    public function getDetail($arrCondition = array()) {
        $arrResult = array();
        if ($arrCondition && is_array($arrCondition)) {
            $keyCaching = 'getDetailTakecareCustomer:';
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
}
