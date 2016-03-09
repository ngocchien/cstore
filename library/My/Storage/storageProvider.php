<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql,
    My\Validator\Validate;

class storageProvider extends AbstractTableGateway {

    protected $table = 'tbl_provider';
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

    public function getListLimit($arrCondition, $intPage, $intLimit, $strProvider) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table);
            $select->where('1=1' . $strWhere)
                    ->order($strProvider)
                    ->limit($intLimit)
                    ->offset($intLimit * ($intPage - 1));
            $query = $sql->getSqlStringForSqlObject($select);
//            p($query);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
    }

    public function getList($arrCondition = null) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('prov_id ASC'));
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
            $query = $sql->update($this->table)->set($p_arrParams)->where('1=1 AND prov_id = ' . $intMeetID);
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

        $strWhere = null;
        if (empty($arrCondition)) {
            return $strWhere;
        }
        if (isset($arrCondition['prov_email'])) {
            $strWhere .= " AND prov_email='" . $arrCondition['prov_email'] . "'";
        }
        if (isset($arrCondition['prov_code'])) {
            $strWhere .= " AND prov_code ='" . $arrCondition['prov_code'] . "'";
        }
        if (isset($arrCondition['login_name'])) {
            $strWhere .= " AND login_name ='" . $arrCondition['login_name'] . "'";
        }

        if (isset($arrCondition['prov_email_or_login_name'])) {
            $strWhere .= " AND (LOWER(prov_email)='" . strtolower($arrCondition['prov_email_or_login_name']) . "' OR LOWER(login_name)='" . strtolower($arrCondition['prov_email_or_login_name']) . "')";
        }
        if (isset($arrCondition['password'])) {
            $strWhere .= " AND password='" . $arrCondition['password'] . "'";
        }
        if (isset($arrCondition['prov_id'])) {
            $strWhere .= " AND prov_id=" . $arrCondition['prov_id'];
        }
        if (isset($arrCondition['prov_status'])) {
            $strWhere .= " AND prov_status=" . $arrCondition['prov_status'];
        }
        if (isset($arrCondition['not_prov_status'])) {
            $strWhere .= " AND prov_status!=" . $arrCondition['not_prov_status'];
        }
        if (isset($arrCondition['code_or_name_or_phone'])) {
            $strWhere .= " AND prov_code LIKE '%" . $arrCondition['code_or_name_or_phone'] . "%' OR prov_name LIKE '%" . $arrCondition['code_or_name_or_phone'] . "%' OR prov_phone LIKE '%" . $arrCondition['code_or_name_or_phone'] . "%'";
        }
        return $strWhere;
    }

}
