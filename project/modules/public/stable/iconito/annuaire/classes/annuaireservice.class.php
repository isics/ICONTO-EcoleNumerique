<?php

/**
 * Fonctions diverses du module Annuaire
 * 
 * @package Iconito
 * @subpackage	Annuaire
 */
class AnnuaireService {


	/**
	 * Retourne les villes d'un groupe de villes
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/23
	 * @param integer $grville Id du groupe de villes
	 * @return array Tableau avec les villes
	 */
	function getVillesInGrville ($grville, $options=array()) {
		$villes = array();

		if( isset($options['getNodeInfo_light']) && $options['getNodeInfo_light'] ) {
			$getNodeInfo_full = false;
		} else {
			$getNodeInfo_full = true;
		}

		$childs = Kernel::getNodeChilds ('BU_GRVILLE', $grville);
		foreach ($childs as $child) {
			if ($child['type']=='BU_VILLE') {
				$node = Kernel::getNodeInfo ($child['type'], $child['id'], $getNodeInfo_full);
				//print_r($node);
				$villes[] = array('id'=>$child['id'], 'nom'=>$node['nom']);
			}
		}
		//print_r($villes);
		usort ($villes, array('AnnuaireService', 'usort_nom'));
		return $villes;
	}
	
	
	/**
	 * Retourne les �coles d'une ville
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/17
	 * @param integer $ville Id de la ville
	 * @param array $options (option) Options facultatives, [directeur] (true/false) indiquant 
	 * @return array Tableau avec les �coles
	 */
	function getEcolesInVille ($ville, $params=array('directeur'=>true)) {
		$ecoles = array();

		if( isset($options['getNodeInfo_light']) && $options['getNodeInfo_light'] ) {
			$getNodeInfo_full = false;
		} else {
			$getNodeInfo_full = true;
		}

		$childs = Kernel::getNodeChilds ('BU_VILLE', $ville);
		foreach ($childs as $child) {
			if ($child['type']=='BU_ECOLE') {
				$node = Kernel::getNodeInfo ($child['type'], $child['id'], false);
				//print_r($node);
				$ecoles[] = array('id'=>$child['id'], 'nom'=>$node['nom'], 'type'=>$node['ALL']->eco_type, 'web'=>$node['ALL']->eco_web, 'directeur'=>(($params['directeur']) ? AnnuaireService::getDirecteurInEcole($child['id']) : NULL));
			}
		}
		//print_r($ecoles);
		usort ($ecoles, array('AnnuaireService', 'usort_nom'));
		return $ecoles;
	}


	/**
	 * Retourne les �coles d'un groupe de ville
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/19
	 * @param integer $grville Id du groupe de ville
	 * @return array Tableau avec les �coles
	 */
	function getEcolesInGrville ($grville, $options=array()) {
		$ecoles = array();

		if( isset($options['getNodeInfo_light']) && $options['getNodeInfo_light'] ) {
			$getNodeInfo_full = false;
		} else {
			$getNodeInfo_full = true;
		}

		$childs = Kernel::getNodeChilds ('BU_GRVILLE', $grville);
		//print_r($childs);
		foreach ($childs as $child) {
			if ($child['type']=='BU_VILLE') {
				$node = Kernel::getNodeInfo ($child['type'], $child['id'], false);
				// $ecoles[] = array('id'=>'0', 'nom'=>'');
				$ecoles[] = array('id'=>'0', 'nom'=>$node['nom']);
				// $ecoles[] = array('id'=>'0', 'nom'=>'=======================');
				$tmp = AnnuaireService::getEcolesInVille ($child['id'], 'TYPE');
				$ecoles = array_merge ($ecoles, $tmp);
			}
		}
		//print_r($ecoles);
		return $ecoles;
	}


