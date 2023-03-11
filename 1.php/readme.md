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
class Realization {
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
		while (true) {
			$items = $this->realization->getElement($root);
			foreach ($items as $item) {
				if ($item->isElement) {
					if ($stringData) $returnValues[] = $item->stringData;
					else $returnValues[] = $item->data;
				}
				else {
					if ($stringData) $returnValues[] = $item->stringData;
					else $returnValues[] = $item->data;
					
					$childElements = $this->getModelElement($item->id, $stringData);
					foreach ($childElements as $childElement) {
						if ($stringData) $returnValues[] = $childElements->stringData;
						else $returnValues[] = $childElements->data;
					}
				}
			}
		}
		
		return $returnValues;
	}
}
```
Как можно понять, <Model.object>->getModelElement(); является рекурсивным методом и из этого вытекают как плюсы, так и минусы.  
Главным минусом является то, что при запросах к БД, у нас вместо одного запроса, который вернёт остальные элементы будет несколько запросов к БД. Поэтому, при первой инициализации экземпляра класса Realization у нас должен происходить только один запрос. В связи с чем, мы меняем код в <Model.object>->getModelElement(); а также его входные параметры.
```php
class Model {
	private $realization;
	
	public function __construct( $realization ) {
		$this->realization = $realization;
	}
	
	public function getModelElement ($root = null, $stringData = true) {
		$returnValues = array();
		$this->realization->ready();
		while (true) {
			$items = $this->realization->getElement($root);
			foreach ($items as $item) {
				if ($item->isElement) {
					if ($stringData) $returnValues[] = $item->stringData;
					else $returnValues[] = $item->data;
				}
				else {
					if ($stringData) $returnValues[] = $item->stringData;
					else $returnValues[] = $item->data;
					
					$childElements = $this->getModelElement($item->id, $stringData);
					foreach ($childElements as $childElement) {
						if ($stringData) $returnValues[] = $childElements->stringData;
						else $returnValues[] = $childElements->data;
					}
				}
			}
		}
		
		$this->realization->finish();
		return $returnValues;
	}
}
```

Как мы видим, для класса Realization появились новые методы. Метод ready будет означать, что реализации необходимо совершить запрос и записать его в кэш, чтобы при помощи метода getElement мы могли вместо отправки запросов отправлять нужные данные из кэша. Реализуем это:  
```php
class Realization {
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
```
Для метода ready мы реализовали механизм проверки, чтобы кэш не писался несколько раз, поскольку данный метод, в виду рекурсивности метода <Model.object>->getModelElement(), будет вызываться несколько раз.  
Теперь ближе к конкретной реализации: будем использовать pdo в нашей реализации. Будем использовать базу данных MySQL в виду того, что прописывать адрес сервера MySQL будет легче, чем путь до файла БД SQLite3 *(Однако ввиду специфики pdo и модульности нашей системы, какая БД и какая модель будет использоваться не играет значимой роли)*
```php
class RealizationMySQL {  // Да, я изменил имя класса, чтобы было яснее какая именно это реализация.
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
```