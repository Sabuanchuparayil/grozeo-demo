<?php
	//Define Path of the Module going to Load
	static $allowedModules = null;
	if ($allowedModules === null) {
		$allowedModules = array_map('basename', glob(MODULES_PATH . '/*', GLOB_ONLYDIR));
	}
	if (!in_array($module, $allowedModules, true)) {
		echo "{session_expired:true}";
		exit;
	}
 	define ("THIS_MODULE_PATH",MODULES_PATH."/$module");

	/*
	 * Include the functions.php of the Requested Module if exists
	 * It is good to write the module specific (used in this module only)
	 * functions in this file.
	 */
	if (file_exists(THIS_MODULE_PATH."/functions.php"))
		include(THIS_MODULE_PATH."/functions.php");

	/*
	 * Include the config.php of the Requested Module if exists
	 * It is good to write the module specific (used in this module only)
	 * configuration values in this file.
	 */
	if (file_exists(THIS_MODULE_PATH."/config.php"))
		include(THIS_MODULE_PATH."/config.php");

	/*
	 * Include the Language file of the Requested Module if exists
	 * Based on the language selected by the user.
	 * Each module will have language files for each of the possible language
	 */
	 /*********** LANGUAGE FILE ONLY **************/
	//if (file_exists(THIS_MODULE_PATH."/config.php"))
		//include(THIS_MODULE_PATH."/config.php");

	/*
	 * Include the Module Handler. index.php
	 * This needs to be present in all module and it is the handler
	 * of each module. So it needs to be included.
	 */
	include("modules/$module/index.php");