{strip}
<td>
	{$l.uID}
</td>
<td>
	{$l.uGroup}
</td>
<td>
	<a href="{_link module='account/admin/user'}?id={$l.uID}">{$l.uLogin}</a>
</td>
<td>
	<a href="{_link module='account/admin/user2'}?id={$l.uID}">{$l.aName}</a>
</td>
<td>
	<a href="{_link module='account/admin/user'}?id={$l.uID}">{$l.uMail}</a>
</td>
<td>
	{$usr_statuses[$l.uState]}
</td>
<td>
	{$l.uLevel}
</td>
<td>
	<a href="{_link module='account/admin/user'}?id={$l.uRef}">{$l.RefLogin}</a>
</td>
<td>
  <table>
   <tr>
     <td style="font-size:10px;"><strong>USD</strong></td>
     <td style="font-size:10px;"><strong>EUR</strong></td>
     <td style="font-size:10px;"><strong>RUB</strong></td>
     <td style="font-size:10px;"><strong>BTC</strong></td>
   </tr>
    <tr>
      <td style="font-size:10px;">{_z($l.uBalUSD, 'USD')}</td>
      <td style="font-size:10px;">{_z($l.uBalEUR, 'EUR')}</td>
      <td style="font-size:10px;">{_z($l.uBalRUB, 'RUB')}</td>
      <td style="font-size:10px;">{_z($l.uBalBTC, 'BTC')}</td>
    </tr>
  </table>
</td>
{/strip}