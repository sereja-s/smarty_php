/**
 * Получение данных с формы
 * 
 */
function getData(obj_form) {
	var hData = {};
	// берём данные со всех указанных элементов (тегов), создавая массив
	$('input, textarea, select', obj_form).each(function () {
		if (this.name && this.name != '') {
			hData[this.name] = this.value;
			console.log('hData[' + this.name + '] = ' + hData[this.name]);
		}
	});
	// возвращаем массив полученных данных
	return hData;
}

/**
 * Добавление новой категории
 */
function newCategory() {
	// делаем сбор данных объекта с указанным id
	var postData = getData('#blockNewCategory');

	$.ajax({
		type: 'POST',
		async: false,
		url: "/admin/addnewcat/",
		data: postData,
		dataType: 'json',
		success: function (data) {
			if (data['success']) {
				// выводим сообщение об успехе
				alert(data['message']);
				// обнуляем имя категории
				$('#newCategoryName').val('');
			} else {
				// сообщение об ошибке
				alert(data['message']);
			}
		}
	});
}

/**
 *  Обновление данных категории  
 */
function updateCat(itemId) {
	// берём значение у элементов на странице (описан в скобках): 

	var parentId = $('#parentId_' + itemId).val();
	var newName = $('#itemName_' + itemId).val();

	// вормируем массив из данных параметров
	var postData = { itemId: itemId, parentId: parentId, newName: newName };

	$.ajax({
		type: 'POST',
		async: false,
		url: "/admin/updatecategory/",
		data: postData,
		dataType: 'json',
		success: function (data) {
			alert(data['message']);
		}
	});
}
/**
 * добавление нового продукта
 */
function addProduct() {
	var itemName = $('#newItemName').val();
	var itemPrice = $('#newItemPrice').val();
	var itemDesc = $('#newItemDesc').val();
	var itemCatId = $('#newItemCatId').val();

	// формируем массив в формате: json для отправки POST-запроса
	var postData = { itemName: itemName, itemPrice: itemPrice, itemDesc: itemDesc, itemCatId: itemCatId };

	$.ajax({
		type: 'POST',
		async: false,
		url: "/admin/addproduct/",
		data: postData,
		dataType: 'json',
		success: function (data) {
			alert(data['message']);
			if (data['success']) {
				// очищаем все поля, чтобы мы могли водить следующий продукт
				$('#newItemName').val('');
				$('#newItemPrice').val('');
				$('#newItemDesc').val('');
				$('#newItemCatId').val('');
			}
		}
	});
}

/**
 * Изменение данных продукта
 */
function updateProduct(itemId) {
	var itemName = $('#itemName_' + itemId).val();
	var itemPrice = $('#itemPrice_' + itemId).val();
	var itemCatId = $('#itemCatId_' + itemId).val();
	var itemDesc = $('#itemDesc_' + itemId).val();
	var itemStatus = $('#itemStatus_' + itemId).attr('checked');

	if (!itemStatus) {
		itemStatus = 1;
	} else {
		itemStatus = 0;
	}

	var postData = { itemId: itemId, itemName: itemName, itemPrice: itemPrice, itemCatId: itemCatId, itemDesc: itemDesc, itemStatus: itemStatus };

	$.ajax({
		type: 'POST',
		async: false,
		url: "/admin/updateproduct/",
		data: postData,
		dataType: 'json',
		success: function (data) {
			alert(data['message']);
		}
	});
}

/**
 * показать или скрыть данные о заказе
 */
function showProducts(id) {
	var objName = "#purchaseForOrderId_" + id;
	if ($(objName).css('display') != 'table-row') {
		$(objName).show();
	} else {
		$(objName).hide();
	}
}
/**
 * Устанавливаем новый статус для заказа (ajax-запрос)  
 */
function updateOrderStatus(itemId) {
	var status = $('#itemStatus_' + itemId).attr('checked');

	if (!status) {
		status = 0;
	} else {
		status = 1;
	}

	// инициализируем переменную в котороую положим json-массив
	var postData = { itemId: itemId, status: status };

	$.ajax({
		type: 'POST',
		async: false,
		url: '/admin/setorderstatus/',
		data: postData,
		dataType: 'json',
		success: function (data) {
			if (data['success']) {
				alert(data['message']);
			} else {
				// сообщение об ошибке
				alert(data['message']);
			}
		}
	});
}

/**
 * обновить дату оплаты заказа
 */
function updateOrderDatePayment(itemId) {
	var datePayment = $('#datePayment_' + itemId).val();

	var postData = { itemId: itemId, datePayment: datePayment };

	$.ajax({
		type: 'POST',
		async: false,
		url: '/admin/setorderdatepayment/',
		data: postData,
		dataType: 'json',
		success: function (data) {
			if (data['success']) {
				alert(data['message']);
			} else {
				// сообщение об ошибке
				alert(data['message']);
			}
		}
	});
}

/**
 * создать XML-файл
 */
function createXML() {
	$.ajax({
		type: 'POST',
		async: false,
		url: '/admin/createxml/',
		dataType: 'html',
		success: function (data) {
			// при удачном выполнениии ajax-запроса:

			// в div с id="xml-place" на странице: adminProduct.tpl поместим результат выполнения (то что нам возвратит
			// function createxmlAction() из AdminController.php)
			$('#xml-place').html(data);
			// откроем вкладку в браузере по указанному адресу
			window.open('http://shop-master/www/xml/products.xml', '_blank');
		}
	});
}