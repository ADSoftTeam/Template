<?php
// Шаблонизатор Template, версия 2.0
// AD Soft, Денисов Андрей
// https://github.com/ADSoftTeam/Template

/* Надо придерживаться одних правил:
	- в шаблон передаем переменные обрамленные в %% - set_tpl("%a%",5), set_tpl('%news%',$news); итд
	- в самом шаблоне используем переменные:
		- в сравнениях, циклах - как есть, то есть {|if|%a%|=|5| равно 5| не равно 5|}, {|if|%new['title']%|||%news['title']%|}
		- вне сравнений, просто для подстановки - в обрамлении %%, то есть - %a%, %array[5]%, %a['name']% и.т.д
		- @ - для экранирования (прогоняет вывод через htmlspecialchars() - %@a%, %@sw['volume']% и.т.д
	- в качестве имен переменных: буквы,цифры и знак подчеркивания: %flip%, %a14%, %count_pos%, %имя%
	- цикл только первого уровня - вложенные циклы не поддерживаются
*/


class template {
    public $vars;
    public $template;
	private $recursive_plan;
	private $id;
	
	function __construct() {
		// обнуляем все при создании
		$this->vars = array();
		$this->recursive_plan = array();
		$this->template = "";
		$this->id = 0;
	}
	
	/**
		Возвращает все текущие переменные класса в одном массиве
		param  $clear - (boolean) очищает текущие параметры
		return mix array
	*/
	private function store($clear) {
		$out = array(
			"vars" 		=> $this->vars,
			"template" 	=> $this->template,
			"plan"		=> $this->recursive_plan,
			"id"		=> $this->id
		);
		if ($clear) {
			$this->vars = array();
			$this->recursive_plan = array();
			$this->template = "";
			$this->id = 0;
		}
		return $out;
	}
	
	/**
		Устанавливает все текущие переменные класса из массива
		param $aray - mix array
	*/
	private function restore($array) {
		$this->vars = $array["vars"];
		$this->recursive_plan = $array["plan"];
		$this->template = $array["template"];
		$this->id = $array["id"];		
	}
	
	/**
		Внутренний метод для получения значения из переданной строки
		param $string  	- string, входная строка
		param $perfix 	- char(1), символы обрамляющие переменную использовать какие, по умолчанию %
		param $data		- array(), массив переменных, если не задано - то берется общешаблонный массив $this->vars
		return 		- string		
		разбирает следующие выражения
		1. %var%  	- проверяет из массива переменных $data
		2. %var[index]%	- элемент c индексом index массива в переменной %var% из $data, в '' или "", или без - но число
		- при данных проверках, если не найдено считаем за пустое значение
		3. Иначе просто пустое значение - если это перменная всеже, или значение которое в $string		
	*/
	private function get_value($string,$data = null) {
		$perfixs = "%";		
		$data = (empty($data)) ? $this->vars : $data;		
		$patterns = array(
			'#^'.$perfixs.'[a-zA-Z,а-яА-ЯёЁ,_,\-,\d,\@]+'.$perfixs.'$#',
			'#'.$perfixs.'(\@*\w+)(\[\'\w+\']|\["\w+\"]|\[\d+\])'.$perfixs.'#'
		);		
		preg_match($patterns[0], $string, $matches);
		// Это проверка - передана константа или переменная
		$without = ((mb_substr($string,0,1)=="%" && mb_substr($string,-1,1)=="%")) ? false : true;
		if (COUNT($matches)==1 && $matches[0]==$string) {
			// Это простая переменная			
			$find =  ($string[1]=="@") ? str_replace('@','',$matches[0]) : $matches[0];// В данных всегда ищем с %%						
			return array_key_exists($find,$data) ? (($string[1]=="@") ? htmlspecialchars($data[$find]) : $data[$find]) : ($without ? $string: "");
		} else {
			// Это элемент массива
			$matches = array();
			preg_match($patterns[1], $string, $matches);
			$find =  ($string[1]=="@") ? str_replace('@','',$matches[1]) : $matches[1];
			if (COUNT($matches)==3 && array_key_exists("%$find%",$data) 
				&& is_array($data["%$find%"])) {					
			
				// Удволетворяет нашему паттерну, есть такой массив переданный в шаблон и он действительно массив
				$index = mb_substr($matches[2],1,mb_strlen($matches[2])-2);
				if ($index[0]=='"' || $index[0]=="'") {
					$index = mb_substr($index,1,mb_strlen($index)-2);
				}
				// Если нет с таким индексом - то отдаем пустоту
				return (array_key_exists($index,$data["%$find%"])) ? 
					(($string[1]=="@") ? htmlspecialchars($data["%$find%"][$index]) : $data["%$find%"][$index]) : "";
			} else {
				// Иначе получается нет такой переменной или она не массив
				// возвращаем пустоту				
				return $without ? $string: "";
			}
		}
	}
	
