


{if not $errors eq null}
	<DIV CLASS="message_erreur">
	<UL>
	{foreach from=$errors item=error}
		<LI>{$error}</LI><br/>
	{/foreach}
	</UL></DIV>
{elseif not $ok eq null}
	<DIV CLASS="message_ok">
	<UL>
	{foreach from=$ok item=item}
		<LI>{$item}</LI><br/>
	{/foreach}
	</UL></DIV>
{/if}

<div class="actions noPrint">

<form action="{copixurl dest="|changeResponsables"}" method="post">
<input type="hidden" name="id" value="{$rFiche->idinter}"/>

	<table class="" border="0">
		<tr>
			<td width="50%"><div class="linkannuaire">{$linkpopup_responsables}</div>{i18n key=teleprocedures.type.field.responsables}
		<br/><textarea class="form" style="width:350px; height: 80px;" name="responsables" id="responsables">{$rFiche->responsables|escape}</textarea>
			</td>
			<td width="50%"><div class="linkannuaire">{$linkpopup_lecteurs}</div>{i18n key=teleprocedures.type.field.lecteurs}
		<br/><textarea class="form" style="width:350px; height: 80px;" name="lecteurs" id="lecteurs">{$rFiche->lecteurs|escape}</textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2">Saisissez les logins des comptes utilisateurs, en les s&eacute;parant par des virgules, ou cliquez sur les liens ci-dessus pour recherches les personnes via l'annuaire.</td>
		</tr>
	</table>
<div align="center"><input class="teleprocedures" type="submit" value="Valider les modifications" /></div>
</form>
</form>
</div>
