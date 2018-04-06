<h2>Довольно интересный пример</h2>

{|foreach|%list%|<b>%name%</b> {|if|%list['name']%|tv|This is TVset + a = %a%| %list['name']% no 23|}<br/>|}

{|if|%a%|!=|0|
{|foreach|%arr1%|%name% |}
|no data|}

Ещё: %@vars%
<br>А это не будет - %vars%
<hr>
Ещё: %@ars['text']%
<br>А это не будет - %ars["text"]%
