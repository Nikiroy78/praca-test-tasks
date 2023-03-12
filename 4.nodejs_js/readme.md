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
``` или 
```javascript[11, -1]```, так как -1 + 11 = 10 = targetSum
Код написанной функции прокомментируйте.