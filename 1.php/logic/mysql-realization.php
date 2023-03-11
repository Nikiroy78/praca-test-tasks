<?php
include "model.php";

class RealizationMySQL extends Realization {  // Создал класс, наследующий Realization, чтобы можно было выбирать при необходимости между реализациями.
	public $isReady = false;
	private $db;               // Объект базы данных pdo
	private $cache = array();  // Тот самый кэш
	
	public function __construct ($host, $user, $pass, $dbname) {
		$this->db = new PDO("mysql:host=". $host .";dbname=". $dbname, $user, $pass);
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	}
	
	private function hasLegatees ($elId) {  // Метод для определения есть ли у элемента наследники
		foreach ($this->cache as $item) {
			if ($item['legatee'] == $elId) return true;
		}
		return false;
	}
	
	public function getElement ($root = null) {
		$selectedItems = array();
		
		foreach ($this->cache as $item) {
			if ($item['legatee'] == $root) {
				$selectedItems[] = array(
					"isElement"  => $this->hasLegatees($item['id']),
					"data"       => $item['data'],
					"stringData" => $item['data'],
					"id"         => $item['id']
				);
			}
		}
		
		return $selectedItems;
	}
	
	public function ready () {
		if (!$this->isReady) {
			$this->isReady = true;
			// Запрос и запись в кэш
			$selectedItems = $this->db->query('SELECT * FROM `model-table`');
			$selectedItems->setFetchMode(PDO::FETCH_ASSOC);
			
			// Очистим кэш
			$this->cache = array();
			while($row = $selectedItems->fetch()) {
				$this->cache[] = $row;
			}
		}
	}
	
	public function finish () {
		$this->isReady = false;
	}
}
?>