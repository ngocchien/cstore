<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql,
    My\Validator\Validate;

class storageMessage extends AbstractTableGateway {

    protected $table = 'tbl_message';
    protected $adapter;

    public function __construct(Adapter $adapter) {
        $adapter->getDriver()->getConnection()->connect();
        $this->adapter = $adapter;
    }

    public function __destruct() {
        $this->adapter->getDriver()->getConnection()->disconnect();
    }

    public function add($p_arrParams) {
        try {
            if (!is_array($p_arrParams) || empty($p_arrParams)) {
                return false;
            }
            $result = $this->insert($p_arrParams);
            if ($result) {
                $result = $this->lastInsertValue;
            }
            return $result;
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }
    
    public function getList($arrCondition = null) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('mess_id ASC'));
            $query = $sql->getSqlStringForSqlObject($select);
            //p($query);die;
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
    }
    
    public function getListLimit($arrCondition, $intPage, $intLimit, $strMessage) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table);
            $select->where('1=1' . $strWhere)
                    ->order($strMessage)
                    ->limit($intLimit)
                    ->offset($intLimit * ($intPage - 1));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
    }

    public function edit($p_arrParams, $intMessage) {
        if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intMessage)) {
            return false;
        }
        try {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = $sql->update($this->table)->set($p_arrParams)->where('1=1 AND mess_id = ' . $intMessage);
            $query = $sql->getSqlStringForSqlObject($query);
            $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
            $resultSet = new \Zend\Db\ResultSet\ResultSet();
            $resultSet->initialize($result);
            $result = $resultSet->count() ? true : false;
            return $result;
        } catch (\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }
    
    
    public function getDetail($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
            return current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray());
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
    }
    
    public function getTotal($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->columns(array('total' => new \Zend\Db\Sql\Expression('COUNT(*)')))
                    ->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
            return (int) current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray())['total'];
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }
    
    public function delMessage($intMessageID) {
        if (empty($intMessageID)) {
            return false;
        }
        try {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = $sql->delete($this->table)->where('1=1 AND mess_id = ' . $intMessageID . ' AND mess_user ='. UID);
            $query = $sql->getSqlStringForSqlObject($query);
            $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
            $resultSet = new \Zend\Db\ResultSet\ResultSet();
            $resultSet->initialize($result);
            $result = $resultSet->count() ? true : false;
            return $result;
        } catch (\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }
    
    private function _buildWhere($arrCondition) {
        $now = getdate();
        $totalSeconds= ($now['seconds']+(60*$now['minutes'])+(3600*$now['hours']));
        $nowdate0h = time() - $totalSeconds;
        $nowdate24h = $nowdate0h + (60*60*24)-1;
        
        //$strWhere = ' AND mess_date <= ' . $nowdate24h;
        //p($strWhere);die();
        $strWhere = null;
        if (empty($arrCondition)) {
            return $strWhere;
        }
        if(isset($arrCondition['mess_id']) != '' && isset($arrCondition['mess_id']) != NULL){
            $strWhere .= ' AND mess_id=' . $arrCondition['mess_id'];
        }
        if(isset($arrCondition['mess_user']) != '' && isset($arrCondition['mess_user']) != NULL){
            $strWhere .= ' AND mess_user=' . $arrCondition['mess_user'];
        }
        if(isset($arrCondition['mess_status']) != '' && isset($arrCondition['mess_status']) != NULL){
            $strWhere .= ' AND mess_status=' . $arrCondition['mess_status'];
        }
        if(isset($arrCondition['strListUserID']) != '' && isset($arrCondition['strListUserID']) != NULL){
            $strWhere .= ' AND mess_user IN (' . $arrCondition['strListUserID']. ')';
        }
        if (isset($arrCondition['date_from'])) {
            $strWhere .= " AND mess_date >=" . $arrCondition['date_from'];
        }
        if (isset($arrCondition['date_to'])) {
            $strWhere .= " AND mess_date <=" . $arrCondition['date_to'];
        }
        /* get all mess <= mess_date */
        if (isset($arrCondition['ByDate'])) {
            $strWhere .= " AND mess_date <= " . $nowdate24h;
        }
        if (isset($arrCondition['date_from_all'])) {
            $strWhere .= " AND (mess_date >= " . $arrCondition['date_from_all'] . " OR mess_created >= " . $arrCondition['date_from_all'] . ")";
        }
        if (isset($arrCondition['date_to_all'])) {
            $strWhere .= " AND (mess_date <= " . $arrCondition['date_to_all'] . " OR mess_created <= " . $arrCondition['date_to_all'] . ")";
        }
        if (isset($arrCondition['date_from_created'])) {
            $strWhere .= " AND mess_created >=" . $arrCondition['date_from_created'];
        }
        if (isset($arrCondition['date_to_created'])) {
            $strWhere .= " AND mess_created <=" . $arrCondition['date_to_created'];
        }
        return $strWhere;
    }
}
