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
        CREATE TABLE IF NOT EXISTS `{$this->_db->CollectionRemoteData}` (
          `hasRemoteData` BOOLEAN,
          'urlRemoteData`   varchar(50),
          PRIMARY KEY (`id`),
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $this->_db->query($sql);
        
        
        // Save all collections in the collection_trees table.
        $collectionTable = $this->_db->getTable('Collection');
        $collections = $this->_db->fetchAll("SELECT id FROM {$this->_db->Collection}");
        foreach ($collections as $collection) {
            $collectionObj = $collectionTable->find($collection['id']);
            $collectionTree = new CollectionTree;
            $collectionTree->hasRemoteData = FALSE;
            $collectionTree->urlRemoteData = '';
            $collectionTree->save();
        }
    }
    
    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        $sql = "DROP TABLE IF EXISTS {$this->_db->CollectionRemoteData}";
        $this->_db->query($sql);
        
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
