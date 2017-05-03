<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
include libfile('function/admin');
include libfile('function/organization');
$do = empty($_GET['do']) ? 'available' : trim($_GET['do']);
$refer = urlencode(ADMINSCRIPT . '?mod=app&op=list&do=' . $do);
$grouptitle = array('0' => lang('all'), '-1' => lang('visitors_visible'), '1' => lang('members_available'), '2' => lang('section_administrators_available'), '3' => lang('system_administrators_available'));
$list = array();
if ($do == 'available') {
	$list = array();
	foreach (DB::fetch_all("SELECT * FROM ".DB::table('app_market')." WHERE  available<1") as $value) {
		$value['grouptitle'] = $grouptitle[$value['group']];
		$value['newversion'] = $newversion;
		if ($value['appico'] && $value['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $value['appico'])) {
			$value['appico'] = $_G['setting']['attachurl'] . $value['appico'];
		}
		if (empty($value['appico']))
			$value['appico'] = 'dzz/images/default/icodefault.png';
		$list[] = $value;
	}

} elseif ($do == 'notinstall') {
	$identifiers = C::t('app_market') -> fetch_all_identifier();
	$appdir = DZZ_ROOT . './dzz';
	$appsdir = dir($appdir);
	$newapps = array();
	$list = array();
	while ($entry = $appsdir -> read()) {
		if (!in_array($entry, array('.', '..')) && is_dir($appdir . '/' . $entry) && !in_array($entry, $identifiers)) {
			$entrydir = DZZ_ROOT . './dzz/' . $entry;

			$filemtime = filemtime($entrydir);
			$importtxt = '';
			if (file_exists($entrydir . '/dzz_app_' . $entry . '.xml')) {
				//echo $entrydir.'/dzz_app_'.$entry.'.xml'.'<br>';
				$importtxt = implode('', file($entrydir . '/dzz_app_' . $entry . '.xml'));
			}else{
				$plugindir1 = $entrydir;
				$pluginsdir1 = dir($plugindir1);
				while ($entry1 = $pluginsdir1 -> read()) {
					if (!in_array($entry1, array('.', '..')) && is_dir($plugindir1 . '/' . $entry1) && !in_array($entry.':'.$entry1, $identifiers)) {
						$entrydir1 = $entrydir.'/'. $entry1;
						//$filemtime = filemtime($entrydir1);
						$entrytitle1 = $entry1;
						$entryversion1 = $entrycopyright1 = $importtxt = '';
						if (file_exists($entrydir1 . '/dzz_app_' . $entry.'_'.$entry1 . '.xml')) {
							
							$importtxt = @implode('', file($entrydir1 . '/dzz_app_' . $entry.'_'.$entry1 . '.xml'));
						}
						if ($importtxt) {
							$apparray = getimportdata('Dzz! app', 0, 1, $importtxt);
							$value = $apparray['app'];
							if (!empty($value['appname'])) {
								$value['appname'] = dhtmlspecialchars($value['appname']);
								$value['identifier'] = dhtmlspecialchars($entry.':'.$entry1);
								$value['version'] = dhtmlspecialchars($value['version']);
								$value['vendor'] = dhtmlspecialchars($value['vendor']);
								$value['grouptitle'] = $grouptitle[$value['group']];
								$list[$entry.':'.$entry1] = $value;
							}
							
						}
					}
					
				}
							
			}
			if ($importtxt) {
				$apparray = getimportdata('Dzz! app', 0, 1, $importtxt);
				$value = $apparray['app'];
				if (!empty($value['appname'])) {
					$value['appname'] = dhtmlspecialchars($value['appname']);
					$value['identifier'] = dhtmlspecialchars($entry);
					$value['version'] = dhtmlspecialchars($value['version']);
					$value['vendor'] = dhtmlspecialchars($value['vendor']);
					$value['grouptitle'] = $grouptitle[$value['group']];
					$list[$entry] = $value;
				}
			}
		}
	}
} elseif ($do == 'updatelist') {
	$list = array();
	$appdir = DZZ_ROOT . './dzz';
	foreach (DB::fetch_all("select * from %t where identifier!=''",array('app_market')) as $value) {
		$entrydir = DZZ_ROOT . './dzz/' . $value['identifier'];
		$filemtime = filemtime($entrydir);
		$importtxt = '';
		if (file_exists($entrydir . '/dzz_app_' . $value['identifier'] . '.xml')) {
			//echo $entrydir.'/dzz_app_'.$entry.'.xml'.'<br>';
			$importtxt = implode('', file($entrydir . '/dzz_app_' . $value['identifier'] . '.xml'));
		}
		if ($importtxt) {
			$apparray = getimportdata('Dzz! app', 0, 1, $importtxt);
			$newversion = dhtmlspecialchars($apparray['app']['version']);
			if ($value['version'] < $newversion) {
				$value['grouptitle'] = $grouptitle[$value['group']];
				$value['newversion'] = $newversion;
				if ($value['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $value['appico'])) {
					$value['appico'] = $_G['setting']['attachurl'] . $value['appico'];
				}
				$list[$value['appid']] = $value;
			}
		}
	}
}
include template('list');
?>
