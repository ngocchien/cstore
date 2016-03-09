<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql,
    My\Validator\Validate;

class storageOrderLog extends AbstractTableGateway {

    protected $table = 'tbl_order_logs';
    protected $adapter;

    public function __construct(Adapter $adapter) {
        $adapter->getDriver()->getConnection()->connect();
        $this->adapter = $adapter;
    }

    public function __destruct() {
        $this->adapter->getDriver()->getConnection()->disconnect();
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

    public function getListLimit($arrCondition, $intPage, $intLimit, $strOrder) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table);
            $select->where('1=1' . $strWhere)
                    ->join('tbl_users', 'tbl_order_logs.user_id = tbl_users.user_id',array('user_fullname'),'left')
                    ->order($strOrder)
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

    public function getDetail($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
            return current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray());
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
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

//    public function edit($p_arrParams, $intOrderLogID) {
//        if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intOrderLogID)) {
//            return false;
//        }
//        try {
//            $adapter = $this->adapter;
//            $sql = new Sql($adapter);
//            $query = $sql->update($this->table)->set($p_arrParams)->where('1=1 AND orde_id = ' . $intOrderLogID);
//            $query = $sql->getSqlStringForSqlObject($query);
//            $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
//            $resultSet = new \Zend\Db\ResultSet\ResultSet();
//            $resultSet->initialize($result);
//            $result = $resultSet->count() ? true : false;
//            return $result;
//        } catch (\Exception $exc) {
//            if (APPLICATION_ENV !== 'production') {
//                throw new \Zend\Http\Exception($exc->getMessage());
//            }
//            return false;
//        }
//    }
    private function _buildWhere($arrCondition) {
        $strWhere = null;

        if (empty($arrCondition)) {
            return $strWhere;
        }
        if ($arrCondition['orde_id'] !== '' && $arrCondition['orde_id'] !== NULL) {
            $strWhere .= " AND orde_id=" . $arrCondition['orde_id'];
        }
        
        if ($arrCondition['orde_is_paymemt'] !== '' && $arrCondition['orde_is_paymemt'] !== NULL) {
            $strWhere .= " AND orde_is_paymemt =" . $arrCondition['orde_is_paymemt'];
        }
        
        if ($arrCondition['orde_created'] !== '' && $arrCondition['orde_created'] !== NULL) {
            $strWhere .= " AND orde_created =" . $arrCondition['orde_created'];
        }
        
        if ($arrCondition['user_id'] !== '' && $arrCondition['user_id'] !== NULL) {
            $strWhere .= " AND user_id=" . $arrCondition['user_id'];
        }
        
        if ($arrCondition['user_change_status'] !== '' && $arrCondition['user_change_status'] !== NULL) {
            $strWhere .= " AND user_change_status=" . $arrCondition['user_change_status'];
        }
        
        if (isset($arrCondition['orde_id_like']) && $arrCondition['orde_id_like']) {
            $keyword = "'%" . $arrCondition['orde_id_like'] . "%'";
            $strWhere .= ' AND orde_id LIKE ' . $keyword;
        }
        
        return $strWhere;
    }

}
