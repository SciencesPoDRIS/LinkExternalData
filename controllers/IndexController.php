<?php
/**
 * LinkExternalData plugin
 * 
 * @copyright Copyright 2015 Sciences Po
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 * @package Omeka\Plugins\LinkExternalData
 */

class LinkExternalData_IndexController extends Omeka_Controller_AbstractActionController
{
    public function indexAction()
    {
        $this->view->full_link_external_data = $this->view->linkExternalDataFullList();
    }
}