	/**
	 * Retourne les classes d'une �cole, avec les infos des enseignants affect�s
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/18
	 * @param integer $ecole Id de l'�cole
	 * @param array $options Tableau d'options. Implemente : [forceCanViewEns] force l'affichage des enseignants au lieu de regarder si l'usager a les droits [onlyWithBlog] ne renvoie que les classes ayant un blog [enseignant] si on veut avoir l'enseignant de la classe (true par defaut) [withNiveaux] cherche les niveaux de chaque classe
	 * @return array Tableau avec les classes
	 * @todo Voir pour remplacer le -1 par un ID d'un enseignant
	 */
	function getClassesInEcole ($ecole, $options=array()) {
		$cache_getUserVisibility = Kernel::getUserVisibility('USER_ENS', -1);
		$classes = array();

		if( isset($options['getNodeInfo_light']) && $options['getNodeInfo_light'] ) {
			$getNodeInfo_full = false;
		} else {
			$getNodeInfo_full = true;
		}

		$childs = Kernel::getNodeChilds ('BU_ECOLE', $ecole, $getNodeInfo_full);

//return($childs);
//echo "<pre>"; print_r($childs); die();

//return($childs);
		foreach ($childs as $child) {
			//print_r($child);
			if ($child['type']=='BU_CLASSE') {
				$add = true;
				$node = Kernel::getNodeInfo ($child['type'], $child['id'], false);
				$classe = array(
					'id' => $child['id'],
					'nom' => $node['nom'],
				);

				// On cherche les enseignants
				if (!isset($options['enseignant']) || $options['enseignant']) {
					if (isset($options['forceCanViewEns']))
						$canViewEns = $options['forceCanViewEns'];
					else {
						$canViewEns = $cache_getUserVisibility; 
					}
					//Kernel::deb ("canViewEns=$canViewEns");
					if( !isset($options['no_enseignant']) || $options['no_enseignant']==0 )
						$classe['enseignant'] = (($canViewEns) ? AnnuaireService::getEnseignantInClasse($child['id']) : NULL);
				}
				// On cherche seulement les classes avec blog
				if (isset($options['onlyWithBlog']) && $options['onlyWithBlog']) {
					if ($blog = getNodeBlog ('BU_CLASSE', $child['id'])) {
						//var_dump($blog);
						$classe['url_blog'] = $blog->url_blog;
					} else
						$add = false;
				}
				
				// Ajout eventuel des niveaux
				if (isset($options['withNiveaux']) && $options['withNiveaux']) {
					//var_dump($child);
					$classe['niveaux'] = $child['ALL']->getNiveaux();
				}
				
				
				if ($add)
					$classes[] = $classe;
			}
		}
// $start = microtime(true);	
// echo "&gt;&gt; foreach (childs) ".(microtime(true)-$start)."<br />";
		return $classes;
	}
	
