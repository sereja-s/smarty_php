<?php

/**
 * контроллер бэкенда сайта:  /admin/
 */

// подключаем модели
include_once '../models/CategoriesModel.php';
include_once '../models/ProductsModel.php';
include_once '../models/OrdersModel.php';
include_once '../models/PurchaseModel.php';

// переопределим переменные
$smarty->setTemplateDir(TemplateAdminPrefix);
$smarty->assign('TemplateWebPath', TemplateAdminWebPath);

function indexAction($smarty)
{
	$rsCategories = getAllMainCategories();

	$smarty->assign('pageTitle', 'Управление сайтом');
	$smarty->assign('rsCategories', $rsCategories);

	loadTemplate($smarty, 'adminHeader');
	loadTemplate($smarty, 'admin');
	loadTemplate($smarty, 'adminFooter');
}

function addnewcatAction()
{
	$catName = $_POST['newCategoryName'];
	$catParentId = $_POST['generalCatId'];

	$res = insertCat($catName, $catParentId);
	if ($res) {
		$resData['success'] = 1;
		$resData['message'] = 'Категория добавлена';
	} else {
		$resData['success'] = 0;
		$resData['message'] = 'Ошибка добавления категории';
	}
	echo json_encode($resData);
	return;
}

/**
 * Страница управления категориями
 * @param type $smarty
 */
function categoryAction($smarty)
{
	$rsCategories = getAllCategories();

	$rsMainCategories = getAllMainCategories();

	$smarty->assign('pageTitle', 'Категории');
	$smarty->assign('rsCategories', $rsCategories);
	$smarty->assign('rsMainCategories', $rsMainCategories);
	loadTemplate($smarty, 'adminHeader');
	loadTemplate($smarty, 'adminCategory');
	loadTemplate($smarty, 'adminFooter');
}

/**
 * Обновление категорий
 */
function updatecategoryAction()
{
	// начальные значения, полученные со страницы
	$itemId = $_POST['itemId'];
	$parentId = $_POST['parentId'];
	$newName = $_POST['newName'];

	$res = updateCategoryData($itemId, $parentId, $newName);

	if ($res) {
		$resData['success'] = 1;
		$resData['message'] = 'Категория обновлена';
	} else {
		$resData['success'] = 0;
		$resData['message'] = 'Ошибка изменения данных категории';
	}

	echo json_encode($resData);
	return;
}

/**
 * Страница управления товарами
 * @param type $smarty
 */
function productsAction($smarty)
{
	$rsCategories = getAllCategories();

	$rsProducts = getProducts();

	$smarty->assign('rsCategories', $rsCategories);
	$smarty->assign('rsProducts', $rsProducts);
	$smarty->assign('pageTitle', 'Товары');

	loadTemplate($smarty, 'adminHeader');
	loadTemplate($smarty, 'adminProducts');
	loadTemplate($smarty, 'adminFooter');
}


/**
 * Добавление продукта
 */
function addproductAction()
{
	// данные для инициализации переменных будем брать массива: $_POST (они появятся там при нажатии кнопки: сохранить)
	$itemName = $_POST['itemName'];
	$itemPrice = $_POST['itemPrice'];
	$itemDesc = $_POST['itemDesc'];
	$itemCat = $_POST['itemCatId'];

	$res = insertProduct($itemName, $itemPrice, $itemDesc, $itemCat);

	if ($res) {
		$resData['success'] = 1;
		$resData['message'] = 'Изменения успешно внесены';
	} else {
		$resData['success'] = 0;
		$resData['message'] = 'Ошибка изменения данных';
	}

	echo json_encode($resData);
	return;
}

function updateproductAction()
{
	$itemId = $_POST['itemId'];
	$itemName = $_POST['itemName'];
	$itemPrice = $_POST['itemPrice'];
	$itemStatus = $_POST['itemStatus'];
	$itemDesc = $_POST['itemDesc'];
	$itemCat = $_POST['itemCatId'];

	$res = updateProduct($itemId, $itemName, $itemPrice, $itemStatus, $itemDesc, $itemCat);

	if ($res) {
		$resData['success'] = 1;
		$resData['message'] = 'Изменения успешно внесены';
	} else {
		$resData['success'] = 0;
		$resData['message'] = 'Ошибка изменения данных';
	}

	echo json_encode($resData);
	return;
}

/**
 * загрузка файла (картинки)
 */
function uploadAction()
{
	// максимальный размер загружаемого файла
	$maxSize = 2 * 1024 * 1024;

	$itemId = $_POST['itemId'];

	// получаем расширение загружаемого файла
	$ext = pathinfo($_FILES['filename']['name'], PATHINFO_EXTENSION);

	// создаем имя файла
	$newFileName = $itemId . '.' . $ext;
	if ($_FILES['filename']['size'] > $maxSize) {
		echo 'Размер файла превышает 2 мегабайта';
		return;
	}

	// загружен ли файл
	if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
		// если файл загружен, то перемещаем его из временной папки в конечную
		$res = move_uploaded_file($_FILES['filename']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/images/products/' . $newFileName);
		if ($res) {
			$res = updateProductImage($itemId, $newFileName);
			if ($res) {
				redirect('/admin/products/');
			}
		}
	} else {
		echo 'Ошибка загрузки файла';
		//redirect('/admin/products/');
	}
}

/** 
 * Страница заказов в админке
 */
