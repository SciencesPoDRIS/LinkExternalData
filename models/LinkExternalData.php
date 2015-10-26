<?php
/**
 * Link External Data
 * 
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * A link_external_data row.
 * 
 * @package Omeka\Plugins\LinkExternalData
 */
class LinkExternalData extends Omeka_Record_AbstractRecord
{
    public $collection_id;
    public $name;
    public $hasExternalData;
    public $urlExternalData;
    
}
