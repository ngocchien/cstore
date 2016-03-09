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

class Keywords extends SearchAbstract {

    public function __construct() {
        $this->setSearchIndex(SEARCH_PREFIX . 'tbl_keyword');
        $this->setSearchType('keywordList');
    }

    public function createIndex() {

        $strIndexName = SEARCH_PREFIX . 'tbl_keyword';

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
            'number_of_shards' => 20,
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
        $searchType = $searchIndex->getType('keywordList');
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($searchType);
        $mapping->setProperties([
            'word_id' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'word_key' => ['type' => 'string', 'store' => 'yes', 'index_analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'word_name' => ['type' => 'string', 'index' => 'not_analyzed'],
            'word_slug' => ['type' => 'string', 'index' => 'not_analyzed'],
            'word_data' => ['type' => 'string', 'index' => 'no'],
            'word_status' => ['type' => 'integer', 'index' => 'no'],
            'word_parent' => ['type' => 'integer', 'index' => 'no'],
            'word_samelevel' => ['type' => 'string', 'index' => 'no'],
            'word_level' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'word_loop' => ['type' => 'integer', 'index' => 'no'],
            'word_volume' => ['type' => 'integer', 'index' => 'no'],
            'word_iscrawler' => ['type' => 'integer', 'index' => 'not_analyzed'],
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
            $this->getSearchType()->deleteById($Id);
            return true;
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }

    public function getSearchData() {
        $params = $this->getParams();
        $intLimit = $this->getLimit();

        $intPage = $params['page'] ? $params['page'] : 1;
        $intFrom = $intLimit * ($intPage - 1);
        $boolQuery = new Bool();
        $filter = new BoolAnd();

        $strWordKey = $params['word_key'] ? $params['word_key'] : '*';
        if ($strWordKey === "*") {
            $wordNameQueryString = new QueryString();
            $wordNameQueryString->setDefaultField('word_key')
                    ->setQuery($strWordKey)
                    ->setDefaultOperator('AND');
            $wordNameBool = new Bool();
            $wordNameBool->addMust($wordNameQueryString);
            $boolQuery->addMust($wordNameBool);
            $wordPre = new \Elastica\Query\Term();
            $wordPre->setTerm('word_id', 0);
            $boolQuery->addMustNot($wordPre);
            $arrSort = array('word_level' => array('order' => 'ASC'));
        } else {
            $math = new Query\Match();
            $math->setParam('word_key', $strWordKey);
            $boolQuery->addMust($math);
            $arrSort = array('_score');
        }


        $query = new Query();
        $query->setQuery($boolQuery)
                ->setFrom($intFrom)
                ->setSize($intLimit)
                ->setSort($arrSort);

        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);
        $this->setResultSet($resultSet);
        $arrTagsList = $this->toArray();
        return $arrTagsList;
    }

    public function getKeyword() {
        $params = $this->getParams();
        $intLimit = $this->getLimit();

        $intPage = $params['page'] ? $params['page'] : 1;
        $intFrom = $intLimit * ($intPage - 1);
        $boolQuery = new Bool();
        $filter = new BoolAnd();

        $strWordKey = !empty($params['word_like']) ? $params['word_like'] : '*';
        if ($strWordKey === "*") {
            $wordNameQueryString = new QueryString();
            $wordNameQueryString->setDefaultField('word_key')
                    ->setQuery($strWordKey)
                    ->setDefaultOperator('AND');
            $boolQuery->addMust($wordNameQueryString);
            $wordPre = new \Elastica\Query\Term();
            $wordPre->setTerm('word_id', 0);
            $boolQuery->addMustNot($wordPre);
            $arrSort = array('word_level' => array('order' => 'asc'));
        } else {
            $wildcard = new Query\Wildcard;
            $wildcard->setParam('word_name', $strWordKey);
            $boolQuery->addMust($wildcard);
            $arrSort = array('_score');
        }


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

    public function getTotalData() {
        $params = $this->getParams();

        $boolQuery = new Bool();
        $filter = new BoolAnd();

        $strWordKey = !empty($params['word_like']) ? $params['word_like'] : '*';
        if ($strWordKey === "*") {
            $wordNameQueryString = new QueryString();
            $wordNameQueryString->setDefaultField('word_key')
                    ->setQuery($strWordKey)
                    ->setDefaultOperator('AND');
            $wordNameBool = new Bool();
            $wordNameBool->addMust($wordNameQueryString);
            $boolQuery->addMust($wordNameBool);
            $wordPre = new \Elastica\Query\Term();
            $wordPre->setTerm('word_id', 0);
            $boolQuery->addMustNot($wordPre);
        } else {
            $wildcard = new Query\Wildcard;
            $wildcard->setParam('word_name', $strWordKey);
            $boolQuery->addMust($wildcard);
        }

        $query = new Query();
        $query->setQuery($boolQuery);

        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->count($query);
        return $resultSet;
    }

    public function getMax() {
        $params = $this->getParams();
        $intLimit = 1;
        $intPage = 1;
        $intFrom = 0;

        $boolQuery = new Bool();
        $filter = new BoolAnd();
        $strWordKey = "*";
        $wordNameQueryString = new QueryString();
        $wordNameQueryString->setDefaultField('word_id')
                ->setQuery($strWordKey)
                ->setDefaultOperator('AND');
        $boolQuery->addMust($wordNameQueryString);

        $wordPre = new \Elastica\Query\Term();
        $wordPre->setTerm('word_id', 0);
        $boolQuery->addMustNot($wordPre);


        if (!empty($params['check_crawler'])) {
            $wordCrawlered = new \Elastica\Query\Term();
            $wordCrawlered->setTerm('word_iscrawler', 0);
            $boolQuery->addMust($wordCrawlered);
            $arrSort = array('word_id' => array('order' => 'asc'));
        } else {
            $arrSort = array('word_id' => array('order' => 'desc'));
        }


        $query = new Query();
        $query->setQuery($boolQuery)
                ->setFrom($intFrom)
                ->setSize($intLimit)
                ->setSort($arrSort);
        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);
        $this->setResultSet($resultSet);
        $arrTagsList = $this->toArray();
        return $arrTagsList;
    }

    public function getDataNull() {
        $params = $this->getParams();
        $intLimit = 4;
        $intPage = 1;
        $intFrom = 0;

        $boolQuery = new Bool();
        $arrSort = array('word_id' => array('order' => 'asc'));

        $wordCrawlered = new \Elastica\Query\Term();
        $wordCrawlered->setTerm('word_iscrawler', 0);
        $boolQuery->addMust($wordCrawlered);
        $wordPre = new \Elastica\Query\Term();
        $wordPre->setTerm('word_id', 0);
        $boolQuery->addMustNot($wordPre);


        $query = new Query();
        $query->setQuery($boolQuery)
                ->setFrom($intFrom)
                ->setSize($intLimit)
                ->setSort($arrSort);
        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);
        $this->setResultSet($resultSet);
        $arrTagsList = $this->toArray();
        return $arrTagsList;
    }

    public function getDetailData() {
        $params = $this->getParams();
        $boolQuery = new Bool();

        if (!empty($params['word_slug'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('word_slug', $params['word_slug']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['word_id'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('word_id', $params['word_id']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['word_iscrawler'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('word_iscrawler', $params['word_iscrawler']);
            $boolQuery->addMust($addQuery);
        }
        $query = new Query();
        $query->setQuery($boolQuery);
        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);
        $this->setResultSet($resultSet);
        $arrTagsList = $this->toArray();
        return $arrTagsList;
    }

    public function getListLimit() {
        $params = $this->getParams();
        $boolQuery = new Bool();
        $intLimit = $this->getLimit();
        $wordPre = new \Elastica\Query\Term();
        $wordPre->setTerm('word_id', 0);
        $boolQuery->addMustNot($wordPre);

        if (!empty($params['word_slug'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('word_slug', $params['word_slug']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['word_id'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('word_id', $params['word_id']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['word_iscrawler'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('word_iscrawler', $params['word_iscrawler']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['word_id_between'])) {
            $addQuery = new Query\Range();
            $addQuery->addField('word_id', array('from' => $params['word_id_between'] - 100, 'to' => $params['word_id_between'] + 5000));
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['word_id_greater'])) {
            $addQuery = new Query\Range();
            $addQuery->addField('word_id', array('gt' => $params['word_id_greater']));
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['word_id_less'])) {
            $addQuery = new Query\Range();
            $addQuery->addField('word_id', array('lt' => $params['word_id_less']));
            $boolQuery->addMust($addQuery);
        }

        $query = new Query();
        $query->setQuery($boolQuery)->setSize($intLimit);
        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);
        $this->setResultSet($resultSet);
        $arrTagsList = $this->toArray();
        return $arrTagsList;
    }

    public function removeAllDoc() {
        $respond = $this->getSearchType()->deleteByQuery('_type:keywordList');
        $this->getSearchType()->getIndex()->refresh();
        if ($respond->isOk()) {
            return true;
        }
        return false;
    }

}
