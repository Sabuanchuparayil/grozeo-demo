<?php
/*
 * Created on 01-Aug-08
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 *
 * Complete Role Management Operation of the Application will be
 * handled by this module.
 *
 */
writeLog(__FILE__);
 switch($op){
  	case 'save':
 			require(THIS_MODULE_PATH."/save.php");
 		break;
 	case 'role_combo':
 			generateJsComboStore();
 		break;
 	case 'delete':
 	        require(THIS_MODULE_PATH."/delete.php");
 	    break;
    default:
            require(THIS_MODULE_PATH."/list.php");
        break;
 }