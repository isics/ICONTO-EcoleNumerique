<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>
  <meta content="text/html; charset=UTF-8" http-equiv="content-type" />
  <link rel="stylesheet" href="http://local.iconito.fr/themes/default/styles/theme.css" type="text/css"/>
</head>
<body>
  {i18n key="comptes|comptes.strings.dateliste" 1=$smarty.now|datei18n:"date_short_time"}

  <table border="1" cellspacing="2" cellpadding="2" style="-moz-border-radius:6px 6px 6px 6px;background-color:#FFFFFF;font-size:12px;width:50%">
  	<tr>
  		<th class="liste_th">{i18n key="comptes|comptes.colonne.nom"}</th>
  		<th class="liste_th">{i18n key="comptes|comptes.colonne.prenom"}</th>
  		<th class="liste_th">{i18n key="comptes|comptes.colonne.login"}</th>
  		<th class="liste_th">{i18n key="comptes|comptes.colonne.password"}</th>
  		<th class="liste_th">{i18n key="comptes|comptes.colonne.type"}</th>
  		<th class="liste_th">{i18n key="comptes|comptes.colonne.localisation"}</th>
  	</tr>
  	{if $sessionDatas neq null}
  		{counter assign="i" name="i"}
  		{foreach from=$sessionDatas item=sessionData}
  			{counter name="i"}
  			<tr class="list_line{math equation="x%2" x=$i}">
  				<td align="LEFT">{$sessionData.lastname}</td>
  				<td align="LEFT">{$sessionData.firstname}</td>
  				<td align="LEFT">{$sessionData.login}</td>
  				<td align="LEFT">{$sessionData.password}</td>
  				<td align="LEFT">{$sessionData.type_nom}</td>
  				<td align="LEFT">{$sessionData.node_nom}</td>
  			</tr>
  			{foreach from=$sessionData.person item=person}
    			<tr>
    				<td align="LEFT">{$person.lastname}</td>
    				<td align="LEFT">{$person.firstname}</td>
    				<td align="LEFT">{$person.login}</td>
    				<td align="LEFT">{$person.password}</td>
    				<td align="LEFT">{$person.nom_pa}</td>
    				<td align="LEFT">{$person.node_nom}</td>
    			</tr>
    		{/foreach}
  		{/foreach}
  	{/if}
  </table>
</body>
</html>