#!/usr/bin/php -q
<?php  //  -*- mode:php; tab-width:2; c-basic-offset:2; -*-

namespace mod\lang;

require(dirname(__FILE__).'/../../../core/Core.php');
\core\Core::init();

$lang='C';
$langs=array();

function addstr($d, $m) {
	global $langs;
	if (!isset($langs[$d])) $langs[$d]=array();
	if (!isset($langs[$d][$m])) $langs[$d][$m]=$m;
}

function scanlangs() {
	global $langs;
	global $lang;

	foreach(scandir(CH_MODDIR) as $modname) {
		if (substr($modname, 0, 1) == '.') continue;
		if (!is_dir(CH_MODDIR.'/'.$modname)) continue;

		$langdir=CH_MODDIR.'/'.$modname.'/lang';
		if (!file_exists($langdir) || !is_dir($langdir)) continue;

		$langfile=$langdir.'/'.$lang.'.js';
		if (!is_file($langfile)) continue;

		$langs[$modname]=array();
		$langs[$modname]=json_decode(file_get_contents($langfile), true);
	}
}

function writelangs() {
	global $langs;
	global $lang;
	
	foreach($langs as $modname => $langd) {
		$langdir=CH_MODDIR.'/'.$modname.'/lang';
		if (!file_exists($langdir) || !is_dir($langdir)) mkdir($langdir);
		file_put_contents($langdir.'/'.$lang.'.js', myjson_encode($langd));
	}
	if (!file_exists(CH_MODDIR.'/lang/cache') || !is_dir(CH_MODDIR.'/lang/cache')) mkdir(CH_MODDIR.'/lang/cache');
	file_put_contents(CH_MODDIR.'/lang/cache/'.$lang.'.js', json_encode($langs));
}

function scantemplates() {
	foreach(scandir(CH_MODDIR) as $modname) {
		if (substr($modname, 0, 1) == '.') continue;
		if (!is_dir(CH_MODDIR.'/'.$modname)) continue;

		$tpldir=CH_MODDIR.'/'.$modname.'/templates';
		if (!file_exists($tpldir) || !is_dir($tpldir)) continue;

		foreach(scandir($tpldir) as $tplname) {
			$tplfile=$tpldir.'/'.$tplname;
			if (substr($tplname, 0, 1) == '.') continue;
			if (!is_file($tplfile)) continue;

			echo "parsing: $tplfile ...\n";
			$tplcontent=file_get_contents($tplfile);
			$matches=array();
			preg_match_all('/{t d=[\'"]([^\'"]*)[\'"] m="([^"]*)".*}/', $tplcontent, $matches);
			foreach($matches[1] as $k=>$domain) addstr($domain, $matches[2][$k]);
			preg_match_all('/{t d=[\'"]([^\'"]*)[\'"] m=\'([^\']*)\'.*}/', $tplcontent, $matches);
			foreach($matches[1] as $k=>$domain) addstr($domain, $matches[2][$k]);
		}
	}
}

function myjson_encode($blup, $offset='') {
	if (is_array($blup)) {
		$res='';
		$offset2=$offset.'  ';
		foreach($blup as $k => $v) {
			$res.=($res == '' ? "{\n" : ",\n");
			$res.=$offset2.json_encode($k).' : '.myjson_encode($v, $offset2);
		}
		$res.="\n".$offset."}";
		return $res;
	} else return json_encode($blup);
}

if ($argc == 2) $lang=$argv[1];
else die($argv[0]." {lang}     # where {lang} can be fr_FR for exemple\n");

scanlangs();
scantemplates();
//print_r($langs);
writelangs();
