<?php

$_auth = 1;
require_once('module/auth.php');

$table = 'Deps';
$id_field = 'dID';
$uid_field = 'duID';
	
try 
{

} 
catch (Exception $e) 
{
}

$list = opPageGet(_GETN('page'), 20, "$table LEFT JOIN Users ON uID=duID LEFT JOIN Currs ON cID=dcID LEFT JOIN Plans ON pID=dpID", '*',
	"$uid_field=?d", array(_uid()), 
	array(
		$id_field => array(),
		'uLogin' => array('uLogin', 'uLogin desc'),
		'pName' => array('pName', 'pName desc'),
		'dLTS' => array('dLTS desc', 'dLTS'),
		'dNTS' => array('dNTS desc', 'dNTS')
	), 
	_GET('sort'), $id_field
);
stampTableToStr($list, 'dCTS, dLTS, dNTS');

setPage('list', $list);

showPage();

?>