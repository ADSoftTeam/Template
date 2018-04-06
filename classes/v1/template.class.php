<?php
// шаблонизатор от 14.01.2009 с поддержкой множественных if
// up 22.12.2016

class template {
    public $vars;
    public $template;
	
	function __construct() {
		// обнуляем все при создании
		$this->vars = array();
		$this->template = "";
	}

	private function replace_arg($arg,$str) {
		if (!is_null($arg)) {
			foreach ($arg as $a) {							
				$replace_tmp = str_replace($a['var'], $a['value'], $str); 
			}
		} else {
			$replace_tmp = $str;
		}
		return $replace_tmp;
	}

    public function get_tpl($tpl_name) {
		if (empty($tpl_name) || !file_exists($tpl_name)) {          	
			return false;
		} else {
			$this->template ='';
			$this->template  = file_get_contents($tpl_name);			
			return true;
        }
	}

    public function set_tpl($key,$var) {
		$this->vars[$key] = $var;
	}
	
	public function set_tpl_array($array) {
		if (is_array($array)) {
			$this->vars = $array;
		}
	}
	
	// Возвращает строку $replace_str  в которой осуществлена замена имени параметра на его значение
	// Если переданы $key и $arr_key  - то в строке имя параметра будет заменено на элемент массива $arr_key
	// с ключем $key
	// replace_str, param, key 	- string
	// arr, arr_key 			- array
	
	private function replace_element($replace_str,$param,$arr,$use=false,$key=null,$arr_key=array()) {		
		//print_r(array($replace_str,$param,$arr));
		if (isset($arr[$param])) {
			$search = $use ? "%$param%"	: $param;
			if (COUNT($arr_key)>0 && $param==$key && is_array($arr_key)) {				
				$replace_str = str_replace($search, $arr_key[$arr[$key]], $replace_str);
			} else {			
				$replace_str = str_replace($search, $arr[$param], $replace_str);			
			}
		}
		return $replace_str;
	}	
	
	// set_tpl_list(массив значений, имя шаблона tpl, имя переменной для подмены, массив для подмены)
	// set_tpl_list($all,"list.tpl","mode",array("1"=>"фиксированное значение 1","2"=>"фиксированное значение 2"))
	// соответственно %mode% в шаблоне будет заменен в зависиимости от того 1 или 2 соотв текстом
	public function set_tpl_list($mylist,$name,$select=null,$prd=null,$lst=array()) {
		if (empty($name) || !file_exists($name)) {          	
			return false;
		} else {
			$this->template ='';
			$tmp ='';
			$n=0;
			if (count($mylist)!=0) {
				$tmp = file_get_contents($name);
				$cur_col=0;
				foreach ($mylist as $varlist) {					
					$cur_col++;
					$n++;					
					$replace_tmp = $tmp;
					// Тут для if парсим
					while (stristr($replace_tmp, '{|if')) {
						$aaa="";
						$i++;
						$ifstroka = stristr($replace_tmp, '{|if');
						$ifstroka = substr($ifstroka,0,strpos($ifstroka,'|}')+2);
						$aaa = explode('|',$ifstroka);
						$f = $aaa[2];						
						// дополнения от 27/02/2017						
						if ($select!=null && is_array($select)) {
							$slct = array_keys($select);
							if ($f==$slct[0]) {
								$compare = $select[$f];
							}														
							// задан селект - сравниваем с ним
						} else {
							$compare = $this->replace_element($aaa[3],$aaa[3],$varlist,false,$prd,$lst);
							// новинка, результат с чем сравниваем - теперь может быть и переменной из того же массива							
						}
						//
						if ($varlist[$f] == $compare) {
							$replace_tmp = str_replace($ifstroka, $aaa[4], $replace_tmp);
						} else {
							$replace_tmp = str_replace($ifstroka, $aaa[5], $replace_tmp);
						}
					}  						
					// вроде для всех        					
					$param = array_keys($varlist);
					for ($i=0;$i<COUNT($varlist);$i++) {						
						$replace_tmp = $this->replace_element($replace_tmp,$param[$i],$varlist,true,$prd,$lst);
						$replace_tmp = str_replace('%count_line%', $n, $replace_tmp);
					}
					$this->template  .= $replace_tmp;
				}				
			}			
			return true;
		}
	}
	
