<?php

class Searchdata_ListController extends Zend_Controller_Action
{
    public function regionesAction()
    {
        $Regiones = new Searchdata_Model_Regions();
        $this->view->regiones = $Regiones->getItems($this->_getParam('countryid'));
        
        $this->_helper->layout->disableLayout();
    }
    
    public function citiesAction()
    {
        $Cities = new Searchdata_Model_Cities();
        $this->view->cities = $Cities->getItems($this->_getParam('regionid'));
        
        $this->_helper->layout->disableLayout();
    }
}