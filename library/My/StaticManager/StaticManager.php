<?php

namespace My\StaticManager;

class StaticManager {

    private $serviceLocator;
    private $arrData = ['js' => [], 'css' => []];
    private $strResource = '';

    /**
     * 
     * @param String $strResource
     */
    public function __construct($strResource = '', $serviceLocator) {

        if (empty($strResource) || !$serviceLocator instanceof \Zend\ServiceManager\ServiceLocatorInterface) {
            throw new \Exception('resource cannot be blank and $serviceLocator must be instance of Zend\ServiceManager\ServiceLocatorInterface');
        }

        $this->strResource = $strResource;
        $this->serviceLocator = $serviceLocator;
        $minCSS = APPLICATION_ENV === 'production' ? 'styles.min.css' : 'styles.css';

        list($strModule, $strController, $strAction) = explode(':', $strResource);
        switch ($strModule) {
            case 'frontend':
                if (FRONTEND_TEMPLATE == 'v1') {
                    $this->arrData['css']['defaultCSS'] = STATIC_URL . '/f/' . FRONTEND_TEMPLATE . '/css/??style.css,facebox.css,autocomplete.css';
                    $this->arrData['js']['defaultJS'] = STATIC_URL . '/f/' . FRONTEND_TEMPLATE . '/js/library/??jquery-1.11.1.min.js,autocomplete.js,facebox.js,jssor.slider.mini.js,script.js,index.js,cart.js,account.js,topbar.js';
                } else {
                    $this->arrData['css']['defaultCSS'] = STATIC_URL . '/f/' . FRONTEND_TEMPLATE . '/css/??swiper.min.css,style.css';
                    $this->arrData['js']['defaultJS'] = STATIC_URL . '/f/' . FRONTEND_TEMPLATE . '/js/library/??jquery-1.11.1.min.js,swiper.jquery.min.js,script.js';
                }
                break;
            case 'backend':
                $this->arrData['css']['defaultCSS'] = STATIC_URL . '/b/css/??bootstrap.min.css,bootstrap-reset.css,font-awesome.min.css,style.css,style-responsive.css,custom.css,table-responsive.css,tasks.css,selectivity-full.min.css';
                $this->arrData['js']['defaultJS'] = STATIC_URL . '/b/js/library/??jquery-1.11.1.min.js,bootstrap.min.js,jquery.dcjqaccordion.2.7.js,jquery.scrollTo.min.js,jquery.nicescroll.js,respond.min.js,bootbox.min.js,common-scripts.js,selectivity-full.min.js,script.js';
                break;
            case 'partner':
                $this->arrData['css']['defaultCSS'] = STATIC_URL . '/p/css/??bootstrap.min.css,bootstrap-reset.css,font-awesome.min.css,style.css,style-responsive.css,custom.css,table-responsive.css,tasks.css,selectivity-full.min.css';
                $this->arrData['js']['defaultJS'] = STATIC_URL . '/p/js/library/??jquery-1.11.1.min.js,bootstrap.min.js,jquery.dcjqaccordion.2.7.js,jquery.scrollTo.min.js,jquery.nicescroll.js,respond.min.js,bootbox.min.js,common-scripts.js,selectivity-full.min.js,script.js';
                break;
            default:
                $this->arrData['css']['defaultCSS'] = '';
                $this->arrData['js']['defaultJS'] = '';
                break;
        }
    }

    /**
     * 
     * @param String|Array $arrData //ex : a.css | a.css,b.css | [a.css, b.css]
     * @return $this
     */
    public function setCSS($arrData = '') {
        if (!is_array($arrData) && $arrData) {
            $arrData = [$arrData];
        }
        if (is_array($arrData['defaultCSS'])) {
            $arrData = $arrData ? $arrData['defaultCSS'][$this->strResource] : '';
            $this->arrData['css']['defaultCSS'] .= $arrData ? ',' . $arrData : '';
        }
        if (is_array($arrData['externalCSS'])) {
            $arrCSS = is_array($arrData['externalCSS'][$this->strResource]) ? $arrData['externalCSS'][$this->strResource] : [$arrData['externalCSS'][$this->strResource]];
            $this->arrData['css']['externalCSS'] = $arrCSS ? $arrCSS : [];
        }
        foreach ($arrData['externalCSS'] as $key => $link) {
            if (is_int($key)) {
                array_push($this->arrData['css']['externalCSS'], $link);
            }
        }
        return $this;
    }

    /**
     * 
     * @param String|Array $data
     * @return $this
     */
    public function setJS($arrData = '') {
        if (!is_array($arrData) && $arrData) {
            $arrData = [$arrData];
        }

        if (is_array($arrData['defaultJS'])) {
            $arrData = $arrData ? $arrData['defaultJS'][$this->strResource] : '';
            $this->arrData['js']['defaultJS'] .= $arrData ? ',' . $arrData : '';
        }
        if (is_array($arrData['externalJS'])) {
            $arrJS = is_array($arrData['externalJS'][$this->strResource]) ? $arrData['externalJS'][$this->strResource] : [$arrData['externalJS'][$this->strResource]];
            $this->arrData['js']['externalJS'] = $arrJS ? $arrJS : [];
        }
        foreach ($arrJS['externalJS'] as $key => $link) {
            if (is_int($key)) {
                $this->arrData['js']['externalJS'][] = $link;
            }
        }
        return $this;
    }

    public function render($version = '') {
        $version = $version && APPLICATION_ENV === 'production' ? $version : time();
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        if ($this->arrData['css']['defaultCSS']) {
            $renderer->headLink()->offsetSetStylesheet(1, $this->arrData['css']['defaultCSS'] . '?v=' . $version);
        }
        if ($this->arrData['js']['defaultJS']) {
            $renderer->headScript()->setAllowArbitraryAttributes(true)->offsetSetFile(1, $this->arrData['js']['defaultJS'] . '?v' . $version, 'text/javascript');
        }
        if ($this->arrData['css']['externalCSS']) {
            $tmp = 1;
            foreach ($this->arrData['css']['externalCSS'] as $k => $css) {
                $tmp +=1;
                $renderer->headLink()->offsetSetStylesheet($tmp, $css . '?v=' . $version);
            }
        }
        if ($this->arrData['js']['externalJS']) {
            $tmp = 1;
            foreach ($this->arrData['js']['externalJS'] as $k => $arrData) {
                $tmp +=1;
                $renderer->headScript()->setAllowArbitraryAttributes(true)->offsetSetFile($tmp, $arrData . '?v' . $version, 'text/javascript');
            }
        }
    }

}
