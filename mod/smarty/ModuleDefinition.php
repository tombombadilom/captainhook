<?php

namespace mod\smarty;

class ModuleDefinition extends \core\ModuleDefinition {

	function __construct() {
		$this->description = 'Smarty';
		$this->name = 'smarty';
		$this->version = '0.1';
		$this->dependencies = array();
		parent::__construct();
	}

	function install() {
    \core\Core::$db->execute("CREATE TABLE `ch_smarty_plugins` ("
                             ." `id_module` INT(11) NULL,"
                             ." `name` VARCHAR(255) NOT NULL,"
                             ." `type` ENUM('function','block','compiler','modifier') NOT NULL,"
                             ." `method` VARCHAR(255) NOT NULL,"
                             ." KEY `kidmodule` (`id_module`),"
                             ." KEY `kname` (`name`)"
                             .") ENGINE=InnoDB DEFAULT CHARSET=utf8");

		parent::install();
	}

	function uninstall() {
		parent::uninstall();
    \core\Core::$db->execute("DROP TABLE `ch_smarty_plugins`");
	}
}
