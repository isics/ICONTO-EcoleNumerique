<?php
/**
 * @package     
 * @subpackage
 * @author      
 */

/**
 *
 */
class ZoneAccountsInfo extends CopixZone {

	function _createContent (& $toReturn) {
	  
	  $ppo = new CopixPPO ();                               

    $toReturn = $this->_usePPO ($ppo, '_accounts_info.tpl');
  }
}