	/**
		Внутренний метод для получения массива значний по его имени из переданной строки
		param $string  	- string, входная строка
		param $data		- array(), массив переменных, если не задано - то берется общешаблонный массив $this->vars
		return 		- string		
		разбирает следующие выражения
		1. %var%  	- проверяет из массива переменных $data
		2. %var[index]%	- массив, который сам я является элементом c индексом index массива в переменной %var% из $data, в '' или "", или без - но число
		- при данных проверках, если не найдено считаем за пустое значение
		3. Иначе просто пустое значение - если это перменная всеже, или значение которое в $string		
	*/
	private function get_array($string,$data = null) {
		$perfixs = "%";		
		$data = (empty($data)) ? $this->vars : $data;		
		$patterns = array(
			'#^'.$perfixs.'[a-zA-Z,а-яА-ЯёЁ,_,\-,\d]+'.$perfixs.'$#',
			'#'.$perfixs.'(\w+)(\[\'\w+\']|\["\w+\"]|\[\d+\])'.$perfixs.'#'
		);		
		preg_match($patterns[0], $string, $matches);
		// Это проверка - передана константа или переменная
		if (COUNT($matches)==1 && $matches[0]==$string) {
			// Это простая переменная			
			$find = $matches[0];// В данных всегда ищем с %%						
			return (array_key_exists($find,$data) && is_array($data)) ? $data[$find] : $string;
		} else {
			// Это элемент массива
			$matches = array();
			preg_match($patterns[1], $string, $matches);			
			if (COUNT($matches)==3 && array_key_exists("%{$matches[1]}%",$data) 
				&& is_array($data["%{$matches[1]}%"])) {					
				// Удволетворяет нашему паттерну, есть такой массив переданный в шаблон и он действительно массив
				$index = mb_substr($matches[2],1,mb_strlen($matches[2])-2);
				if ($index[0]=='"' || $index[0]=="'") {
					$index = mb_substr($index,1,mb_strlen($index)-2);
				}								
				// Если нет с таким индексом - то отдаем пустоту
				return (array_key_exists($index,$data["%{$matches[1]}%"]) && is_array($data["%{$matches[1]}%"][$index])) ? $data["%{$matches[1]}%"][$index] : "";
			} else {
				// Иначе получается нет такой переменной или она не массив
				// возвращаем пустоту				
				return $string;
			}
		}
	}
	
	/**
		Метод получает массив и тело цикла для последующей обработки
		param $string - (string) входная строка для foreach
		return array(array,array_index,body)
	*/
	private function extract_body_foreach($string) {
		// {|foreach|arr|body cycles|}		
		$separator = "|";
		$split = explode($separator,$string);
		if (strtolower($split[1])=="foreach") {
			$start = mb_stripos($string,$split[2]);
			$length = mb_strlen($string);			
			$array = $this->get_array($split[2]);
			$body  = mb_substr($string,$start+mb_strlen($split[2])+1,$length-$start-mb_strlen($split[2])-3);
			return array("array"=>$array,"body"=>$body,"array_index"=>$split[2]);
		}
		return "";		
	}
		
