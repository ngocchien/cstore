<?php

namespace My\Search;

use Elastica\Query\QueryString,
    Elastica\Filter\BoolAnd,
    Elastica\Filter\Term,
    Elastica\Filter\Terms,
    Elastica\Query\Bool,
    Elastica\Search,
    Elastica\Query,
    My\General;

class Products extends SearchAbstract {

    public function __construct() {
        $this->setSearchIndex(SEARCH_PREFIX . 'tbl_products');
        $this->setSearchType('productList');
    }

    public function createIndex() {

        $strIndexName = SEARCH_PREFIX . 'tbl_products';

        $searchClient = General::getSearchConfig();

        $searchIndex = $searchClient->getIndex($strIndexName);

        $objStatus = new \Elastica\Status($searchIndex->getClient());

        $arrIndex = $objStatus->getIndexNames();

        //delete index
        if (in_array($strIndexName, $arrIndex)) {
            $searchIndex->delete();
        }

        //create new index
        $searchIndex->create([
            'name' => 'translations',
            'number_of_shards' => 5,
            'number_of_replicas' => 0,
            'analysis' => [
                'analyzer' => [
                    'translation_index_analyzer' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['standard', 'lowercase', 'asciifolding', 'trim']
                    ],
                    'translation_search_analyzer' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['standard', 'lowercase', 'asciifolding', 'trim']
                    ]
                ]
            ],
            'filter' => [
                'translation' => [
                    'type' => 'edgeNGram',
                    'token_chars' => ["letter", "digit", " whitespace"],
                    'min_gram' => 1,
                    'max_gram' => 30,
                ]
            ],
                ], true);

        //set search type
        $searchType = $searchIndex->getType('productList');
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($searchType);
        $mapping->setProperties([
            'prod_id' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'prod_name' => ['type' => 'string', 'store' => 'yes', 'index_analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'prod_name_like' => ['type' => 'string', 'index' => 'not_analyzed'],
            'prod_slug' => ['type' => 'string', 'index' => 'not_analyzed'],
            'prod_call_price' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'prod_price' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'prod_promotion_price' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'prod_is_promotion' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'prod_detail' => ['type' => 'string', 'store' => 'yes', 'index_analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'prod_description' => ['type' => 'string', 'store' => 'yes', 'index_analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'cate_id' => ['type' => 'string', 'index' => 'not_analyzed'],
            'prod_code' => ['type' => 'string', 'index' => 'not_analyzed'],
            'bran_id' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'main_cate_id' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'prod_created' => ['type' => 'integer', 'index' => 'no'],
            'prod_updated' => ['type' => 'integer', 'index' => 'no'],
            'prod_rate' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'prod_count_rate' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'prod_viewer' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'user_created' => ['type' => 'integer', 'index' => 'no'],
            'user_updated' => ['type' => 'integer', 'index' => 'no'],
            'prod_image' => ['type' => 'string', 'index' => 'no'],
            'prod_image_sub' => ['type' => 'string', 'index' => 'no'],
            'prod_media' => ['type' => 'string', 'index' => 'no'],
            'prop_id' => ['type' => 'string', 'index' => 'not_analyzed'],
            'prop_info' => ['type' => 'string', 'index' => 'not_analyzed'],
            'tags_id' => ['type' => 'string', 'index' => 'not_analyzed'],
            'prod_meta_title' => ['type' => 'string', 'index' => 'not_analyzed'],
            'prod_meta_keyword' => ['type' => 'string', 'index' => 'not_analyzed'],
            'prod_meta_description' => ['type' => 'string', 'index' => 'not_analyzed'],
            'prod_social_meta' => ['type' => 'string', 'index' => 'not_analyzed'],
            'prod_status' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'prod_bestselling' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'prod_meta_robot' => ['type' => 'string', 'index' => 'not_analyzed'],
            'prod_actived' => ['type' => 'integer', 'index' => 'not_analyzed']
        ]);
        $mapping->send();
    }

    public function add($arrDocument) {
        try {
            if (empty($arrDocument) && !$arrDocument instanceof \Elastica\Document) {
                throw new \Exception('Document cannot be blank or must be instance of \Elastica\Document class');
            }

            $arrDocument = is_array($arrDocument) ? $arrDocument : [$arrDocument];
            $this->getSearchType()->addDocuments($arrDocument);
            $this->getSearchType()->getIndex()->refresh();
            return true;
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }

    public function delete($Id) {
        try {
            if(is_array($Id)){
                $this->getSearchType()->deleteIds($Id);
            }else{
                $this->getSearchType()->deleteById($Id);
            }
            return true;
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }

    public function getList() {
        $params = $this->getParams();
        $intLimit = 10000;
        $boolQuery = new Bool();
        $filter = new BoolAnd();

        $wordNameQueryString = new QueryString();
        $wordNameQueryString->setDefaultField('prod_name')
                            ->setQuery('*');
        $boolQuery->addMust($wordNameQueryString);
        $arrSort = array('_score');
        if(!empty($params['sort'])){
            foreach ($params['sort'] as $key => $value) {
                $arrSort = array($key => array('order' => $value));
            }
        }                   
        $boolQuery = $this->_buildWhere($params, $boolQuery);

        $query = new Query();
        $query->setQuery($boolQuery)
                ->setSort($arrSort)
                ->setSize($intLimit);
        if (!empty($params['source'])) {
            $query->setSource($params['source']);
        }
       // p(json_encode($query->getParams()));die;
        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);
        $this->setResultSet($resultSet);
        $arrWordList = $this->toArray();
        return $arrWordList;
    }

    public function getListLimit() {
        $params = $this->getParams();
        $intLimit = $this->getLimit();
        $intPage = $params['page'] ? $params['page'] : 1;
        $intFrom = $intLimit * ($intPage - 1);
        $boolQuery = new Bool();
        $filter = new BoolAnd();

        $wordNameQueryString = new QueryString();
        $wordNameQueryString->setDefaultField('prod_name')
                            ->setQuery('*');
        $boolQuery->addMust($wordNameQueryString);
        $arrSort = array('_score');
        if(!empty($params['sort'])){
            foreach ($params['sort'] as $key => $value) {
                $arrSort = array($key => array('order' => $value));
            }
        } 
        $boolQuery = $this->_buildWhere($params, $boolQuery);

        $query = new Query();
        $query->setQuery($boolQuery)
                ->setFrom($intFrom)
                ->setSize($intLimit)
                ->setSort($arrSort);
        if (!empty($params['source'])) {
            $query->setSource($params['source']);
        }
//p(json_encode($query->getParams()));die;
        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);
        $this->setResultSet($resultSet);
        $arrWordList = $this->toArray();
        return $arrWordList;
    }
    
    public function getTotal() {
        $params = $this->getParams();
        $boolQuery = new Bool();
        $filter = new BoolAnd();

        $wordNameQueryString = new QueryString();
        $wordNameQueryString->setDefaultField('prod_name')
                            ->setQuery('*');
        $boolQuery->addMust($wordNameQueryString);
        $boolQuery = $this->_buildWhere($params, $boolQuery);

        $query = new Query();
        $query->setQuery($boolQuery);

        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->count($query);
        return $resultSet;
    }

    public function getDetail() {
        $params = $this->getParams();
        $boolQuery = new Bool();
        
        $wordNameQueryString = new QueryString();
        $wordNameQueryString->setDefaultField('prod_name')
                            ->setQuery('*');
        $boolQuery->addMust($wordNameQueryString);
        $boolQuery = $this->_buildWhere($params, $boolQuery);
        $query = new Query();
        $query->setQuery($boolQuery);
    
        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);
        $this->setResultSet($resultSet);
        $detailContent = current($this->toArray());
        return $detailContent;
    }

    public function removeAllDoc() {
        $respond = $this->getSearchType()->deleteByQuery('_type:contentList');
        $this->getSearchType()->getIndex()->refresh();
        if ($respond->isOk()) {
            return true;
        }
        return false;
    }
    

    public function _buildWhere($params, $boolQuery) {
        
        if (empty($params)) {
            return $boolQuery;
        }
        if (!empty($params['prod_name'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_name', $params['prod_name']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['main_cate_id'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('main_cate_id', $params['main_cate_id']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['not_main_cate_id'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('main_cate_id', $params['not_main_cate_id']);
            $boolQuery->addMustNot($addQuery);
        }
        if (!empty($params['bran_id'])) {
            $regex = $params['bran_id'] . '|' . $params['bran_id'] . ',.*|.*,' . $params['bran_id'] . ',.*|.*(,)' . $params['bran_id'];
            $addQuery = new Query\Regexp();
            $addQuery->setValue('bran_id', $regex);
            $boolQuery->addMust($addQuery);           
        }
        if (!empty($params['not_prod_id'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('not_prod_id', $params['not_prod_id']);
            $boolQuery->addMustNot($addQuery);
        }
        if (!empty($params['prod_bestselling'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_bestselling', $params['prod_bestselling']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['prod_id'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_id', $params['prod_id']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['listProductID'])) {
            $addQuery = new Query\Terms();
            $addQuery->setTerms('prod_id', $params['listProductID']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['listBrandID'])) {
            $addQuery = new Query\Terms();
            $addQuery->setTerms('bran_id', $params['listBrandID']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['listTagsID'])) {
            $addQuery = new Query\Terms();
            $addQuery->setTerms('tags_id', $params['listTagsID']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['prod_status'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_status', $params['prod_status']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['prod_actived'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_actived', $params['prod_actived']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['not_prod_status'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_status', $params['not_prod_status']);
            $boolQuery->addMustNot($addQuery);
        }
        if (!empty($params['not_prod_actived'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_actived', $params['not_prod_actived']);
            $boolQuery->addMustNot($addQuery);
        }
        if (!empty($params['prod_slug'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_slug', $params['prod_slug']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['user_id'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('user_id', $params['user_id']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['prod_name_like'])) {
            $bool = new Bool();
            $math = new Query\Match();
            $math->setParam('cont_name_like', $params['prod_name_like']);
            $bool->addShould($math);
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_code', $params['prod_name_like']);
            $bool->addShould($addQuery);
            $boolQuery->addMust($bool);
        }
        if (!empty($params['prod_name_like_end'])) {
            $math = new Query\Match();
            $math->setParam('cont_name_like', $params['prod_name_like_end']);
            $boolQuery->addMust($math);
        }
        if (!empty($params['search'])) {
            $bool = new Bool();
            $math = new Query\Match();
            $math->setParam('prod_name', $params['search']);
            $bool->addShould($math);
            $math = new Query\Match();
            $math->setParam('prod_detail', $params['search']);
            $bool->addShould($math);
            $math = new Query\Match();
            $math->setParam('prod_description', $params['search']);
            $bool->addShould($math);
            $boolQuery->addMust($bool);
        }
        if (!empty($params['tags_id'])) {
            $regex = $params['tags_id'] . '|' . $params['tags_id'] . ',.*|.*,' . $params['tags_id'] . ',.*|.*(,)' . $params['tags_id'];
            $addQuery = new Query\Regexp();
            $addQuery->setValue('tags_id', $regex);
            $boolQuery->addMust($addQuery);           
        }
        if (isset($params['tags_id_not'])) {
            $bool = new Bool();
            $addQuery = new Query\Term();
            $addQuery->setTerm('tags_id', NULL);
            $bool->addShould($addQuery);
            $addQuery = new Query\Term();
            $addQuery->setTerm('tags_id', "");
            $bool->addShould($addQuery);
            $boolNot = new Bool();
            $regex = $params['tags_id_not'] . '|' . $params['tags_id_not'] . ',.*|.*,' . $params['tags_id_not'] . ',.*|.*(,)' . $params['tags_id_not'];
            $addQuery = new Query\Regexp();
            $addQuery->setValue('tags_id', $regex);
            $boolNot->addMustNot($addQuery);
            $bool->addShould($boolNot);
            $boolQuery->addShould($bool);                       
        }
        if (!empty($params['not_id_product'])) {
            $addQuery = new Query\Terms();
            $addQuery->setTerms('prod_id', $params['not_id_product']);
            $boolQuery->addMustNot($addQuery);
        }
        if (!empty($params['cate_id'])) {
            $regex = $params['cate_id'] . '|' . $params['cate_id'] . ',.*|.*,' . $params['cate_id'] . ',.*|.*(,)' . $params['cate_id'];
            $addQuery = new Query\Regexp();
            $addQuery->setValue('cate_id', $regex);
            $boolQuery->addMust($addQuery);           
        }
        if (!empty($params['not_cate_id'])) {
            $regex = $params['not_cate_id'] . '|' . $params['not_cate_id'] . ',.*|.*,' . $params['not_cate_id'] . ',.*|.*(,)' . $params['not_cate_id'];
            $addQuery = new Query\Regexp();
            $addQuery->setValue('cate_id', $regex);
            $boolQuery->addMust($addQuery);           
        }
        if (!empty($params['cate_id_or_main_cate_id'])) {
            $listCate = explode(',', $params['cate_id_or_main_cate_id']);
            $bool = new Bool();
            foreach ($listCate as $value) {
                $regex = $value . '|' . $value . ',.*|.*,' . $value . ',.*|.*(,)' . $value;
                $addQuery = new Query\Regexp();
                $addQuery->setValue('cate_id', $regex);
                $bool->addShould($addQuery);
                $addQuery = new Query\Term();
                $addQuery->setTerm('main_cate_id', $value);
                $bool->addShould($addQuery);
            }
            $boolQuery->addMust($bool);
        }
        if (!empty($params['brand_id'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('brand_id', $params['brand_id']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['price_start'])) {
            $boolTemp = new Bool();
            $bool = new Bool();
            $addQuery = new Query\Range();
            $addQuery->addField('price_start', array('gte' => $params['price_start']));
            $bool->addMust($addQuery);
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_is_promotion', 0);
            $bool->addMust($addQuery);
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_call_price', 0);
            $bool->addMust($addQuery);
            $boolTemp->addShould($bool);
            $bool = new Bool();
            $addQuery = new Query\Range();
            $addQuery->addField('prod_promotion_price', array('gte' => $params['price_start']));
            $bool->addMust($addQuery);
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_is_promotion', 1);
            $bool->addMust($addQuery);
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_call_price', 0);
            $bool->addMust($addQuery);
            $boolTemp->addShould($bool);
            $boolQuery->addMust($boolTemp);
        }
        if (!empty($params['price_end'])) {
            $boolTemp = new Bool();
            $bool = new Bool();
            $addQuery = new Query\Range();
            $addQuery->addField('price_start', array('lte' => $params['price_end']));
            $bool->addMust($addQuery);
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_is_promotion', 0);
            $bool->addMust($addQuery);
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_call_price', 0);
            $bool->addMust($addQuery);
            $boolTemp->addShould($bool);
            $bool = new Bool();
            $addQuery = new Query\Range();
            $addQuery->addField('prod_promotion_price', array('lte' => $params['price_end']));
            $bool->addMust($addQuery);
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_is_promotion', 1);
            $bool->addMust($addQuery);
            $addQuery = new Query\Term();
            $addQuery->setTerm('prod_call_price', 0);
            $bool->addMust($addQuery);
            $boolTemp->addShould($bool);
            $boolQuery->addMust($boolTemp);
        }       
        return $boolQuery;
    }
}
