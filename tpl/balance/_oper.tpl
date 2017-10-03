{strip}

<div class="block_form">

	<form method="post" name="{$edit_form_name}">
		<input name="oID" id="{$edit_form_name}_oID" value="{$el.oID}" type="hidden">

        <div class="block_form_el text_type cfix">
            <label>{$_TRANS['Status']}</label>
            <div class="block_form_el_right"><i>
				{$op_statuses[$el.oState]}
            </i></div>
        </div>
        <div class="block_form_el text_type cfix">
            <label>{$_TRANS['Date']}</label>
            <div class="block_form_el_right"><i>{$el.oCTS}</i></div>
        </div>
        {if $from_admin}
	        <div class="block_form_el text_type cfix">
	            <label>{$_TRANS['User']}</label>
	            <div class="block_form_el_right"><i><a href="{_link module='account/admin/user'}?id={$el.uID}">{$el.uLogin}</a></i></div>
	        </div>
        {/if}
        {if $el.oParams.uid2}
	        <div class="block_form_el text_type cfix">
	            <label>{valueIf($el.oOper == 'TR', $_TRANS['Receiver'], $_TRANS['Sender'])}</label>
	            <div class="block_form_el_right"><i>{valueIf($from_admin, "<a href=\"{_link module='account/admin/user'}?id={$el.oParams.uid2}\">{$el.oParams.user}</a>", $el.oParams.user)}</i></div>
	        </div>
        {/if}
        <div class="block_form_el text_type cfix">
            <label>{$_TRANS['Curr']}</label>
            <div class="block_form_el_right"><i>{$el.ocCurrID}</i></div>
        </div>
        <div class="block_form_el text_type cfix">
            <label>{$_TRANS['Amount']}</label>
            <div class="block_form_el_right"><i>{_z($el.oSum, $el.ocCurrID, 1)}</i></div>
        </div>
        {if ($el.oComis != 0)}
	        <div class="block_form_el text_type cfix">
	            <label>{$_TRANS['Comission']}</label>
	            <div class="block_form_el_right"><i>{_z($el.oComis, $el.ocCurrID, 1)}</i></div>
	        </div>
        {/if}
        {if ($el.oComis != 0)}
	        <div class="block_form_el text_type cfix">
	            <label>{$op_sums[$el.oOper]}</label>
	            <div class="block_form_el_right"><i>{_z($el.oSum - $el.oComis, $el.ocCurrID, 1)}</i></div>
	        </div>
        {/if}
        {*if ($el.oParams.cid2)}
	        <div class="block_form_el text_type cfix">
	            <label>{valueIf($el.oOper == 'EX', $_TRANS['To payment system'], $_TRANS['From payment system'])}</label>
	            <div class="block_form_el_right"><i>{$el.oParams.psys}</i></div>
	        </div>
        {/if*}
		{*if $el.ocID > 0*}
		{if in_array($el.oOper, ['BONUS', 'PENALTY', 'CASHIN', 'CASHOUT', 'TR', 'TRIN'])}
        <div class="block_form_el text_type cfix">
            <label>{$_TRANS['Payment system']}</label>
            <div class="block_form_el_right"><i>{valueIf($from_admin, "<a href=\"{_link module='balance/admin/curr'}?id={$el.ocID}\">{$el.cName}</a>", $el.cName)}</i></div>
        </div>
		{/if}
        {if ($el.oSum2 > 0)}
	        <div class="block_form_el text_type cfix">
	            <label>{$_TRANS['Amount to receive']}</label>
	            <div class="block_form_el_right"><i>{_z($el.oSum2, exValue($el.ocID, $el.ocCurrID2), 1)}</i></div>
	        </div>
        {/if}
        {if $smarty.get.payto}
	        <div class="block_form_el text_type cfix">
	            <label>{$_TRANS['Pay to account']}</label>
	            <div class="block_form_el_right"><i>{$smarty.get.payto}</i></div>
	        </div>
	    {/if}
        {if $el.oParams2.acc}
	        <div class="block_form_el text_type cfix">
	            <label>{valueIf($el.oOper == 'CASHOUT', $_TRANS['Payee account'], $_TRANS['Payeer account'])}</label>
	            <div class="block_form_el_right"><i>{$el.oParams2.acc}</i></div>
	        </div>
        {/if}
        {if ($el.oBatch)}
	        <div class="block_form_el text_type cfix">
	            <label>{$_TRANS['Batch-number']}</label>
	            <div class="block_form_el_right"><i>{$el.oBatch}</i></div>
	        </div>
        {/if}
        {if ($el.oMemo)}
	        <div class="block_form_el text_type cfix">
	            <label>{$_TRANS['Memo']}</label>
	            <div class="block_form_el_right"><i>{valueIf(!$from_admin and ($el.oMemo[0] == '~'), {$_TRANS['Error']}, $el.oMemo)}</i></div>
	        </div>
        {/if}
        {if ($el.oTS)}
	        <div class="block_form_el text_type cfix">
	            <label>{$_TRANS['Modified']}</label>
	            <div class="block_form_el_right"><i>{$el.oTS}</i></div>
	        </div>
        {/if}
        {if ($el.oNTS)}
	        <div class="block_form_el text_type cfix">
	            <label>{$_TRANS['Completed']}</label>
	            <div class="block_form_el_right"><i>{$el.oNTS}</i></div>
	        </div>
        {/if}

        {_getFormSecurity form=$edit_form_name}

        {if $bt !== ' '}
			<input name="{$edit_form_name}_btn" value="{if $bt}{$bt}{elseif !$edit_is_new}Сохранить{else}Создать{/if}" type="submit" class="button-blue">
		{/if}

        {if is_array($b)}
			{foreach from=$b key=f item=v}
				&nbsp;<input name="{$edit_form_name}_btn{$f}" value="{$v}" onClick="return confirm('Подтвердите операцию \'{$v}\'');" type="submit" class="button-red">
			{/foreach}
		{/if}
	</form>
