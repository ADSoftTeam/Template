<?php	
	include_once("./../classes/template.class.php");	
		
	$parse = new template(true); // Без удаление переводов строк, табуляци
   	$parse->get_tpl("./../template/include.tpl");
	$parse->set_tpl('%block%',1);
	$parse->tpl_parse();	
	echo $parse->template;