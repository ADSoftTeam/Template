<?php	
	include_once("./../classes/template.class.php");	
	
	$offset = (isset($_GET['offset'])) ? intval($_GET['offset']) : 0;
	
	$parse = new template;
   	$parse->get_tpl("./../template/ex6.tpl");
	$parse->set_tpl('%list1%',array(
		array("level"=>0,"id"=>"Гость"),
		array("level"=>1,"id"=>"Пользователь"),
		array("level"=>2,"id"=>"Автор"),
		array("level"=>3,"id"=>"Модератор")
		));
	$parse->set_tpl('%list2%',array(
		array("id"=>0),
		array("id"=>1),
		array("id"=>2),
		array("id"=>3)
		));
	$parse->tpl_parse();	
	echo $parse->template;