<?php
/**
 * @package     
 * @subpackage
 * @author      
 */

/**
 *
 */
class ZoneFilterClass extends CopixZone {

	function _createContent (& $toReturn) {
	  
	  $ppo = new CopixPPO ();                               
    
    // Récupérations des filtres en session
	  $ppo->selected = $this->getParam ('selected', null);
	  
	  if (!is_null ($schoolId = $this->getParam('school_id', null))) {

	    // Récupération des écoles de la ville sélectionnée pour liste déroulante
	    $classDAO = _ioDAO ('kernel|kernel_bu_ecole_classe');
	    $classes = $classDAO->getBySchool ($schoolId);
    
      $ppo->classesIds   = array('');
      $ppo->classesNames = array('');
    
  	  foreach ($classes as $class) {
	    
  	    $ppo->classesIds[]   = $class->id;
  	    $ppo->classesNames[] = $class->nom;
  	  }
    }
    
    $toReturn = $this->_usePPO ($ppo, '_filter_class.tpl');
  }
}