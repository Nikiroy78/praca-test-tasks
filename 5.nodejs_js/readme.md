# Блок заданий "Задание, Java Script, Node JS:" (5)
5. Попробуйте проанализировать код и понять что делает следующая функция на javascript, т.е. что получит функция call_back в первом параметре.
```javascript
function func(arr, call_back) {
	
	if(!Array.isArray(arr) || arr.some(it => parseInt(it)!=it || it < 0))
		call_back(null, "Неверный формат входящих данных, должен быть массив положительных чисел");
	
	let res = [];
	const f = (val) => {
		res.push(val);
		if(res.length==arr.length)
			call_back(res);
	}
	
	for(let i = 0; i < arr.length; i++) {
		setTimeout(f, arr[i], arr[i]);
	}
}
```
**5\*** *(задача повышенной сложности, тем кто сможет)* Переписать эту функцию на использование Promise и/или async-await.
