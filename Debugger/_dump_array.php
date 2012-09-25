<table class="LemmonDebuggerDump">
	<thead>
		<tr>
			<th colspan="2"><span class="mark">array</span><span class="note">(<?php echo count($array) ?>)</span></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($array as $key => $value): ?>
		<tr>
			<th><?php echo (is_numeric($key) ? '#' : '$') . $key ?></th>
			<td><?php echo self::loop($value) ?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
