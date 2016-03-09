<?php

namespace My\Models;

class Images extends ModelAbstract {

    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageImages($dbAdapter);
    }

    public function __construct() {
        $this->setTmpKeyCache('tmpImages');
        parent::__construct();
    }

    public function getList($arrCondition = array()) {
        return $this->getParentTable()->getList($arrCondition);
    }
}
