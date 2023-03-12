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