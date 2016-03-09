<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql,
    My\Validator\Validate;

class storageOrder extends AbstractTableGateway {

    protected $table = 'tbl_orders';
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
//            p($arrCondition);
//            die();
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('orde_id ASC'));
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
//           p($query);
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

    public function add($p_arrParams) {
        // p($p_arrParams);die;
        try {
            if (!is_array($p_arrParams) || empty($p_arrParams)) {
                return false;
            }
//            p($p_arrParams);die;
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

    public function getOrderForChart($intPage, $intLimit) {
        try {

            $adapter = $this->adapter;
            $query = 'SET time_zone = "+00:00";';
            $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
            $query = 'SELECT count(*)as Num,sum(IF( is_payment = 3 || is_payment = 4,1,0)) as is_pay,DATE_FORMAT(FROM_UNIXTIME(orde_created),\'%d-%m-%Y\') as date
                    from tbl_orders
                    GROUP BY date
                    ORDER BY orde_created DESC'
                    . ' limit ' . $intLimit
                    . ' offset ' . ($intLimit * ($intPage - 1));
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
    }

    public function edit($p_arrParams, $intOrderID) {
        if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intOrderID)) {
            return false;
        }
        try {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = $sql->update($this->table)->set($p_arrParams)->where('1=1 AND orde_id = ' . $intOrderID);
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

    public function delOrder($intOrderID) {
        if (empty($intOrderID)) {
            return false;
        }
        try {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = $sql->delete($this->table)->where('1=1 AND orde_id = ' . $intOrderID);
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

    public function getCountTotalOrder($arrCondition) {

        try {
            $strWhere = $this->_buildWhere($arrCondition);

            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = 'select SUM(orde_total_price) as sum_total_order'
                    . ' from ' . $this->table
                    . ' where 1=1 ' . $strWhere;

            return current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray());
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getTotalUserBought($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = 'select SUM(orde_total_price) as sum_total_order'
                    . ' from ' . $this->table
                    . ' where is_payment in (3,4,2) ' . $strWhere;

            return current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray());
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    private function _buildWhere($arrCondition) {
//        p($arrCondition);die;
        $strWhere = null;

        if (empty($arrCondition)) {
            return $strWhere;
        }
        if (isset($arrCondition['orde_id'])) {
            $strWhere .= " AND orde_id=" . $arrCondition['orde_id'];
        }

        if ($arrCondition['list_orde_id'] !== '' && $arrCondition['list_orde_id'] !== NULL) {
            $strWhere .= " AND orde_id IN (" . $arrCondition['list_orde_id'] . ")";
        }

        if (isset($arrCondition['is_payment'])) {
            $strWhere .= " AND is_payment =" . $arrCondition['is_payment'];
        }

        if ($arrCondition['orde_created'] !== '' && $arrCondition['orde_created'] !== NULL) {
            $strWhere .= " AND orde_created =" . $arrCondition['orde_created'];
        }

        if ($arrCondition['user_id'] !== '' && $arrCondition['user_id'] !== NULL) {
            $strWhere .= " AND user_id=" . $arrCondition['user_id'];
        }

        if ($arrCondition['content_cancel'] !== '' && $arrCondition['content_cancel'] !== NULL) {
            $strWhere .= " AND content_cancel != ''";
        }

        if ($arrCondition['user_change_status'] !== '' && $arrCondition['user_change_status'] !== NULL) {
            $strWhere .= " AND user_change_status =" . $arrCondition['user_change_status'];
        }

        /* if ($arrCondition['cusOrders'] !== '' && $arrCondition['cusOrders'] !== NULL) {
          $strWhere .= " AND user_id != user_updated AND user_updated != ''";
          } */

        /* if (isset($arrCondition['orde_id_like']) && $arrCondition['orde_id_like']) {
          $keyword = "'%" . $arrCondition['orde_id_like'] . "%'";
          $strWhere .= ' AND orde_id LIKE ' . $keyword;
          } */
        if (isset($arrCondition['orde_id_like']) && $arrCondition['orde_id_like']) {
            $keyword = "'%" . $arrCondition['orde_id_like'] . "%'";
            $strWhere .= ' AND (orde_id LIKE ' . $keyword . ' OR user_fullname LIKE ' . $keyword . ' OR user_phone LIKE ' . $keyword . ' OR user_id LIKE ' . $keyword . ')';
        }

        if (isset($arrCondition['strListUserID'])) {
            $strWhere .= " AND user_updated in (" . $arrCondition['strListUserID'] . ')';
        }

        if (isset($arrCondition['strListStatus'])) {
            $strWhere .= " AND is_payment in (" . $arrCondition['strListStatus'] . ')';
        }

//        p($arrCondition);die;
        if (isset($arrCondition['user_fullname'])) {
            $strWhere .= " AND user_fullname ='" . $arrCondition['user_fullname'] . "'";
        }
//        p($arrCondition);die;
        if (isset($arrCondition['user_email'])) {
            $strWhere .= " AND user_email ='" . $arrCondition['user_email'] . "'";
        }
        if (isset($arrCondition['is_acp'])) {
            $strWhere .= " AND is_acp ='" . $arrCondition['is_acp'] . "'";
        }

        if (isset($arrCondition['user_phone'])) {
            $strWhere .= " AND user_phone ='" . $arrCondition['user_phone'] . "'";
        }

        if (isset($arrCondition['date_from'])) {
            $strWhere .= " AND orde_created >=" . $arrCondition['date_from'];
        }

        if (isset($arrCondition['orde_payment'])) {
            $strWhere .= " AND orde_payment =" . $arrCondition['orde_payment'];
        }

        if (isset($arrCondition['orde_ship'])) {
            $strWhere .= " AND orde_ship =" . $arrCondition['orde_ship'];
        }

        if (isset($arrCondition['date_to'])) {
            $strWhere .= " AND orde_created <=" . $arrCondition['date_to'];
        }
        if (!empty($arrCondition['arr_is_payment'])) {
            $strArr = implode(" OR is_payment=", $arrCondition['arr_is_payment']);
            $strWhere .= ' AND (' . $strArr . ') ';
        }
//        if (isset($arrCondition['orde_status'])) {
//            $strWhere .= " AND orde_status =" . $arrCondition['orde_status'];
//        }
//        p($strWhere);die;
        return $strWhere;
    }

}
