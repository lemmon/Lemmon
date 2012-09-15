<table class="LemmonDebuggerDump">
	<tbody>
		<?php if (is_array($data)): ?>
		<?php foreach ($data as $key => $val): ?>
		<tr>
			<th><?php if (is_numeric($key)): ?><?php echo $key ?><?php else: ?>$<?php echo $key; ?><?php endif ?></th>
			<td><?php $value=$val; include __DIR__ . '/_dump_value.php'; ?></td>
		</tr>
		<?php endforeach ?>
		<?php else: ?>
			<tr>
				<td><?php $value=$data; include __DIR__ . '/_dump_value.php'; ?></td>
			</tr>
		<?php endif ?>
	</tbody>
</table>
