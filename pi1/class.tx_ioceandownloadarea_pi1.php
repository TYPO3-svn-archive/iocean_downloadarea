<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 iocean_lmeunier <>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http:// www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * class.tx_ioceandownloadarea_pi1.php
 *
 * Provides XYZ plugin implementation.
 *
 * $Id: class.tx_ioceandownloadarea_pi1.php 9514 2009-07-23 17:06:12Z iocean_lmeunier $
 *
 * @author lionel Meunier <lmeunier@iocean.fr>
 * 
 * Principal's Class of plugin ioceandownloadarea
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  66: class tx_ioceandownloadarea_pi1
 * 117: function main()
 * 175: public function initAllConfigurations ()
 * 189: public function fetchConfigurationValue ($param)
 * 203: function buildArbo ($path, $level)
 * 343: function scanArbo ($path, $level) 
 * 400: function buildLink ($path, $file, $count)
 * 478: public function buildSingleView ($conf,$data,$template) 
 * 592: private function buildLinkAjax ($link)
 * 609: private function recupImgExt($ext) 
 * 640: public function addHeaderParts() 
 * 
 * TOTAL FUNCTIONS: 10
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_tslib.'class.tslib_content.php');

/**
 * Plugin 'download area' for the 'iocean_downloadarea' extension.
 *
 * @author	iocean_lmeunier <>
 * @package	TYPO3
 * @subpackage	tx_ioceandownloadarea
 */
