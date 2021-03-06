<?php  //  -*- mode:php; tab-width:2; c-basic-offset:2; -*-
/**
 * CaptainHook
 *
 * PHP Version 5
 *
 * @category  CaptainHook
 * @package   Core 
 * @author    Christophe Beveraggi (beve) and Nicolas Dimitrijevic (niclone)
 * @copyright 2011-2012 CROLL (http://www.croll.fr)
 * @link      http://github.com/croll/captainhook
 * @license   LGPLv3
 *
 * CaptainHook is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * CaptainHook is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with CaptainHook.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace core;

/**
 * This class provides general functions for various use.
 *
 * @category  CaptainHook
 * @package   Core
 * @author    Christophe Beveraggi (beve) and Nicolas Dimitrijevic (niclone)
 * @license   LGPLv3
 * @link      http://github.com/croll/captainhook
 *
 */

class Tools {

	/** 
	 * Get the PHP memory usage and return it in human a readdable form.
	 *
	 * @return string Memory usage
	 *
	 */
	public static function getMemoryUsage() {
		$size = memory_get_usage(true);
		$unit=array('B','kB','MB','GB','TB','PB');
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}


	/** 
	 * Clean a string.
	 *
	 * @param string String to be cleaned
	 * @return string Cleaned string
	 *
	 * It a simple function intended to be used to clean GET/POST vars and things like that.
	 *
	 */
	public static function cleanString($str) {
		$str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
		return htmlentities($str, ENT_QUOTES, 'UTF-8');
	}

}
