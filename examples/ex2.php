<?php
	// подключение необходимых классов
	include_once("./../classes/template.class.php");
	
	$parse = new template;
   	$parse->set_tpl_list(array(array('%name%'=>'book'),array('%name%'=>'table'),array('%name%'=>'tv'),array('%name%'=>'phone')),"./../template/ex2_list.tpl");
	$parse->set_tpl('%var2%','Two');
	$parse->tpl_parse();	
	$list = $parse->template;
	
	$parse->get_tpl("./../template/ex2.tpl");	
	$parse->set_tpl('%list%',$list);
	$parse->set_tpl('%sdd%',array('a'=>5559,'b'=>5559));
	$parse->set_tpl('%a%','0');
	$parse->set_tpl('%b%',"");
	$parse->set_tpl('%d%','');
	//
	$parse->set_tpl('%arr1%',array(0=>1,1=>'',2=>2));
	$parse->set_tpl('%arr12%',array('one'=>1,'two'=>'',2=>2));
	$parse->tpl_parse();
	
	echo $parse->template;
