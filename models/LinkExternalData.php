<?php
/**
 * LinkExternalData plugin
 * 
 * @copyright Copyright 2015 Sciences Po
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 * @package Omeka\Plugins\LinkExternalData
 */

class LinkExternalData extends Omeka_Record_AbstractRecord
{
    public $collection_id;
    public $name;
    public $hasExternalData;
    public $urlExternalData;
}
