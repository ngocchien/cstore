<?php

namespace My\Models;

class DetailContent extends ModelAbstract {

    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageDetailContent($dbAdapter);
    }

    public function __construct() {
        $this->setTmpKeyCache('tmpDetailContent');
        parent::__construct();
    }
    
    public function getList($arrCondition = array()) {
        return $this->getParentTable()->getList($arrCondition);
    }
    
    public function edit($p_arrParams, $intContentID) {
        $ttl = 60 * 60 * 24 * 7;
        $intResult = $this->getParentTable()->edit($p_arrParams, $intContentID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }
}