</div>

{if $afields}

	<br>
	<p class="info">
		{$_TRANS['For current payment system not set']} <a href="{_link module='balance/admin/curr'}?id={$el.ocID}">{$_TRANS['API settings']}</a><br>
		{$_TRANS['You can perform this operation by specifying the settings above']}. <br>
		{$_TRANS['It is safe, because entered data are not stored anywhere']}
	</p>

{/if}

{if $el.oOper == 'CASHIN'}

	{if !isset($smarty.get.payto)}
		{include file='balance/_pform.tpl' btn_text=$_TRANS['Pay through the merchant']}
	{/if}

	{if $pvalues.acc}

		{if $el.oState <= 1}

			<br>
			<p class="info">
				{if $pform}{$_TRANS['If you can not pay directly through the merchant']},<br>{/if}
				{$_TRANS['You can make a payment on the specified details manually,<br>and then specify the details of this payment in the form below']|html_entity_decode}
			</p>

		{/if}

		<h2>{$_TRANS['Our payment details']}<br>({$_TRANS['to pay manually through']} {$el.cName})</h2>

		<table class="styleTable" border="0" cellspacing="0" cellpadding="0" width="100%">
		{foreach from=$pfields key=f item=v}
		{if $pvalues[$f]}
			<tr>
				<td align="right">
					{$v[0] = str_replace('*', ' <span class="descr_rem">($_TRANS["optional"])</span>', $v[0])}
					{if $f === 'acc'}
						{$_TRANS['Payee account']} <span class="descr_rem">({$v[0]})</span>
					{else}
						{$v[0]}
					{/if}
				</td>
				<td>
					<span class="uline">{$pvalues[$f]}</span>
				</td>
			</tr>
		{/if}
		{/foreach}
			<tr>
				<td align="right">
					{$_TRANS['Amount']}
				</td>
				<td>
					<span class="uline">{_z($el.oSum2, $el.ocID, 1)}</span>
				</td>
			</tr>
		</table>

	{/if}

	{if $pvalues.acc or ($from_admin and ($el.oState <= 2))}

		{include file='balance/_oper.data.tpl' oComis=0}

	{/if}

{elseif ($el.oOper == 'CASHOUT') and ($from_admin and ($el.oState <= 2))}

	{include file='balance/_oper.data.tpl' oComis=$el.oComis}

{/if}

{/strip}