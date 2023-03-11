<?php
include "./logic/mysql-realization.php";

function output ($root = "") {
	$itemIerarchyId = "";  // Без этого параметра у нас будет выводиться с единицы, а не с номера элемента в общей иерархии
	
	$model = new Model(
		new RealizationMySQL("127.0.0.1", "root", "root", "realizations-db")
	);
	
	if ($root == "" || $root == null) {
		$root = null;
	}
	else {
		$modelElementsForSearch = $model->getModelElement(null, true);
		foreach ($modelElementsForSearch as $i) {
			if ($i['id'] == $root) {
				$itemIerarchyId = $i['id'];
				$root = $i['root_id'];
				break;
			}
		}
	}
	
	$modelElements = $model->getModelElement($root, true, $itemIerarchyId);
	$outputList = array();
	
	foreach ($modelElements as $item) {
		$outputList[] = $item['id'] . ' ' . $item['data'];
	}
	
	return implode("\n", $outputList);
}
?>