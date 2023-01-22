<?php

/**
 * контроллер главной страницы
 */

// подключаем модели
include_once '../models/CategoriesModel.php';
include_once '../models/ProductsModel.php';

/**
 * тестовая функция
 */
function testAction()
{
	echo 'IndexController.php > testAction';
}

/** 
 * функция формирует главную страницу сайта
 * (в параметр передаём объект: $smarty)
 */
function indexAction($smarty)
{
	//> Пагинатор
	$paginator = array();

	// установим кол-во элементов на странице
	$paginator['perPage'] = 9;
	// определим на какой странице находимся (текущая страница)
	$paginator['currentPage'] = isset($_GET['page']) ? $_GET['page'] : 1;
	// вычислим смещение (с какого элемента надо делать выборку)
	$paginator['offset'] = ($paginator['currentPage'] * $paginator['perPage']) - $paginator['perPage'];
	// сформируем ссылку пагинатора
	$paginator['link'] = '/index/?page=';

	// получим массив (из 2-х элеменов) который возвращает ф-ия: getLastProducts()
	// 1-ый элемент массива будет помещён в $rsProducts- продукты, 2-ой: $allCnt- общее кол-во продуктов в БД
	list($rsProducts, $allCnt) = getLastProducts($paginator['offset'], $paginator['perPage']);
	// округляем результат деления (в большую сторону) Получили: всего страниц
	$paginator['pageCnt'] = ceil($allCnt / $paginator['perPage']);

	// передаём переменную в шаблон
	$smarty->assign('paginator', $paginator);
	//<

	// в переменную сохраним категории
	$rsCategories = getAllMainCatsWithChildren();
	//d($rsCategories);

	// инициализируем переменную: pageTitle и присваиваем её значение: Главная страница сайта (для передачи в шаблон и вывода на странице)
	$smarty->assign('pageTitle', 'Главная страница');

	// инициализируем переменную: rsCategories (массив с категориями) для передачи в шаблон и вывода на странице
	$smarty->assign('rsCategories', $rsCategories);
	// инициализируем переменную: rsProducts (массив с товарами) для передачи в шаблон и вывода на странице
	$smarty->assign('rsProducts', $rsProducts);

	// для загрузки соответствующих шаблонов на странице вызываем функцию: loadTemplate()
	loadTemplate($smarty, 'header');
	loadTemplate($smarty, 'index');
	loadTemplate($smarty, 'footer');
}