	/**
	 * Retourne les classes d'une ville, avec les infos des enseignants affect�s
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/19
	 * @param integer $ville Id de la ville
	 * @return array Tableau avec les classes
	 */
	function getClassesInVille ($ville, $options=array()) {
		$classes = array();

		if( isset($options['getNodeInfo_light']) && $options['getNodeInfo_light'] ) {
			$getNodeInfo_full = false;
			$getClassesInEcole_params = array('no_enseignant'=>1, 'getNodeInfo_light'=>true);
		} else {
			$getNodeInfo_full = true;
			$getClassesInEcole_params = array();
		}

//$start = microtime(true);
		$ecoles = AnnuaireService::getEcolesInVille ($ville, 'TYPE');
//echo "&gt; getEcolesInVille ".(microtime(true)-$start)."<br />";
//$start = microtime(true);	
		foreach ($ecoles as $ecole) {
			//$classes[] = array('id'=>'0', 'nom'=>'');
			$classes[] = array('id'=>'0', 'nom'=>$ecole['nom']);
			//$classes[] = array('id'=>'0', 'nom'=>'------------------------');
			$tmp = AnnuaireService::getClassesInEcole ($ecole['id'], $getClassesInEcole_params);
			$classes = array_merge ($classes, $tmp);
		}
//echo "&gt; getClassesInEcole ".(microtime(true)-$start)."<br />";
		return $classes;
	}
	
	
	/**
	 * Retourne les classes d'un groupe de villes, avec les infos des enseignants affect�s
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/19
	 * @param integer $grville Id du groupe de villes
	 * @return array Tableau avec les classes
	 */
	function getClassesInGrville ($grville, $options=array()) {
		$classes = array();

		if( isset($options['getNodeInfo_light']) && $options['getNodeInfo_light'] ) {
			$getNodeInfo_full = false;
		} else {
			$getNodeInfo_full = true;
		}

		$childs = Kernel::getNodeChilds ('BU_GRVILLE', $grville);
		foreach ($childs as $child) {
			if ($child['type']=='BU_VILLE') {
				$node = Kernel::getNodeInfo ($child['type'], $child['id'], $getNodeInfo_full);
				//$classes[] = array('id'=>'0', 'nom'=>'');
				$classes[] = array('id'=>'0', 'nom'=>$node['nom']);
				//$classes[] = array('id'=>'0', 'nom'=>'=======================');
				$tmp = AnnuaireService::getClassesInVille ($child['id']);
				$classes = array_merge ($classes, $tmp);
			}
		}
		return $classes;
	}
	
	
	/**
	 * Retourne le(s) directeur(s) d'une �cole
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/18
	 * @param integer $ecole Id de l'�cole
	 * @return array Tableau avec les directeurs
	 */
	function getDirecteurInEcole ($ecole, $options=array()) {	
		$directeur = array();
    /*
    // Ancienne version
		$result = Kernel::getNodeChilds('BU_ECOLE', $ecole );
		foreach ($result AS $key=>$value) {
			//print_r($value);
			if ($value['type']=='USER_ENS') {
				$droit = Kernel::getLevel ('BU_ECOLE', $ecole, $value["type"], $value["id"]);
				if ($droit >= PROFILE_CCV_ADMIN) {
					$nodeInfo = Kernel::getUserInfo ($value["type"], $value["id"]);
					//print_r($nodeInfo);
					$dir = array('type'=>$nodeInfo['type'], 'id'=>$nodeInfo['id'], 'login'=>$nodeInfo['login'], 'nom'=>$nodeInfo['nom'], 'prenom'=>$nodeInfo['prenom']);
					//$enseignants[] = $result[$key];
					$directeur[] = $dir;
				}
			} 
		}
    usort ($directeur, array('AnnuaireService', 'usort_nom'));
    */
    
    // 2e version
   	$dbw = & CopixDbFactory::getDbWidget ();
    $sql = "SELECT PER.numero, PER.nom, PER.prenom1 FROM kernel_bu_personnel PER, kernel_bu_personnel_entite ENT WHERE PER.numero=ENT.id_per AND ENT.reference=".$ecole." AND type_ref='ECOLE' AND role=2 ORDER BY PER.nom, PER.prenom1";
   	$list = $dbw->fetchAll ($sql);
    foreach ($list as $r) {
      $res = array('type'=>'USER_ENS', 'id'=>$r->numero, 'login'=>NULL, 'nom'=>$r->nom, 'prenom'=>$r->prenom1);
      // A-t-il un compte ?
      $sql = "SELECT USR.login_cusr AS login FROM copixuser USR, kernel_link_bu2user LIN WHERE LIN.user_id=USR.id_cusr AND LIN.bu_id=".$r->numero." AND LIN.bu_type='USER_ENS' LIMIT 1";
     	if ($usr = $dbw->fetchFirst ($sql)) {
        $res['login'] = $usr->login;
      }
      $directeur[] = $res;
    }
    //print_r($directeur);
		return $directeur;
	}


	/**
	 * Retourne le personnel administratif d'une �cole
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/12/20
	 * @param integer $ecole Id de l'�cole
	 * @return array Tableau avec le personnel
	 */
	function getAdministratifInEcole ($ecole, $options=array()) {	
		$personnel = array();
		$result = Kernel::getNodeChilds('BU_ECOLE', $ecole );
		foreach ($result AS $key=>$value) {
			//print_r($value);
			if ($value['type']=='USER_ADM') {
					$tmp = array('type'=>$value['type'], 'id'=>$value['id'], 'login'=>$value['login'], 'nom'=>$value['nom'], 'prenom'=>$value['prenom']);
					$personnel[] = $tmp;
			} 
		}
		usort ($personnel, array('AnnuaireService', 'usort_nom'));
		return $personnel;
	}