class tx_ioceandownloadarea_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_ioceandownloadarea_pi1';		//  Same as class name
	var $scriptRelPath = 'pi1/class.tx_ioceandownloadarea_pi1.php';	//  Path to this script relative to the extension dir.
	var $extKey        = 'iocean_downloadarea';	//  The extension key.
	var $pi_checkCHash = true;

	var $extListeForbidden = false;
	// default's parameter of plugin
	var $defaultDownload = array(	'labelStdWrap.' => array('field' => 'tx_ioceandownloadareaTitle //  tx_ioceandownloadareaFilename'),
									'icon' => 0,
									'icon_link' => 0,
									'icon.' => array('noTrimWrap' => '| | |'),
									'size' => 0,
									'size.' => array(	'noTrimWrap' => '| (| Bytes) |',
														'bytes' => 1,
														'bytes.' => array('labels' => '|&nbsp;Kb|&nbsp;Mb|&nbsp;Gb')),
									'jumpurl' => 1,
									'jumpurl.' => array('secure' => 1)
	);
	var $expectingConf = array('rootPoint'    => array('sheet'    => 'sDEF',
                                                     'default'  => './fileadmin/'),
                              'fileStep'   => array('sheet'    => 'sDEF',
                                                     'default'  => 10),
                              'openAllTree'    => array('sheet'    => 'sDEF',
                                                     'default'  => false),
                              'extList'      => array('sheet'    => 'sDEF',
                                                     'default'  => ''),
							  'displayTime' => array('sheet' => 'sDEF',
													 'default' => false),
							  'displaySize' => array('sheet' => 'sDEF',
													 'default' => false),
                              'templateFile'=> array('sheet'    => 'sDEF',
                                                     'default'  => 'EXT:iocean_downloadarea/res/downloadarea.tmpl'),
							  'download' => array('sheet' => 'sDEF',
													 'default' => ''),
							  'iconDirectory' => array('sheet' => 'sDEF',
													 'default' => 'EXT:iocean_downloadarea/res/icones/'),
							  'singleView' => array('sheet' => 'sDEF',
													 'default' => false),
							  'extConfig' => array('sheet' => 'sDEF',
													 'default' => false),
							  'includeCSS' => array('sheet' => 'sDEF',
													 'default' => 'EXT:iocean_downloadarea/res/downloadarea.css')
	);
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main ($content, $conf) {
		global $TYPO3_CONF_VARS;
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		//  init


		$this->expectingConf['download']['default'] = $this->defaultDownload;
		$this->pi_initPIflexForm();
		$this->initAllConfigurations();
		$this->addHeaderParts();


		$extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$this->extKey]);
		//gestion Case of extList
		if ($this->conf['extConfig']) {
			$this->extListeForbidden = strtolower($extConf['EXTENTION']).','.strtoupper($extConf['EXTENTION']);
			$this->conf['extList'] = strtolower($this->conf['extList']).','.strtoupper($this->conf['extList']);
		} else {
			$this->conf['extList'] = strtolower($this->conf['extList']).','.strtoupper($this->conf['extList']).','.strtolower($extConf['EXTENTION']).','.strtoupper($extConf['EXTENTION']);
		}



		if ($_GET['singleView'] != 1) {

			//  display
			$content = '';
			$contentFinal = '';
			$subpart = $this->cObj->getSubpart($this->templateCode, '###PRINCIPAL###');
			$this->markerarray['###DIRECTORY_TITLE###'] = $this->pi_getLL('directory_title');
			$this->markerarray['###FILE_TITLE###'] = $this->pi_getLL('file_title');
			if (is_dir($this->conf['rootPoint'])) {
				$content .= $this->buildArbo($this->conf['rootPoint'], 0);
			} else {
				$content .= $this->pi_getLL('error_root_point');
			}
			$imgWarning = $this->cObj->fileResource('EXT:iocean_downloadarea/res/warning.png',' alt="'.$this->pi_getLL('alt_warning').'"');
			$this->markerarray['###AFFICHE_ARBO###'] = $content;
			$this->markerarray['###AFFICHE_CONTENU###'] = $this->scanArbo($this->conf['rootPoint'], 0);
			$this->markerarray['###TEXT_DEFAULT###'] = $imgWarning.$this->pi_getLL('text_default');
			$this->markerarray["###TEXT_DEFAULT_DIR###"]=$this->pi_getLL("text_default_dir");
			$tabPagination['###NAME_PREC###'] = $this->pi_getLL('name_prec');
			$tabPagination['###NAME_SUIV###'] = $this->pi_getLL('name_suiv');
			$pagination = $this->cObj->getSubpart($this->templateCode, '###PAGINATION###');
			$this->markerarray['###AFFICHE_PAGINATION###'] = $this->cObj->substituteMarkerArray($pagination, $tabPagination);
			$this->markerarray['###AFFICHE_SINGLEVIEW###'] = "";
			$contentFinal = $this->cObj->substituteMarkerArray($subpart, $this->markerarray);

			return $this->pi_wrapInBaseClass($contentFinal);
		}
	}

	/** Initializes all the settings necessary for the proper functioning of the plugin
	 * @return void
	 */
	public function initAllConfigurations () {
		if (is_array($this->expectingConf)) {
			foreach ($this->expectingConf as $param=>$conf) {
				$this->fetchConfigurationValue($param);
			}
		}
		$this->templateCode = $this->cObj->fileResource($this->conf['templateFile']);
	}

	/** Get the value of $param configuration ( flexform or typoscript ) 
	 * with a priority on flexform (allows to override the settings in flexform) 
	 * @param  string $param the parameter whose value is sought 
	 * @return string the value found
	 */
	public function fetchConfigurationValue ($param) {
		$value = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], $param, $this->expectingConf[$param]['sheet'] ));
		if ($value) {
			$this->conf[$param] = $value;
		} elseif (!$this->conf[$param]) { //  aucune configuration définie pour ce paramètre -> valeur par défaut
			$this->conf[$param] = $this->expectingConf[$param]['default'];
		}
	}
	/**
	 * Build arborescence on basis of the directory
	 * @param  string $path the way's directory
	 * @param  int $niv the level's directory in arborescence compared with root point
	 * @return all display's arborescence
	 */
	function buildArbo ($path, $level) {

		$content = '';
		$contentFinal = '';
		$count = 0;
		$afficheNiv = '';
		$level++;

		// defind div name
		$recordModuleFin['###ID_DIV###'] = $path;

		// recursive read's directory
		$countFile = 0;
		$sous_dossier=0;
		if ($dir = opendir($path)) {
			while (($file = readdir($dir)) != false) {
				if (is_dir($path.'/'.$file)) {
					// call recursive of method
					if ($file != '.' && $file != '..') {
						if ($file{0} != '.') {
							$sous_dossier++;
							$count++;
							if ($level == 1) {
								$content .= $this->buildArbo($path.$file, $level);
							} else {
								$content .= $this->buildArbo($path.'/'.$file, $level);
							}
						}
					}
				} else {
					// count file
					if ($file{0} != '.') {
						$ext = pathinfo($file, PATHINFO_EXTENSION);

						$arrayExt = explode(',', $this->conf['extList']);
						if($this->extListeForbidden != false){
							$arrayExtForb = explode(',', $this->extListeForbidden);
						}
						if ($this->conf['extConfig']) {
							if (in_array($ext, $arrayExt) && !in_array($ext, $arrayExtForb)) {
								$countFile++;
							}
						} else {
							if (!in_array($ext, $arrayExt)) {
								$countFile++;
							}
						}

					}

				}
			}
			closedir($dir);
		}


		// recovery name and number file
		$nom = basename($path);
		$recordModule['###NOM###'] = htmlentities($nom);
		if ($countFile == 0) {
			$recordModule['###NBR###'] = '';
		} else {
			$recordModule['###NBR###'] = ' ('.$countFile.')';
		}

		// build href
		if ($countFile>0) {
			$numPage = intval($countFile / $this->conf['fileStep'])+1;
		} else {
			$numPage = 0;
		}
		if ($path{0} == '.') {
			$path = substr($path, 1);
		}
		$pathCode = md5($path);
		$recordModule['###HREF###'] = $pathCode.';'.$numPage.';'.$sous_dossier;
		$subpart = $this->cObj->getSubpart($this->templateCode, '###LIEN_DOSSIER###');
		$recordModuleFin['###NOM###'] = $this->cObj->substituteMarkerArray($subpart, $recordModule);


		// show button and sub arborescence
		if ($count != 0) {
			// show div sub arborescence
			$recordModuleArbo['###ID_DIV###'] = $pathCode;
			$recordModuleArbo['###CONTENU###'] = $content;
			// show or not all arbborescence
			if ($this->conf['openAllTree']) {
				$recordModuleArbo['###DISPLAY###'] = 'block';
			} else {
				if ($level > 1) {
					$recordModuleArbo['###DISPLAY###'] = 'none';
				} else {
					$recordModuleArbo['###DISPLAY###'] = 'block';
				}
			}
			$sousArbo = $this->cObj->getSubpart($this->templateCode, '###SOUS_ARBO_ITEM###');
			$recordModuleFin['###SOUS_ARBO###'] = $this->cObj->substituteMarkerArray($sousArbo, $recordModuleArbo);


			if ($this->conf['openAllTree']) {
				$recordModule['###CLASS_BUTTON###'] = 'button minus';
			} else {
				if ($level > 1) {
					$recordModule['###CLASS_BUTTON###'] = 'button plus';
				} else {
					$recordModule['###CLASS_BUTTON###'] = 'button minus';
				}
			}
			$recordModule['###ID_DIV###'] = $pathCode;
			$button = $this->cObj->getSubpart($this->templateCode, '###BUTTON_PLUS###');
			$recordModuleFin['###BUTTON###'] = $this->cObj->substituteMarkerArray($button, $recordModule);
		} else {
			$levelDisplay = $this->cObj->getSubpart($this->templateCode, '###SEPARATOR_ARBO###');
			$arrayLevel['###LIEN_IMAGE###'] = t3lib_extMgm::siteRelPath($this->extKey).'res/line.gif';
			$afficheNiv .= $this->cObj->substituteMarkerArray($levelDisplay, $arrayLevel);
			$recordModuleFin['###BUTTON###'] = '';
			$recordModuleFin['###SOUS_ARBO###'] = '';
		}

		// display level
		for ($i = 1; $i<$level; $i++) {
			$levelDisplay = $this->cObj->getSubpart($this->templateCode, '###SEPARATOR_ARBO###');
			$arrayLevel['###LIEN_IMAGE###'] = t3lib_extMgm::siteRelPath($this->extKey).'res/line.gif';
			$afficheNiv .= $this->cObj->substituteMarkerArray($levelDisplay, $arrayLevel);
		}

		$recordModuleFin['###NIV###'] = $afficheNiv;
		// return arborescene
		$subpart = $this->cObj->getSubpart($this->templateCode, '###ARBO_ITEM###');
		$contentFinal .= $this->cObj->substituteMarkerArray($subpart, $recordModuleFin);

		return $contentFinal;
	}

	/**
	 * Scan arborescence for build div content link
	 * @param  string $path the way's directory
	 * @return All div content link and information
	 */

	function scanArbo ($path, $level) {
		$contentFinal = '';
		$count = 0;
		$level++;
		if ($dir = opendir($path)) {
			while(($file = readdir($dir)) != false) {
				if (is_dir($path.'/'.$file)) {
					if ($file != '.' && $file != '..') {
						if ($file{0} != '.') {
							if ($level == 1) {
								$contentFinal .= $this->scanArbo($path.$file, $level);
							} else {
								$contentFinal .= $this->scanArbo($path.'/'.$file, $level);
							}
							// call recursive of method

						}
					}
				} else {
					if ($file{0} != '.') {
						$ext = pathinfo($file, PATHINFO_EXTENSION);
						$arrayExt = explode(',', $this->conf['extList']);
						if($this->extListeForbidden != false){
							$arrayExtForb = explode(',', $this->extListeForbidden);
						}
						if ($this->conf['extConfig']) {
							if (in_array($ext, $arrayExt) && !in_array($ext, $arrayExtForb)) {
								// buil div of link
								$contentFinal .= $this->buildLink($path, $file, $count);
								$count++;
							}
						} else {
							if (!in_array($ext, $arrayExt)) {
								// buil div of link
								$contentFinal .= $this->buildLink($path, $file, $count);
								$count++;
							}
						}

					}

				}
					
			}
			closedir($dir);
		}
		return $contentFinal;
	}

	/**
	 * Build link for one files
	 * @param  string $path the file directory's way
	 * @param  string $file the name's file
	 * @param  int $count the number's file in directory
	 * @return div content link and information
	 */

	function buildLink ($path, $file, $count) {

		if (substr($path,-1) == "/") {
			$link = $path.$file;
		}else{
			$link = $path.'/'.$file;
		}
		// recovery name, size and time


		$statTab = stat($link);
		$recordModule['###NOM_FICHIER###'] = $file;
		$recordModule['###INFORMATION###'] = '';

		if ($this->conf['displayTime']) {
			$time = $this->pi_getLL('date_format');
			$recordModule['###INFORMATION###'] .= '    '.date($time, $statTab['mtime']);
		}

		if ($this->conf['displaySize']) {
			if ($statTab['size'] > 1048576) {
				$size = round($statTab['size'] / 1048576);
				$unit = $this->pi_getLL('unit_mo');
			} else {
				if ($statTab['size'] > 1024) {
					$size = round($statTab['size'] / 1024);
					$unit = $this->pi_getLL('unit_ko');
				} else {
					$size = $statTab['size'];
					$unit = $this->pi_getLL('unit_o');
				}
			}
			$recordModule['###INFORMATION###'] .= '    ('.$size.$unit.')';

		}

		// icone config
		$recordModule['###LIEN_FICHIER###'] = $this->recupImgExt(pathinfo($link,PATHINFO_EXTENSION));
		$this->conf['download']['icon'] = 0;


		// build link
		$this->cObj->data['tx_ioceandownloadareaTitle'] = htmlentities($file);
		$this->cObj->data['tx_ioceandownloadareaFilename'] = $link;

		//t3lib_div::debug($this->conf);
		if($this->conf['singleView']){
			$recordModule['###LIEN_FICHIER###'] .=$this->buildLinkAjax($link);
		} else {
			$recordModule['###LIEN_FICHIER###'] .= $this->cObj->filelink($link, $this->conf['download']);
		}





		// build classe
		if ($path{0} == '.') {
			$path = substr($path, 1);
		}
		$pathCode = md5($path);
		$recordModule['###DOSSIER###'] = $pathCode;
		$recordModule['###COUNT###'] = intval($count / $this->conf['fileStep']);


		// return div content link and information
		$subpart = $this->cObj->getSubpart($this->templateCode, '###VISUAL_DOSSIER###');
		$contentFinal .= $this->cObj->substituteMarkerArray($subpart, $recordModule);
		return $contentFinal;

	}
	/**
	 * Build Single View
	 * @param  array $conf 
	 * @param  array $data 
	 * @param  string $template
	 * @return div content file's information
	 */
	public function buildSingleView ($conf,$data,$template) {
		//	config ajax

		// config lang
		if (isset($_GET['L'])){
			$this->altLLkey = $_GET['L'];
		}
		$this->pi_loadLL();
		if (empty($this->LOCAL_LANG[$this->altLLkey])){
			$this->altLLkey = 'default';
		}

		//	recup config
		$this->conf = $conf;
		$this->templateCode = $template;
		$link = $_GET['link'];

		// config typo3
		$this->pi_setPiVarDefaults();
		$this->cObj = new tslib_cObj();
		$this->cObj->start($data[0]);

		//	config & diplay label

		$recordModule['###LABEL1###'] = (empty($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView1'])) ?  htmlentities($this->LOCAL_LANG['default']['LABEL_singleView1']) : htmlentities($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView1']);
		$recordModule['###LABEL2###'] = (empty($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView2'])) ?  htmlentities($this->LOCAL_LANG['default']['LABEL_singleView2']) : htmlentities($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView2']);
		$recordModule['###LABEL3###'] = (empty($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView3'])) ?  htmlentities($this->LOCAL_LANG['default']['LABEL_singleView3']) : htmlentities($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView3']);
		$recordModule['###LABEL4###'] = (empty($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView4'])) ?  htmlentities($this->LOCAL_LANG['default']['LABEL_singleView4']) : htmlentities($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView4']);
		$recordModule['###LABEL5###'] = (empty($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView5'])) ?  htmlentities($this->LOCAL_LANG['default']['LABEL_singleView5']) : htmlentities($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView5']);
		$recordModule['###LABEL6###'] = (empty($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView6'])) ?  htmlentities($this->LOCAL_LANG['default']['LABEL_singleView6']) : htmlentities($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView6']);
		$recordModule['###LABEL7###'] = (empty($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView7'])) ?  htmlentities($this->LOCAL_LANG['default']['LABEL_singleView7']) : htmlentities($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView7']);
		if ($size = @getimagesize($link)) {
			$recordModule['###LABEL8###'] = (empty($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView8'])) ?  htmlentities($this->LOCAL_LANG['default']['LABEL_singleView8']) : htmlentities($this->LOCAL_LANG[$this->altLLkey]['LABEL_singleView8']);
		} else {
			$recordModule['###LABEL8###'] = "";
		}

		//	config & diplay name

		$recordModule['###NAME###'] =  htmlentities(basename($link));

		//	config & diplay extention
		$this->conf['iconDirectory'] = $_GET['icone'];
		
		$recordModule['###EXT###'] =  $this->recupImgExt(pathinfo($link, PATHINFO_EXTENSION));


		$stat = stat($link);
		(empty($this->LOCAL_LANG[$this->altLLkey]['date_format'])) ?  $date_format = $this->LOCAL_LANG['default']['date_format'] : $date_format = $this->LOCAL_LANG[$this->altLLkey]['date_format'];
		//	config & diplay created date

		$recordModule['###DATEC###'] =  date($date_format, $stat['ctime']);

		//	config & diplay updated date

		$recordModule['###DATEM###'] =  date($date_format, $stat['mtime']);

		//	config & diplay size

		if ($stat['size'] > 1048576) {
			$size = round($stat['size'] / 1048576);
			(empty($this->LOCAL_LANG[$this->altLLkey]['unit_mo'])) ?  $unit = $this->LOCAL_LANG['default']['unit_mo'] : $unit = $this->LOCAL_LANG[$this->altLLkey]['unit_mo'];
		} else {
			if ($stat['size'] > 1024) {
				$size = round($stat['size'] / 1024);
				(empty($this->LOCAL_LANG[$this->altLLkey]['unit_ko'])) ?  $unit = $this->LOCAL_LANG['default']['unit_ko'] : $unit = $this->LOCAL_LANG[$this->altLLkey]['unit_ko'];
			} else {
				$size = $stat['size'];
				(empty($this->LOCAL_LANG[$this->altLLkey]['unit_o'])) ?  $unit = $this->LOCAL_LANG['default']['unit_o'] : $unit = $this->LOCAL_LANG[$this->altLLkey]['unit_o'];
			}
		}
		$recordModule['###SIZE###'] =  $size.$unit;

		//	config & diplay link
		$imgLink = '<img src="typo3conf/ext/iocean_downloadarea/res/view.png" alt="'.$recordModule['###LABEL6###'].'" title="'.$recordModule['###LABEL6###'].'"/>';
		$recordModule['###LINK###'] = '<a target="_blank" href="'.$link.'">'.$imgLink.'</a>';
		
		//	config & diplay link download
		$imgLinkDown = '<img src="typo3conf/ext/iocean_downloadarea/res/download.png" alt="'.$recordModule['###LABEL7###'].'" title="'.$recordModule['###LABEL7###'].'"/>';
		$this->cObj->data['tx_ioceandownloadareaTitle'] = $imgLinkDown;
		$this->cObj->data['tx_ioceandownloadareaFilename'] = $link;
		$GLOBALS['TSFE']->id = $data[0]['pid'];
		$GLOBALS['TSFE']->type = 0;
		$GLOBALS['TSFE']->TYPO3_CONF_VARS['SYS']['encryptionKey'] = $_GET['encryptionKey'];
		$recordModule['###LINKD###'] = $this->cObj->filelink($link, $this->defaultDownload);

		//	config & diplay image
		if ($size = @getimagesize($link)) {
			$recordModule['###IMG###'] = '<img src="'.$link.'"';
			if ($size[0] > $size[1]) {
				if($size[0] > 220){
					$recordModule['###IMG###'] .=' width="220"';
				}
			} else {
				if($size[1] > 200){
					$recordModule['###IMG###'] .=' height="200"';
				}
			}
			$recordModule['###IMG###'] .=' />';
		} else {
			$recordModule['###IMG###'] = '';
		}

		//	display
		$subpart = $this->cObj->getSubpart($this->templateCode, '###SINGLEVIEW###');
		return $this->cObj->substituteMarkerArray($subpart, $recordModule);
	}
	
	/**
	 * Build Link for SingleView
	 * @param  string $link file's path
	 * @return link content 
	 */
	
	private function buildLinkAjax ($link) {
		$recordModule["###HREF###"] = "index.php?eID=tx_ioceandownloadarea_pi1&amp;singleView=1&amp;link=".urlencode($link)."&amp;uid=".$this->cObj->data['uid']."&amp;encryptionKey=".$GLOBALS['TSFE']->TYPO3_CONF_VARS['SYS']['encryptionKey'];
		//config langue
		$recordModule["###HREF###"] .= "&amp;L=".$this->LLkey;
		//config icon directory
		$recordModule["###HREF###"] .= "&amp;icone=".urlencode($this->conf['iconDirectory']);

		$recordModule["###LINK###"] = basename($link);
		$subpart = $this->cObj->getSubpart($this->templateCode, '###LINK_AJAX###');
		return $this->cObj->substituteMarkerArray($subpart, $recordModule);
	}

	/**
	 * Build Link for SingleView
	 * @param  string $ext file's extension
	 * @return img content of file's extension 
	 */
	private function recupImgExt($ext) {

		if (preg_match('/iocean_downloadarea/', $this->conf['iconDirectory'])) {
			$tabPath = explode("iocean_downloadarea/", $this->conf['iconDirectory']);
			$path = t3lib_extMgm::siteRelPath($this->extKey).$tabPath[1];
		} else {
			$path = $this->conf['iconDirectory'];
		}

		if (file_exists($path)) {
			if ($dir = opendir($path)) {
				while (($file = readdir($dir)) != false) {
					if($file == $ext.'.gif'){
						$recordModule['###SRC###'] = $path.$file;
						$subpart = $this->cObj->getSubpart($this->templateCode, '###IMG_EXT###');
						return  $this->cObj->substituteMarkerArray($subpart, $recordModule);
					}
				}
			}
		}

		$iconP = t3lib_extMgm::siteRelPath('cms').'tslib/media/fileicons/';
		$icon = @is_file($iconP.$ext.'.gif') ? $iconP.$ext.'.gif' : $iconP.'default.gif';
		$recordModuleIcon['###SRC###'] = $icon;
		$subpart = $this->cObj->getSubpart($this->templateCode, '###IMG_EXT###');
		return $this->cObj->substituteMarkerArray($subpart, $recordModuleIcon);

	}
	
	/**Adds information in the header of the page to include
	* Files .css and .js files associated with the plugin */
	public function addHeaderParts() {
		$key = 'EXT:'. $this->extKey.md5($this->templateCode);
		if (!isset($GLOBALS['TSFE']->additionalHeaderData[$key])) {
			$headerParts = $this->cObj->getSubpart($this->templateCode, '###HEADER_PARTS###');
			if ($headerParts) {
				$headerParts = $this->cObj->substituteMarker($headerParts, '###SITE_REL_PATH###', t3lib_extMgm::siteRelPath($this->extKey));
				$GLOBALS['TSFE']->additionalHeaderData[$key] = $headerParts;
			}
		}
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/iocean_downloadarea/pi1/class.tx_ioceandownloadarea_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/iocean_downloadarea/pi1/class.tx_ioceandownloadarea_pi1.php']);
}

?>