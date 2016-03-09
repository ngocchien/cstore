<?php

namespace My\Search;

use Elastica\Query\QueryString,
    Elastica\Filter\BoolAnd,
    Elastica\Filter\Term,
    Elastica\Query\Bool,
    Elastica\Search,
    Elastica\Query,
    My\General;
use Elastica\Query\Prefix;

class Tags extends SearchAbstract {

    public function __construct() {
        $this->setSearchIndex(SEARCH_PREFIX . 'tags');
        $this->setSearchType('tagsList');
    }

    public function getSearchData() {
        $params = $this->getParams();
        $intLimit = $this->getLimit();

        $intPage = $params['page'] ? $params['page'] : 1;
        $intFrom = $intLimit * ($intPage - 1);

        $boolQuery = new Bool();
        $filter = new BoolAnd();
                        
        $strTagsName = $params['tagsName'] ? $params['tagsName'] : '*';
        $tagNameQueryString = new QueryString();
        $tagNameQueryString->setDefaultField('tag_name')
                ->setQuery($strTagsName)
                ->setDefaultOperator('AND');
        $tagNameBool = new Bool();
        $tagNameBool->addMust($tagNameQueryString);
        $boolQuery->addMust($tagNameBool);
        
        $filterIsDeleted = new Term();
        $filterIsDeleted->setTerm('is_deleted', 0);
        $filter->addFilter($filterIsDeleted);
        
        if ($params['status'] > -1) {
            $filterStatus = new Term();
            $filterStatus->setTerm('status', (int) $params['status']);
            $filter->addFilter($filterStatus);
        }


        $arrSort = array('_score');

        $query = new Query();
        $query->setQuery($boolQuery)
                ->setFilter($filter)
                ->setFrom($intFrom)
                ->setSize($intLimit)
                ->setSort($arrSort);

        $query = $this->setHighLight($query);
        
        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);

        $this->setResultSet($resultSet);
        $arrTagsList = $this->toArray();

        return $arrTagsList;
    }

}
