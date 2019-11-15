**Простой шаблонизатор Template**
версия 2.1

Очень простой шаблонизатор (уже не очень) на php. Всего один подключаемый класс
* /classes/template.class.php
В папке /template  находятся шаблоны (может быть любое другое место)

**Публичные методы класса:**

* get_tpl($name string) полчает шаблон из файла, возвращает true/false в зависисмости от того, найден шаблон иил нет
* set_tpl($key,$var string) задает переменную для её замены
* set_tpl_array($array array)  то-же самое что и предыдущее, только для массива переменных (массив элементов в виде - "ключ"=>"значение")
* set_paginate($point,$all_pages,$current,$template,$url,$arguments = null, $width = 9) - отображает пагинацию в точке %point%
* tpl_parse()- выполняет замену в шаблоне и формирует окончательный результат

**Пример вызова**

```php
<?php
// подключение необходимых классов
include_once("./../classes/template.class.php");	
$parse = new template($debug); // При заданном true - отключает автоочистку резлуьтата от перевода строк и табуляций, по умолчанию false - очистка присутствует
$parse->get_tpl("./../template/ex1.tpl");	
$parse->set_tpl('%var1%','One');
$parse->set_tpl('%var2%','Two');
$parse->set_tpl('%arr%',array("name'=>"Ivan","age"=>24));
$parse->tpl_parse();
echo $parse->template;
```

**Синтаксис**

* Обычный вывод переменных - %имя_переменной%
* Вывод элемента массива -  %a[0]% для числового, %mas['name']% или %mas["title"] для ассоциативного
* Экранирование: 
* @  -  через htmlspecialchars, %@a% или %@a['text']% для элементов массива
* @@ - для экранирования js (прогоняет вывод через json_encode - %@@a%, %@@sw['volume']% и.т.д
* !  - для экранирования значения заключенного в %%, в основном для foreach, синтаксис: %!arr['name']%, где значение arr['name'] = %переменная%
* \#  - для сохранения форматирование строки, применяется функция nl2br, пример  %#message%
* Проверка на пустоту - empty, {|empty|%элемент%|true|false|} - предпочтительнее использовать чем {if|%элемент%||true|false|}
* Краткий формат условия {|if|%имя_переменная_без%|с чем сравниваем|это выводим если равны|тут выводим если не равны|} так-же можно использовать имена элементов массивов, и сравнивать с элементами переменных, например {|if|%a%|%b%|true|false|}|
* Расширеное условие - {|if|%a%|операция|%b%|true|false|}, где операция = {=,!=,>,<,>=,=>,<=,=<} дсотупные операции сравнения
* Цикл foreach  - перебирает все значения массива {|foreach|%массив%|тело цикла с параметрами %массив[индекс]%|} индекс - определяеся так же по праилам вывода элементов массива
* В цикле foreach добавлена переменная с порядковым номером элемента - %array['foreach_position']%
* Включение подшаблонов в шаблоны - @include(/path/to/tpl)@ - включает указанный файл в шаблон (поддерживается вложенность)
* Добавлена конструкция для вывода деревьев {|tree|%массив%|%начало оформление ветви%|%окончание оформления ветви%|%начало оформление листьев%|%окончание оформления листьев%|%строка-лист%|}
	

** Что планируется в будущем **

Расширение синтаксиса:

* Самое сложное и главное - неограниченная вложенность циклов

Переработка/доработка класса:
* добавить логирование (вывод ошибок на экран/в файл)
* переработка кода по ходу реализации функций

