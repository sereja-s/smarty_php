<?php

/**
 * контроллер  страницы пользователя
 * 
 */

// подключаем модели
include_once '../models/CategoriesModel.php';
include_once '../models/UsersModel.php';
include_once '../models/OrdersModel.php';
include_once '../models/PurchaseModel.php';

/**
 * AJAX регистрация пользователя
 * инициализация сессионной переменной $_SESSION['user'] для сохранения данных пользователя при регистрации (авторизации)
 * 
 * @return json массив данных нового пользователя
 */
function registerAction()
{
	$email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
	$email = trim($email);
	$pwd1 = isset($_REQUEST['pwd1']) ? $_REQUEST['pwd1'] : null;
	$pwd2 = isset($_REQUEST['pwd2']) ? $_REQUEST['pwd2'] : null;
	$phone = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : null;
	$adress = isset($_REQUEST['adress']) ? $_REQUEST['adress'] : null;
	$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : null;
	$name = trim($name);

	$resData = null;

	$resData = checkRegisterParams($email, $pwd1, $pwd2);

	// если есть ошибка при регистрации и если введённый пароль в БД уже есть
	if (!$resData && checkUserEmail($email)) {
		$resData['success'] = 0;
		$resData['message'] = "Пользователь с таким email ('{$email}') уже зарегестрирован";
	}
	if (!$resData) {
		// хешируем(шифруем) пароль
		$pwdMD5 = md5(trim($pwd1));

		$userData = registerNewUser($email, $pwdMD5, $name, $phone, $adress);

		if ($userData['success']) {
			$resData['message'] = 'Пользователь успешно зарегестрирован';
			$resData['success'] = 1;

			$userData = $userData[0];
			$resData['userName'] = $userData['name'] ? $userData['name'] : $userData['email'];
			$resData['userEmail'] = $email;

			// сессионная переменная небходима для хранения данных о пользователе во время запущенной сессии (чтобы не 
			// обращаться в БД при каждой перезагрузке страницы)
			$_SESSION['user'] = $userData;
			$_SESSION['user']['displayName'] = $userData['name'] ? $userData['name'] : $userData['email'];
		} else {
			$resData['success'] = 0;
			$resData['message'] = 'Ошибка регистрации';
		}
	}
	// json массив данных нового пользователя (для работы с ним в js-файлах)
	echo json_encode($resData);
}

/**
 * Выход пользователя (разлогинивание)
 */
function logoutAction()
{
	if (isset($_SESSION['user'])) {
		unset($_SESSION['user']);
		unset($_SESSION['cart']);
	}
	redirect('/');
}

/**
 * AJAX авторизация пользователя
 * 
 * @return json массив данных пользователя
 */
function loginAction()
{
	$email = isset($_REQUEST['email']) ? $_REQUEST['email'] : NULL;
	$email = trim($email);
	$pwd = isset($_REQUEST['pwd']) ? $_REQUEST['pwd'] : NULL;
	$pwd = trim($pwd);

	$userData = loginUser($email, $pwd);

	if ($userData['success']) {
		$userData = $userData[0];

		$_SESSION['user'] = $userData;
		$_SESSION['user']['displayName'] = $userData['name'] ? $userData['name'] : $userData['email'];

		$resData = $_SESSION['user'];
		$resData['success'] = 1;
		//$resData['userName'] = $userData['name'] ? $userData['name'] : $userData['email'];
		//$resData['userEmail'] = $email;
	} else {
		$resData['success'] = 0;
		$resData['message'] = 'Неверный логин или пароль';
	}
	echo json_encode($resData);
}

/**
 * Формирование страницы пользователя
 * 
 * @link /user/
 * @param object $smarty
 */
function indexAction($smarty)
{
	// если пользователь не залогинен то редирект на главную страницу
	if (!isset($_SESSION['user'])) {
		redirect('/');
	}
	// получаем список категорий для меню
	$rsCategories = getAllMainCatsWithChildren();

	// получаем список заказов пользователя
	$rsUserOrders = getCurUserOrders();

	$smarty->assign('pageTitle', 'Страница пользователя');
	$smarty->assign('rsCategories', $rsCategories);
	$smarty->assign('rsUserOrders', $rsUserOrders);

	loadTemplate($smarty, 'header');
	loadTemplate($smarty, 'user');
	loadTemplate($smarty, 'footer');
}

/**
 * обновление данных пользователя
 * @return json массив данных пользователя (обновлённые)
 */
function updateAction()
{
	// если пользователь не залогинен, выходим
	if (!isset($_SESSION['user'])) {
		redirect('/');
	}

	// инициализация переменных:

	$resData = array();

	$phone = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : NULL;
	$adress = isset($_REQUEST['adress']) ? $_REQUEST['adress'] : NULL;
	$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : NULL;
	$pwd1 = isset($_REQUEST['pwd1']) ? $_REQUEST['pwd1'] : NULL;
	$pwd2 = isset($_REQUEST['pwd2']) ? $_REQUEST['pwd2'] : NULL;
	$curPwd = isset($_REQUEST['curPwd']) ? $_REQUEST['curPwd'] : NULL;

	// проверка правильности пароля
	$curPwdMd5 = md5($curPwd);
	if (!$curPwd || ($_SESSION['user']['pwd'] != $curPwdMd5)) {
		$resData['success'] = 0;
		$resData['message'] = 'Текущий пароль неверный';
		echo json_encode($resData);
		return FALSE;
	}

	// обновление данных
	$res = updateUserData($name, $phone, $adress, $pwd1, $pwd2, $curPwdMd5);

	if ($res) {
		$resData['success'] = 1;
		$resData['message'] = 'Данные сохранены';
		$resData['userName'] = $name;

		// обновляем сессионные данные пользователя
		$_SESSION['user']['name'] = $name;
		$_SESSION['user']['phone'] = $phone;
		$_SESSION['user']['adress'] = $adress;

		// переменной присваиваем текущее значение пароля, который находится в сессии
		$newPwd = $_SESSION['user']['pwd'];

		// если пароль изменялся
		if ($pwd1 && ($pwd1 == $pwd2)) {
			$newPwd = md5(trim($pwd1));
		}
		// обновляем пароль в сессии
		$_SESSION['user']['pwd'] = $newPwd;

		$_SESSION['user']['displayName'] = $name ? $name : $_SESSION['email'];
	} else {
		$resData['success'] = 0;
		$resData['message'] = 'Ошибка изменения данных';
	}
	echo json_encode($resData);
}
