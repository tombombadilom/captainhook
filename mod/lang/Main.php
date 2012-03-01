<?php  //  -*- mode:php; tab-width:2; c-basic-offset:2; -*-

namespace mod\lang;

class Main {

  private static function init() {
		global $ch_lang;
		global $ch_langs;
    global $ch_inited;

    if (isset($ch_inited) && $ch_inited==1) continue;
    $ch_inited==1;

		$ch_lang='fr_FR';
		$ch_langs=array();

		$ch_langs[$ch_lang]=json_decode(file_get_contents(CH_MODDIR.'/lang/cache/'.$GLOBALS['ch_lang'].'.json'), true);
  }

  public static function hook_core_init_http($hookname, $userdata) {
    self::init();
  }

  public static function hook_mod_webpage_construct($hookname, $userdata, $webpage) {
		global $ch_lang;
		global $ch_langs;

    self::init();

		\mod\cssjs\Main::addJs($webpage, '/mod/cssjs/js/mootools.js');
		\mod\cssjs\Main::addJs($webpage, '/mod/cssjs/js/sprintf-0.7-beta1.js');
		\mod\cssjs\Main::addJs($webpage, '/mod/lang/js/lang.js');
    $langfile=CH_MODDIR.'/lang/cache/'.$GLOBALS['ch_lang'].'.js';
    error_log("LANG FILE : ".$langfile);
    if (is_file($langfile)) \mod\cssjs\Main::addJs($webpage, '/mod/lang/cache/'.$GLOBALS['ch_lang'].'.js');
	}

	/*
	public static function hook_smarty_new($hookname, $userdata, $sm) {
		global $ch_lang;
		// we add $lang to smarty, so it compile different cache version with every langs
		$sm->compile_id.='_'.$ch_lang;
	}
	*/

	public static function smartyFunction_t($params, $template) {
    self::init();
		foreach($params as $k => $v)
			if (!preg_match('/^p[0-9]+$/', $k))
				$paf[$k]=$v;

		for ($i=0; isset($params['p'.$i]); $i++) $paf['p'][]=$params['p'.$i];
		return self::t($paf);
	}

	private static function t($paf) {
		global $ch_langs;
		global $ch_lang;

    self::init();

		$m=$paf['m'];

    if (isset($ch_langs[$ch_lang]) && isset($ch_langs[$ch_lang][$paf['d']]) && isset($ch_langs[$ch_lang][$paf['d']][$paf['m']]))
			$m=$ch_langs[$ch_lang][$paf['d']][$paf['m']];

		$tag=isset($paf['tag']) ? $paf['tag'] : '';
		$p=isset($paf['p']) ? $paf['p'] : array();
		unset($paf['tag']);
		unset($paf['p']);

		if ($tag=='')
			return vsprintf($m, $p);
		else
			return "<$tag>".vsprintf($m, $p)."</$tag>";
    //return "<$tag class='ch_lang_trad' paf=\"".urlencode(json_encode($paf)).'">'.vsprintf($m, $p)."</$tag>";
	}

  public static function ch_t($d, $m) {
    self::init();
    $paf=array('d' => $d, 'm' => $m);
    for ($i=2; $i<func_num_args(); $i++)
      $paf['$'.($i-2)]=func_get_arg($i);
    return ch_t($paf);
  }

  public static function getCurrentLang() {
    self::init();
    return $GLOBALS['ch_lang'];
  }

  public static function setCurrentLang($lang) {
    self::init();
    $GLOBALS['ch_lang']=$lang;
  }

  public static function getActiveLangs() {
    self::init();
    return array("fr_FR", "de_DE");
  }
}
