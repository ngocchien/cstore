<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    My\Validator\Validate;

class storageStatistic extends AbstractTableGateway {

    protected $table = 'tbl_orders';

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

    public function getRevenue($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = 'select user_updated,sum(orde_total_price) as total,sum( if(is_payment = 2, orde_total_price, 0)) as notPay, sum(if( is_payment = 3 or is_payment =4, orde_total_price,0)) as pay'
                    . ' from ' . $this->table
                    . ' where is_payment not in (-1,0,1)' . $strWhere
                    . ' GROUP BY user_updated';
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getStatus($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = 'select user_updated,user_change_status, sum(IF(is_payment = -1,1,0)) as Cancel,'
                    . 'sum(IF(is_payment = 0,1,0)) as Waiting,'
                    . 'sum(IF(is_payment = 1,1,0)) as Approve,'
                    . 'sum(IF(is_payment = 2,1,0)) as Running,'
                    . 'sum(IF(is_payment = 3,1,0)) as Recevied,'
                    . 'sum(IF(is_payment = 4,1,0)) as Paid,'
                    . 'sum(IF(is_payment = 5,1,0)) as getReturn,'
                    . 'sum(IF(is_payment = 6,1,0)) as inWarehouse,'
                    . 'sum(IF(is_payment = -1,orde_total_price,0)) as totalCancel,'
                    . 'sum(IF(is_payment = 2,orde_total_price,0)) as totalRunning,'
                    . 'sum(IF(is_payment = 3,orde_total_price,0)) as totalReceived,'
                    . 'sum(IF(is_payment = 4,orde_total_price,0)) as totalPaid,'
                    . 'sum(IF(is_payment = 5,orde_total_price,0)) as totalgetReturn,'
                    . 'sum(IF(is_payment = 6,orde_total_price,0)) as totalinWarehouse'
                    . ' from ' . $this->table
                    . ' where user_updated != ""' . $strWhere
                    . ' GROUP BY user_updated';
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getShip($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = 'select orde_ship,count(*) as total, sum(if(is_payment = -1,1,0)) as fail, sum(if(is_payment = 3 or is_payment = 4,1,0)) as success'
                    . ' from ' . $this->table
                    . ' where orde_ship IS NOT NULL'
                    . ' GROUP BY orde_ship';
            //p($query);die;
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getTotal($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = 'select count(*) as Total'
                    . ' from ' . $this->table
                    //    . ' where user_updated != ""' . $strWhere;
                    . ' where 1=1 ' . $strWhere;
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }
//
//    public function getListLimitProdOrder($arrCondition = [], $intPage = 1, $intLimit = 15) {
//        try {
//            $strWhere = $this->_buildWhere($arrCondition);
//            $adapter = $this->adapter;
//            $sql = new Sql($adapter);
//
//            $query = 'select count(*) as total, prod_id'
//                    . ' from tbl_product_order'
//                    . ' where 1=1' . $strWhere
//                    . ' group by prod_id'
//                    . ' order by total DESC'
//                    . ' limit ' . $intLimit
//                    . ' offset ' . ($intLimit * ($intPage - 1));
//            //p($query);die;
//            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
//        } catch (\Zend\Http\Exception $exc) {
//            if (APPLICATION_ENV !== 'production') {
//                die($exc->getMessage());
//            }
//            return array();
//        }
//    }

    public function getReturn($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = 'select orde_id, orde_total_price,orde_created,user_updated,user_change_status,user_id,content_cancel'
                    . ' from ' . $this->table
                    . ' where 1=1 ' . $strWhere;
            //p($query);die;
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }
    public function getListProdOrder($arrCondition = []) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $query = 'SELECT *
                    from (SELECT t1.prod_id, t1.prod_name, COUNT(*) as num, SUM(t1.prod_quantity) as quantity, sum(t1.total_price) as total
                    FROM tbl_product_order t1
                    WHERE EXISTS (SELECT * FROM tbl_orders t2 WHERE t1.orde_id = t2.orde_id and t2.is_payment in (2,3,4)' . $strWhere .')
                    GROUP BY t1.prod_id
                    ORDER BY num DESC) as T1
                    where 1=1 ' . $arrCondition['T1.num'];
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }
    public function getListLimitProdOrder($arrCondition, $intPage = 1, $intLimit = 15, $strOrder = 'T1.quantity DESC') {
        //p($intPage);die;
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $search = '';
            if(!empty($arrCondition['s'])){
                $search = " and T1.prod_name like '%" . $arrCondition['s'] . "%'";
            }
            $query = 'SELECT *
                    from (SELECT t1.prod_id, t1.prod_name, COUNT(*) as num, SUM(t1.prod_quantity) as quantity, sum(t1.total_price) as total
                    FROM tbl_product_order t1
                    WHERE EXISTS (SELECT * FROM tbl_orders t2 WHERE t1.orde_id = t2.orde_id and t2.is_payment in (2,3,4)' . $strWhere .')
                    GROUP BY t1.prod_id) as T1
                    where 1=1 ' . $arrCondition['T1.num'] . $search
                    . ' order by ' . $strOrder
                    . ' limit ' . $intLimit
                    . ' offset ' . ($intLimit * ($intPage - 1));
            //p($query);die;      
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }
    public function getTotalProdOrder($arrCondition = []) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            if(!empty($arrCondition['s'])){
                $search = " and T1.prod_name like '%" . $arrCondition['s'] . "%'";
            }
            $query = 'SELECT count(*) as total
                    from (SELECT t1.prod_id, t1.prod_name, COUNT(*) as num, SUM(t1.prod_quantity) as quantity, sum(t1.total_price) as total
                    FROM tbl_product_order t1
                    WHERE EXISTS (SELECT * FROM tbl_orders t2 WHERE t1.orde_id = t2.orde_id and t2.is_payment in (2,3,4)' . $strWhere .')
                    GROUP BY t1.prod_id
                    ORDER BY num DESC) as T1
                    where 1=1 ' . $arrCondition['T1.num'] . $search;
            return (int) current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray())['total'];
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }
    
