<?php

namespace My\Shipping;

class Shipping {

    const KG_EXCHANGE_RATE = 0.4536;

    protected $_address;
    protected $_city;
    protected $_district;
    protected $_ward;
    
    protected $_arrCity;
    protected $_arrDistrict;
    protected $_arrWard;
    
    // don vi tinh: gram
    protected $_weight;
    protected $_dimension;

    //service locator
    protected $serviceLocator;
    
    public function __construct($serviceLocator) {
        $this->setServiceLocator($serviceLocator);
    }
    
    /**
     * @return the $_address
     */
    public function getAddress() {
        return $this->_address;
    }

    /**
     * @param field_type $_address
     */
    public function setAddress($_address) {
        $this->_address = $_address;
        return $this;
    }

    /**
     * @return the $_city
     */
    public function getCity() {
        return $this->_city;
    }

    /**
     * @param field_type $_city
     */
    public function setCity($_city) {
        $this->_city = $_city;
        return $this;
    }

    /**
     * @return the $_district
     */
    public function getDistrict() {
        return $this->_district;
    }

    /**
     * @param field_type $_district
     */
    public function setDistrict($_district) {
        $this->_district = $_district;
        return $this;
    }

    /**
     * @return the $_ward
     */
    public function getWard() {
        return $this->_ward;
    }

    /**
     * @param field_type $_ward
     */
    public function setWard($_ward) {
        $this->_ward = $_ward;
        return $this;
    }

    /**
     * @return the $_weight
     */
    public function getWeight() {
        return $this->_weight;
    }

    /**
     * @param field_type $_weight
     */
    public function setWeight($_weight) {
        $this->_weight = $_weight;
        return $this;
    }

    /**
     * @return the $_dimension
     */
    public function getDimension() {
        return $this->_dimension;
    }
    

    /**
     * @return the $_arrCity
     */
    public function getArrCity ()
    {
        $serviceCity = $this->getServiceLocator()->get('My\Models\City');
        $this->_arrCity = $serviceCity->getDetail(array('city_id' => $this->getCity()));
        
        return $this->_arrCity;
    }

	/**
     * @return the $_arrDistrict
     */
    public function getArrDistrict ()
    {
        $serviceDistrict = $this->getServiceLocator()->get('My\Models\District');
        $this->_arrDistrict = $serviceDistrict->getDetail(array('district_id' => $this->getDistrict()));
        
        return $this->_arrDistrict;
    }

	/**
     * @return the $_arrWard
     */
    public function getArrWard ()
    {
        $serviceWard = $this->getServiceLocator()->get('My\Models\Ward');
        $this->_arrWard = $serviceWard->getDetail(array('ward_id' => $this->getWard()));
        
        return $this->_arrWard;
    }

	/**
     * @return the $serviceLocator
     */
    public function getServiceLocator ()
    {
        return $this->serviceLocator;
    }


	/**
     * @param field_type $serviceLocator
     */
    public function setServiceLocator ($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

	/**
     * @param field_type $_dimension
     */
    public function setDimension($_dimension) {
        $this->_dimension = $_dimension;
        return $this;
    }

    public function __lbsToGram($lbs = 0) {
        return $lbs * self::KG_EXCHANGE_RATE * 1000;
    }

}
