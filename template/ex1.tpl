<!DOCTYPE HTML>
<html lang="ru" class="no-js">
	<head>
		<title>Example 1</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">		
	</head>
	<body>
		<p>This is %var1% example</p>
		<p>View is %var2% example </p>	
		{|if|var1|First|<p>Is realy First!!!</p>||}
		escape 1 = %@var2%
		escape 2 = %@@var2%
	</body>
</html>