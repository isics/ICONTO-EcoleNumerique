<?php
/**
* @package	copix
* @version	$Id: blogfunctions.dao.class.php,v 1.6 2006-10-09 16:21:31 cbeyer Exp $
* @author	Sylvain DACLIN see copix.aston.fr for other contributors.
* @copyright 2001-2005 CopixTeam
* @link		http://copix.org
* @licence  http://www.gnu.org/licenses/lgpl.htmlGNU Leser General Public Licence, see LICENCE file
*/
class DAOBlogfunctions {
  
   /**
    * @param 
    * createBlogFunctions
    * @return
    */
   function createBlogFunctions ($id_blog, $tabBlogFunctions) { 
			 $blogFunctions = CopixDAOFactory::createRecord('blog|blogfunctions');
       foreach($tabBlogFunctions as $fct) {
       		eval('$blogFunctions->'.$fct->value.'='.$fct->selected.';');
       }
       $blogFunctions->id_blog = $id_blog;
			 $this->_compiled->insert($blogFunctions);     
   }
  
  
   /**
    * @param 
    * updateBlogFunctions
    * @return
    */
   function updateBlogFunctions ($id_blog, $tabBlogFunctions) {
			 $blogFunctions = CopixDAOFactory::createRecord('blogfunctions');
       foreach($tabBlogFunctions as $fct) {
       		eval('$blogFunctions->'.$fct->value.'='.$fct->selected.';');
       }
       $blogFunctions->id_blog = $id_blog;
			 $this->_compiled->update($blogFunctions);     
   }
  
}


?>