<?php
class Model {
	private $realization;
	
	public function __construct( $realization ) {
		$this->realization = $realization;
	}
	
	public function getModelElement ($root = null, $stringData = false, $reversiveModelId="") {
		$returnValues = array();
		$this->realization->ready();
		$items = $this->realization->getElement($root);
		
		$logicItemId = 1;
		foreach ($items as $item) {
			$rootId = $reversiveModelId == "" ? (string)$logicItemId : $reversiveModelId . "." . $logicItemId;
			if ($item['isElement'] == false) {
				if ($stringData) $returnValues[] = array (
					"id"      => $rootId,
					"data"    => $item['stringData'],
					"root_id" => $item['id']
				);
				else $returnValues[] = array (
					"id"      => $rootId,
					"data"    => $item['data'],
					"root_id" => $item['id']
				);
			}
			else {
				if ($stringData) $returnValues[] = array (
					"id"      => $rootId,
					"data"    => $item['stringData'],
					"root_id" => $item['id']
				);
				else $returnValues[] = array (
					"id"      => $rootId,
					"data"    => $item['data'],
					"root_id" => $item['id']
				);
				
				$childElements = $this->getModelElement(
					$item['id'],
					$stringData,
					$rootId
				);
				foreach ($childElements as $childElement) {
					$returnValues[] = array (
						"id"      => $childElement['id'],
						"data"    => $childElement['data'],
						"root_id" => $item['id']
					);
				}
			}
			$logicItemId++;
		}
		
		if ($reversiveModelId == "") $this->realization->finish();
		return $returnValues;
	}
}

abstract class Realization {
	public $isReady = false;
	
	public function getElement ($root = null) {
		// ... код реализации
	}
	
	public function ready () {
		if (!$this->isReady) {
			$this->isReady = true;
			// ... Запрос и запись это в кэш
		}
	}
	
	public function finish () {
		$this->isReady = false;
	}
}
?>