<?php
/* 
 * @package   [siteroot-export-bundle]
 * @author    Taheri Create Core Team
 * @license   GNU/LGPL
 * @copyright Taheri Create 2023 - 2026
 */

// Adding the export button
$GLOBALS['TL_DCA']['tl_page']['list']['operations']['export'] = [
    'href' => 'mode=export-pages',
    'icon' => 'theme_export.svg',
    'button_callback' => ['AllSiteroot', 'exportToExcel']
];

// Handle Export
if(\Input::get('mode') == 'export-pages'){	

	// Initialize local vars
	$level = 1;
	
	// Force download
	header("Content-Type: application/xls; charset=utf-8");    
	header("Content-Disposition: attachment; filename=export-root-" . \Input::get('id') . ".xls");  
	header("Pragma: no-cache"); 
	header("Expires: 0");
	
	// Get the required pages
	$arrayPages = AllSiteroot::getChildPages(\Input::get('id'));	
	foreach($arrayPages as $arrayPage){
		if($arrayPage['level'] > $level){
			$level = $arrayPage['level'];
		}
	}
	
	echo '<table border="1">';	
	echo '<tr>';
	for($i = 1; $i <= $level; $i ++){
		echo '<td><b>Seitenebene-' . sprintf('%02d', $i) . '</b></td>';
	}
	echo '</tr>';
	foreach($arrayPages as $arrayPage){
		echo '<tr>';
		for($i = 1; $i <= $level; $i ++){
			if($arrayPage['level'] == $i){
				echo '<td>' . $arrayPage['title'] . '<br>' . $arrayPage['url'] . '</td>';
			}
			else{
				echo '<td></td>';
			}
		}
		echo '</tr>';
	}
	echo '</table>';
	exit;
}

class AllSiteroot extends \Backend
{
	/**
	 * Return the export button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
    public function exportToExcel($row, $href, $label, $title, $icon, $attributes){
		if($row['pid']){
			return '<img src="system/themes/flexible/icons/theme_export_.svg" width="16" height="16" alt="export">';
		}
		return '
			<a href="contao?do=page&amp;mode=export-pages&id=' . $row['id'] . '&rt=' . REQUEST_TOKEN . '" title="" class="export"><img src="system/themes/flexible/icons/theme_export.svg" width="16" height="16" alt="export"></a>
		';
    }

    /**
	 * Return the child pages - called recursively for n-levels
	 * @param integer
	 * @param integer
	 * @return array
	 */
   	public static function getChildPages($pId, $level = 0){
		$level ++;
		$objAllPages = [];
		$objPages = \PageModel::findByPid($pId);
		if($objPages){
			foreach($objPages as $objPage){
				if($objPage->alias && $objPage->published){
					$objAllPages[] = [
						'title' => $objPage->title,
						'url'	=> \PageModel::findById($objPage->id)->getAbsoluteUrl(),
						'level'	=> $level,
					];
					$objAllPages = array_merge($objAllPages, self::getChildPages($objPage->id, $level));
				}
			}
		}
		return $objAllPages;
	}
}
