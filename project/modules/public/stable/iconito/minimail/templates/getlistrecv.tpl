
{if $list|@count}
<FORM NAME="form" ID="form" ACTION="{copixurl dest="|doDelete"}" METHOD="POST">
    <INPUT TYPE="hidden" NAME="mode" VALUE="recv" />
    <table border="0" CLASS="liste" ALIGN="CENTER" CELLSPACING=2 CELLPADDING=2>
        <tr>
            <th CLASS="liste_th">{i18n key="minimail.list.read"}</th>
            <th CLASS="liste_th">{i18n key="minimail.list.title"}</th>
            <th CLASS="liste_th">{i18n key="minimail.list.from"}</th>
            <th CLASS="liste_th">{i18n key="minimail.list.attach"}</th>
            <th CLASS="liste_th">{i18n key="minimail.list.date"}</th>
            <th CLASS="liste_th">{i18n key="minimail.list.delete"}</th>
        </tr>
        </tr>
		{counter assign="i" name="i"}
		{foreach from=$list item=mp}
			{counter name="i"}
        <tr CLASS="list_line{math equation="x%2" x=$i}">
            <td ALIGN="CENTER"><img width="20" height="20" src="{copixresource path="img/minimail/status`$mp->is_read*1``$mp->is_replied*1``$mp->is_forwarded*1`.png"}" /></td>
            <td>
                <a href="{copixurl dest="|getMessage" id=$mp->id}">{$mp->title}</a></td>
            <td>

				{user label=$mp->from_id_infos userType=$mp->from.type userId=$mp->from.id linkAttribs='STYLE="text-decoration:none;";'}

            </td>
            <td ALIGN="CENTER">{if $mp->attachment1 }<IMG src="{copixresource path="img/minimail/attachment.gif"}" ALT="{i18n key="minimail.msg.attachments"}" TITLE="{i18n key="minimail.msg.attachments"}" />{/if}</td>
            <td ALIGN="CENTER">{$mp->date_send|datei18n:"date_short_time"}</td>
            <td ALIGN="CENTER"><input type="checkbox" name="messages[]" value="{$mp->id2}" class="noBorder" /></td>
        </tr>
		{/foreach}
        <tr class="liste_footer">
            <td colspan="5"></TD>
            <td class="center"><a class="button button-delete" href="javascript:deleteMsgs();">{i18n key="minimail.btn.delete"}</a></td>
        </tr>
    </table>

    {$pager}

</FORM>

{else}
<p>{i18n key="minimail.list.empty"}</p>
{/if}