	/**
	 * Retourne le(s) enseignants(s) d'une classe
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/18
	 * @param integer $classe Id de la classe
	 * @return array Tableau avec les enseignants
	 */
	function getEnseignantInClasse ($classe, $options=array()) {
		$enseignant = array();
		$result = Kernel::getNodeChilds('BU_CLASSE', $classe, false); // true=normal false=optimis�
		foreach ($result AS $key=>$value) {
			//print_r($value);
			if ($value['type']=='USER_ENS') {
				$nodeInfo = Kernel::getUserInfo ($value["type"], $value["id"]);
				//print_r($nodeInfo);
				$ens = array('type'=>$nodeInfo['type'], 'id'=>$nodeInfo['id'], 'login'=>$nodeInfo['login'], 'nom'=>$nodeInfo['nom'], 'prenom'=>$nodeInfo['prenom'], 'sexe'=>$nodeInfo['sexe']);
				//$enseignants[] = $result[$key];
				$enseignant[] = $ens;
			} 
		}
		usort ($enseignant, array('AnnuaireService', 'usort_nom'));
		return $enseignant;
	}


	/**
	 * Retourne les �l�ves d'une classe
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/18
	 * @param integer $classe Id de la classe
	 * @return array Tableau avec les �l�ves
	 * @todo Ajouter les parents
	 */
	function getElevesInClasse ($classe, $options=array()) {	
		$eleves = array();
		$result = Kernel::getNodeChilds('BU_CLASSE', $classe);
		foreach ($result AS $key=>$value) {
			//print_r($value);
			if ($value['type']=='USER_ELE') {
				$nodeInfo = Kernel::getUserInfo ($value["type"], $value["id"]);
				//print_r($nodeInfo);
				
				//$parents = Kernel::getNodeChilds ($value["type"], $value["id"]);
				//print_r($parents);
				
				$ele = array('type'=>$nodeInfo['type'], 'id'=>$nodeInfo['id'], 'login'=>$nodeInfo['login'], 'nom'=>$nodeInfo['nom'], 'prenom'=>$nodeInfo['prenom'], 'sexe'=>$nodeInfo['sexe']);
				//$enseignants[] = $result[$key];
				$eleves[] = $ele;
			} 
		}
		usort ($eleves, array('AnnuaireService', 'usort_nom'));
		return $eleves;
	}


	/**
	 * Retourne les agents de ville d'une ville
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/02/13
	 * @param integer $ville id de la ville
	 * @return array Tableau avec les agents
	 */
	function getAgentsInVille ($ville, $options=array()) {	
		$agents = array();
		$result = Kernel::getNodeChilds('BU_VILLE', $ville);
		foreach ($result AS $key=>$value) {
			//print_r($value);
			if ($value['type']=='USER_VIL') {
				$nodeInfo = Kernel::getUserInfo ($value["type"], $value["id"]);
				//print_r($nodeInfo);
				
				$age = array('type'=>$nodeInfo['type'], 'id'=>$nodeInfo['id'], 'login'=>$nodeInfo['login'], 'nom'=>$nodeInfo['nom'], 'prenom'=>$nodeInfo['prenom'], 'sexe'=>$nodeInfo['sexe']);
				$agents[] = $age;
			} 
		}
		usort ($agents, array('AnnuaireService', 'usort_nom'));
		return $agents;
	}

	/**
	 * Fonction de comparaison permettant de trier avec usort un tableau selon le nom des �l�ments
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/18
	 * @link http://www.php.net/usort
	 * @param mixed $a Premier �l�ment
	 * @param mixed $b Deuxi�me �l�ment
	 * @return integer Valeur de comparaison : inf�rieur, �gal ou sup�rieur � z�ro suivant que le premier �l�ment est consid�r� comme plus petit, �gal ou plus grand que le second �l�ment
	 */
	function usort_nom ($a, $b) {
		//print_r($a);
  	$comp = strcmp($a["nom"], $b["nom"]);
		if ($comp == 0)
			$comp = strcmp($a["prenom"], $b["prenom"]);
		return $comp;
	}
	

	/**
	 * Tous les �l�ves d'une classe, d'une �cole, d'une ville ou d'un groupe de villes
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/19
	 * @param string $type Type du parent o� sont rattach�s les �l�ves (BU_CLASSE, BU_ECOLE, BU_VILLE, BU_GRVILLE)
	 * @param integer $id Id du parent
	 * @return array Tableau contenant tous les �l�ves, tri�s alphab�tiquement
	 */
	function getEleves ($type, $id) {
		$dao = CopixDAOFactory::create("kernel|kernel_bu_ele");
		
		if ($type == 'BU_CLASSE')
			$res = $dao->getElevesInClasse($id);
		elseif ($type == 'BU_ECOLE')
			$res = $dao->getElevesInEcole($id);
		elseif ($type == 'BU_VILLE')
			$res = $dao->getElevesInVille($id);
		elseif ($type == 'BU_GRVILLE')
			$res = $dao->getElevesInGrville($id);
		//print_r($res);
		return $res;
	}

