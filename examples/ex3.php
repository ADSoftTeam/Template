<?php
	header ("Content-Type: text/html; charset=utf-8");
	// подключение необходимых классов
	include_once("./../classes/template.class.php");
	
	$parse = new template;
	$arr = array(array('name'=>'book'),array('name'=>'table'),array('name'=>'tv'));
   	
	$parse->get_tpl("./../template/ex3.tpl");	
	$parse->set_tpl('%list%',$arr);
	$parse->set_tpl('%a%',11);	
	$parse->set_tpl('%b%',3);	
	$parse->set_tpl('%s%',3);	
	$parse->set_tpl('%name%','Это имя');
	$parse->set_tpl('%vars%','Это выражение "будет" экранироваться htmlspecialchars');
	$parse->set_tpl('%ars%',array('text'=>'Это значение элемента массива "будет" экранироваться htmlspecialchars'));	
	
	$parse->tpl_parse();
	
	echo $parse->template;
	
	