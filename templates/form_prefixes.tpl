<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="{$mode}">
<input type="hidden" name="{$session_name}" value="{$session_id}">
{if $prefix_id}
<input type="hidden" name="prefix_id" value="{$prefix_id}">
{/if}
<input type="hidden" name="location_id" value="{$location_id}">
<input type="hidden" name="prefix_mode" value="{$prefix_mode}">
<table border=0 bgcolor="white">
<tr><td>

    <table border=0 width="100%">
    <tr bgcolor="#cccccc">
        <td colspan=2><b>{$action|capitalize}ing prefix for {$location}</b></td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Prefix</td>
        <td><input type="text" name="prefix" value="{$prefix|escape:'html'}"></td>
    </tr>
    </table>
</td></tr>
</table>

<input type="submit" value="{$action}">

</form>
