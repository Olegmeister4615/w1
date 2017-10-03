<?php

$_auth = 1;
require_once('module/auth.php');
require_once('lib/psys.php');

$table = 'Opers';
$id_field = 'oID';
$uid_field = 'ouID';
$out_link = moduleToLink('balance');

try
{
	if (sendedForm('', 'add'))
	{
		checkFormSecurity('add');

        if (!_IN('Oper'))
			goToURL($out_link);
		$a = $_IN;
		if (($a['Oper'] == 'CASHOUT') and $_cfg['Bal_NeedPIN'] and (md5($a['PIN'] . $_cfg['Const_Salt']) != $_user['uPIN']))
			setError('pin_wrong', 'add');
		if ($_cfg['Depo_AutoDepo'] and ($a['Oper'] == 'CASHIN'))
		{
            useLib('depo');
			$err = opDepoCreate(_uid(), $a['PSys'], $a['Sum'], $a['Compnd'], $a['Plan'], false, 2);
   
			if ($err != 'passed')
				setError($err, 'add');  
		}
        
        
		if (!isset($a['PSys']))
			$a['PSys'] = 1;
		$params = array(
			'uid2' => $db->fetch1($db->select('Users', 'uID', 'uLogin=?', array($a['Login2']))),
			'cid2' => $a['PSys2'],
			'pid' => $a['Plan'],
			'compnd' => $a['Compnd']
		);
		$cid = $a['PSys'];
		if ($_cfg['Const_IntCurr'] and ($a['Oper'] == 'CASHOUT'))
		{
			$params['cid2'] = $a['PSys'];
			$a['Oper'] = 'EX';
			$a['PSys'] = 1;
		}


        //process currency wallet
        $currs_rates_list=array();

        $sql="SELECT Prop
             FROM  Cfg
             WHERE Prop LIKE 'Rate%'
             AND Module='Bal'
             AND Val>0
             ORDER BY Prop";
        $result = $db->_doQuery($sql);
        while ($row = $db->fetch($result))
        {
          $currs_rates_list[]=str_replace("Rate", "", $row['Prop']);
        }

        $wallet_currency=isset($_POST['currency'])?trim($_POST['currency']):"";
        $wallet_currency=htmlspecialchars(stripslashes($wallet_currency));

        if (empty($wallet_currency) || !empty($wallet_currency) && !in_array($wallet_currency, $currs_rates_list))
        {
           $sql="SELECT cCurrID
                 FROM currs
                 WHERE cID='".mysql_escape_string($a['PSys'])."'";
           $result = $db->_doQuery($sql);
           $row = $db->fetch($result);
           $wallet_currency=$row['cCurrID'];
        }

		setError($id = opOperCreate(_uid(), $a['Oper'], $a['PSys'], $a['Sum'], $params, $a['Memo'], $and_complete = false, $by_admin = false, $wallet_currency), 'add');
        
		showInfo('Saved', moduleToLink() . "?id=$id" . valueIf($a['Oper'] == 'CASHIN', '&pay'));
	}
    
	if (sendedForm('', 'data'))
	{
		checkFormSecurity('data');

		if ($o = $db->fetch1Row($db->select('Opers LEFT JOIN Currs on cID=ocID', '*',
			"$id_field=?d and $uid_field=?d and oOper=? and oState<=1", array(_INN('oID'), _uid(), 'CASHIN'))))
		{
			$a = array(
				'date' => timeToStamp(textToTime(_IN('date'))),
				'batch' => strip_tags(_IN('batch')),
				'memo' => _IN('memo')
			);
			if (!$a['date'] or (stampToTime($a['date']) >= time()))
				setError('data_date_wrong', 'data');
			if (!$a['batch'])
				setError('data_batch_wrong', 'data');
			setError($a = opEditToCurrParams(getPayFields($o['cCID']), $a, $_IN), 'data');
			$db->update('Opers', array('oParams2' => arrayToStr($a)), '', 'oID=?d', ($o['oID']));
			showInfo('Saved');
		}
		showInfo('*Error');
	}

	if ($id = _INN($id_field))
	{
		checkFormSecurity();

		if ($o = $db->fetch1Row($db->select('Opers', '*', "$id_field=?d and $uid_field=?d", array($id, _uid()))))
		{
			if (sendedForm('del') and ($o['oState'] >= 5))
			{
				$db->delete('Opers', 'oID=?d', array($id));
				showInfo('Deleted', $out_link);
			}
			elseif (sendedForm('cancel') and ($o['oState'] <= 2))
			{
				if (opOperCancel(_uid(), $id, array()) === true)
					showInfo('Canceled');
			}
			elseif (sendedForm() and ($o['oState'] <= 1))
			{
				if ($_cfg['SMS_CASHOUT'] and (($o['oOper'] == 'CASHOUT') or (($o['oOper'] == 'EX') and ($o['ocID'] == 1))))
				{
					useLib('confirm');
					$tel = $_user['aTel'];
					opConfirmPrepareSMS(_uid(), 'OPER', array('oid' => $id, 'tel' => $tel), '', $tel);
					showInfo('Saved', moduleToLink('confirm') . '?need_confirm_sms');
				}
				$err = opOperConfirm(_uid(), $id, array());
				if (($err === 'limit_exceeded') and ($_currs2[$o['ocID']]['c' . $o['oOper'] . 'Mode'] == 2))
				{
					setError(opOperConfirm(_uid(), $id, array(), true));
					sendMailToAdmin('OperRequired',
						opUserConsts($_user, array('oid' => $id, 'url' => fullURL(moduleToLink('balance/admin/oper')))));
				}
				else
				{
					setError($err);
					if ($o['oOper'] != 'CASHIN')
					{
						if ($_currs2[$o['ocID']]['c' . $o['oOper'] . 'Mode'] == 2)
						{
							setError(opOperComplete(_uid(), $id, array('initid' => $id)));
							if ($p = strToArray($db->fetch1($db->select('Opers', 'oParams2', "$id_field=?d", array($id)))))
								if ($id = $p['newid'])
									showInfo('Completed', moduleToLink() . "?id=$id");
						}
						else
							sendMailToAdmin('OperRequired',
								opUserConsts($_user, array('oid' => $id, 'url' => fullURL(moduleToLink('balance/admin/oper')))));
					}
				}
				showInfo();
			}
		}
		showInfo('*Error');
	}

}
catch (Exception $e)
{
}

