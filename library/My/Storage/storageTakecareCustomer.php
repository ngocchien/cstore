<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql,
    My\Validator\Validate;

class storageTakecareCustomer extends AbstractTableGateway {

    protected $table = 'tbl_takecare_customer';
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
                    ->order(array('orde_id ASC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
    }

    public function edit($p_arrParams, $arrID) {
        /*if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intprOderID)) {
            return false;
        }*/
        try {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = $sql->update($this->table)->set($p_arrParams)->where('1=1 AND orde_id = ' . $arrID['orde_id'] . ' AND prod_id=' . $arrID['prod_id']);
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
    
    public function del($arrConditrion) {
        if (empty($arrConditrion)) {
            return false;
        }
        try {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = $sql->delete($this->table)->where('1=1 AND orde_id=' . $arrConditrion['orde_id'] . ' AND prod_id=' . $arrConditrion['prod_id']);
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
    
    private function _buildWhere($arrCondition) {
        $strWhere = null;

        if (empty($arrCondition)) {
            return $strWhere;
        }
        if($arrCondition['orde_id'] != '' && $arrCondition['orde_id'] != NULL){
            $strWhere .= ' AND orde_id =' . $arrCondition['orde_id'];
        }
        if($arrCondition['prod_id'] != '' && $arrCondition['prod_id'] != NULL){
            $strWhere .= ' AND prod_id =' . $arrCondition['prod_id'];
        }
        if($arrCondition['user_update'] != '' && $arrCondition['user_update'] != NULL){
            $strWhere .= ' AND user_update =' . $arrCondition['user_update'];
        }
        if($arrCondition['nowdate0h'] != '' && $arrCondition['nowdate24h'] != ''){
            $strWhere .= ' AND prod_care_time BETWEEN '. $arrCondition['nowdate0h'] . ' AND '. $arrCondition['nowdate24h'];
        }
        
        return $strWhere;
    }

}
