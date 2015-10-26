<?php
/**
 * LinkExternalData plugin
 * 
 * @copyright Copyright 2015 Sciences Po
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
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
        'after_save_collection'
     );

    /**
     * Install the plugin.
     *
     * ------------------------------------------------------
     */
    public function hookInstall()
    {
        //-------------- create a table ---------------------
        $sql = "
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

    /****************Uninstall the plugin**************************************/
    public function hookUninstall()
    {
        $sql = "DROP TABLE IF EXISTS {$this->_db->LinkExternalData}";
        $this->_db->query($sql);
    }

    public function hookAdminCollectionsForm($args)
    {
        $linkExternalData = $this->_db->getTable('LinkExternalData')->findByCollectionId($args['collection']->id);
        if ($linkExternalData->hasExternalData == true) {
            $centrImpor = 'checked';
            $centrLocal = '';
        } else {
            $centrLocal = 'checked';
            $centrImpor = '';
        }

        echo '<div id="chemin_image">';
            echo '<fieldset class="set"><h2>Collection Importée</h2></fieldset>';
        echo'</div>';

        echo '<div class="field">';
            echo '<div class="two columns alpha"><label for="per_page">Type de collection</label></div>';
            echo '<div class="inputs five columns omega">';
            echo '<div class="input-block">';
            $startLocal = '<p><input type= "radio" name="hasExternalData" value="false"';
            $startImpor = '<p><input type= "radio" name="hasExternalData" value="true"';
            $endLocal = '> Collection Locale (valeur par défaut)</p>';
            $endImpor = '> Collection Importée </p>';
            echo $startLocal . $centrLocal . $endLocal;
            echo $startImpor . $centrImpor . $endImpor;
        echo'</div></div>';

        echo '<div class="field">';
            echo '<div class="two columns alpha"><label for="per_page">URL si collection importée</label></div>';
            echo '<input type="text" class="textinput" size="45" name="urlExternalData" value="' . $linkExternalData->urlExternalData . '" id="urlExternalData" />';
            echo '</div>';
        echo '</div>';
    }


    public function hookAdminCollectionsShow($args)
    {
        $linkExternalData = $this->_db->getTable('LinkExternalData')->findByCollectionId($args['collection']->id);
        echo '<p><b> Numéro de la collection</b> : ' . $linkExternalData->collection_id . '</p>';
        echo '<p><b> Nom de la collection</b> : ' . $linkExternalData->name . '</p>';
        echo '<p><b> Collection avec données externes</b> : ' . $linkExternalData->hasExternalData . '</p>';
        echo '<p><b> URL des données externes</b> : ' . $linkExternalData->urlExternalData . '</p>';
    }

    public function hookBeforeSaveCollection($args)
    {
        $linkExternalData = $this->_db->getTable('LinkExternalData')->findByCollectionId($args['record']->id);
        if (!$linkExternalData) {
            return;
        }

        // Only validate the relationship during a form submission.
        if (isset($args['post']['urlExternalData'])) {
            $linkExternalData->urlExternalData = $args['post']['urlExternalData'];
            if (!$linkExternalData->isValid()) {
                $args['record']->addErrorsFrom($linkExternalData);
            }
        }

        // Only validate the relationship during a form submission.
        if (isset($args['post']['hasExternalData'])) {
            $linkExternalData->hasExternalData = $args['post']['hasExternalData'];
            if (!$linkExternalData->isValid()) {
                $args['record']->addErrorsFrom($linkExternalData);
            }
        }
    }

    public function hookAfterSaveCollection($args){
        set_option('urlExternalData', trim($_POST['urlExternalData']));

        // Radio button has been set to "true"
        if(isset($_POST['hasExternalData']) && $_POST['hasExternalData'] == 'true') {
            $_POST['hasExternalData'] = true;
            // Radio button has been set to "false" or a value was not selected
        } else {
            $_POST['hasExternalData'] = false;
        }
        set_option('hasExternalData',$_POST['hasExternalData']);
        $linkExternalData = $this->_db->getTable('LinkExternalData')->findByCollectionId($args['record']->id);
        $linkExternalData->collection_id = $args['record']->id;
        $linkExternalData->name = metadata($args['record'], array('Dublin Core', 'Title'));
        $linkExternalData->hasExternalData = get_option('hasExternalData');
        $linkExternalData->urlExternalData = get_option('urlExternalData');
        $linkExternalData->save();
    }

    /**
     * Handle collection deletions.
     */
    public function hookAfterDeleteCollection($args)
    {
        $linkExternalDataTable = $this->_db->getTable('LinkExternalData');
        // Delete the relationship with the parent collection
        $linkExternalData = $linkExternalDataTable->findByCollectionId($args['collection']->id);
        if ($linkExternalData) {
            $linkExternalData->delete();
        }
    }
}