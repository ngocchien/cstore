<?php

namespace My\Search;

use Elastica\Query\QueryString,
    Elastica\Filter\BoolAnd,
    Elastica\Filter\Term,
    Elastica\Query\Bool,
    Elastica\Search,
    Elastica\Query,
    My\General;

class User extends SearchAbstract {

    public function __construct() {
        $this->setSearchIndex(SEARCH_PREFIX . 'users');
        $this->setSearchType('userList');
    }

    public function getSearchData() {
        $params = $this->getParams();
        $intLimit = $this->getLimit();

        $intPage = $params['page'] ? $params['page'] : 1;
        $intFrom = $intLimit * ($intPage - 1);

        $boolQuery = new Bool();
        $filter = new BoolAnd();


        $strFullname = $params['fullname'] ? $params['fullname'] : '*';
        $fullnameQueryString = new QueryString();
        $fullnameQueryString->setDefaultField('fullname')
                ->setQuery($strFullname)
                ->setDefaultOperator('AND');
        $fullnameBool = new Bool();
        $fullnameBool->addMust($fullnameQueryString);
        $boolQuery->addMust($fullnameBool);

        if (isset($params['userRole']) && $params['userRole'] > 0) {
            // search exactly by role
            $intUserRole = (int) $params['userRole'];
            $filterUserRole = new Term();
            $filterUserRole->setTerm('user_role', $intUserRole);
            $filter->addFilter($filterUserRole);
        } else {
            // search all role
            $rangeFilter = new \Elastica\Filter\Range();
            $rangeFilter->addField('user_role', array('from' => 0));
            $filter->addFilter($rangeFilter);
        }

        //Search by email
        if ($params['email']) {
            $emailQueryString = new QueryString();
            $emailQueryString->setDefaultField('email')
                    ->setQuery($params['email'])
                    ->setDefaultOperator('AND');
            $emailBool = new Bool();
            $emailBool->addMust($emailQueryString);
            $boolQuery->addMust($emailBool);
        }

        if ($params['phoneNumber']) {
            $phoneQueryString = new QueryString();
            $phoneQueryString->setDefaultField('phone')
                    ->setQuery($params['phoneNumber'])
                    ->setDefaultOperator('AND');
            $phoneBool = new Bool();
            $phoneBool->addMust($phoneQueryString);
            $boolQuery->addMust($phoneBool);
        }
        //sort by
        $arrSort = $params['fullname'] || $params['email'] || $params['phoneNumber'] || $params['userRole'] ? array('_score') : array('created_date' => array('order' => 'desc'));

        //set query
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
        $arrUserList = $this->toArray();

        return $arrUserList;
    }

}
