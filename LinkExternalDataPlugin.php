<?php
/**
 * LinkExternalData
 * 
 * @copyright Copyright 2015 Sciences Po
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The LinkExternalData plugin
 * 
 * @package Omeka\Plugins\LinkExternalData
 */
class LinkExternalDataPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'uninstall',        
        'initialize',   
        'upgrade',            
        'admin_collections_show',               
        'admin_collections_form',
        'before_save_collection',
        'after_save_collection',
     );
    


    /**
     * Install the plugin.
     *
     * ------------------------------------------------------
     */
    public function hookInstall()
    {
        //-------------- create a table ---------------------
        $sql  = "
        CREATE TABLE IF NOT EXISTS `{$this->_db->LinkExternalData}` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `collection_id` int(10) unsigned NOT NULL,
          `name` text COLLATE utf8_unicode_ci,
          `hasExternalData` BOOLEAN,
          `urlExternalData` text COLLATE utf8_unicode_ci,      
          PRIMARY KEY (`id`),
          UNIQUE KEY `collection_id` (`collection_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        $this->_db->query($sql);   
        
        // Save all collections in the collection_trees table.
        $collectionTable = $this->_db->getTable('Collection');
        $collections = $this->_db->fetchAll("SELECT id FROM {$this->_db->Collection}");
        foreach ($collections as $collection) {
            $collectionObj = $collectionTable->find($collection['id']);
            $linkExternalData = new LinkExternalData();
            $linkExternalData->collection_id = $collection['id'];           
            $linkExternalData->hasExternalData = FALSE;
            $linkExternalData->urlExternalData = '';
            $linkExternalData->name = metadata($collectionObj, array('Dublin Core', 'Title'));            
            $linkExternalData->save();
        }
    }
    
    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        $sql = "DROP TABLE IF EXISTS {$this->_db->LinkExternalData}";
        $this->_db->query($sql);
        
    }
    
    /**
     * Initialize the plugin.
     */
    public function hookInitialize()
    {
        // Add translation.
        add_translation_source(dirname(__FILE__) . '/languages');
    }


  /**
     * Upgrade from earlier versions.
     */
    public function hookUpgrade($args)
    {
        // Prior to Omeka 2.0, collection names were stored in the collections 
        // table; now they are stored as Dublin Core Title. This upgrade 
        // compensates for this by moving the collection names to the 
        // collection_trees table.
        if (version_compare($args['old_version'], '2.0', '<')) {
            
            // Add the name column to the collection_trees table.
            $sql = "ALTER TABLE {$this->_db->LinkExternalData} ADD `name` TEXT NULL";
            $this->_db->query($sql);
            
            // Assign names to their corresponding collection_tree rows.
            $collectionTreeTable = $this->_db->getTable('CollectionTree');
            $collectionTable = $this->_db->getTable('Collection');
            $collections = $this->_db->fetchAll("SELECT id FROM {$this->_db->Collection}");
            foreach ($collections as $collection) {
                $collectionTree = $collectionTreeTable->findByCollectionId($collection['id']);
                if (!$collectionTree) {
                    $collectionTree = new CollectionTree;
                    $collectionTree->collection_id = $collection['id'];
                    $collectionTree->parent_collection_id = 0;
                }
                $collectionObj = $collectionTable->find($collection['id']);
                $collectionTree->name = metadata($collectionObj, array('Dublin Core', 'Title'));
                $collectionTree->save();
            }
        }
    }







    public function hookAdminCollectionsForm($args)
    {
        echo '<div id="chemin_image">';
            echo '<fieldset class="set"><h2>Collection Importée</h2></fieldset>';
        echo'</div>';

        echo '<div class="field">';
            echo '<div class="two columns alpha"><label for="per_page">Collection Importée ?</label></div>';
            echo '<div class="inputs five columns omega">';
                echo '<div class="input-block">';

                    $start='<p><input type= "radio" name="origine_collection" value="local"';
                    $end  ='> Collection Locale (valeur par défaut)</p>';
                    $centr1=get_option('origine_collection')=="local" ? 'checked' :'';
                    $centr2=get_option('origine_collection')=="imported" ? 'checked' :'';


                    echo $start.$centr1.$end;
                    echo $start.$centr2.$end;

                    echo '<input type="text" class="textinput" size="45" name="chemin_image_http" value="'.get_option('chemin_image_http').'" id="chemin_image_http" />';

                
            echo '</div>';            
        echo '</div>';

      
    }


    public function hookAdminCollectionsShow($args)
    {
                echo '<p><b> Origine Collection</b> : '.get_option('origine_collection').'</p>';
                echo '<p><b> Chemin Image</b> : '.get_option('chemin_image_http').'</p>';

                echo '<p><b> Collection numéro</b> : '.$args['collection']->id.'</p>';

                $args['collection']->origine_collection=get_option('origine_collection');       

                print_r($args['collection']);
                

    }


    public function hookBeforeSaveCollection($args)
    {

        $collection_id=$args['collection']->id;

        set_option('chemin_image_http',trim($_POST['chemin_image_http']));
        set_option('origine_collection',trim($_POST['origine_collection']));
      //  $args['collection']->origine_collection=get_option('origine_collection');       

    }



      public function hookAfterSaveCollection($args)
    {
        
      //  $args['collection']->origine_collection=get_option('origine_collection');       

    }

}
