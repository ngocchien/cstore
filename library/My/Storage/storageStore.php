<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql,
    My\Validator\Validate;

class storageStore extends AbstractTableGateway {

    protected $table = 'tbl_store';
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

    public function getListLimit($arrCondition, $intPage, $intLimit, $strStore) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table);
            $select->where('1=1' . $strWhere)
                    ->order($strStore)
                    ->limit($intLimit)
                    ->offset($intLimit * ($intPage - 1));
            $query = $sql->getSqlStringForSqlObject($select);
            // p($query);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
    }

    public function getSumTotal($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = "SELECT SUM(store_prod_total) as total_store_prod_total,
                        SUM(store_export) as total_store_export,
                        SUM(store_import) as total_store_import,
                        SUM(prov_total) as total_prov_total,
                        SUM(prov_payment) as total_prov_payment,
                        SUM(prov_not_payment) as total_prov_not_payment,
                        SUM(prov_return) as total_prov_return
                        FROM
                        tbl_store                       
                        WHERE 1=1 " . $strWhere . "
                        GROUP BY  prov_code";
            $result = current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray());
            return $result;
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

    public function edit($p_arrParams, $intMeetID) {
        if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intMeetID)) {
            return false;
        }
        try {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = $sql->update($this->table)->set($p_arrParams)->where('1=1 AND store_id = ' . $intMeetID);
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

    public function editToWhere($p_arrParams, $arrCondition) {
        if (!is_array($p_arrParams) || empty($p_arrParams) || empty($arrCondition)) {
            return false;
        }
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = $sql->update($this->table)->set($p_arrParams)->where('1=1 ' . $strWhere);
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
        $strWhere = ' AND store_status != -1';
        if (empty($arrCondition)) {
            return $strWhere;
        }
        if (isset($arrCondition['store_id'])) {
            $strWhere .= " AND store_id=" . $arrCondition['store_id'];
        }
        if (isset($arrCondition['prov_code'])) {
            $strWhere .= " AND prov_code='" . $arrCondition['prov_code'] . "'";
        }
        if (isset($arrCondition['prod_code'])) {
            $strWhere .= " AND prod_code='" . $arrCondition['prod_code'] . "'";
        }
        if (isset($arrCondition['store_status'])) {
            $strWhere .= " AND store_status ='" . $arrCondition['store_status'] . "'";
        }
        if (isset($arrCondition['prod_id'])) {
            $strWhere .= " AND prod_id='" . $arrCondition['prod_id'] . "'";
        }

        if (isset($arrCondition['search'])) {
            $strWhere .= " AND (prod_id like '" . $arrCondition['search'] . "' OR  store_prod_name like '%" . $arrCondition['search'] . "%') ";
        }
        if (isset($arrCondition['masp_or_tensp_or_mancc'])) {
            $strWhere .= " AND (prod_id like '%" . $arrCondition['masp_or_tensp_or_mancc'] . "' OR  store_prod_name like '%" . $arrCondition['masp_or_tensp_or_mancc'] . "%' OR prov_code LIKE '%" . $arrCondition['masp_or_tensp_or_mancc'] . "%') ";
        }
        return $strWhere;
    }

}
