<?php
/**
 * LinkExternalData
 *
 * @copyright 
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * @package LinkExternalData\View\Helper
 */
class LinkExternalData_View_Helper_LinkExternalData extends Zend_View_Helper_Abstract
{

    /**
     * ... description
     * @param 
     * @return string
     */
    public function linkexternaldata($item)
    {

        $ID = metadata('item', array('Dublin Core', 'Identifier')); 
        
        $linkExternalDataTable = get_db()->getTable('LinkExternalData');

        $toBePrinted='';

        /*if no link table table or not attached to collection*/
        if (!$linkExternalDataTable || $item->collection_id == 0) {
            /*not need to go further*/
        }
        else{
            $linkExternalData = $linkExternalDataTable->findByCollectionId($item->collection_id);        

            if ($linkExternalData->hasExternalData == true){           
                
                $found = false;
                $image_found = false;
                $link_found = false;

                /*search for the */
                switch ($linkExternalData->urlExternalData) {
                    case "http://archive.org" : {
                        /*Internet Archive collection harvested from OAI-PMH harvester*/                    
                        //1. look for archive.org/details/ in ID                    
                        $p = stripos($ID,"archive.org/details/");
                            if (!$p) {
                                ;/*warning should be added here, nothing will be displayed*/
                            } else {
                               $link_found = true;
                               $image_found = true;
                               //2. replace "details" by "services/img" to find the link to the thumbnail
                               $vignetteIA = str_replace("details","services/img",$ID); 
                            }
                            $found = $image_found && $link_found;
                            if ($found) {
                                $link_to = $ID;
                                $img_src = $vignetteIA;
                                $alt_image_comment = '"Visiter le document sur Internet Archive"';
                                $comment_after_thumbnail = 'IA';
                            }                         
                        }
                        break;
                    case "http://bibnum.prive.bulac.fr" : {                    
                        /*Bulac collection harvested from OAI-PMH harvester*/
                        /*this one has several ID ...*/                    
                        /*1. searches for the ID that contains the item in Bulac collection (for exhttp://bibnum.prive.bulac.fr/items/show/108)*/
                        $existLinktoBulac = false;
                        $i=0;$link_found = false;
                        while ($i<30 /*limit the reseach ot the 30 first items*/ && $link_found === false){
                            $ID_n = metadata('item', array('Dublin Core', 'Identifier'),$i);
                            if (stripos($ID_n,"bibnum.prive.bulac.fr/items") === false){$i++;}
                            else{
                                /*found the ID that describes the link to the item in Bulac collection*/
                                $existLinktoBulac = true;
                                $lienItemBulac = str_replace(".prive.",".",$ID_n);   
                                $ID = $ID_n;
                                $link_found = true;
                            }
                        }

                        /*2. searches for the ID that contains the link to the JPG file*/
                        $i=0;$image_found = false;
                        while ($i<30 && $image_found === false){
                            $ID_n = metadata('item', array('Dublin Core', 'Identifier'),$i); 
                            if (stripos($ID_n,"jpg")===false) {
                                $i++;
                            } else {
                                /*cas de collection importée par OAI-harvester depuis le site de la bulac*/
                                $existLinktoBulac = true;
                                $lienVignetteBulac = str_replace(".prive.",".",$ID_n);   
                                $continue = false;
                                $image_found = true;
                            }
                        }
                        
                        $found = $image_found && $link_found;
                        if ($found) {
                              $link_to = $lienItemBulac;
                              $img_src = $lienVignetteBulac;
                              $alt_image_comment = '"Visiter le document sur num.bulac.fr"';
                              $comment_after_thumbnail = 'bulac';
                        }                    
                    }
                    break;
                    case "" : {
                        echo '<div>???Actually has no urlExternalData???</div>';
                    }
                    break;
                    default :{
                        echo '<div>??'.$linkExternalData->urlExternalData.'</div>';
                    }
                    break;
                }

                if ($found){
                    $toBePrinted = '<h2>External Data</h2>';
                    $toBePrinted .= '<a href="'.$link_to.'"><img style="height:100px;"  src="'.$img_src.'" alt='.$alt_image_comment.' /></a>';                
                    $toBePrinted .= '</p><i>'.$comment_after_thumbnail.'</i>';
                }
                else if (!$image_found) {
                    $toBePrinted = '<small>no image_found</small>';
                }
                else /*!$link_found*/
                    $toBePrinted = '<small>no link_found</small>';
            }
    
            return $toBePrinted;
        }
  }
}