	/**
	 * Tous le personnel �cole d'une classe, d'une �cole, d'une ville ou d'un groupe de villes
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/20
	 * @param string $type Type du parent o� sont rattach�s les personnels (BU_CLASSE, BU_ECOLE, BU_VILLE, BU_GRVILLE)
	 * @param integer $id Id du parent
	 * @return array Tableau contenant tout le personnel �cole, tri� alphab�tiquement
	 */
	function getPersonnel ($type, $id) {
	
		$dao = CopixDAOFactory::create("kernel|kernel_bu_personnel");
		
		if ($type == 'BU_CLASSE')
			$res = $dao->getPersonnelInClasse($id);
		elseif ($type == 'BU_ECOLE')
			$res = $dao->getPersonnelInEcole($id);
		elseif ($type == 'BU_VILLE')
			$res = $dao->getPersonnelInVille($id);
		elseif ($type == 'BU_GRVILLE')
			$res = $dao->getPersonnelInGrville($id);
		//print_r($res);
		return $res;
	}
	
	/**
	 * Tous les parents ayant un enfant dans une classe, une �cole, une ville ou un groupe de villes
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/20
	 * @param string $type Type du parent o� sont rattach�s les parents (BU_CLASSE, BU_ECOLE, BU_VILLE, BU_GRVILLE)
	 * @param integer $id Id du parent
	 * @return array Tableau contenant tout les parents, tri�s alphab�tiquement
	 */
	function getParents ($type, $id) {
		$dao = CopixDAOFactory::create("kernel|kernel_bu_res");
		if ($type == 'BU_CLASSE')
			$res = $dao->getParentsInClasse($id);
		elseif ($type == 'BU_ECOLE')
			$res = $dao->getParentsInEcole($id);
		elseif ($type == 'BU_VILLE')
			$res = $dao->getParentsInVille($id);
		elseif ($type == 'BU_GRVILLE')
			$res = $dao->getParentsInGrville($id);
		//print_r($res);
		return $res;
	}
		

	/**
	 * Tout le personnel ext�rieur d'une classe, d'une �cole, d'une ville ou d'un groupe de villes
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/05/15
	 * @param string $type Type du parent o� sont rattach�s les personnes (BU_CLASSE, BU_ECOLE, BU_VILLE, BU_GRVILLE)
	 * @param integer $id Id du parent
	 * @return array Tableau contenant les personnes, tri�es alphab�tiquement
	 */
	function getPersonnelExt ($type, $id) {
	
		$dao = CopixDAOFactory::create("kernel|kernel_ext_user");
		
		if ($type == 'BU_CLASSE')
			$res = $dao->getPersonnelExtInClasse($id);
		elseif ($type == 'BU_ECOLE')
			$res = $dao->getPersonnelExtInEcole($id);
		elseif ($type == 'BU_VILLE')
			$res = $dao->getPersonnelExtInVille($id);
		elseif ($type == 'BU_GRVILLE')
			$res = $dao->getPersonnelExtInGrville($id);
		//print_r($res);
		return $res;
	}


	/**
	 * Tout le personnel administratif d'une �cole, d'une ville ou d'un groupe de villes
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2007/01/19
	 * @param string $type Type du parent o� sont rattach�s les personnes (BU_ECOLE, BU_VILLE, BU_GRVILLE)
	 * @param integer $id Id du parent
	 * @return array Tableau contenant les personnes, tri�es alphab�tiquement
	 */
	function getPersonnelAdm ($type, $id) {
		$dao = CopixDAOFactory::create("kernel|kernel_bu_personnel");
		if ($type == 'BU_ECOLE')
			$res = $dao->getPersonnelAdmInEcole($id);
		elseif ($type == 'BU_VILLE')
			$res = $dao->getPersonnelAdmInVille($id);
		elseif ($type == 'BU_GRVILLE')
			$res = $dao->getPersonnelAdmInGrville($id);
		//print_r($res);
		return $res;
	}

