<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo get_class($exception) ?></title>
	<?php include __DIR__ . '/_headers2.php' ?>
	<?php self::includeHeaders() ?>
</head>
<body class="LemmonDebugger">
	
	<header id="header">
		<h1><?php echo get_class($exception) ?></h1>
		<h2><?php echo $exception->getMessage() ?></h2>
	</header>
	
	<section>
		<h3>Source File</h3>
		<a class="LemmonDebugerExpander" href="#">
			<strong>File:</strong> <?php echo $exception->getFile() ?>
			<strong>Line:</strong> <?php echo $exception->getLine() ?>
			<span class="more">&hellip;</span>
		</a>
		<span class="collapse">
			<pre class="LemmonDebuggerDump source"><?php echo self::printSource($exception->getFile(), $exception->getLine()) ?></pre>
		</span>
	</section>
	<section>
		<h3>Call Stack</h3>
		<ol>
			<?php foreach ($exception->getTrace() as $i => $trace): ?>
			<li>
				<a class="LemmonDebugerExpander" href="#">
					<strong>File:</strong> <?php echo $trace['file'] ?>
					<strong>Line:</strong> <?php echo $trace['line'] ?>
					<em style="color:#268bd2"><?php echo $trace['class'] . $trace['type'] . ($trace['function'] ? $trace['function'] . '()' : '') ?></em>
					<span class="more<?php if (!$i): ?> hide<?php endif ?>">&hellip;</span>
				</a>
				<span class="collapse<?php if (!$i): ?> expand<?php endif ?>">
					<pre class="LemmonDebuggerDump source"><?php echo self::printSource($trace['file'], $trace['line']) ?></pre>
					<?php if ($trace['args']): ?>
					<h4>Arguments</h4>
					<?php echo self::dumpArray($trace['args']) ?>
					<?php endif ?>
				</span>
			</li>
			<?php endforeach ?>
		</ol>
	</section>
	<?php include __DIR__ . '/_dump_general.php' ?>
	
</body>
</html>