	/**
		Метод рекурсивного обхода текста в поисках вложенных операторов в фигурных скобках {}
		param $strin - string (text) входной текст
		param $parent - int() ид родителя, изначально 0
		param $layer - int() уровень вложености выражения
		return array - массив с планом вычислений
		так же устанавливает результат в текущий глобальный массив recursive_plan
	*/		
	private function recursiveSplit($string, $parent = 0, $layer = 0) {
		$start_tags = preg_quote("{");
		$stop_tags  = preg_quote("}");
		$pattern = "#$start_tags(([^$start_tags$stop_tags]*|(?R))*)$stop_tags#ui";		
		preg_match_all($pattern,$string,$matches);		
		// iterate thru matches and continue recursive split		
		if (count($matches) >= 1) {
			for ($i = 0; $i < count($matches[1]); $i++) {
				if (is_string($matches[1][$i]) && mb_strlen($matches[1][$i]) > 0) {
					$this->id++;
					// Для foreach дальше вглубь не идем - там отд
					$operation = explode("|",$matches[1][$i]);
					$foreach = ($operation[1]=="foreach") ? 1 : 0;						
					//
					$this->recursive_plan[] = array(
						"layer"=>$layer,				// уровень вложенности
						"etalon"=>$matches[0][$i],		// эталонное выражение
						"expression"=>$matches[0][$i],  // вычисленное выражение
						"id"=>$this->id,				// ид выражения
						"foreach"=>$foreach,
						"parent"=>$parent);				// ид "родителя" данного выражения - в какое выражение вложен						
					if ($foreach==0) {
						$this->recursiveSplit($matches[1][$i], $this->id, $layer + 1);
					}						
				}				
			}
		}
		if ($layer==0) {
			// Делаем реверс, так-как обрабатывать будем с конца
			$out = $this->recursive_plan = array_reverse($this->recursive_plan);
			$this->recursive_plan = array();
			return $out;
		}
	}
		
	/**
		Метод проверяет выражение expression для набора данных data
		Если data не указано явно - вычисляется для глобального массива шаблона vars
		param $expression - string, выражение для вычисления
		param $data		- array, данные для подстановки, если не указано - берется глобальный набор		
		return string с вычисленным результатом, либо ошибку
	*/
	private function checkExpression($expression = null, $data = null) {
		$separator = "|";
		$rules = array(
			array("count"=>7,"operator"=>"if","compare"=>
				array("=")),
			array("count"=>8,"operator"=>"if","compare"=>
				array("=",">","<","<=",">=","!=","=<","=>")
			),
			array("count"=>6,"operator"=>"empty")
		);
		// |if|var|value|then|else|
		// |if|var|compare|value|then|else|
		// |empty|var|then|else|		
		if ($expression) {
			$data = empty($data) ? $this->vars : $data;
			$split = explode($separator,$expression);
			$perfix = "%";
			$operator = strtolower($split[1]);
			switch ($operator) {
				case "if":
					if (COUNT($split)==7) {
						// это краткий if и его параметры внутри выражения													
						$var = $this->get_value($split[2],$data);
						$var = (!(empty($var)) && is_numeric($var)) ? (int)$var : $var;
						// 
						$compare = $this->get_value($split[3],$data);						
						$compare = (!(empty($compare)) && is_numeric($compare)) ? (int)$compare : $compare;					
						// результат с чем сравниваем - может быть и переменной из того же массива						
						return ($var === $compare) ? $split[4] : $split[5];
					}
					if (COUNT($split)==8 && in_array($split[3],$rules[1]['compare'])) {
						// Расширенный if
						$var = $this->get_value($split[2],$data);
						$var = (!(empty($var)) && is_numeric($var)) ? (int)$var : $var;
						//
						$compare = $this->get_value($split[4],$data);
						$compare = (!(empty($compare)) && is_numeric($compare)) ? (int)$compare : $compare;							
						switch ($split[3]) {
							case "=": 
								return (!(empty($data)) && $var == $compare) ? $split[5] : $split[6];
							break 1;
								
							case "!=": 
								return (!(empty($data)) && $var != $compare) ? $split[5] : $split[6];
							break 1;
									
							case ">":									
								return (!(empty($data)) && $var > $compare) ? $split[5] : $split[6];
							break 1;

							case "<": 
								return (!(empty($data)) && $var < $compare) ? $split[5] : $split[6];
							break 1;
				
							case ">=": 
								return (!(empty($data)) && $var >= $compare) ? $split[5] : $split[6];
							break 1;
									
							case "=>": 
								return (!(empty($data)) && $var >= $compare) ? $split[5] : $split[6];
							break 1;
																		
							case "<=": 									
								return (!(empty($data)) && $var <= $compare) ? $split[5] : $split[6];
							break 1;
									
							case "=<": 									
								return (!(empty($data)) && $var <= $compare) ? $split[5] : $split[6];
							break 1;
						}
					} else {
						return "Неподерживаемый оператор в выражении [$expression]";
					}					
				break 1;
				
				case "empty":
					$var = $this->get_value($split[2],$data);
					$var = (!(empty($var)) && is_numeric($var)) ? (int)$var : $var;
					// Это empty
					return (empty($var)) ? $split[3] : $split[4];
				break 1;
		
				default: 
					return "Нет выражения удволетворяющго данному правилу [$expression]";
				break 1;
			}
		} else {
			// Передано пустое выражение
			return "Пустое выражение";
		}
	}

