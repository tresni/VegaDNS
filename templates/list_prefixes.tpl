<table width="70%" border=0 cellspacing=5 cellpadding=3 bgcolor="white">
    <tr bgcolor="#cccccc">
        <td><b>Prefixes for {$location}</b></td>
        <td><a href="{$add_prefix_url}">Add Prefix</a></td>
    </tr>
    </tr>
    <tr bgcolor="#cccccc">
        <td nowrap>Prefix</td>
        <td width="1%">Delete</td>
    </tr>

    {foreach from=$out_array item=row}
    <tr bgcolor="{cycle values="#ffffff,#dcdcdc"}">
        <td><a href="{$row.edit_url}">{$row.prefix}</td>
        <td align="center" width="1%"><a href="{$row.delete_url}"><img src="images/trash.png" border=0 alt="Trash"></a></td>
        </tr>
    {/foreach}
</table>
