# Блок заданий "Задание, Java Script, Node JS:" (4)
4. Напишите на js функцию, которая принимает два аргумента: массив из уникальных целых чисел и сумму в виде целого числа. 
Если сумма двух любых чисел массива из аргумента равна числу, которое приходит вторым аргументом, 
функция должна вернуть новый массив из этих двух чисел в любом порядке. 
Если решения нет, вернуть пустой массив. Текущее число само с собой складывать нельзя.
Пример входных данных:
```javascript
array = [3, 5, -4, 8, 11, 1, -1, 6]
targetSum = 10
```
На выходе:
```javascript
[-1, 11]
```
или 
```javascript
[11, -1]
```
так как
```javascript
-1 + 11 = 10 = targetSum
```
Код написанной функции прокомментируйте.
## Алгоритм
Алгоритм будет заключаться в переборе всех значений массива с вычетом из входного параметра с целью поиска полученного значения в массиве:
```
: [3, 5, -4, 8, 11, 1, -1, 6], 10
---------------------------------
>> 10 - 3 = 7      | false
>> 10 - 5 = 5      | false
>> 10 - (-4) = -14 | false
>> 10 - 8 = 2      | false
>> 10 - 11 = -1    | true
---------------------------------
<< [11, -1]
```
## Реализация
```javascript
function isInt (value) {  // Функция проверки целочисленного типа
	if (typeof(value) == 'number') {
		return parseInt(value) == value;
	}
	else {
		return false;
	}
}

function isArray (value, typeCheck=null) {  // Функция проверки типа массива
	if (!Array.isArray(value)) {
		return false;
	}
	else if (typeCheck == null) {
		return true
	}
	else {
		for (let i in value) {
			if (!typeCheck(value[i])) {
				return false;
			}
		}
		return true;
	}
}

function getTwoSummands (array, findValue) {
	if (isInt(findValue) && isArray(array, isInt)) {  // Вы же ещё помните, что javascript - язык с динамической, а не статической типизацией данных?
		let calculatedVal;
		for (let i in array) {
			calculatedVal = findValue - array[i];
			if (array.indexOf(calculatedVal) != -1 && array.indexOf(calculatedVal) != i) {  // Проверим есть ли значение в массиве и имеет ли данное значение рассматриваемый индекс.
				return [array[i], calculatedVal];
			}
		}
		return [];
	}
	else {
		throw new Error("Wrong arguments: <Array(int)>, <int>");
	}
}
```