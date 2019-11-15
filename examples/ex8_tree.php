<?php	
	include_once("./../classes/template.class.php");	
	$array = array(
		array(
			'id' 		=> 1,
			'sub_id' 	=> 0,
			'title'		=> 'Item 1'
		),
		array(
			'id' 		=> 2,
			'sub_id' 	=> 1,
			'title'		=> 'SubItem 1_1'
		),
		array(
			'id' 		=> 3,
			'sub_id' 	=> 2,
			'title'		=> 'SubSubItem 1_1_1'
		),
		array(
			'id' 		=> 4,
			'sub_id' 	=> 2,
			'title'		=> 'SubItem 1_2'
		),
		array(
			'id' 		=> 5,
			'sub_id' 	=> 0,
			'title'		=> 'Item 2'
		),
		array(
			'id' 		=> 6,
			'sub_id' 	=> 5,
			'title'		=> 'SubItem 2_1'
		)
	);
	
	$parse = new template(true); // Без удаление переводов строк, табуляци
   	$parse->get_tpl("./../template/tree.tpl");
	$parse->set_tpl('%array%',$array);
	$parse->tpl_parse();	
	echo $parse->template;