	/**
	 * Tout les agents de ville d'une ville
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2007/11/06
	 * @param string $type Type du parent o� sont rattach�s les personnes (BU_VILLE uniquement)
	 * @param integer $id Id du parent
	 * @return array Tableau contenant les personnes, tri�es alphab�tiquement
	 */
	function getPersonnelVil ($type, $id) {
		$dao = CopixDAOFactory::create("kernel|kernel_bu_personnel");
		if ($type == 'BU_VILLE')
			$res = $dao->getPersonnelVilInVille($id);
		elseif ($type == 'BU_GRVILLE')
			$res = $dao->getPersonnelVilInGrville($id);
		//print_r($res);
		return $res;
	}

	/**
	 * Retourne les parents d'un �l�ve
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/23
	 * @param integer $eleve Id de l'�l�ve
	 * @return array Tableau avec les parents
	 */
	function getParentsFromEleve ($eleve) {	
		$res = array();
		$parents = Kernel::getNodeChilds ('USER_ELE', $eleve);
		foreach ($parents as $parent) {
			//print_r($parent);
			$userInfo = Kernel::getUserInfo ($parent['type'], $parent['id']);
			//print_r($userInfo);
			$tmp = array_merge ($parent, $userInfo);
			$res[] = $tmp;
		}
		//print_r($res);
		return $res;
	}

	/**
	 * Retourne les enfants d'un parent
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/12/19
	 * @param integer $parent Id du parent
	 * @return array Tableau avec les enfants
	 */
	function getEnfantsFromParent ($parent) {	
		$res = array();
		$enfants = Kernel::getNodeParents ('USER_RES', $parent);
    //print_r($enfants);
		foreach ($enfants as $enfant) {
			if ($enfant['type'] != 'USER_ELE') continue;
			//print_r($parent);
			$userInfo = Kernel::getUserInfo ($enfant['type'], $enfant['id']);
			//print_r($userInfo);
			$tmp = array_merge ($enfant, $userInfo);
			$res[] = $tmp;
		}
		//print_r($res);
		return $res;
	}
  
  /**
	 * Teste si l'usager courant peut effectuer une certaine op�ration dans l'annuaire
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/01/25
	 * @param string $action Action pour laquelle on veut tester le droit
	 * @return bool true s'il a le droit d'effectuer l'action, false sinon
	 * @todo Tester si adulte plut�t que USER_ENS (utiliser fonction du kernel)
	 */
	function canMakeInAnnuaire ($action) {
		$can = false;
		switch ($action) {
			case "POPUP_CHECK_ALL_ELEVES" : // Cocher tous les �l�ves affich�s (version popup)
			case "POPUP_CHECK_ALL_PARENTS" : // Cocher tous les parents (version popup)
			case "POPUP_CHECK_ALL_PERSONNEL" : // Cocher tous le personnel (version popup)
				$can = ($_SESSION["user"]->bu["type"] == "USER_ENS");
				break;
			case "POPUP_CHECK_ALL_PERSONNEL_VIL" : // Cocher tous les agents de ville (version popup)
				$can = ($_SESSION["user"]->bu["type"] == "USER_ENS" || $_SESSION["user"]->bu["type"] == "USER_VIL");
				break;
			case "POPUP_CHECK_ALL_PERSONNEL_ADM" : // Cocher tous le personnel administratif (version popup)
				$can = ($_SESSION["user"]->bu["type"] == "USER_ENS" || $_SESSION["user"]->bu["type"] == "USER_ADM");
				break;
			case "POPUP_CHECK_ALL_PERSONNEL_EXT" : // Cocher tous le personnel administratif (version popup)
				$can = ($_SESSION["user"]->bu["type"] == "USER_ENS" || $_SESSION["user"]->bu["type"] == "USER_EXT");
				break;
		}
		return $can;
	}

