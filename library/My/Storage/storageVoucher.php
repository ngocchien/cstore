<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql,
    My\Validator\Validate;

class storageVoucher extends AbstractTableGateway {

    protected $table = 'tbl_voucher';
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
                    ->order(array('vouc_id ASC'));
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
            //p($query);die;
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
    public function edit($p_arrParams, $intVoucherID) {
        try {
            $result = false;
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intVoucherID)) {
                return $result;
            }
            return $this->update($p_arrParams, 'vouc_id=' . $intVoucherID);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }
    
    
    public function addList($arrData = array()) {
        if (!empty($arrData)) {
            $size = count($arrData);
            $num = $size / 30;
            $adapter = $this->adapter;
            $j = 0;
            for ($i = 1; $i < $num + 1; $i++) {
                $query = 'insert into tbl_voucher (vouc_code,vouc_value,vouc_money,vouc_percent,vouc_type,vouc_start,vouc_end,vouc_created) values';
                for ($j; $j < 30 * $i; $j++) {
                    $value = $arrData[$j];
                    if (!empty($value)) {
                        $query .= "('" . $value['vouc_code'] . "','" . $value['vouc_value'] . "','" . $value['vouc_money'] . "','" . $value['vouc_percent'] . "'," . $value['vouc_type'] . ",'" . $value['vouc_start'] . "'," . $value['vouc_end'] . ',' . $value['vouc_created']  . '),';
                    }
                }
                $query = rtrim($query, ',');
                $query .= ' on duplicate key update vouc_id = LAST_INSERT_ID(vouc_id)';

                $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
            }
        }
    }

    private function _buildWhere($arrCondition) {
        $strWhere = null;
        if (empty($arrCondition)) {
            return $strWhere;
        }
        if ($arrCondition['vouc_id'] !== '' && $arrCondition['vouc_id'] !== NULL) {
            $strWhere .= " AND vouc_id = " . $arrCondition['vouc_id'];
        }
        if ($arrCondition['vouc_created'] !== '' && $arrCondition['vouc_created'] !== NULL) {
            $strWhere .= " AND vouc_created = " . $arrCondition['vouc_created'];
        }
        if ($arrCondition['vouc_code'] !== '' && $arrCondition['vouc_code'] !== NULL) {
            $strWhere .= " AND vouc_code = '" . $arrCondition['vouc_code'] . "'";
        }
        if ($arrCondition['vouc_status'] !== '' && $arrCondition['vouc_status'] !== NULL) {
            $strWhere .= " AND vouc_status=" . $arrCondition['vouc_status'];
        }
         if ($arrCondition['time'] !== '' && $arrCondition['time'] !== NULL) {
            $strWhere .= " AND ( vouc_start < " . $arrCondition['time'] . " AND vouc_end > " . $arrCondition['time'] . " )";
        }
        return $strWhere;
    }

}
