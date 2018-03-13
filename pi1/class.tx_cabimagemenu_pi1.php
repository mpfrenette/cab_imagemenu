<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Martin-Pierre Frenette <typo3@cablan.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'Image menu' for the 'cab_imagemenu' extension which generates
 * an image menu.
 *
 * @author	Martin-Pierre Frenette <typo3@cablan.net>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_cabimagemenu_pi1 extends tslib_pibase {
	var $prefixId = 'tx_cabimagemenu_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_cabimagemenu_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'cab_imagemenu';	// The extension key.
	var $pi_checkCHash = TRUE;
    var $lang = 0;
    var $excludeCurrent = 0;
    
	
	/**
	 * This function prepares the media image via the IMAGE function.
	 * @param [type]  $media   link to the file or comma list of images
	 * @param [type]  $alttext alttext of the image
	 * @param string  $Width   width of the images,
	 * @param integer $Heigth  height of the image 
	 * @param integer $rank    if multiple images are passed, which one is used.
	 */
	function ShowMediaImage ( $media, $alttext, $Width="130", $Heigth=0, $rank = 0 ){
		
		$imgs = explode( ",", $media);
		$img = $imgs[$rank];
		$imgTSConfig['altText'] =  $alttext;
		$imgTSConfig['file'] = 'uploads/media/'. $img;
		$imgTSConfig['file.']['width'] = $Width ;
		$imgTSConfig['file.']['height'] = $Heigth ;
		$content .= $this->cObj->IMAGE($imgTSConfig);
                
		return $content;
	}
	
	
	/**
	 * This will fetch the template from the typoscript, first from the page, then globally,
	 * and if not found, will use the default one.
	 */
	function GetTemplate($conf, $pid){

		if ( $conf["tx_cabimagemenu."][$pid.'.']["template"] != ''){
			$content = $conf["tx_cabimagemenu."][$pid.'.']["template"];
		}
		else if ( $conf["tx_cabimagemenu."]["template"] != ''){
			$content = $conf["tx_cabimagemenu."]["template"];
		}
		else{
			$content = '
		<div class="tx_cabimagemenu_container">
		<div class="tx_cabimagemenu_image">###IMAGE###</div>
		<div class="tx_cabimagemenu_right">
		<h3>###FIELD_TITLE###</h3><p>###FIELD_ABSTRACT###</p>
		</div>
		<br class="clearit" />
		</div>';
		}
		return $content; 
	}
	
    
    
    
	
	/**
	 * The main method of the PlugIn: it generates the menu, respecting languages.
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
	
        
        $this->lang =intval($this->cObj->data["sys_language_uid"]);
        
        // This determines if we do not link the current page in the menu: useful for listing
        // other pages.                           
        $this->excludeCurrent = intval($this->cObj->data["tx_cabimagemenu_exclude_current"]);
                                   
        $pidlist =  intval($GLOBALS['TSFE']->id);
        // if we defined other pages, it means we want a menu of another section,
        // so we use that pidlist instead of the current page.
        if ( $this->cObj->data["pages"] != ""){
            $pidlist = $this->cObj->data["pages"];
        }
        
        
        $pids = explode( ",", $pidlist );
        foreach( $pids as $pid ){
        
	        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery( "*", "pages", "PID=". $pid .' '. $this->cObj->enableFields('pages'), "", "sorting" );
        	while ( $result != null && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $result ) ){
                while ( $row["doktype"] == 4 ){ 
                	// internal shortcut, so we cycle thru until we find the destination.
                    $newrow = $this->pi_getrecord( "pages", $row["shortcut"] );
                    if ( $newrow != null ){
                        $row = $newrow;
                    }
                    else{
                        break;
                    }
                }
                
                if ( intval($row["uid"]) != intval($GLOBALS['TSFE']->id) || !$this->excludeCurrent ){
				    $template = $this->GetTemplate($conf, $row['uid']);
		            if(  $this->lang > 0 ){
                        $resOverlay = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages_language_overlay', 'pid='.$row["uid"]. " AND sys_language_uid=". $this->lang. $GLOBALS['TSFE']->sys_page->enableFields('pages_language_overlay'), 'sys_language_uid');
                        if ( $resOverlay ){
                            $overlay = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $resOverlay ) ; 
                            if ( $overlay ){
                                foreach ( $overlay as $field => $value ){
                                    if ( $field != "uid" &&  !is_null( $value ) && (!is_string($value) || strlen($value) >0 ) ){
                                        $row[$field] = $value;
                                    }
                                }
                            }
                        }
                    }
            	    
				    foreach( $row as $field=>$data ){
					    $MarkerArray['###FIELD_'. strtoupper($field).'###'] = $data;
				    }
					if ($row["media"]){
				    	$MarkerArray["###IMAGE###"] = $this->pi_linkToPage($this->ShowMediaImage($row["media"], $row["title"]), $row["uid"] );
					}
					else
					{
						$MarkerArray["###IMAGE###"] = $this->pi_linkToPage('<img src="/clear.gif" border="0" width="130px" height="75px" alt="'.$row["title"].'" />', $row["uid"] );
					}
				    $MarkerArray["###FIELD_TITLE###"] = $this->pi_linkToPage($row["title"],$row["uid"]);
				    
				    $content .= $this->cObj->substituteMarkerArray( $template, $MarkerArray );
                }
			}
        }
		
		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cab_imagemenu/pi1/class.tx_cabimagemenu_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cab_imagemenu/pi1/class.tx_cabimagemenu_pi1.php']);
}

?>