if (!isset($_GET['add']))
{
	if ($id = _GETN('id'))
    {
        //LEFT JOIN Cfg AS t3 ON t3.Module='Bal' AND t3.Prop=CONCAT('Rate', IF(Opers.cCurrID <>'', Opers.cCurrID, Currs.cCurr)) LEFT JOIN Cfg AS t4 ON t4.Module='Bal' AND t4.Prop=CONCAT('Rate', Currs.cCurr)

		$el = $db->fetch1Row($db->select("$table LEFT JOIN Users on uID=ouID LEFT JOIN Currs on cID=ocID LEFT JOIN Cfg AS t3 ON t3.Module='Bal' AND t3.Prop=CONCAT('Rate', IF(Opers.cCurrID <>'', Opers.cCurrID, Currs.cCurr)) LEFT JOIN Cfg AS t4 ON t4.Module='Bal' AND t4.Prop=CONCAT('Rate', Currs.cCurr)", '*, Opers.cCurrID AS operation_currency, IF(Currs.cCurr = Opers.cCurrID, Opers.oSum, ROUND(ROUND(Opers.oSum/t4.Val,2)*t3.Val,2)) AS operation_currency_summ', "$id_field=?d and $uid_field=?d", array($id, _uid())));
    }
	if (!$el)
		goToURL($out_link);
	if (isset($_GET['check']) and ($el['oOper'] == 'CASHIN'))
		if ($el['oState'] >= 3)
			goToURL(moduleToLink('balance/oper') . "?id=$id");
		else
			refreshToURL(5, moduleToLink('balance/oper') . "?id=$id&check");
	stampArrayToStr($el, 'oCTS, oTS, oNTS');
	$el['oParams'] = strToArray($el['oParams']);
	$el['oParams2'] = strToArray($el['oParams2']);
	stampArrayToStr($el['oParams2'], 'date', 1);
	setPage('el', $el);
    

	if (($el['oOper'] == 'CASHIN') and ($el['oState'] <= 2))
	{
		opDecodeCurrParams($el, $p, $p_sci, $p_api);
		if (in_array($el['cCASHINMode'], array(2, 3)))
		{
			setPage('pform', prepareSCI($el['cCID'], $p, $p_sci, $el['oSum'], $el['oParams2']['memo'], $id,
				fullURL(moduleToLink('balance/oper')) . "?id=$id&check",
				fullURL(moduleToLink('balance')) . '?fail',
				valueIf(!$p_sci['hideurl'],
				fullURL(moduleToLink('balance/status'))),
				opDecodeUserCurrParams($_currs2[$el['cID']]), $_cfg['Bal_ForcePayer']
			), 0);
			if (isset($_GET['pay']))
				showPage('_pform');
		}
		if (in_array($el['cCASHINMode'], array(1, 3)))
		{
			setPage('pfields', $pf = getPayFields($el['cCID']));
			setPage('pvalues', $p);
			if ($a = opCurrParamsToEdit($pf, '', $el['oState'] == 2))
				setPage('dfields', array(1 => '') + $a, 1);
			$c = getCIDs($el['cCID']);
			if (!$c[2])
				setPage('defaultbatch', 'IN' . str_pad($el['oID'], 6, '0', STR_PAD_LEFT));
		}
	}
	$db->update('Opers', array('oNTS' => ''), '', "$id_field=?d", array($id));
	updateUserCounters();
}
else
{
	$oper = _GET('add');
	if (!in_array($oper, array('CASHIN', 'CASHOUT', 'EX', 'TR')))
		goToURL($out_link);

    //currency rates
    $currency_rates_data=array();

    $sql="SELECT Prop, Val
             FROM  Cfg
             WHERE Prop LIKE 'Rate%'
             AND Module='Bal'
             AND Val>0
             ORDER BY Prop";
    $result = $db->_doQuery($sql);
    while ($row = $db->fetch($result))
    {
        $currency_rates_data[str_replace("Rate", "", $row['Prop'])]=$row['Val'];
    }
    setPage('currency_rates_data', $currency_rates_data);

    if ($oper == 'CASHIN')
    {
       //get currency list
       $currency=isset($_POST['currency'])?trim($_POST['currency']):"";
       $currency=htmlspecialchars(stripslashes($currency));

       $currs_rates_list=array();

       $sql="SELECT Prop
             FROM  Cfg
             WHERE Prop LIKE 'Rate%'
             AND Module='Bal'
             AND Val>0
             ORDER BY Prop";
        $result = $db->_doQuery($sql);
        while ($row = $db->fetch($result))
        {
          $currs_rates_list[]=str_replace("Rate", "", $row['Prop']);
        }

        setPage('currs_rates_list', $currs_rates_list);
        setPage('currency', $currency);
        //get currency list

       //на любую
       setPage('currency_list_data', $_currs2);
    }
    elseif ($oper == 'CASHOUT')
    {

       //cashout process
       if ($_cfg['Bal_PayOutType'] == 0 || $_cfg['Bal_PayOutType'] == 3)
       {
         //на любую
         setPage('currency_list_data', $_currs2);
       }
       elseif ($_cfg['Bal_PayOutType'] == 1 || $_cfg['Bal_PayOutType'] == 2 || $_cfg['Bal_PayOutType'] == 4)
       {
         $operations_currs_data=array();
         $operations_currs_data_summ=array();
         $operations_currs_data_summ_max=array();
         
         $sql="SELECT t2.cID, t2.cName, t2.cCurr, IF(t1.cCurrID <>'', t1.cCurrID, t2.cCurr) AS curr_operation,
               IF(IF(t1.cCurrID <>'', t1.cCurrID, t2.cCurr) = t2.cCurr, t1.oSum, SUM(ROUND(ROUND(t1.oSum/t4.Val,2)*t3.Val,2))) AS summ_currency_operation
               FROM Opers AS t1
               INNER JOIN Currs AS t2 ON t1.ocID=t2.cID
               LEFT JOIN Cfg AS t3 ON t3.Module='Bal' AND t3.Prop=CONCAT('Rate', IF(t1.cCurrID <>'', t1.cCurrID, t2.cCurr))
               LEFT JOIN Cfg AS t4 ON t4.Module='Bal' AND t4.Prop=CONCAT('Rate', t2.cCurr)
               WHERE (t1.oOper='BONUS' OR t1.oOper='CASHIN' OR t1.oOper='EXIN' OR t1.oOper='TRIN' OR t1.oOper='SELL'  OR t1.oOper='REF' OR t1.oOper='TAKE' OR t1.oOper='CALCIN')
               AND t1.ouID='".mysql_escape_string(_uid())."'
               GROUP BY t2.cID, curr_operation";
         $result = $db->_doQuery($sql);
         while ($row = $db->fetch($result))
         {
            if (!isset($operations_currs_data[$row['curr_operation']]))
            {
              $operations_currs_data[$row['curr_operation']]=array();
              $operations_currs_data_summ[$row['curr_operation']]=0;
              $operations_currs_data_summ_max[$row['curr_operation']]=0;
            }

            if ($_cfg['Bal_PayOutType'] == 4)
            {
                if ($operations_currs_data_summ_max[$row['curr_operation']]<$row['summ_currency_operation'])
                {
                    $operations_currs_data_summ_max[$row['curr_operation']]=$row['summ_currency_operation'];
                    $operations_currs_data_summ[$row['curr_operation']]=$row['summ_currency_operation'];

                    $operations_currs_data[$row['curr_operation']]=array();

                    $operations_currs_data[$row['curr_operation']][]=array(
                                                                     'id' => $row['cID'],
                                                                     'name' => $row['cName'],
                                                                     'paysys_curr' => $row['cCurr'],
                                                                     'coef' => $row['summ_currency_operation']
                                                              );
                }
            }
            else
            {
                $operations_currs_data_summ[$row['curr_operation']]+=$row['summ_currency_operation'];
                $operations_currs_data[$row['curr_operation']][]=array(
                                                                     'id' => $row['cID'],
                                                                     'name' => $row['cName'],
                                                                     'paysys_curr' => $row['cCurr'],
                                                                     'coef' => $row['summ_currency_operation']
                                                              );
            }
         }

         if (is_array($operations_currs_data) && count($operations_currs_data)>0)
         {
           foreach ($operations_currs_data as $k => $v)
           {
              for ($i=0, $co=sizeof($v); $i<$co; $i++)
              {
                $operations_currs_data[$k][$i]['coef']=round($operations_currs_data[$k][$i]['coef']/$operations_currs_data_summ[$k],2);
              }
           }
         }

         setPage('operations_currs_data', $operations_currs_data);
       }
       //cashout process
    }
    
	if (!$_cfg['Const_IntCurr'] or in_array($oper, array('CASHIN', 'CASHOUT', 'EX')))
	{
		$list = array();
		$list2 = array();
		foreach ($_currs2 as $id => $r)
        {
			if (!$r['cHidden'])
			{
				switch ($oper)
				{
				case 'CASHIN':
					if (($r['cCASHINMode'] > 0))
						$list[$id] = $r['cName'];
					break;
				case 'CASHOUT':
					if (($r['cCASHOUTMode'] > 0))
						$list[$id] = $r['cName'];
					break;
				case 'EX':
					if ($r['cEXMode'] > 0)
						$list[$id] = $r['cName'] . valueIf($r['wBal'] > 0, '{!!} [' . _z($r['wBal'], $r['wcID'], -1) . ']');
					if ($r['cEXMode'] > 0)
						$list2[$id] = $r['cName'] . valueIf($r['wBal'] > 0, '{!!} [' . _z($r['wBal'], $r['wcID'], -1) . ']');
					break;
				case 'TR':
					if ($r['cTRMode'] > 0)
						$list[$id] = $r['cName'] . valueIf($r['wBal'] > 0, '{!!} [' . _z($r['wBal'], $r['wcID'], -1) . ']');
					break;
				}
			}
        }    
		if (!$list)
			showInfo('*CantComplete', $out_link);
		if ($oper == 'EX' || $oper == 'TR')
        {
          $list2=array();
          for ($i=0, $co=sizeof($_currs); $i<$co; $i++)
          {
            if ($oper == 'EX' || ($_currs[$i]['wBal']>0 && $oper == 'TR'))
            {
              $list2[$_currs[$i]['currency_account']]=$_currs[$i]['currency_account'].' ('.round($_currs[$i]['wBal'],2).')';
            }
          }
          $list=$list2;
        }

		setPage('clist2', $list2);
        setPage('clist', $list); 
	}
	if ($_cfg['Depo_AutoDepo'])
	{
		useLib('depo');
		$plans = opDepoGetPlanList(_uid());
		$pl = array();
		$cmax = 0;
		foreach ($plans as $pid => $p)
			if (!$p['Disabled'])
			{
				$pl[$pid] = $p['pName'];
				if ($p['pCompndMax'] > $cmax)
					$cmax = $p['pCompndMax'];
			}
		if (!$pl)
			showInfo('*CantComplete', $out_link);
		setPage('plans', $pl);
		setPage('pcmax', $cmax);
		setPage('icurr', $_currs2[1]['cCurr']);
	}
}
setPage('currs', $_currs);

$_GS['vmodule'] = 'balance';
showPage();
?>