	public function set_tpl_pages($all,$cur,$elem_page,$cat,$templ1,$templ2,$width=5,$arg=null) {	
		if(empty($templ1) || !file_exists($templ1)) {          	
			return false;
		} else {
			$this->template ='';
			$tmp ='';
			$tmp = file_get_contents($templ1);
			$tmp_cur = file_get_contents($templ2);
			$n = 0;
			$start = ($cur-ceil(($width-1)/2));
			if ($start<1) {
				$start=1;
			}
			$end = $start+$width;	
			if ($end>$all) {
				$end = $all;
				$start = $all-$width;
			}

			if ($all<=$width) {
				$start = 1;
				$end = $all;
			}				
			
			if ($start>1) {
				$replace_tmp = $tmp;	
				$off = 0;
				$replace_tmp = str_replace('%offset%', $off, $replace_tmp);
				$replace_tmp = str_replace('%cat%', $cat, $replace_tmp); 
				$replace_tmp = str_replace('%page%', 1, $replace_tmp); 
				$replace_tmp = $this->replace_arg($arg,$replace_tmp);
				
				$this->template .=  $replace_tmp."...";
				$replace_tmp = $tmp;	
				$off = ($cur-2)*$elem_page;
				$replace_tmp = str_replace('%offset%', $off, $replace_tmp);
				$replace_tmp = str_replace('%cat%', $cat, $replace_tmp); 
				$replace_tmp = str_replace('%page%', '<<', $replace_tmp);
				$replace_tmp = $this->replace_arg($arg,$replace_tmp);
				$this->template .=  $replace_tmp."...";
			}
			for ($i=$start;$i<=$end;$i++) {
				if ($i == $cur) { 
					$replace_tmp = $tmp_cur;	
					$replace_tmp = str_replace('%page%', $i, $replace_tmp); 
				} else {
					$replace_tmp = $tmp;	
					$off = ($i-1)*$elem_page;
					$replace_tmp = str_replace('%offset%', $off, $replace_tmp);
					$replace_tmp = str_replace('%cat%', $cat, $replace_tmp); 
					$replace_tmp = str_replace('%page%', $i, $replace_tmp);
					$replace_tmp = $this->replace_arg($arg,$replace_tmp);								
				}
				$this->template .=  $replace_tmp;
			}	
			if ($start+$width<$all) {
				$replace_tmp = $tmp;	
				$off = ($cur)*$elem_page;
				$replace_tmp = str_replace('%offset%', $off, $replace_tmp);
				$replace_tmp = str_replace('%cat%', $cat, $replace_tmp); 
				$replace_tmp = str_replace('%page%', '>>', $replace_tmp);
				$replace_tmp = $this->replace_arg($arg,$replace_tmp);						
				$this->template .=  "...".$replace_tmp;
				$replace_tmp = $tmp;	
				$off = ($all-1)*$elem_page;
				$replace_tmp = str_replace('%offset%', $off, $replace_tmp);
				$replace_tmp = str_replace('%cat%', $cat, $replace_tmp); 
				$replace_tmp = str_replace('%page%', $all, $replace_tmp);
				$replace_tmp = $this->replace_arg($arg,$replace_tmp);						
				$this->template .=  "...".$replace_tmp;                   
			}
			return true;
		}
    }

    public function tpl_parse() {
		$i = 0;
        // Тут для if парсим
        $replace_tmp = $this->template;		
        while (stristr($replace_tmp, '{|if')) {
			$aaa='';
			$i++;
			$ifstroka = stristr($replace_tmp, '{|if');
			$ifstroka = substr($ifstroka,0,strpos($ifstroka,'|}')+2);
			$aaa = explode('|',$ifstroka);
	        $f="%{$aaa[2]}%";
			// новинка, результат с чем сравниваем - теперь может быть и переменной из того же массива
			$compare = $this->replace_element($aaa[3],$aaa[3],$this->vars);			
	     	if ($this->vars[$f] == $compare) {
				$this->template = str_replace($ifstroka, $aaa[4], $this->template);
			} else {
				$this->template = str_replace($ifstroka, $aaa[5], $this->template);
			}
			$replace_tmp = $this->template;
		}  
		// заменяем переданные ключи их значениями
		foreach($this->vars as $find => $replace) {
			$this->template = str_replace($find, $replace, $this->template);
    	}
	}
}