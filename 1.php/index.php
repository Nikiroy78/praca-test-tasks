<?php
error_reporting(E_ERROR | E_PARSE);
include "./logic/mysql-realization.php";

function output () {
	$model = new Model(
		new RealizationMySQL("127.0.0.1", "root", "root", "realizations-db")
	);
	$modelElements = $model->getModelElement();
	$outputList = array();
	
	foreach ($modelElements as $item) {
		$outputList[] = $item['id'] . ' ' . $item['data'];
	}
	
	return implode("\n", $outputList);
}

die(output());
?>