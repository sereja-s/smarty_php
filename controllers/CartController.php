<?php

/**
 * контроллер работы с корзиной
 * 
 */

// подключаем модели
include_once '../models/CategoriesModel.php';
include_once '../models/ProductsModel.php';
include_once '../models/OrdersModel.php';
include_once '../models/PurchaseModel.php';

/**
 * Добавление продукта в корзину
 * 
 * @param int id Get параметр - ID добавленого продукта
 * @return json информация об операции (успех, кол-во элементов в корзине)
 */
function addtocartAction()
{
	$itemId = isset($_GET['id']) ? intval($_GET['id']) : NULL;
	if (!$itemId) return false;

	$resData = array();

	// если значение не найдено, то добавляем
	if (isset($_SESSION['cart']) && array_search($itemId, $_SESSION['cart']) === FALSE) {
		$_SESSION['cart'][] = $itemId;
		$resData['cntItems'] = count($_SESSION['cart']);
		$resData['success'] = 1;
	} else {
		$resData['success'] = 0;
	}
	// массив преобразуем в json-данные (для передачи в js-файл из которого функциия была вызвана)
	echo json_encode($resData);
}

/**
 * Удаление продукта из корзины
 * 
 * @param int id Get параметр - ID удаляемого продукта
 * @return json информация об операции (успех, колво элементов в корзине)
 */
function removefromcartAction()
{
	$itemId = isset($_GET['id']) ? intval($_GET['id']) : NULL;
	if (!$itemId) exit();

	$resData = array();
	$key = array_search($itemId, $_SESSION['cart']);
	if ($key !== FALSE) {
		unset($_SESSION['cart'][$key]);
		$resData['success'] = 1;
		$resData['cntItems'] = count($_SESSION['cart']);
	} else {
		$resData['success'] = 0;
	}

	echo json_encode($resData);
}

/**
 * формирование страницы заказа
 * @link /cart/ 
 * 
 */
function indexAction($smarty)
{
	// получим массив идентификаторов товара, который лежит в корзине (если нет, переменная инициализируется пустым массивом)
	$itemsIds = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();

	$rsCategories = getAllMainCatsWithChildren();
	$rsProducts = getProductsFromArray($itemsIds);

	$smarty->assign('pageTitle', 'Корзина');
	$smarty->assign('rsCategories', $rsCategories);
	$smarty->assign('rsProducts', $rsProducts);

	loadTemplate($smarty, 'header');
	loadTemplate($smarty, 'cart');
	loadTemplate($smarty, 'footer');
}

/**
 * Формирование страницы заказа
 */
function orderAction($smarty)
{
	// получаем массив ID продуктов которые пользователь желает купить
	$itemsIds = isset($_SESSION['cart']) ? $_SESSION['cart'] : NULL;

	// если корзина пуста то редирект в корзину
	if (!$itemsIds) {
		redirect('/cart/');
		return;
	}


	// получаем из массива $_POST кол-во покупаемых товаров:

	$itemsCnt = array();

	foreach ($itemsIds as $item) {
		// формируем ключ для массива POST
		$postVar = 'itemCnt_' . $item;

		// создаем новый элемент массива, где
		// ключ массива ($item) - ID покупаемого товара, значение массива - кол-во этого товара		
		$itemsCnt[$item] = isset($_POST[$postVar]) ? $_POST[$postVar] : NULL;
	}

	// получаем список продуктов по массиву корзины (массив с ID тех продуктов которые пользователь желает купить)
	$rsProducts = getProductsFromArray($itemsIds);

	// добавляем каждому продукту дополнительное поле:
	// realPrice = кол-во продуктов * на цену продукта
	// cnt - кол-во покупаемого товара
	// &$item - для того чтобы меняя при изменении переменной $item (передаётся по ссылке)
	// менялся и массив $rsProducts
	$i = 1;
	foreach ($rsProducts as &$item) {
		$item['cnt'] = isset($itemsCnt[$item['id']]) ? $itemsCnt[$item['id']] : NULL;
		if ($item['cnt']) {
			$item['realPrice'] = $item['cnt'] * $item['price'];
		} else {
			// если вдруг получилось так что товар в корзине есть, а кол-во == нулю
			// то удаляем этот товар
			unset($rsProducts[$i]);
		}
		$i++;
	}
	if (!$rsProducts) {
		echo 'Корзина пуста';
		return;
	}
	// полученный массив покупаемых товаров (с нужным кол-вом и перерасчётной ценой) помещаем в сессионную переменную,
	// которая будет необходима для занесения заказа в БД
	$_SESSION['saleCart'] =  $rsProducts;

	// отображаем категории в левом меню
	$rsCategories = getAllMainCatsWithChildren();


	// hideLoginBox переменная-флаг нужна для того чтобы спрятать блоки авторизации и регистрации в боковой панели (если 
	// пользователь зарегистрирован и авторизован)

	// если пользователя нет в сессии (не авторизован)
	if (!isset($_SESSION['user'])) {
		$smarty->assign('hideLoginBox', 1); // покажем блоки авторизации и регистрации под заказом
	}

	$smarty->assign('pageTitle', 'Заказ');
	$smarty->assign('rsProducts', $rsProducts);
	$smarty->assign('rsCategories', $rsCategories);

	loadTemplate($smarty, 'header');
	loadTemplate($smarty, 'order');
	loadTemplate($smarty, 'footer');
}

/**
 * AJAX функция сохранения заказа
 * 
 * @param array $_SESSION['saleCart'] массив покупаемых товаров
 * @return json информация о результате выполнения
 */
function saveorderAction()
{
	// получаем массив покупаемых товаров (данные о заказе)
	$cart = isset($_SESSION['saleCart']) ? $_SESSION['saleCart'] : NULL;

	// если корзина пуста, то формируем ответ с ошибкой, отдаем его в формате: json и выходим из функции
	if (!$cart) {
		$resData['success'] = 0;
		$resData['message'] = 'Нет товаров для заказа';
		echo json_encode($resData);
		return;
	}
	// получим данные авторизованного пользователя (клиента)
	$name = $_POST['name'];
	$phone = $_POST['phone'];
	$adress = $_POST['adress'];

	// создем новый заказ и получаем его ID
	$orderId = makeNewOrder($name, $phone, $adress);

	// если заказ не создан, то выдаем ошибку и завершаем функцию
	if (!$orderId) {
		$resData['success'] = 0;
		$resData['message'] = 'Ошибка создания заказа';
		echo json_encode($resData);
		return;
	}
	// сохраняем товары для созданого заказа
	$res = setPurchaseForOrder($orderId, $cart);

	// если успешно, то формируем ответ, очищаем корзину
	if ($res) {
		$resData['success'] = 1;
		$resData['message'] = 'Заказ сохранен';

		// очистим ячейку которую мы использовали для заказа и корзину
		unset($_SESSION['saleCart']);
		unset($_SESSION['cart']);
	} else {
		$resData['success'] = 0;
		$resData['message'] = 'Ошибка внесения данных для заказа № ' . $orderId;
	}
	echo json_encode($resData);
}
