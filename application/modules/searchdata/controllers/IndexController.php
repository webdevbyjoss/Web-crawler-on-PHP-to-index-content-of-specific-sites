<?php

class Searchdata_IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        
    }
    
    public function regionesAction()
    {
        $Countries = new Searchdata_Model_Countries();
        $this->view->countries = $Countries->getItems();
        
        // TODO: we need to integrade geoIP service to detect current country
        $currentCountryId = 9908;
        
        if (!empty($currentCountryId)) {
            $Regions = new Searchdata_Model_Regions();
            $this->view->regions = $Regions->getItems($currentCountryId);
        }
        
        $this->view->currentCountryId = $currentCountryId;
        $this->_helper->layout->disableLayout();
    }
    
    public function servicesAction()
    {
        $Services = new Searchdata_Model_Services();
        $topLevelServices = $Services->getItems();
        
        $subServices = array();
        foreach ($topLevelServices as $serv) {
            $childs = $Services->getItems($serv->service_id);
            if (!empty($childs)) {
                $subServices[$serv->service_id] = $childs;
            }
            unset($childs);
        }
        
        $this->view->subServices = $subServices;
        $this->view->topServices = $topLevelServices;
        
        $this->_helper->layout->disableLayout();
    }
}