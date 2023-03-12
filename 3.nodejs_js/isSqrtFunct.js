function isInt (value) {  // Функция проверки целочисленного типа
	if (typeof(value) == 'number') {
		return parseInt(value) == value;
	}
	else {
		return false;
	}
}

function isSqrt (value) {
	if (isInt(value)) {  // Проверим тип
		let counter = 1;
		while (counter * counter <= value) {
			if (counter * counter == value) {
				return true;
			}
			counter++;
		}
		return false;
	}
	else {
		throw new Error("Wrong type of first argument: required integer");
	}
}