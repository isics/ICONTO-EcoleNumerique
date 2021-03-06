{copixzone process=classeur|affichageMenu classeurId=$ppo->classeur->id dossierId=$ppo->dossierId current="editerFavori"}

<h2>{i18n key="classeur.message.addFavorite"}</h2>

{if $ppo->success}
  <p class="mesgSuccess">{i18n key="classeur.message.success"}</p>
{elseif not $ppo->erreurs eq null}
	<ul class="mesgErrors">
    {foreach from=$ppo->erreurs item=erreur}
	    <li>{$erreur}</li>
    {/foreach}
</ul>
{/if}

<form id="edit_favorite" action="{copixurl dest="classeur||editerFavori"}" method="post" enctype="multipart/form-data">
  <input type="hidden" name="classeurId" id="classeurId" value="{$ppo->classeur->id}" />
  <input type="hidden" name="dossierId" id="dossierId" value="{$ppo->dossierId}" />
  <input type="hidden" name="favoriId" id="favoriId" value="{$ppo->favori->id}" />
  
  
  <div class="row">
    <label for="favori_adresse" class="form_libelle">{i18n key="classeur.message.url"}</label>
    <p class="field"><input class="form" type="url" id="favori_adresse" name="favori_adresse" value="{if $ppo->lien neq null}{$ppo->lien}{else}http://{/if}" placeholder="http://www.google.fr" required="required" autofocus="autofocus" /></p>
  </div>
  
  <div class="row">
    <label for="favori_titre" class="form_libelle">{i18n key="classeur.message.title"}</label>
    <p class="field"><input type="text" name="favori_titre" id="fichier_titre" value="{$ppo->favori->titre}" required="required" /></p>
  </div>
  
  <div class="row">
    <label for="fichier_emplacement" class="form_libelle">{i18n key="classeur.message.repository"}</label>
    <p class="field"><input class="form" type="text" name="fichier_emplacement" id="fichier_emplacement" value="{$ppo->path}" readonly="readonly" /></p>
  </div>
  
  <div class="submit">
    <a href="{copixurl dest=classeur||voirContenu classeurId=$ppo->classeur->id dossierId=$ppo->dossierId}" class="button button-cancel" id="cancel">{i18n key="classeur.message.cancel"}</a>
  	<input class="button button-confirm" type="submit" name="save" id="save" value="{i18n key="classeur.message.save"}" />
  </div>
</form>