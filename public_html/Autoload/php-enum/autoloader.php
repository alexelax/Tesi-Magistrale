<?php
spl_autoload_register(function ($class_name) {
	$preg_match = preg_match('/^MyCLabs\\\Enum\\\/', $class_name);

	if (1 === $preg_match) {
		$class_name = preg_replace('/\\\/', '/', $class_name);
		$class_name = preg_replace('/^MyCLabs\\/Enum\\//', '', $class_name);
		require_once(__DIR__ . '/' . $class_name . '.php');
	}
});