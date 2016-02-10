    <table width="70%" border=0 cellspacing=5 cellpadding=3 bgcolor="white">
        <tr bgcolor="#cccccc">
            <td>&nbsp;</td>
            <td colspan=2><a href="{$add_location_url}">Add Location</a></td>
        </tr>
        </tr>
        <tr bgcolor="#cccccc">
            <td nowrap>Location</td>
            <td width="1%"nowrap>Prefix Count</td>
            <td width="1%">Delete</td>
        </tr>

        {foreach from=$out_array item=row}
        <tr bgcolor="{cycle values="#ffffff,#dcdcdc"}">
            <td><a href="{$row.edit_url}">{$row.location}</td>
            <td width="1%" nowrap>{$row.prefixes}</td>
            <td align="center" width="1%"><a href="{$row.delete_url}"><img src="images/trash.png" border=0 alt="Trash"></a></td>
            </tr>
        {/foreach}
    </table>
