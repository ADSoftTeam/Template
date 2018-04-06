<?php
	// подключение необходимых классов
	include_once("./../classes/template.class.php");
	
	$parse = new template;
   	$parse->get_tpl("./../template/ex1.tpl");	
	$parse->set_tpl('%var1%','First');
	$parse->set_tpl('%var2%','Two');	
	$parse->tpl_parse();	
	
	echo $parse->template;