<?php

namespace My\Models;

class Meeting extends ModelAbstract {

    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageMeeting($dbAdapter);
    }

    public function __construct() {
        $this->setTmpKeyCache('tmpMeeting');
        parent::__construct();
    }
    
    public function add($p_arrParams) {
        $intResult = $this->getParentTable()->add($p_arrParams);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }
    
    public function edit($p_arrParams, $intMeetID) {
        $intResult = $this->getParentTable()->edit($p_arrParams, $intMeetID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }
    
    public function getTotal($arrCondition) {
        return $this->getParentTable()->getTotal($arrCondition);
    }
    
    public function getListLimit($arrCondition = array(), $intPage = 1, $intLimit = 15, $strMeeting = 'meet_id DESC') {
        $keyCaching = 'getListLimitMeeting:';
        foreach ($arrCondition as $k => $condition) {
            $keyCaching .= $k . ':' . $condition . ':';
        }
        $keyCaching .= $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strMeeting) . ':' . $this->cache->read($this->tmpKeyCache);
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimit($arrCondition, $intPage, $intLimit, $strMeeting);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 12);
        }
        return $arrResult;
    }
    public function getDetail($arrCondition = array()) {
        $arrResult = array();
        if ($arrCondition && is_array($arrCondition)) {
            $keyCaching = 'getDetailMeeting:';
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
