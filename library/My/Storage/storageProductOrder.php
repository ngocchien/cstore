<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql,
    My\Validator\Validate;

class storageProductOrder extends AbstractTableGateway {

    protected $table = 'tbl_product_order';
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

    public function getListLimitGroup($arrCondition, $intPage, $intLimit, $strOrder) {

        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $search = '';

            $query = 'SELECT *,count(product_order_id) as total 
                    FROM tbl_product_order
                     where 1=1 ' . $strWhere
                    . ' GROUP BY prod_id'
                    . ' order by total DESC'
                    . ' limit ' . $intLimit
                    . ' offset ' . ($intLimit * ($intPage - 1));
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getGroupSum($arrCondition) {

        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $search = '';

            $query = 'SELECT count(product_order_id) as total,sum(total_price) as sum
                    FROM tbl_product_order
                     where 1=1 ' . $strWhere;

            return current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray());
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
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
                    ->order($strOrder)
                    ->limit($intLimit)
                    ->offset($intLimit * ($intPage - 1));
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

    public function getTotalGroup($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $search = '';

            $query = 'SELECT count(*) as total FROM (SELECT count(product_order_id) as total 
                     FROM tbl_product_order
                     where 1=1 ' . $strWhere
                    . ' GROUP BY prod_id) as temp';


            return (int) current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray())['total'];
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
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

    public function edit($p_arrParams, $intprOderID) {
        if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intprOderID)) {
            return false;
        }
        try {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = $sql->update($this->table)->set($p_arrParams)->where('1=1 AND product_order_id = ' . $intprOderID);
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

    public function editByProdId($p_arrParams, $intProductID) {
//        p($intProductID);die;
        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intProductID)) {
                return $result;
            }
            return $this->update($p_arrParams, 'prod_id=' . $intProductID);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }

    public function del($intprOderID) {
        if (empty($intprOderID)) {
            return false;
        }
        try {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = $sql->delete($this->table)->where('1=1 AND product_order_id = ' . $intprOderID);
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

    public function delOrder($intprOderID) {
        if (empty($intprOderID)) {
            return false;
        }
        try {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = $sql->delete($this->table)->where('1=1 AND orde_id = ' . $intprOderID);
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
        if ($arrCondition['product_order_id'] !== '' && $arrCondition['product_order_id'] !== NULL) {
            $strWhere .= " AND product_order_id=" . $arrCondition['product_order_id'];
        }
        if ($arrCondition['orde_id'] !== '' && $arrCondition['orde_id'] !== NULL) {
            $strWhere .= " AND orde_id=" . $arrCondition['orde_id'];
        }

        if ($arrCondition['list_orde_id'] !== '' && $arrCondition['list_orde_id'] !== NULL) {
            $strWhere .= " AND orde_id IN (" . $arrCondition['list_orde_id'] . ")";
        }

        if ($arrCondition['prod_id'] !== '' && $arrCondition['prod_id'] !== NULL) {
            $strWhere .= " AND prod_id=" . $arrCondition['prod_id'];
        }

        if ($arrCondition['orde_is_paymemt'] !== '' && $arrCondition['orde_is_paymemt'] !== NULL) {
            $strWhere .= " AND orde_is_paymemt =" . $arrCondition['orde_is_paymemt'];
        }

        if ($arrCondition['orde_note'] !== '' && $arrCondition['orde_note'] !== NULL) {
            $strWhere .= " AND orde_note != ''";
        }

        if ($arrCondition['orde_created'] !== '' && $arrCondition['orde_created'] !== NULL) {
            $strWhere .= " AND orde_created =" . $arrCondition['orde_created'];
        }

        if ($arrCondition['user_id'] !== '' && $arrCondition['user_id'] !== NULL) {
            $strWhere .= " AND user_id=" . $arrCondition['user_id'];
        }
        if ($arrCondition['prod_status'] !== '' && $arrCondition['prod_status'] !== NULL) {
            $strWhere .= " AND prod_status=" . $arrCondition['prod_status'];
        }
        if (!empty($arrCondition['prod_name_like']) && $arrCondition['prod_name_like']) {
            $keyword = "'%" . $arrCondition['prod_name_like'] . "%'";
            $strWhere .= ' AND prod_name LIKE ' . $keyword;
        }
        if (!empty($arrCondition['both_name_id_like']) && $arrCondition['both_name_id_like']) {
            $keyword = "'%" . $arrCondition['both_name_id_like'] . "%'";
            $strWhere .= ' AND (prod_name LIKE ' . $keyword . 'OR prod_id = ' . (int) $arrCondition['both_name_id_like'] . ') ';
        }
        

        return $strWhere;
    }

}