	/**
		Метод получает имя файла шаблона, открывет и заносит его в template
		param $tpl_name - string  имя файла с шаблоном
		return boolean - успешно или нет
	*/
    public function get_tpl($tpl_name) {
		if (empty($tpl_name) || !file_exists($tpl_name)) {          	
			return false;
		} else {
			$this->template ='';
			$this->template  = file_get_contents($tpl_name);			
			return true;
        }
	}
	
	/**
		Метод заносит в список переменных очередную переменную $var  с ключем $key
		param $key - string ключ переменной (передаем с %имя%)
		param $var - mixed значение переменной 
	*/
    public function set_tpl($key,$var) {
		$this->vars[$key] = $var;
	}
	
	/**
		Метод добавляет элементы передаваемого массива в массив переменных шаблонизатора
		Если массивы имеют одинаковые строковые ключи, тогда каждое последующее значение будет 
		заменять предыдущее. Однако, если массивы имеют одинаковые числовые ключи, 
		значение, упомянутое последним, не заменит исходное значение, а будет добавлено в конец массива.
		param $array - массив
	*/
	public function set_tpl_array($array) {
		if (is_array($array)) {			
			$this->vars = (empty($this->vars)) ? $array : array_merge($this->vars,$array);
		}
	}
	
	/**
		Публичный метод для показа "пагинатора"
		param $point		- (string) имя переменной, которая будет заменена в шаблоне на данный пагинатор
		param $all_pages 	- (int) всего страниц
		param $current		- (int) текущая страница
		param $template		- (string) шаблон отображения страниц
		param $url 			- передаем как должна выглядеть ссылка (для смещения используется переменная %offset%
		param $width		- (int) количество отображаемых страниц (ширина)
		param $arguments	- (mix array) дополнительные параметры, которые могут использоваться в пагинации
	*/
	public function set_paginate($point,$all_pages,$current,$template,$url,$arguments = null, $width = 9) {
		$recovery = $this->store(true);
		$tmp = "";		
		if (file_exists($template)) {
			if ($all_pages<=$width) {
				$start = 1;
				$end = $all_pages;
			} else {
				$start = ($current-ceil(($width-1)/2));
				if ($start<1) {
					$start = 1;
				}
				$end = $start+$width;	
				if ($end>$all_pages) {
					$end = $all_pages;
					$start = $all_pages-$width;
				}
			}
			
			$pages = array();
			for ($i=$start;$i<=$end;$i++) {
				$pages[] = array("number"=>$i,"current"=> ($i==$current) ? 1 : 0);
			}
			// Замена для читабельности
			$url_start = str_replace("%offset%", 1, $url);
			$url_end = str_replace("%offset%", $all_pages, $url);
			$url = str_replace("%offset%", "%pages['number']%", $url); 
			//
			$this->get_tpl($template);
			$this->set_tpl_array($arguments);
			$this->set_tpl('%url%',$url);
			$this->set_tpl('%url_start%',$url_start);
			$this->set_tpl('%url_end%',$url_end);
			$this->set_tpl('%pages%',$pages);			
			$this->set_tpl('%max%',$end);
			$this->set_tpl('%min%',$start);
			$this->set_tpl('%all_pages%',$all_pages);
			$this->tpl_parse();
			$tmp = $this->template;			
		}
		$this->restore($recovery);
		$this->template = str_replace($point, $tmp, $this->template);
	}

