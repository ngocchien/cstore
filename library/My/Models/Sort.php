<?php

namespace My\Models;

class Sort extends ModelAbstract {

    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageSort($dbAdapter);
    }

    public function __construct() {
        $this->setTmpKeyCache('tmpSort');
        parent::__construct();
    }

    public function getList($arrCondition = array()) {
        return $this->getParentTable()->getList($arrCondition);
    }

    public function getDetail($arrCondition) {
        $keyCaching = 'getDetailSort:';
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
            $this->cache->increase('tmpProduct', 1);
        }
        return $intResult;
    }

    public function edit($p_arrParams, $sortID) {
        $ttl = 60 * 60 * 24 * 7;
        $intResult = $this->getParentTable()->edit($p_arrParams, $sortID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
            $this->cache->increase('tmpProduct', 1);
        }
        return $intResult;
    }

    public function delete($p_arrParams) {
        $intResult = $this->getParentTable()->delete($p_arrParams);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
            $this->cache->increase('tmpProduct', 1);
        }
        return $intResult;
    }

}
