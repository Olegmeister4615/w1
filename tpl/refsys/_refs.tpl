{strip}
{$states=[
	0=>$_TRANS['Non-active'],
	1=>$_TRANS['Active'],
	2=>$_TRANS['Blocked'],
	3=>$_TRANS['Disabled']
]}

{*$_TRANS['Invited']*}
{*<table class="FormatTable" border="1">
	<tr>
		<th>User</th>
		<th>State</th>
		<th>Pay.system</th>
		<th>Amount</th>
	</tr>
{foreach from=$refs key=i item=r}
	<tr>
		<td>{$r.uLogin}</td>
		<td>{$states[$r.uState]}</td>
		<td>{$r.cName}</td>
		<td align="right">{if $r.cName}{_z($r.rSum, $r.cID)}{/if}</td>
	</tr>
{/foreach}
</table>*}
<table cellspacing="0" cellpadding="0" border="0" class="styleTable">
	<tr>
		<th>{$_TRANS['User']}</th>
		<th>{$_TRANS['Reg.date']}</th>
		<th>{$_TRANS['Deposits']}</th>
		<th>{$_TRANS['Amount']}</th>
	</tr>
{foreach from=$refs key=i item=r}
	{if count($refs) > 1}
		<tr>
			<td colspan="4" align="center">{$_TRANS['Level']} {$i + 1}{if $r.perc}: {$r.perc}%{/if}</td>
		</tr>
	{/if}
	{foreach from=$r.users key=j item=u}
		<tr>
			<td>{include file='_usericon.tpl' user=$u} {$u.uLogin}</td>
			<td>{$u.aCTS}</td>
			<td align="right">{_z($u.ZDepo, 1)}</td>
			<td align="right">{_z($u.ZRef, 1)}</td>
		</tr>
	{/foreach}
{/foreach}
</table>

{/strip}