	function checkVisibility ($list) {
		reset($list);
		$visibles = array();
		foreach( $list AS $user ) {
			if( Kernel::getUserVisibility( $user['type'], $user['id'] ) )
				$visibles[] = $user;
		}
		return( $visibles );
	}
	
  
  /**
	 * Renvoie l'entr�e de l'annuaire pour l'usager courant. S'appuie sur Kernel::getSessionHome. Pour les parents, prends le home d'un des enfants. S'il n'y a pas d'enfant ou que le compte n'est rattach� � rien, on l'envoie dans la 1e ville.
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2006/12/20
	 * @return array Tableau avec [type] et [id] du noeud (BU_CLASSE, BU_ECOLE, BU_VILLE, BU_GVILLE)
	 */
  function getAnnuaireHome () {
    $home = Kernel::getSessionHome();
    if (!$home && Kernel::isParent()) {  // Cas du parent d'�l�ve
      $enfants = Kernel::getNodeParents( $_SESSION["user"]->bu["type"], $_SESSION["user"]->bu["id"] );
      while (list($k,$v) = each($enfants)) {
        if ($v["type"] != "USER_ELE") continue;
        // Pour chaque enfant...
        //print_r($v);
        if (is_array($v['link']->classe) && ($id=array_shift(array_keys($v['link']->classe))))
          $home = array('type'=>'BU_CLASSE', 'id'=>$id);
        elseif (is_array($v['link']->ecole) && ($id=array_shift(array_keys($v['link']->ecole))))
          $home = array('type'=>'BU_ECOLE', 'id'=>$id);
        elseif (is_array($v['link']->ville) && ($id=array_shift(array_keys($v['link']->ville))))
          $home = array('type'=>'BU_VILLE', 'id'=>$id);
        break;
      }
    }
    if ( !$home || Kernel::isAdmin() ) {  // Si rattach� � rien, on l'envoie dans la 1e ville
    	$dbw = & CopixDbFactory::getDbWidget ();
      $sql = "SELECT MIN(id_vi) AS ville FROM kernel_bu_ville LIMIT 1";
    	$v = $dbw->fetchAll ($sql);
      $home = array('type'=>'BU_VILLE', 'id'=>$v[0]->ville);
    }
    //print_r($home);
    return $home;
  }
	

  /**
	 * Renvoie l'adresse d'une entite au format souhaite par l'API Google Maps. Correspond a un tableau avec [latitude] et [longitude]
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2008/09/04
	 * @param string $node_type Type de l'entite. Implemente : ecole
	 * @param object $node Entite en elle-meme (recordset)
	 * @return string Adresse reformatee
	 */
  function googleMapsFormatAdresse ($node_type, $node) {
		//var_dump($node);
		$adr = '';
		switch ($node_type) {
			case 'ecole' :
				$adr .= ($node->num_rue) ? (($adr)?' ':'').$node->num_rue : '';
				$adr .= ($node->num_seq) ? (($adr)?' ':'').$node->num_seq : '';
				$adr .= ($node->adresse1) ? (($adr)?' ':'').$node->adresse1 : '';
				$adr .= ($node->adresse2) ? (($adr)?' ':'').$node->adresse2 : '';
				$adr .= ($node->code_postal) ? (($adr)?' ':'').$node->code_postal : '';
				$adr .= ($node->commune) ? (($adr)?' ':'').$node->commune : '';
				break;
		}
		//var_dump($adr);
		//die();
    return $adr;
  }



  /**
	 * Renvoie les coordonnes (latitude+longitude) d'une adresse. Renvoie un tableau avec [latitude] et [longitude] ou false si probleme
	 *
	 * @author Christophe Beyer <cbeyer@cap-tic.fr>
	 * @since 2008/09/04
	 * @param string $adresse
	 * @return array Tableau avec [latitude] et [longitude], ou false si probleme
	 */
  function googleMapsAdresseCoords ($adresse) {
		$res = false;
		if ($adresse && CopixConfig::get ('fichesecoles|googleMapsKey')) {
		
			$url = "http://maps.google.com/maps/geo?q=".urlencode($adresse)."&output=csv&key=".CopixConfig::get ('fichesecoles|googleMapsKey');
			//var_dump($url);
			if ($flush = @file_get_contents ($url)) {
				//var_dump($flush);
				list ($statut, $exactitude, $latitude, $longitude) = explode(",", $flush);
				if ($statut == 200) {
					$res = array (
						'latitude' => $latitude,
						'longitude' => $longitude,
					)
					;
				}
			}
		}
		return $res;
  }

}


?>