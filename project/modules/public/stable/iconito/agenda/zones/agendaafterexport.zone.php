<?php
/**
* Zone du module Agenda
* @package  Iconito
* @subpackage Agenda
* @author   Audrey Vassal
* @copyright 2001-2005 CopixTeam
* @link      http://copix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

class ZoneAgendaAfterExport extends CopixZone {

	function _createContent (&$toReturn) {
		
		$tpl = & new CopixTpl ();
		
		/*$tpl->assign('listAgendas', $this->params['listAgendas']);
		
		$agenda = AgendaService::getAgendaAffiches();

		$tpl->assign('agendasSelectionnes', $agenda);
		
		$toReturn = $tpl->fetch ('menu.agenda.tpl');*/
		return true;
	}
}
?>