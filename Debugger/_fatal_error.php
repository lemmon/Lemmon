<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Fatal Error</title>
	<?php include __DIR__ . '/_headers2.php' ?>
	<?php self::includeHeaders() ?>
</head>
<body class="LemmonDebugger">
	
	<header id="header">
		<h1>Fatal Error</h1>
		<h2><?php echo $error['message'] ?></h2>
	</header>
	
	<section>
		<h3>Source File</h3>
		<span>
			<strong>File:</strong> <?php echo $error['file'] ?>
			<strong>Line:</strong> <?php echo $error['line'] ?>
		</span>
		<span>
			<pre class="LemmonDebuggerDump source"><?php echo self::printSource($error['file'], $error['line']) ?></pre>
		</span>
	</section>
	<?php include __DIR__ . '/_dump_general.php' ?>
	
</body>
</html>