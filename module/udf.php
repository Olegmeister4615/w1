<?php

// User Defined Functions module

function _z($z, $cid, $mode = 0) // 0-only sum / 1-sum and curr / 2-sum (+bold) and curr 
{
	global $_GS, $_cfg, $_currs;
	$r = $_currs[$cid]['cNumDec'];
	if ($r <= 0)
		$r = $_cfg['UI_NumDec'];
	$z = number_format(0 + $z, 0 + $r, '.', '');
	if ($mode < 1)
		return $z;
	if ($mode === 2)
		$z = "<b>$z</b>";
	return $z . ' <small>' . textLangFilter($_currs[$cid]['cCurr'], $_GS['lang']) . '</small>';
}

function updateUserCounters()
{
	global $db, $_auth, $_user;
	if ($_user['uLevel'] >= 90)
		setPage('count_aopers', $db->count('Opers', 'oState=2'));
	if ($_auth < 90)
	{
		setPage('count_msg', $db->count('MBox LEFT JOIN Msg ON mID=bmID', 
			'ISNULL(bRTS) and buID=?d and muID<>buID and bDeleted=0', array(_uid())));
		setPage('count_opers', $db->count('Opers', 'oNTS>0 and ouID=?d', array(_uid())));
        setPage('count_tickets', $db->count('Tickets LEFT JOIN TMsg ON tID=mtID',
            'ISNULL(mRTS) and tuID=?d and tuID<>muID', array(_uid())));
	}
	else
	{
	}
}

/*function fetchTopDeps() {
	global $db;
	
	return $db->fetchRows(
		$db->select(
			'Users', 
			"uLogin, (SELECT SUM(oSum) FROM Opers WHERE ouID=uID AND oOper='REF') AS RSUM",
			'',
			0,
			'RSUM desc',
			10
		)
	);
}*/

/*function fetchTopRefs() {
	global $db;
	
	return $db->fetchRows(
		$db->select(
			'Users U',
			"uLogin, (SELECT COUNT(uID) FROM Users R WHERE R.uRef=U.uID) AS RCNT",
			'',
			0,
			'RCNT desc',
			10
		)
	);
}*/

function fetchLatestNews($count = 3) {
	$list = opPageGet(1, $count, 'News', '*', 
		'(nDBegin=0 or nDBegin<=?) and (nDEnd=0 or nDEnd>=?)', array(timeToStamp(), timeToStamp()),
		array(
			'nTS' => array('nAttn desc, nTS desc, nID desc')
		),
		'nTS', 'nID'
	);
	stampTableToStr($list, 'nTS', 0);
	
	return $list;
}

function fetchLatestDeposits($count = 5) {
	global $db;
	
	return $db->fetchIDRows(
		$db->select(
			'Opers LEFT JOIN Users ON uID=ouID',
			'oID, uLogin, ocID, oSum, oBatch',
			'oOper=? and oState=3',
			array('CASHIN'),
			'oID desc',
			$count
		),
		false,
		'oID'
	);
}

function fetchLatestWithdrawals($count = 5) {
	global $db;
	
	return $db->fetchIDRows(
		$db->select(
			'Opers LEFT JOIN Users ON uID=ouID',
			'oID, uLogin, ocID, oSum, oBatch',
			'oOper=? and oState=3',
			array('CASHOUT'),
			'oID desc',
			$count
		),
		false,
		'oID'
	);
}

// copied from function depoGetStat in module/depo/lib.php
function fetchStats() {
	global $db, $_cfg;
	$stat = array();
	$stat['worked'] = floor((time() - stampToTime($_cfg['Depo_S0'])) / HS2_UNIX_DAY);
	$stat['users'] = $db->count('Users', 'uState=1') + $_cfg['Depo_S1'];
	$stat['usersonline'] = $db->count('Users', 'uState=1 and uLTS>?', array(timeToStamp(time() - 5 * HS2_UNIX_MINUTE)));
	$stat['zin'] = $db->fetch1($db->select('Opers', 'SUM(oSum)', "oOper='CASHIN' AND oState=3")) + $_cfg['Depo_S2'];
	$stat['zout'] = $db->fetch1($db->select('Opers', 'SUM(oSum-oComis)', "oOper='CASHOUT' AND oState=3")) + $_cfg['Depo_S3'];
	$stat['zref'] = $db->fetch1($db->select('Opers', 'SUM(oSum)', "oOper='REF' AND oState=3")) + $_cfg['Depo_S4'];
	$stat['zreinv'] = $db->fetch1($db->select('Opers', 'SUM(oSum)', "oOper='GIVE' AND oState=3 AND NOT (oMemo ?%)", array('Auto'))) + $_cfg['Depo_S5'];
	$stat['deps'] = $db->count('Deps', 'dState=1') + $_cfg['Depo_S6'];
	$stat['depsclosed'] = $db->count('Deps', 'dState>=2') + $_cfg['Depo_S7'];
	$stat['lastuser'] = $db->fetch1Row($db->select('Users LEFT JOIN AddInfo ON auID=uID', 'uLogin, aCIP', 'uState=1', array(), 'uID desc', 1));
	if ($_cfg['Depo_S8'] > 0)
		$stat['lastinop'] = array('oSum' => $_cfg['Depo_S8'], 'uLogin' => $_cfg['Depo_S9']);
	else
		$stat['lastinop'] = $db->fetch1Row($db->select('Opers LEFT JOIN Users ON uID=ouID', 'oSum, uLogin', "oOper='CASHIN' AND oState=3", array(), 'oCTS desc', 1));
	if ($_cfg['Depo_S11'] > 0)
		$stat['lastoutop'] = array('oSum' => $_cfg['Depo_S11'], 'uLogin' => $_cfg['Depo_S12']);
	else
		$stat['lastoutop'] = $db->fetch1Row($db->select('Opers LEFT JOIN Users ON uID=ouID', 'oSum, uLogin', "oOper='CASHOUT' AND oState=3", array(), 'oCTS desc', 1));
	return $stat;
}
