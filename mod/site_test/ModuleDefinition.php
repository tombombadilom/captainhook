<?php

namespace mod\site_test;

class ModuleDefinition extends \core\ModuleDefinition {

	function __construct() {
		$this->description = 'Simple site';
		$this->name = 'site_test';
		$this->version = 0.1;
		$this->dependencies = array('smarty', 'regroute', 'webpage');
		parent::__construct();
	}

	function install() {
		parent::install();
    \mod\regroute\Main::registerRoute($this->id, '/.*/', 'mod_site_test_http');
	}

	function uninstall() {
    \mod\regroute\Main::unregister($this->id);
		parent::uninstall();
	}
}
