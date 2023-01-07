/**
 * функция добавления товара в корзину
 * 
 * @param int itemId ID продукта
 * @return в случае успеха обновляются данные корзины на страницеы
 */
function addToCart(itemId) {
	console.log("js - addToCart()");

	// создадим ajax-запрос
	$.ajax({
		type: 'POST',
		async: false,
		url: "/cart/addtocart/" + itemId + '/',
		dataType: 'json',
		// при успешном выполнении запроса отработает свойство типа функция на вход которой передаётся, то что пришло из 
		// контроллера (его метода), путь к которому прописан в свойстве: url
		success: function (data) {
			if (data['success']) {
				// изменяем кол-во элементов в корзине
				$('#cartCntItems').html(data['cntItems']);
				// изменяем текст ссылки на странице (при клике прячем одну показываем другую)
				$('#addCart_' + itemId).hide();
				$('#removeCart_' + itemId).show();
			}
		}
	});
}

/**
 * функция удаления товара в корзину
 * 
 * @param int itemId ID продукта
 * @returns в случае успеха обновляются данные корзины на страницеы
 */
function removeFromCart(itemId) {
	console.log("js - removeToCart()");
	$.ajax({
		type: 'POST',
		async: false,
		url: "/cart/removefromcart/" + itemId + '/',
		dataType: 'json',
		success: function (data) {
			if (data['success']) {
				$('#cartCntItems').html(data['cntItems']);
				$('#removeCart_' + itemId).hide();
				$('#addCart_' + itemId).show();
			}
		}
	});
}

/**
 * Подсчет стоимости выбранного товара (с учётом кол-ва)
 * 
 * @param {int} itemId ID продукта
 * 
 */
function conversionPrice(itemId) {
	var newCnt = $('#itemCnt_' + itemId).val();
	var itemPrice = $('#itemPrice_' + itemId).attr('value');
	var itemRealPrice = newCnt * itemPrice;

	$('#itemRealPrice_' + itemId).html(itemRealPrice);
}

/**
 * Получение данных с формы (собирает в json-массив все нужные значения)
 * 
 */
function getData(obj_form) {
	// в переменной сохраняем пустой массив
	var hData = {};
	// с помощью функыии jQuery пробегаем по всем указанным здементам данного объекта: obj_form
	$('input, textarea, select', obj_form).each(function () {
		if (this.name && this.name != '') {
			hData[this.name] = this.value;
			// для наглядности выводим в консоль каждый элемент
			console.log('hData[' + this.name + '] = ' + hData[this.name]);
		}
	});
	return hData;
}

/**
 * регистрация нового пользователя
 *  
 */
function registerNewUser() {
	// получаем массив данных в формате: json
	var postData = getData('#registerBox');

	$.ajax({
		type: 'POST',
		async: false,
		url: "/user/register/",
		data: postData,
		dataType: 'json',
		success: function (data) {
			if (data['success']) {
				alert(data['message']);

				//> блок в левом углу
				$('#registerBox').hide(); // блок регистрации прячем

				$('#userLink').attr('href', '/user/'); // у объекта с id = userLink меняем значение атрибута: href на указанное
				$('#userLink').html(data['userName']); // у объекта с id = userLink указываем значение, которое будет  выводится на экран (здесь- как ссылка)

				$('#userBox').show(); // блок зарегистрированого пользователя показываем
				//<

				//> на странице заказа
				$('#loginBox').hide();
				$('#btnSaveOrder').show();
				//<
			} else {
				alert(data['message']);
			}
		}
	});
}

/**
 * Авторизация пользователя
 */
function login() {
	// обращаемся к объекту с id = loginEmail и берём его значение: value
	var email = $('#loginEmail').val();
	// обращаемся к объекту с id = loginPwd и берём его значение: value
	var pwd = $('#loginPwd').val();

	// формируем строку запроса
	var postData = "email=" + email + "&pwd=" + pwd;

	// выполнение ajax-запроса:
	$.ajax({
		type: 'POST',
		async: false,
		url: '/user/login/',
		data: postData,
		dataType: 'json',
		// при успешном выполнении запроса
		success: function (data) {
			if (data['success']) {
				// прячем соответствующие формы
				$('#registerBox').hide();
				$('#loginBox').hide();

				$('#userLink').attr('href', '/user/');
				$('#userLink').html(data['displayName']);

				$('#userBox').show(); // показываем блок с id = userBox

				//> заполняем поля на странице заказов
				$('#name').val(data['name']);
				$('#phone').val(data['phone']);
				$('#adress').val(data['adress']);
				//<
				$('#btnSaveOrder').show();
			} else {
				alert(data['message']);
			}
		}
	});
}

/**
 * показать либо скрыть форму регистрации
 */
function showRegisterBox() {
	// проверим наличие стиля у данного блока
	if ($('#registerBoxHidden').css('display') != 'block') {
		// если блок был не виден, покажем его
		$('#registerBoxHidden').show();
	} else {
		// иначе скроем
		$('#registerBoxHidden').hide();
	}
}

/**
 * Обновление данных пользователя
 * 
 */
function updateUserData() {
	console.log("js - updateUserData");
	var phone = $('#newPhone').val();
	var adress = $('#newAdress').val();
	var pwd1 = $('#newPwd1').val();
	var pwd2 = $('#newPwd2').val();
	var curPwd = $('#curPwd').val();
	var name = $('#newName').val();
	var postData = { phone: phone, adress: adress, pwd1: pwd1, pwd2: pwd2, curPwd: curPwd, name: name };
	$.ajax({
		type: 'POST',
		async: false,
		url: '/user/update/',
		data: postData,
		dataType: 'json',
		success: function (data) {
			if (data['success']) {
				$('#userLink').html(data['userName']);
				alert(data['message']);
			} else {
				alert(data['message']);
			}
		}
	});
}

/**
 * сохранение заказа
 */
function saveOrder() {
	var postData = getData('form');
	$.ajax({
		type: 'POST',
		async: false,
		url: '/cart/saveorder/',
		data: postData,
		dataType: 'json',
		success: function (data) {
			if (data['success']) {
				alert(data['message']);
				document.location = '/';
			} else {
				alert(data['message']);
			}
		}
	});
}

/**
 * показать ил скрыть данные о заказе
 */
function showProducts(id) {
	var objName = "#purchasesForOrderId_" + id;
	if ($(objName).css('display') != 'table-row') {
		$(objName).show();
	} else {
		$(objName).hide();
	}
}