<?php	
	include_once("./../classes/template.class.php");	
	
	$offset = (isset($_GET['offset'])) ? intval($_GET['offset']) : 0;
	
	$parse = new template;
   	$parse->get_tpl("./../template/ex4.tpl");
	$parse->set_paginate('%paginate%',25,
	$offset,"./../template/paginate.tpl","/examples/ex4.php?offset=%offset%&cat=%cat%",array("%cat%"=>5));	
	$parse->set_tpl('%a%','0');
	$parse->set_tpl('%b%',"100");
	$parse->set_tpl('%d%',55);	
	$parse->tpl_parse();	
	echo $parse->template;