<?php
/**
* @package  Iconito
* @subpackage Prefs
* @version   $Id: prefs.zone.php,v 1.3 2007-12-20 09:46:27 fmossmann Exp $
* @author   Fr�d�ric Mossmann
* @copyright 2005 CAP-TIC
* @link      http://www.cap-tic.fr
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

require_once (COPIX_MODULE_PATH.'prefs/'.COPIX_CLASSES_DIR.'prefs.class.php');

class ZonePrefs extends CopixZone {
	function _createContent (&$toReturn) {
		$tpl = & new CopixTpl ();
		
		$tpl->assign ("prefs", $this->params['prefs']);
		
		$get = $this->params['get'];
		if( isset($get['msg']) && $get['msg']=='save' ) {
			$tpl->assign ('msg', array(
				'type'  => 'ok',
				'image_url' => 'img/iconito/prefs/smiley_black.png',
				'image_alt' => CopixI18N::get ('prefs.msg.prefsrecorded_alt'),
				'value' => CopixI18N::get ('prefs.msg.prefsrecorded')
			) );
		}
		
		$toReturn = $tpl->fetch("getprefs.tpl");
		return true;
	}
}
?>