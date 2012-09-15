<?php
if ($value===null) {
	echo 'NULL';
} elseif (is_string($value)) {
	echo '"' . $value . '" ' . strlen($value);
} elseif (is_int($value) or is_float($value)) {
	echo $value;
} elseif (is_bool($value)) {
	echo $value ? 'true' : 'false';
} elseif (is_array($value)) {
	var_export($value);
} else {
	echo '!! Unknown value !!';
}
