# Задача
Есть модель представленная в виде дерева (например какой-то каталог), каждый элемент каталога может иметь потомков, по сути связь модели с самой собой  
- необходимо описать как хранить такие связи в БД  
- реализовать метод для получения списка всех потомков в виде дерева  
- реализовать метод для получения всех потомков в виде плоского списка  
подумать о том как эффективно реализовать такие запросы  
желательно сделать используя php фреймворк Laravel  
Если  вы не знакомы с Laravel, то на чистом php, используя pdo.  
  
*Примечание: запрещается использовать сторонние пакеты для решения задачи хранения и извлечения дерева каталога.*  
Подразумевается вывод всех потомков в порядке вложенности, пример:   
- уровень 1  
- потомок 1.1  
- потомок 1.2  
- потомок 1.2.1  
- уровень 2  
- потомок 2.1  
...  
при этом дополнительно метод может обрабатывать параметр для указания родителя - и вывода всех его потомков, если параметр не указан - то выводятся все записи *(это условие не обязательно)*  
# Постановка задачи
Необходимо написать страницу на php, которая будет выводить информацию из модели о его родителях и наследниках. При этом, дополнительно предусмотреть возможность вывода наследников отдельного родителя, введённого в параметрах *(это условие не обязательно)*
# Реализация

## Разработка модели
Поскольку вывод информации происходит из абстрактной модели, нам необходимо сделать собственную реализацию модели. Далее, обеспечить модульность, чтобы можно было менять практическую реализацию модели *(например, заменить запросы к БД на запросы к файловой системе и т.д.)*.  
При разработки модели было принято решение реализовать класс для модели и для её реализации:
```php
abstract class Realization {
	public function getElement ($root = null) {
		// ... код реализации
		/*
			Возвращает объект:
			{
				isElement  : bool             (Является ли объект элементом или же группой (имеет ли потомков))
				data       : string/etc.      (Информация об объекте в оптимальном для реализации виде)
				stringData : string           (Информация об объекте в текстовом виде)
				id         : int/string/etc.  (Индекс элемента)
			}
			
			Все возвращённые объекты находятся на одном уровне:
			==========================
			element 1
			-element 1.1
			--element 1.1.2
			-element 1.2
			element 2
			==========================
			
			При getElement(); вернёт
			element 1
			element 2
			
			При getElement("element 1"); вернёт элементы element 1 на нижележащем уровне:
			element 1.1
			element 1.2
			
			При getElement("element 1.1"); вернёт элементы element 1.1 на нижележащем уровне:
			element 1.1.2
			
			И т.д.
		*/
	}
}

class Model {
	private $realization;
	
	public function __construct( $realization ) {
		$this->realization = $realization;
	}
	
	public function getModelElement ($root = null, $stringData = true) {
		$returnValues = array();
		$items = $this->realization->getElement($root);
		
		foreach ($items as $item) {
			if ($item->isElement) {
				if ($stringData) $returnValues[] = $item['stringData'];
				else $returnValues[] = $item['data'];
			}
			else {
				if ($stringData) $returnValues[] = $item['stringData'];
				else $returnValues[] = $item['data'];
				
				$childElements = $this->getModelElement($item->id, $stringData);
				foreach ($childElements as $childElement) {
					$returnValues[] = $childElement['data'];
				}
			}
		}
		
		return $returnValues;
	}
}
```
Как можно понять, <Model.object>->getModelElement(); является рекурсивным методом и из этого вытекают как плюсы, так и минусы.  
Главным минусом является то, что при запросах к БД, у нас вместо одного запроса, который вернёт остальные элементы будет несколько запросов к БД. Поэтому, при первой инициализации экземпляра класса Realization у нас должен происходить только один запрос. В связи с чем, мы меняем код в <Model.object>->getModelElement(); а также его входные параметры.  
Вдобавок ко всему, нам необходимо найти способ индексации элемента, чтобы его индекс был привязан к родительскому элементу.
```php
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
```

Как мы видим, для класса Realization появились новые методы. Метод ready будет означать, что реализации необходимо совершить запрос и записать его в кэш, чтобы при помощи метода getElement мы могли вместо отправки запросов отправлять нужные данные из кэша. Реализуем это:  
```php
abstract class Realization {
	public $isReady = false;
	
	public function getElement ($root = null) {
		// ... код реализации
	}
	
	public function ready () {
		if (!$this->isReady) {
			$this->isReady = true;
			// ... Запрос и запись в кэш
		}
	}
	
	public function finish () {
		$this->isReady = false;
	}
}
```
Для метода ready мы реализовали механизм проверки, чтобы кэш не писался несколько раз, поскольку данный метод, в виду рекурсивности метода <Model.object>->getModelElement(), будет вызываться несколько раз.  
Теперь ближе к конкретной реализации: будем использовать pdo в нашей реализации. Будем использовать базу данных MySQL в виду того, что прописывать адрес сервера MySQL будет легче, чем путь до файла БД SQLite3 *(Однако ввиду специфики pdo и модульности нашей системы, какая БД и какая модель будет использоваться не играет значимой роли)*
```php
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
```
Теперь подробнее про структуру базы данных: 
id (int) = NULL        | pk, uq, ai, nn | Ключ записи
legatee (int) = NULL   |                | Информация о родительской записи (NULL, если нет родительской записи)
data(VARCHAR(64)) = "" | nn             | Запись в текстовом виде.

SQL-Запрос *(Полный дамп в папке "sql")*:
```sql
CREATE TABLE `realizations-db`.`model-table` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `legatee` INT NULL,
  `data` VARCHAR(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) VISIBLE
);
```
Далее, после того, как мы определились со структурой нашей модели, мы можем закончить с реализацией класса RealizationMySQL:
```php
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
```
## Разработка основной программы
Далее, вся реализация была помещена в папку logic и разбита на два файла: model.php *(Базовый класс интерфейса модели и абстрактный реализации)* и mysql-realization.php *(Класс реализации MySQL)*.  
Далее можно приступить к непосредственной реализации программы: в корне мы создадим два файла: method.php и index.php.  

В method.php будет расположена функция, принимающая в себя параметр рассматриваемого элемента (либо принимающий факт его отсутствия (null)) и возвращающая ответ. Отдельно созданный файл нужен, чтобы в случае надобности применения данных из данной программы с заданными параметрами мы могли их импортировать как модуль программы, а не делать отдельно HTTP-запрос к странице.
```php
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
```

Далее, мы вызываем данную функцию из index.php:
```php
/* Отключим WARNING */
error_reporting(E_ERROR | E_PARSE);
include "method.php";

die(output($_GET['root']));
```
Проект завершён.