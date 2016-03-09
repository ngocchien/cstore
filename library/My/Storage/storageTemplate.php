<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Where,
    Zend\Db\Sql\Select,
    My\Validator\Validate;

class storageTemplate extends AbstractTableGateway {

    protected $table = 'tpl_module_template';

    public function __construct(Adapter $adapter) {
        $adapter->getDriver()->getConnection()->connect();
        $this->adapter = $adapter;
    }

    public function __destruct() {
        $this->adapter->getDriver()->getConnection()->disconnect();
    }

    public function getList($arrCondition = array()) {
        try {
            //    $strWhere = $this->_buildWhere($arrCondition);
            $strWhere = '';
            if (isset($arrCondition['not_status'])) {
                $strWhere.= ' and status != ' . $arrCondition['not_status'];
            }
            if (!empty($arrCondition['status'])) {
                $strWhere.= ' and status = ' . $arrCondition['status'];
            }
            if (isset($arrCondition['is_mobile'])) {
                $strWhere.= ' and is_mobile = ' . $arrCondition['is_mobile'];
            }

            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('sort ASC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getDetail($template_id) {
        try {
            $strWhere = '';
            if ($template_id) {
                $strWhere.=' and tem_id = ' . $template_id;
            }
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
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function edit($p_arrParams, $tem_id) {
        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($tem_id)) {
                return $result;
            }
            return $this->update($p_arrParams, 'tem_id=' . $tem_id);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

//    private function _buildWhere($arrCondition) {
//        $strWhere = '';
//
//        if (isset($arrCondition['not_template_status'])) {
//            $strWhere .= " AND template_status!=" . $arrCondition['not_template_status'];
//        }
//        return $strWhere;
//    }
}
