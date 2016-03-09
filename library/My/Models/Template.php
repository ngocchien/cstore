<?php

namespace My\Models;

class Template extends ModelAbstract {

    public function __construct() {
        $this->setTmpKeyCache('tmpTemplate');
        parent::__construct();
    }
    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageTemplate($dbAdapter);
    }
    public function getDetail($template_id) {
        $keyCaching = 'getDetailTemplate_:'.$template_id;
        $keyCaching .= 'tmp:' . $this->cache->read($this->tmpKeyCache);
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getDetail($template_id);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
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

    public function edit($p_arrParams, $tem_id) {
        $ttl = 60 * 60 * 24 * 7;
        $intResult = $this->getParentTable()->edit($p_arrParams, $tem_id);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

}
