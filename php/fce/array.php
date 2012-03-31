<?php

/**
 * without iterator
 * @param array $keysarray
 * @param array $valuesarray
 * @param type $value
 * @return type
 */
function arrayCombine(array $keysarray, array $valuesarray, $value = NULL)
{
	$diff = count($keysarray) - count($valuesarray);

	if ($diff > 0) {
		$valuesarray = array_merge($valuesarray, array_fill(0, $diff, $value));
	}

	return array_combine($keysarray, $valuesarray);
}