<h2>Вложенный foreach</h2>

{|foreach|%list1%|
	<h3>%list1['id']%</h3>
	<ul>
	{|foreach|%list2%|<li>%list2['id']% - in %list1['id']%|}
	</ul>
|}	