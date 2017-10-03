{strip}
{include file='header.tpl' title=$_TRANS['Referral system'] class="cabinet"}

<h1>{$_TRANS['Referral system']}</h1>


	{if $reflogin}


{*----inviter: pouop---*}
	<style>	{*----style for popup window---*}
		.overlay { display:none; position:fixed; z-index:999; opacity:0.5; filter:alpha(opacity=50); top:0; right:0; left:0; bottom:0; background:#000; }
		.popup  { display:none; position:fixed; border:3px solid #999; background:#fff; width:394px; height:194px; top:50%; left:50%; margin:-100px 0 0 -100px; z-index:1000;  border-radius:10px; padding:30px;}
		.close { display:block; width:12px; text-align:center; cursor:pointer;  height:12px; line-height:12px; background:#fff; color:red; border:3px solid red;  position:absolute; top:10px; right:10px; text-decoration:none; border-radius:3px; font-size:10px; }
	</style>

	<div class="overlay"></div>
	<div class="popup" id="popup1">
		<span class="close">X</span>
	{include file='edit.tpl'
		fields=[
			'InvLogin'=>
				[
					'X',
					{$_TRANS['You invited by']},
					0,
					{$reflogin},
					'skip'=>!$reflogin
				],
			'InvAvatar'=>
			[
				'X',
				"{include file='_usericon.tpl' user=$upref}"
			],
			'InvName'=>
				[
					'X',
					'Name:',
					0,
					{$upref.aName},
					'skip'=>!$upref
				],
			'InvE-mail'=>
				[
					'X',
					'E-mail:',
					0,
					"<a href=\"mailto:{$upref.uMail}\" >{$upref.uMail}</a>"
				]

		]
		btn_text=' '
	}
	</div>
{*-end inviter popup*}
	{/if}




{if _uid()}

	<div class="block_form">
		<form method="post" name="refsys_frm">

			{if $reflogin}
				<div class="block_form_el cfix">
				    <label for="refsys_frm_RefLogin">{$_TRANS['You invited by']}</label>
				    <div class="block_form_el_right">
						<a href='javascript:void(0);'><span class='open_popup' rel='popup1'>{$reflogin}</span></a>
				    </div>
				</div>
			{/if}

			<div class="block_form_el cfix">
				<label for="refsys_frm_RefURL">{$_TRANS['Your referral link']}</label>
				<div class="block_form_el_right">
				    <a href="{$refurl}" target="_blank">{$refurl}</a>
				</div>
			</div>

			{if $refs}{include file="refsys/_refs.tpl"}{/if}

			{_getFormSecurity form='refsys_frm'}
		</form>
	</div>

	{*include file='edit.tpl'
		fields=[
			'RefLogin'=>
				[
					'X',
					'Вас пригласил',
					0,
					$reflogin,
					'skip'=>!$reflogin
				],
			'RefURL'=>
				[
					'X',
					'Ваша реф-ссылка',
					0,
					"<a href=\"{$refurl}\" target=\"_blank\">{$refurl}</a>"
				],
			'Refs'=>
				[
					'U',
					'refsys/_refs.tpl',
					'skip'=>!$refs
				]
		]
		btn_text=' '
	*}

	{if $_cfg.Account_RegMode == 3}

		<h2>{$_TRANS['Invites']}</h2>

	{/if}

{/if}

<h2>{$_TRANS['Promo materials']}</h2>

	<script> {* Script for popup*}
		$('.open_popup').click(function() {
			var popup_id = $('#' + $(this).attr("rel"));
			$(popup_id).show();
			$('.overlay').show();
		});

		$('.popup .close, .overlay').click(function() {
			$('.overlay, .popup').hide();
		});
	</script>


{include file='footer.tpl'}
{/strip}