<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    My\Validator\Validate;

class storageProduct extends AbstractTableGateway {

    protected $table = 'tbl_products';

    public function __construct(Adapter $adapter) {
        $adapter->getDriver()->getConnection()->connect();
        $this->adapter = $adapter;
    }

    public function __destruct() {
        $this->adapter->getDriver()->getConnection()->disconnect();
    }

    public function getList($arrCondition = array()) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('prod_id DESC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getListProductID($arrCondition = array()) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)->columns(array('bran_id', 'cate_id', 'main_cate_id', 'prod_price'))
                    ->where('1=1' . $strWhere)
                    ->order('bran_id DESC');
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getListBrand($arrCondition = array()) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('prod_id DESC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getListLimit($arrCondition = [], $intPage = 1, $intLimit = 15, $strOrder = 'prod_id DESC') {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            if (!empty($arrCondition['order_sort'])) {
                $strOrder = $arrCondition['order_sort'] . 'DESC';
            }
            if (isset($arrCondition['prod_viewer'])) {
                $strOrder = $arrCondition['prod_viewer'] . 'DESC';
            }
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order($strOrder)
                    ->limit($intLimit)
                    ->offset($intLimit * ($intPage - 1));

            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getImages($arrCondition = [], $intPage = 1, $intLimit = 15) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->limit($intLimit)
                    ->offset($intLimit * ($intPage - 1));

            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getLimitUnion($cateID) {
        try {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $query = "SELECT * FROM(SELECT *FROM tbl_products WHERE main_cate_id = " . $cateID . " UNION SELECT * FROM  tbl_products WHERE cate_id IN (" . $cateID . ") ) as tb_temp LIMIT 0,10";
            $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
            return $result;
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getListLimitSortingPrice($arrCondition = [], $intPage = 1, $intLimit = 15, $strOrder = 'prod_id desc') {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            if (substr_count($strOrder, 'prod_price') > 0)
                if ($strOrder == 'prod_price desc')
                    $strOrder = 'prod_price_real desc';
                else if ($strOrder == 'prod_price asc ')
                    $strOrder = 'prod_price_real asc';
            $strScore = !empty($arrCondition['search']) ? ',MATCH(prod_name,prod_detail,prod_description) AGAINST("' . $arrCondition['search'] . '" ) as score' : ',0 as score';
            $query = 'select * , case when prod_is_promotion = 1 then prod_promotion_price when prod_is_promotion = 0 then prod_price end as prod_price_real' . $strScore
                    . ' from ' . $this->table
                    . ' where 1=1' . $strWhere
                    . ' order by ' . $strOrder
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

// khong lam
    public function getListLimitSortingPriceJoinSort($arrConditionProd = [], $arrConditionSort = [], $intPage = 1, $intLimit = 15, $strOrder = 'prod_id desc') {
        try {
            $strWhereSort = $this->_buildWhere($arrConditionSort);
            $strWhereProd = $this->_buildWhere($arrConditionProd);
            $adapter = $this->adapter;

            if (substr_count($strOrder, 'prod_price') > 0)
                if ($strOrder == 'prod_price desc')
                    $strOrder = 'prod.prod_price_real desc';
                else if ($strOrder == 'prod_price asc ')
                    $strOrder = 'prod.prod_price_real asc';
            $querySort = 'select *'
                    . ' from tbl_sort'
                    . ' where 1=1' . $strWhereSort;

            $queryProd = 'select * , case when prod_is_promotion = 1 then prod_promotion_price when prod_is_promotion = 0 then prod_price end as prod_price_real'
                    . ' from ' . $this->table
                    . ' where 1=1' . $strWhere;
            $query = 'select prod.* , sort.*'
                    . ' from (' . $queryProd . ') as prod'
                    . ' left join (' . $querySort . ') as sort'
                    . ' on prod.prod_id = sort.sort_product'
                    . ' order by ' . $strOrder
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

//khong lam
    public function getListLimitSortingPriceJoinSortTags($arrConditionProd = [], $arrConditionSort = [], $intPage = 1, $intLimit = 15, $strOrder = 'prod_id desc') {
        try {
            $strWhereSort = $this->_buildWhere($arrConditionSort);
            $strWhereProd = $this->_buildWhere($arrConditionProd);
            $adapter = $this->adapter;

            if (substr_count($strOrder, 'prod_price') > 0)
                if ($strOrder == 'prod_price desc')
                    $strOrder = 'prod.prod_price_real desc';
                else if ($strOrder == 'prod_price asc ')
                    $strOrder = 'prod.prod_price_real asc';
            $querySort = 'select *'
                    . ' from tbl_sort_tags'
                    . ' where 1=1' . $strWhereSort;

            $queryProd = 'select * , case when prod_is_promotion = 1 then prod_promotion_price when prod_is_promotion = 0 then prod_price end as prod_price_real'
                    . ' from ' . $this->table
                    . ' where 1=1' . $strWhere;
            $query = 'select prod.* , sort.*'
                    . ' from (' . $queryProd . ') as prod'
                    . ' left join (' . $querySort . ') as sort'
                    . ' on prod.prod_id = sort.sort_product'
                    . ' order by ' . $strOrder
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

//khong lam
    public function getListLimitJoinSort($arrConditionProd = [], $arrConditionSort = [], $intPage = 1, $intLimit = 15, $strOrder) {
        try {
            $strWhereSort = $this->_buildWhere($arrConditionSort);
            $strWhereProd = $this->_buildWhere($arrConditionProd);
            $adapter = $this->adapter;
            $strScore = '';
            $queryProd = 'select *'
                    . ' from ' . $this->table
                    . ' where 1=1' . $strWhereProd;
            $querySort = 'select *'
                    . ' from tbl_sort'
                    . ' where 1=1' . $strWhereSort;
            $query = 'select prod.* , sort.*'
                    . ' from (' . $queryProd . ') as prod'
                    . ' left join (' . $querySort . ') as sort'
                    . ' on prod.prod_id = sort.sort_product'
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

    public function editViewer($intProdID) {
        try {
            $adapter = $this->adapter;
            $query = 'UPDATE tbl_products SET prod_viewer = prod_viewer + 1 WHERE prod_id=' . $intProdID;
            $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
            $resultSet = new \Zend\Db\ResultSet\ResultSet();
            $resultSet->initialize($result);
            $result = $resultSet->count() ? true : false;
            return $result;
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

//khong lam
    public function getListLimitJoinSortTags($arrConditionProd = [], $arrConditionSort = [], $intPage = 1, $intLimit = 15, $strOrder) {
        try {
            $strWhereSort = $this->_buildWhere($arrConditionSort);
            $strWhereProd = $this->_buildWhere($arrConditionProd);
            $adapter = $this->adapter;
            $strScore = '';
            $queryProd = 'select *'
                    . ' from ' . $this->table
                    . ' where 1=1' . $strWhereProd;
            $querySort = 'select *'
                    . ' from tbl_sort_tags'
                    . ' where 1=1' . $strWhereSort;
            $query = 'select prod.* , sort.*'
                    . ' from (' . $queryProd . ') as prod'
                    . ' left join (' . $querySort . ') as sort'
                    . ' on prod.prod_id = sort.sort_product'
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

//khong lam
    public function getTotalProdJoinSort($arrConditionProd = [], $arrConditionSort = []) {
        try {
            $strWhereSort = $this->_buildWhere($arrConditionSort);
            $strWhereProd = $this->_buildWhere($arrConditionProd);
            //p($arrConditionProd);die;
            $adapter = $this->adapter;
            $queryProd = 'select *'
                    . ' from ' . $this->table
                    . ' where 1=1' . $strWhereProd;
            $querySort = 'select *'
                    . ' from tbl_sort'
                    . ' where 1=1' . $strWhereSort;
            $query = 'select count(prod.prod_id) as total'
                    . ' from (' . $queryProd . ') as prod'
                    . ' left join (' . $querySort . ') as sort'
                    . ' on prod.prod_id = sort.sort_product';
            return (int) current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray())['total'];
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function getTotal($arrCondition = []) {

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
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function getDetail($arrCondition = array()) {
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
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function add($p_arrParams) {
        try {
            if (!is_array($p_arrParams) || empty($p_arrParams)) {
                return false;
            }
//            $validator = new Validate();
//            $noRecordExists = $validator->noRecordExists($p_arrParams['prod_name'], $this->table, 'prod_name', $this->adapter);
//            if (!$noRecordExists) {
//                return false;
//            }
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

    public function edit($p_arrParams, $intProductID) {
//        p($intProductID);die;
        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intProductID)) {
                return $result;
            }
            return $this->update($p_arrParams, 'prod_id=' . $intProductID);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    private function _buildWhere($arrCondition) {
        $strWhere = '';

        if (isset($arrCondition['prod_name'])) {
            $strWhere .= " AND LOWER(prod_name)='" . strtolower($arrCondition['prod_name']) . "'";
        }

        if (isset($arrCondition['prod_id_more'])) {
            $strWhere .= " AND prod_id>" . $arrCondition['prod_id_more'] . "";
        }

        if (isset($arrCondition['prod_code'])) {
            $strWhere .= " AND prod_code ='" . $arrCondition['prod_code'] . "'";
        }

        if (isset($arrCondition['main_cate_id'])) {
            $strWhere .= " AND main_cate_id =" . $arrCondition['main_cate_id'];
        }
        if (isset($arrCondition['not_main_cate_id'])) {
            $strWhere .= " AND main_cate_id !=" . $arrCondition['not_main_cate_id'];
        }

        if (isset($arrCondition['bran_id'])) {
            $strWhere .= " AND ( FIND_IN_SET('" . $arrCondition['bran_id'] . "',bran_id))";
        }

        if (isset($arrCondition['not_prod_id'])) {
            $strWhere .= " AND prod_id !=" . $arrCondition['not_prod_id'];
        }

        if (isset($arrCondition['prod_bestselling'])) {
            $strWhere .= " AND prod_bestselling =" . $arrCondition['prod_bestselling'];
        }

        if (isset($arrCondition['prod_id'])) {
            $strWhere .= " AND prod_id =" . $arrCondition['prod_id'];
        }

        if (isset($arrCondition['listProductID'])) {
            $strWhere .= " AND prod_id in (" . $arrCondition['listProductID'] . ')';
        }

        if (isset($arrCondition['listBrandID'])) {
            $strListband = $arrCondition['listBrandID'];
            if (is_array($arrCondition['listBrandID'])) {
                $strListband = implode(',', $arrCondition['listBrandID']);
            }
            $strWhere .= " AND bran_id in (" . $strListband . ')';
        }

        if (isset($arrCondition['listTagsID'])) {
            $strWhere .= " AND tags_id in (" . $arrCondition['listTagsID'] . ')';
        }


        if (isset($arrCondition['prod_status'])) {
            $strWhere .= " AND prod_status =" . $arrCondition['prod_status'];
        }

        if (isset($arrCondition['prod_actived'])) {
            $strWhere .= " AND prod_actived =" . $arrCondition['prod_actived'];
        }


        if (isset($arrCondition['not_prod_status'])) {
            $strWhere .= " AND prod_status !=" . $arrCondition['not_prod_status'];
        }

        if (isset($arrCondition['not_prod_actived'])) {
            $strWhere .= " AND prod_actived !=" . $arrCondition['not_prod_actived'];
        }

        if (isset($arrCondition['prod_slug'])) {
            $strWhere .= " AND prod_slug ='" . $arrCondition['prod_slug'] . "'";
        }

        if (isset($arrCondition['user_id'])) {
            $strWhere .= " AND user_id=" . $arrCondition['user_id'];
        }

        if (isset($arrCondition['prod_name_like']) && $arrCondition['prod_name_like']) {
            $keyword = "'%" . \My\General::clean($arrCondition['prod_name_like']) . "%'";
            $strWhere .= ' AND (prod_name LIKE ' . $keyword . ' OR prod_code=' . (int) $arrCondition['prod_name_like'] . ') ';
        }

        if (isset($arrCondition['prod_name_like_end']) && $arrCondition['prod_name_like_end']) {
            $keyword = "'" . $arrCondition['prod_name_like_end'] . "%'";
            $strWhere .= ' AND prod_name LIKE ' . $keyword;
        }

        if (isset($arrCondition['search']) && $arrCondition['search']) {

            // MATCH(prod_name,prod_detail,prod_description) AGAINST("' . $arrCondition['search'] . '" ) OR 
            $strWhere .= ' AND (MATCH(prod_name,prod_detail,prod_description) AGAINST("' . $arrCondition['search'] . '" ) OR prod_name LIKE "%' . \My\General::clean($arrCondition['search']) . '%")';
        }
        if (isset($arrCondition['tags_id'])) {
            $strWhere .= " AND FIND_IN_SET('" . $arrCondition['tags_id'] . "',tags_id)";
        }

        if (isset($arrCondition['tags_id_not'])) {
            $strWhere .= " AND NOT FIND_IN_SET('" . $arrCondition['tags_id_not'] . "',tags_id)";
        }
        if (isset($arrCondition['not_id_product']) && !empty($arrCondition['not_id_product'])) {
            $strWhere .= " AND prod_id NOT IN (" . $arrCondition['not_id_product'] . ")";
        }

        if (isset($arrCondition['cate_id'])) {
            $strWhere .= " AND FIND_IN_SET('" . $arrCondition['cate_id'] . "',cate_id)";
        }
        if (isset($arrCondition['not_cate_id'])) {
            $strWhere .= " AND NOT FIND_IN_SET('" . $arrCondition['not_cate_id'] . "',cate_id)";
        }

        if (isset($arrCondition['cate_id_or_main_cate_id']) && !empty($arrCondition['cate_id_or_main_cate_id'])) {
            $listCate = explode(',', $arrCondition['cate_id_or_main_cate_id']);
            $str_cate = '';
            $str_main = '';
            foreach ($listCate as $value) {
                $str_cate.= " OR FIND_IN_SET('" . $value . "',cate_id) ";
                $str_main.= " OR main_cate_id = " . $value;
            }
            $strWhere .= " AND (  " . ltrim($str_main, ' OR ') . $str_cate . ")";
        }

        if (isset($arrCondition['brand_id']) && $arrCondition['brand_id']) {
            $strWhere .= " AND bran_id = " . $arrCondition['brand_id'];
        }

        if (isset($arrCondition['or_brand_id']) && !empty($arrCondition['or_brand_id'])) {
            $brand_id = explode(',', $arrCondition['or_brand_id']);

            $strType = '';
            foreach ($brand_id as $val) {
                $strType .= " bran_id = '" . $val . "' OR";
            }
            $strType = substr($strType, 0, -2);
            $strWhere .= " AND (" . $strType . ")";
        }

        if (isset($arrCondition['price_start'])) {
            $strWhere .= " AND ( ( prod_price >= " . $arrCondition['price_start'] . " AND prod_is_promotion = 0 AND prod_call_price = 0 ) OR ( prod_promotion_price >= " . $arrCondition['price_start'] . " AND prod_is_promotion = 1 AND prod_call_price = 0 ) )";
        }

        if (isset($arrCondition['price_end'])) {
            $strWhere .= " AND ( ( prod_price <= " . $arrCondition['price_end'] . " AND prod_is_promotion = 0 AND prod_call_price = 0 ) OR ( prod_promotion_price <= " . $arrCondition['price_end'] . " AND prod_is_promotion = 1 AND prod_call_price = 0 ) )";
        }

        if (isset($arrCondition['sort_product'])) {
            $strWhere .= " AND sort_product =" . $arrCondition['sort_product'];
        }
        if (isset($arrCondition['sort_cate'])) {
            $strWhere .= " AND sort_cate =" . $arrCondition['sort_cate'];
        }
        if (isset($arrCondition['sort_tag'])) {
            $strWhere .= " AND sort_tag =" . $arrCondition['sort_tag'];
        }

        if (isset($arrCondition['strBrandList'])) {
            $strWhere .= " AND bran_id IN (" . $arrCondition['strBrandList'] . ")";
        }
        if ($arrCondition['list_prod_id'] !== '' && $arrCondition['list_prod_id'] !== NULL) {
            $strWhere .= " AND prod_id IN (" . $arrCondition['list_prod_id'] . ")";
        }
        return $strWhere;
    }

}