    public function getProd($param) {
        
    }

    private function _buildWhere($arrCondition) {
        $strWhere = null;

        if (empty($arrCondition)) {
            return $strWhere;
        }
        if (!empty($arrCondition['from']) && !empty($arrCondition['to'])) {
            $strWhere .= " AND (orde_created BETWEEN " . $arrCondition['from'] . " and " . $arrCondition['to'] . ")";
        }
        if (!empty($arrCondition['t2.from']) && !empty($arrCondition['t2.to'])) {
            $strWhere .= " AND (t2.orde_created BETWEEN " . $arrCondition['t2.from'] . " and " . $arrCondition['t2.to'] . ")";
        }
        if (isset($arrCondition['p0'])) {
            $strWhere .= " AND (orde_created BETWEEN " . $arrCondition['from'] . " and " . $arrCondition['to'] . ") AND is_payment=0";
        }
        if (isset($arrCondition['cusOrders'])) {
            $strWhere .= " AND (orde_created BETWEEN " . $arrCondition['from'] . " and " . $arrCondition['to'] . ") AND user_id != user_updated AND user_updated != ''";
        }

        if (isset($arrCondition['not_id_product']) && !empty($arrCondition['not_id_product'])) {
            $strWhere .= " AND prod_id NOT IN (" . $arrCondition['not_id_product'] . ")";
        }
        if (isset($arrCondition['return'])) {
            $strWhere .= " AND (orde_created BETWEEN " . $arrCondition['from'] . " and " . $arrCondition['to'] . ") AND is_payment=5";
        }
        if (isset($arrCondition['id_orde_in']) && !empty($arrCondition['id_orde_in'])) {
            $strWhere .= " AND orde_id IN (" . $arrCondition['id_orde_in'] . ")";
        }
        return $strWhere;
    }

}