function ordersAction($smarty)
{
	$rsOrders = getOrders();

	$smarty->assign('pageTitle', 'Заказы');
	$smarty->assign('rsOrders', $rsOrders);

	loadTemplate($smarty, 'adminHeader');
	loadTemplate($smarty, 'adminOrders');
	loadTemplate($smarty, 'adminFooter');
}

/** 
 * Устанавка новый статуса для заказа
 */
function setorderstatusAction()
{
	$itemId = $_POST['itemId'];
	$status = $_POST['status'];

	$res = updateOrderStatus($itemId, $status);

	if ($res) {
		$resData['success'] = 1;
		$resData['message'] = 'Статус заказа обновлён';
	} else {
		$resData['success'] = 0;
		$resData['message'] = 'Ошибка обновления статуса заказа';
	}

	echo json_encode($resData);
	return;
}

/** 
 * Обновление даты оплаты заказа
 */
function setorderdatepaymentAction()
{
	$itemId = $_POST['itemId'];
	$datePayment = $_POST['datePayment'];

	$res = updateOrderDatePayment($itemId, $datePayment);

	if ($res) {
		$resData['success'] = 1;
		$resData['message'] = 'дата оплаты заказа изменена ';
	} else {
		$resData['success'] = 0;
		$resData['message'] = 'Ошибка обновления даты оплаты заказа';
	}

	echo json_encode($resData);
	return;
}

//--------------------------------------------------------------------------------------------------------------------//

/** 
 * Создание XML-файла
 */
function createxmlAction()
{
	$rsProducts = getProducts();

	// создаём экземпляр класса (объект): DomDocument для работы с XML-документами
	$xml = new DomDocument('1.0', 'utf-8');

	// в переменной сохраняем результат работы метода: appendChild() нашего объекта (добавим дочерниий элемент(создаём его под названием: products))
	$xmpProducts = $xml->appendChild($xml->createElement('products'));

	foreach ($rsProducts as $product) {
		// добавляем тег: product
		$xmpProduct = $xmpProducts->appendChild($xml->createElement('product'));

		// пробежимся по всем полям данного продукта (как ключ(название поля в БД) => значение)
		foreach ($product as $key => $val) {
			// создаём каждый злемент (дочерний для этого продукта) с названием: из $key
			$xmlName = $xmpProduct->appendChild($xml->createElement($key));
			// создаётся сам элемент с его значением
			$xmlName->appendChild($xml->createTextNode($val));
		}
	}
	// сохраняем элемент по указанному пути
	$xml->save($_SERVER['DOCUMENT_ROOT'] . '/www/xml/products.xml');

	echo 'XML-файл успешно создан';
}


/** 
 * Загрузка файлов (здесь применяется для загрузки XML-файла)
 */
function uploadFile($localFilename, $localPath = '/www/upload/')
{
	// максимальный размер загружаемого файла (здесь: 2-а Мбайта)
	$maxSize = 2 * 1024 * 1024;

	// получаем расширение загружаемого файла
	$ext = pathinfo($_FILES['filename']['name'], PATHINFO_EXTENSION);

	// получаем расширение файла, которое хотим получить
	$pathInfo = pathinfo($localFilename);

	// сравниваем получаемое и необходимое расширение файла
	if ($ext != $pathInfo['extension']) return false;

	// модифицируем имя загруженного файла
	$newFileName = $pathInfo['filename'] . '_' . time() . '.' . $pathInfo['extension'];

	// проверяем размер файла
	if ($_FILES['filename']['size'] > $maxSize) return false;

	// проверим существует ли папка в которую необходимо поместить загруженный файл (передаётся 2-м параметром)
	$path = $_SERVER['DOCUMENT_ROOT'] . $localPath;
	// если не существует, то создаём её по указанному пути
	if (!file_exists($path)) {
		mkdir($path);
	}

	// если файл успешно загрузился в ячейку для временных файлов супер-глобального массива: $_FILES
	if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
		// сохраняем его по указанному выше пути с назначенным именем
		$res = move_uploaded_file($_FILES['filename']['tmp_name'], $path . $newFileName);
		return ($res == true) ? $newFileName : false;
	} else {
		return false;
	}
}

/** 
 * Загрузка XML-файла
 */
function loadfromxmlAction()
{
	$successUploadFileName = uploadFile('import_products.xml', '/www/xml/import/');

	if (!$successUploadFileName) {
		echo 'Ошибка загрузки файла';
		return;
	}

	$xmlFile = $_SERVER['DOCUMENT_ROOT'] . '/www/xml/import/' . $successUploadFileName;

	// создадим объект для работы с загруженным файлом
	$xmlProducts = simplexml_load_file($xmlFile);

	// разпарсим (получим) нужные нам данные
	$products = array();
	$i = 0;
	foreach ($xmlProducts as $product) {
		// для каждого продукта сохраняем их свойства (значения) предварительно проверив и преобразовав в необходимый вид
		$products[$i]['name'] = htmlentities($product->name);
		$products[$i]['category_id'] = intval($product->category_id);
		$products[$i]['description'] = html_entity_decode($product->description);
		$products[$i]['price'] = intval($product->price);
		$products[$i]['status'] = intval($product->status);
		$i++;
	}
	// в переменную сохраним результат работы ф-ии, отвечающей за массовое добавление импортированных файлов
	$res = insertImportProducts($products);

	if ($res) {
		redirect('/admin/products/');
	}
}
