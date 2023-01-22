<?php

/**
 * модель для таблицы заказов orders
 */

/**
 * создание заказа без привязки товара в таблице БД: orders
 * 
 * @param string $name имя
 * @param string $phone телефон
 * @param string $adress адрес
 * @return int ID созданого заказа
 */
function makeNewOrder($name, $phone, $adress)
{
	global $link;

	//> инициализация переменных
	$userId = $_SESSION['user']['id'];
	$comment = "id пользователя: {$userId}<br>
    Имя: {$name}<br>
    Телефон: {$phone}<br>
    Адрес: {$adress}";
	$dateCreated = date('Y.m.d H:i:s');
	$userIp = $_SERVER['REMOTE_ADDR'];
	//<

	$sql = "INSERT INTO `orders` (`user_id`, `date_created`, `date_payment`, `status`, `comment`, `user_ip`) VALUES ('{$userId}', '{$dateCreated}', null, '0', '{$comment}', '{$userIp}')";

	$rs = mysqli_query($link, $sql);

	// получим id новой записи (созданного заказа)
	if ($rs) {
		$sql = "SELECT `id` FROM `orders` ORDER BY `id` DESC LIMIT 1";

		// делаем запрос
		$rs = mysqli_query($link, $sql);

		// преобразовываем результат запроса в ассоциативный массив
		$rs = createSmartyRsArray($rs);

		if (isset($rs[0])) {
			return $rs[0]['id'];
		}
	}
	return false;
}


/**
 * получить список заказов с привязкой продуктов для пользователя с $userId
 * 
 * @param int $userId ID пользователя
 * @return array массив заказов с привязкой к продуктам
 */
function getOrdersWithProductsByUser($userId)
{
	global $link;

	$userId = intval($userId);

	$sql = "SELECT * FROM `orders` WHERE `user_id` = '{$userId}' ORDER BY `id` DESC";

	$rs = mysqli_query($link, $sql);

	$smartyRs = array();
	while ($row = mysqli_fetch_assoc($rs)) {
		// для каждой записи заказа сделаем выборку из таблицы покупок и получим все покупки по текущему заказу с id = $row['id']
		$rsChildren = getPurchaseForOrder($row['id']);
		if ($rsChildren) {
			// в текущую строку заказа (массив) добавляем ещё один столбец (ячейку): children
			$row['children'] = $rsChildren;
			$smartyRs[] = $row;
		}
	}
	return $smartyRs;
}

/** 
 * Получаем данные(все заказы) для таблицы заказов в админке
 */
function getOrders()
{
	global $link;

	$query = "SELECT o.*, u.name, u.email, u.phone, u.adress FROM `orders` AS `o` LEFT JOIN `users` AS `u` ON o.user_id = u.id ORDER BY `id` DESC";

	$rs = mysqli_query($link, $query);

	$smartyRs = array();

	while ($row = mysqli_fetch_assoc($rs)) {
		$rsChildren = getProductsForOrder($row['id']);

		// по условию добавим отдельным столбцом подмассив дочерних элементов
		if ($rsChildren) {
			$row['children'] = $rsChildren;
			$smartyRs[] = $row;
		}
	}
	return $smartyRs;
}

/** 
 * Получаем продукты принадлежащие конкретному заказу
 */
function getProductsForOrder($orderId)
{
	global $link;

	$query = "SELECT * FROM `purchase` AS `pe` LEFT JOIN `products` AS `ps` ON pe.product_id = ps.id WHERE (`order_id` = '{$orderId}')";

	$rs = mysqli_query($link, $query);
	return createSmartyRsArray($rs);
}

/** 
 * Обновить статус заказа
 */
function updateOrderStatus($itemId, $status)
{
	global $link;

	$status = intval($status);

	$query = "UPDATE `orders` SET `status` = '{$status}' WHERE `id` = '{$itemId}'";

	$rs = mysqli_query($link, $query);
	return $rs;
}

/** 
 * Обновить дату оплаты заказа
 */
function updateOrderDatePayment($itemId, $datePayment)
{
	global $link;

	$query = "UPDATE `orders` SET `date_payment` = '{$datePayment}' WHERE `id` = '{$itemId}'";

	$rs = mysqli_query($link, $query);
	return $rs;
}
