<?php
// 蠡�������� �� 14.01.2009 � �����প�� ������⢥���� if
// up 22.12.2016

class template {
    public $vars;
    public $template;
	
	function __construct() {
		// ����塞 �� �� ᮧ�����
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
	
	// �����頥� ��ப� $replace_str  � ���ன �����⢫��� ������ ����� ��ࠬ��� �� ��� ���祭��
	// �᫨ ��।��� $key � $arr_key  - � � ��ப� ��� ��ࠬ��� �㤥� �������� �� ����� ���ᨢ� $arr_key
	// � ���祬 $key
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
	
	// set_tpl_list(���ᨢ ���祭��, ��� 蠡���� tpl, ��� ��६����� ��� �������, ���ᨢ ��� �������)
	// set_tpl_list($all,"list.tpl","mode",array("1"=>"䨪�஢����� ���祭�� 1","2"=>"䨪�஢����� ���祭�� 2"))
	// ᮮ⢥��⢥��� %mode% � 蠡���� �㤥� ������� � ����ᨨ���� �� ⮣� 1 ��� 2 ᮮ� ⥪�⮬
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
					// ��� ��� if ���ᨬ
					while (stristr($replace_tmp, '{|if')) {
						$aaa="";
						$i++;
						$ifstroka = stristr($replace_tmp, '{|if');
						$ifstroka = substr($ifstroka,0,strpos($ifstroka,'|}')+2);
						$aaa = explode('|',$ifstroka);
						$f = $aaa[2];						
						// ���������� �� 27/02/2017						
						if ($select!=null && is_array($select)) {
							$slct = array_keys($select);
							if ($f==$slct[0]) {
								$compare = $select[$f];
							}														
							// ����� ᥫ��� - �ࠢ������ � ���
						} else {
							$compare = $this->replace_element($aaa[3],$aaa[3],$varlist,false,$prd,$lst);
							// �������, १���� � 祬 �ࠢ������ - ⥯��� ����� ���� � ��६����� �� ⮣� �� ���ᨢ�							
						}
						//
						if ($varlist[$f] == $compare) {
							$replace_tmp = str_replace($ifstroka, $aaa[4], $replace_tmp);
						} else {
							$replace_tmp = str_replace($ifstroka, $aaa[5], $replace_tmp);
						}
					}  						
					// �த� ��� ���        					
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
        // ��� ��� if ���ᨬ
        $replace_tmp = $this->template;		
        while (stristr($replace_tmp, '{|if')) {
			$aaa='';
			$i++;
			$ifstroka = stristr($replace_tmp, '{|if');
			$ifstroka = substr($ifstroka,0,strpos($ifstroka,'|}')+2);
			$aaa = explode('|',$ifstroka);
	        $f="%{$aaa[2]}%";
			// �������, १���� � 祬 �ࠢ������ - ⥯��� ����� ���� � ��६����� �� ⮣� �� ���ᨢ�
			$compare = $this->replace_element($aaa[3],$aaa[3],$this->vars);			
	     	if ($this->vars[$f] == $compare) {
				$this->template = str_replace($ifstroka, $aaa[4], $this->template);
			} else {
				$this->template = str_replace($ifstroka, $aaa[5], $this->template);
			}
			$replace_tmp = $this->template;
		}  
		// �����塞 ��।���� ���� �� ���祭�ﬨ
		foreach($this->vars as $find => $replace) {
			$this->template = str_replace($find, $replace, $this->template);
    	}
	}
}