    public function tpl_parse() {
		$e = $this->recursiveSplit($this->template);
		$i = 0;
		// сначала обрабатываем все foreach
		// Тут ищем в массиве по значению ключа foreach
		$w = array_filter($e, function($k) use ($parent) {				
			return $k['foreach'] == 1;
		});
		$foreach_array = array_keys($w);
		foreach ($foreach_array AS $index_arr) {
			$element = $e[$index_arr];
			// Тут надо извлечь только тело цикла
			$v = $this->extract_body_foreach($element['expression']);			
			// Для всех элементов полученого массива прогоняем цикл
			if (!empty($v['body']) && is_array($v['array'])) {
				$foreach_temp = "";
				foreach ($v['array'] AS $item) {					
					$temp = $v['body'];					
					$new_foreach = $this->recursiveSplit($temp);					
					$j = 0;
					$n_data = array_merge($this->vars,array("{$v['array_index']}"=>$item));
					while ($j<COUNT($new_foreach)) {		
						$foreach_eval = $this->checkExpression($new_foreach[$j]["expression"],$n_data);						
						if ($new_foreach[$j]['parent']!=0) {
							$parent = $new_foreach[$j]['parent'];
							// Тут ищем в массиве по значению ключа id - TODO попробовать потом другие самописные функции
							$w = array_filter($new_foreach, function($k) use ($parent) {				
								return $k['id'] == $parent;
							});
							$key = array_keys($w);
							$key = $key[0];				
							// Этот элемент будет один			
							// Делаем в родительском элементе - замену дочернего-вычисленного			
							$new_foreach[$key]['expression'] = str_replace($new_foreach[$j]["etalon"],$foreach_eval,$new_foreach[$key]['expression']);
						} else {
							// В самом элементе заменяем вычисленное значение
							$new_foreach[$j]['expression'] = str_replace($new_foreach[$j]["expression"],$foreach_eval,$new_foreach[$j]['expression']);
							// И заменяем в самом шаблоне
							$temp = str_replace($new_foreach[$j]['etalon'], $foreach_eval, $temp);
						}
						$j++;
					}					
					// ищем оставшиеся переменные в шаблоне
					$pattern_var = "#(%\@*[a-zA-Zа-яА-ЯёЁ,\[,\],\',\",_,\-,\d]+%)#";
					preg_match_all($pattern_var, $temp, $matches);					
					// Пробегаемся по ним, заменяя их значением
					foreach($matches[1] as $find) {						
						$temp = str_replace($find, $this->get_value($find,$n_data), $temp);
					}
					$foreach_temp .= $temp;
				}				
			}
			$e[$index_arr]['expression'] = $foreach_temp;
		}
		
		// обработка всех вложенных и нет, условий и других операторов		
		while ($i<COUNT($e)) {		
			// Учитываем, что для foreach элементов вычислено все уже
			$eval = ($e[$i]['foreach']!=1) ? $this->checkExpression($e[$i]["expression"]) : $e[$i]["expression"];// 
			if ($e[$i]['parent']!=0) {
				$parent = $e[$i]['parent'];
				// Тут ищем в массиве по значению ключа id - TODO попробовать потом другие самописные функции
				$w = array_filter($e, function($k) use ($parent) {				
					return $k['id'] == $parent;
				});
				$key = array_keys($w);
				$key = $key[0];				
				// Этот элемент будет один			
				// Делаем в родительском элементе - замену дочернего-вычисленного			
				$e[$key]['expression'] = str_replace($e[$i]["etalon"],$eval,$e[$key]['expression']);
			} else {
				// В самом элементе заменяем вычисленное значение
				$e[$i]['expression'] = str_replace($e[$i]["expression"],$eval,$e[$i]['expression']);
				// И заменяем в самом шаблоне
				$this->template = str_replace($e[$i]['etalon'], $eval, $this->template);
			}
			$i++;
		}		
		// ищем оставшиеся переменные в глобальном шаблоне
		$pattern_var = "#(%\@*[a-zA-Zа-яА-ЯёЁ,\[,\],\',\",_,\-,\d]+%)#";
		preg_match_all($pattern_var, $this->template, $matches);
		// Пробегаемся по ним, заменяя их значением
		foreach($matches[1] as $find) {			
			$this->template = str_replace($find, $this->get_value($find), $this->template);
		}		
	}
}