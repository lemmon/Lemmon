<section>
	<a class="LemmonDebugerExpander" href="#">
		<h3>HTTP Request</h3>
		<span class="more">&hellip;</span>
	</a>
	<span class="collapse">
		<h4>Headers</h4>
		<?php echo self::dumpArray($_SERVER) ?>
		<h4>GET</h4>
		<?php echo self::dumpArray($_GET) ?>
		<h4>POST</h4>
		<?php echo self::dumpArray($_POST) ?>
		<h4>COOKIE</h4>
		<?php echo self::dumpArray($_COOKIE) ?>
	</span>
</section>
<section>
	<a class="LemmonDebugerExpander" href="#">
		<h3>HTTP Response</h3>
		<span class="more">&hellip;</span>
	</a>
	<span class="collapse">
		<h4>Headers</h4>
		<?php echo self::dumpArray(apache_response_headers()) ?>
	</span>
</section>
