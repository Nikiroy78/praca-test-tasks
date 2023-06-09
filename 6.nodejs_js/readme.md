# Блок заданий "Задание, Java Script, Node JS:" (6)
6. Установите node.js (https://nodejs.org/en/, версия LTS например, хотя не важно).
Используйте документацию (https://nodejs.org/dist/latest-v16.x/docs/api/, точнее описание использования модуля "fs" https://nodejs.org/dist/latest-v16.x/docs/api/fs.html) для создания модуля (файла "\*.js") который будет выполнять следующие действия:
```
1) получать список всех файлов '*.html' и '*.htm' в некоторой папке (путь к папке можно захардкодить в константу в шапке модуля) (см. fs.readdir / fs.readdirSync);
2) читать каждый файл полученный в п.1 (см. fs.readFile / fs.readFileSync);
3) разбирать прочитанный файл, получать информацию о количестве параграфов (тегов <P ...>...</P>) содержащих некоторую подстроку (строку можно захардкодить в константу в шапке модуля);
4) выводить статистику собранную в п.3 в консоль (console.log(...)) с указанием имени файла в котором собрана эта статистика.
```
## Реализованный код
```javascript
const path = require("path");
const fs = require("fs");
const PATH_TO_HTML_DIR = path.join(__dirname, "html");  // Путь до папки с .html/.htm файлами

function getHtmlFilesList () {  // Получаем список .html .htm файлов
	return fs.readdirSync(PATH_TO_HTML_DIR).filter(file => {
		let fileExtension = file.split('.')[file.length - 1];
		
		return ['html', 'htm'].indexOf(fileExtension) == -1;  // Проверяем, чтобы расширение файла находилось в списке (['html', 'htm'])
	}).map(file => path.join(PATH_TO_HTML_DIR, file));
}

function getCountParagraphs (htmlCode) {  // Посчитать количество тэгов <p>
	/*
	Ps: в виду того, что задание не предалагает конкретный парсинг HTML, модгут возникнуть ошибки в местах содержащих примерно следующее: <tag value="..<p..">
	*/
	return htmlCode.split('<p').length - 1;
}

function sum (array) {  // Фукнция суммирования
	if (!Array.isArray(array)) {
		throw new Error("Argument error: required Array type");
	}
	else if (array.length == 0) {
		return 0;
	}
	else if (array.length == 1) {
		return array[0];
	}
	let count = array[0];
	for (let i = 1; i < array.length; i++) {
		count += array[i];
	}
	return count;
}

function getParagraphStats () {  // "Главная" функция.
	let countsParagraph = getHtmlFilesList().map(file => {
		let data = fs.readFileSync(file, {encoding:'utf8'});
		return {
			file            : file,
			countParagraphs : getCountParagraphs(data)
		};
	});
	
	return "Статистика:\n=============================\n" + countsParagraph.map(i => `Файл: ${i.file}\nКоличество <p>: ${i.countParagraphs}`).join('\n') + `\n=============================\nВсего: ${sum(countsParagraph.map(i => i.countParagraphs))}`;
}

console.log(getParagraphStats());
```