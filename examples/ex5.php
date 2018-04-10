<?php	
	include_once("./../classes/template.class.php");	
	
	$offset = (isset($_GET['offset'])) ? intval($_GET['offset']) : 0;
	
	$parse = new template;
   	$parse->get_tpl("./../template/ex5.tpl");
	$parse->set_tpl_lists('%lists%',
	array(array("level"=>0,"name"=>"Гость"),
	array("level"=>1,"name"=>"Пользователь"),
	array("level"=>2,"name"=>"Автор"),
	array("level"=>3,"name"=>"Модератор")),
	'%users%',"./../template/levels.tpl",array("%select%"=>3));	
	$parse->set_tpl('%a%','0');
	$parse->set_tpl('%b%',"100");
	$parse->set_tpl('%d%',55);	
	$parse->tpl_parse();	
	echo $parse->template;