<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="{$mode}">
<input type="hidden" name="{$session_name}" value="{$session_id}">
<input type="hidden" name="location_mode" value="add_now">
<table border=0 bgcolor="white">
<tr><td>

    <table border=0 width="100%">
    <tr bgcolor="#eeeeee">
        <td>Location</td>
        <td><input type="text" maxlength=2 name="location" value="{$location|escape:'html'}"></td>
    </tr>
    </table>
</td></tr>
</table>

<input type="submit" value="add">

</form>
