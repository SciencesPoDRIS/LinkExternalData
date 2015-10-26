<?php
/**
 * Collection Tree
 * 
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The link_external_data table.
 * 
 * @package Omeka\Plugins\LinkExternalData
 */
class Table_LinkExternalData extends Omeka_Db_Table
{
    /**
     * Cache of all collections, 
     */
    protected $_collections;

    /**
     * Cache of variables needed for some use.
     *
     * Caching is often needed to extract variables from recursive methods. Be
     * sure to reset the cache when it's no longer needed using
     * self::_resetCache().
     *
     * @see self::getDescendantTree()
     */
    protected $_cache = array();

   
    /**
     * Find by collection ID.
     *
     * @param int $collectionId
     * @return Omeka_Record
     */
    public function findByCollectionId($collectionId)
    {
        $db = $this->getDb();

        $sql = "
        SELECT *
        FROM {$db->LinkExternalData}
        WHERE collection_id = ?";

        // Child collection IDs are unique, so only fetch one row.
        return $this->fetchObject($sql, array($collectionId));
    }


    /**
     * Cache collection data.
     */
    public function cacheCollections()
    {
        $db = $this->getDb();
        $sql = "
        SELECT c.*, ct.name
        FROM {$db->Collection} c
        LEFT JOIN {$db->LinkExternalData} ct
        ON c.id = ct.collection_id";

        // check whether the acl exists -- it doesn't within a background process
        $acl = get_acl();
        // Cache only those collections to which the current user has access.
        if ($acl && ! $acl->isAllowed(current_user(), 'Collections', 'showNotPublic')) {
            $sql .= ' WHERE c.public = 1';
        }

        // Order alphabetically if configured to do so.
        if (get_option('link_external_data_alpha_order')) {
            $sql .= ' ORDER BY ct.name';
        }

        $this->_collections = $db->fetchAll($sql);
    }

    /**
     * Get the specified collection.
     *
     * @param int $collectionId
     * @return array|bool
     */
    public function getCollection($collectionId)
    {
        // Cache collections in not already.
        if (!$this->_collections) {
            $this->cacheCollections();
        }

        foreach ($this->_collections as $collection) {
            if ($collectionId == $collection['id']) {
                return $collection;
            }
        }
        return false;
    }

   
    /**
     * Reset the cache property.
     */
    protected function _resetCache()
    {
        $this->_cache = array();
    }
}
