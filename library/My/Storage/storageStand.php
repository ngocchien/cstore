<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql,
    My\Validator\Validate;

class storageStand extends AbstractTableGateway {

    protected $table = 'tbl_stands';
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
                    ->order(array('user_id DESC'));
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
            //p($query);die;
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

    public function edit($p_arrParams, $intStandID) {
//        p($p_arrParams);die;
        if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intStandID)) {
            return false;
        }


        try {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = $sql->update($this->table)->set($p_arrParams)->where('1=1 AND stan_id = ' . $intStandID);
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

    public function statisticUserRegistered($strBenginDate, $strEndDate) {
        try {

            list($beginDay, $beginMonth, $beginYear) = explode('/', $strBenginDate);
            list($endDay, $endMonth, $endYear) = explode('/', $strEndDate);

            $endMonth = $endMonth > 12 ? 12 : $endMonth;
            $lastDate = date('t', \My\General::dateToTimestamp(01, $endMonth, $endYear));
            $endDay = $lastDate < $endDay ? $lastDate : $endDay;

            $first = $beginYear . '/' . $beginMonth . '/' . $beginDay;
            $last = $endYear . '/' . $endMonth . '/' . $endDay;
            $arrDateRange = \My\General::dateRange($first, $last);

            if (empty($arrDateRange)) {
                return [];
            }

            $groupByMonth = false;

            if (count($arrDateRange) < 93) {
                $tmp = count($arrDateRange);
            } else {
                $groupByMonth = true;
                $arrDateRange = \My\General::dateRange($first, $last, '+1 month');
                $tmp = count($arrDateRange);
            }

            $strSql = 'SELECT';

            for ($i = 1; $i <= $tmp; ++$i) {
                if ($groupByMonth === true) {
                    $strDate = $arrDateRange[$i - 1];
                    $from = strtotime($strDate);
                    list($year, $month, $day) = explode('/', $strDate);
                    $to = \My\General::dateToTimestamp(date('t', $from), $month, $year, 23, 59, 59);
                    $strTitle = 'Tháng ' . $month . '/' . $year;
                } else {
                    $from = strtotime($arrDateRange[$i - 1]);
                    $to = $from + 86400 - 1;
                    $strTitle = date('d/m/Y', $from);
                }
                $strSql .= '(SELECT COUNT(*) FROM ' . $this->table . ' WHERE is_deleted=0 AND created_date BETWEEN ' . $from . ' AND ' . $to . ') AS "' . $strTitle . '", ';
            }
            $strSql = rtrim($strSql, ', ');
            $adapter = $this->adapter;
            return current($adapter->query($strSql, $adapter::QUERY_MODE_EXECUTE)->toArray());
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
    }

    public function getUserBought($arrCondition, $intPage = 1, $intLimit = 15, $strOrder = 'total DESC') {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = 'select u.user_id,o.orde_id,o.orde_detail,sum(o.orde_total_price) as total,u.user_fullname,u.user_email,u.user_phone'
                    . ' from ' . $this->table . ' u,tbl_orders o'
                    . ' where u.user_id = o.user_id  AND o.is_payment IN (3,4,2) ' . $strWhere
                    . ' group BY u.user_id'
                    . ' order by ' . $strOrder
                    . ' limit ' . $intLimit
                    . ' offset ' . ($intLimit * ($intPage - 1));
//            p($query);die;
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
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
            $query = 'select count(DISTINCT u.user_id) as total'
                    . ' from ' . $this->table . ' u,tbl_orders o'
                    . ' where u.user_id = o.user_id AND o.is_payment IN (3,4,2) ' . $strWhere;
//            p($query);die;
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    private function _buildWhere($arrCondition) {
        $strWhere = null;
        if (empty($arrCondition)) {
            return $strWhere;
        }

        if ($arrCondition['user_id'] !== '' && $arrCondition['user_id'] !== NULL) {
            $strWhere .= " AND user_id=" . $arrCondition['user_id'];
        }

        if ($arrCondition['stan_id'] !== '' && $arrCondition['stan_id'] !== NULL) {
            $strWhere .= " AND stan_id=" . $arrCondition['stan_id'];
        }

//        if ($arrCondition['abc'] !== '' && $arrCondition['abc'] !== NULL) {
//            $strWhere .= " AND user_email =" . $arrCondition['abc'];
//        }
//        p($strWhere);die;
//        if (isset($arrCondition['is_actived'])) {
//            $strWhere .= " AND is_actived=" . $arrCondition['is_actived'];
//        }
//        if (isset($arrCondition['active_code'])) {
//            $strWhere .= " AND active_code='" . $arrCondition['active_code'] . "'";
//        }
//        if (isset($arrCondition['has_discount']) && $arrCondition['has_discount']) {
//            $strWhere .= ' AND total_discount_code IS NOT NULL AND total_discount_code <> "" ';
//        }
//        if ($arrCondition['total_discount_code'] !== '' && $arrCondition['total_discount_code'] !== NULL) {
//            $strWhere .= ' AND total_discount_code LIKE "%' . $arrCondition['total_discount_code'] . '%"';
//        }
        //rint_r($strWhere);die;
        return $strWhere;
    }

}
