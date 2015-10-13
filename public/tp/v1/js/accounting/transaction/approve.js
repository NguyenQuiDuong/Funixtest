var isSubmited = false;
$(function(){
	$('#transactionItems').on('click', '.changeAccount', function(){
		$(this).closest('td').addClass('editing');
		var accountId = $(this).attr('data-id');
		$('#accountRadio_'+accountId).prop('checked', true);
		$('#selectAccountModal').modal('show');
		$('#accountRadio_'+accountId).focus();
	});
	$('#accountSearch').on('keyup', function(){
		var textSearch = $(this).val();
		if(textSearch){
			$('#dgAccounts tbody tr').hide()
			$('#dgAccounts tbody tr:contains('+textSearch+')').show();
			$('#dgAccounts tbody tr:contains('+textSearch+')').show();
		} else {
			$('#dgAccounts tbody tr').show()
		}
	});
	$('#selectAccountModal .select').on('click', function(){
		var item = $('#dgAccounts tbody tr input:checked');
		var id = item.attr('data-id');
		var displayName = item.closest('tr').find('label:eq(0)').text();
		displayName += ' - ' + item.closest('tr').find('label:eq(1)').text();
		
		$('#transactionItems .editing .changeAccount').attr('data-id', id).text(displayName);
		$('#transactionItems .editing .item-accountId').val(id);
		
		$('#selectAccountModal').modal('hide');
	});
	$('#selectAccountModal').on('show.bs.modal', function (e) {
		$('#dgAccounts tbody tr').show();
	});
	$('#selectAccountModal').on('hide.bs.modal', function (e) {
		$('#dgAccounts tbody tr input[type="radio"]:checked').prop('checked', false);
		$('#transactionItems .editing').removeClass('editing');
	});
	
	
	$('#transactionItems').on('click', '.changeCategory', function(){
		$(this).closest('td').addClass('editing');
		var categoryId = $(this).attr('data-id');
		$('#categoryRadio_'+categoryId).prop('checked', true);
		$('#selectExpenseCategoryModal').modal('show');
		$('#categoryRadio_'+categoryId).focus();
	});
	$('#categorySearch').on('keyup', function(){
		var textSearch = $(this).val();
		if(textSearch){
			$('#dgCategories tbody tr').hide()
			$('#dgCategories tbody tr:contains('+textSearch+')').show();
			$('#dgCategories tbody tr:contains('+textSearch+')').show();
		} else {
			$('#dgCategories tbody tr').show()
		}
	});
	$('#selectExpenseCategoryModal').on('show.bs.modal', function (e) {
		$('#dgCategories tbody tr').show();
	});
	$('#selectExpenseCategoryModal').on('hide.bs.modal', function (e) {
		$('#dgCategories tbody tr input[type="radio"]:checked').prop('checked', false);
		$('#transactionItems .editing').removeClass('editing');
	});
	$('#selectExpenseCategoryModal .select').on('click', function(){
		var item = $('#dgCategories tbody tr input:checked');
		var id = item.attr('data-id');
		var displayName = item.closest('tr').find('label:eq(0)').text();
		displayName += ' - ' + item.closest('tr').find('label:eq(1)').text();
		
		$('#transactionItems .editing .changeCategory').attr('data-id', id).text(displayName);
		$('#transactionItems .editing .item-expenseCategoryId').val(id);
		
		$('#selectExpenseCategoryModal').modal('hide');
	})
	
	calculateTotalAmount();
	$(document).on('keypress', function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode==13){
			addNewRow();
			$('#transactionItems .table tbody tr').last().find('.item-expenseCategory').focus();
		}
	});
	$('#transactionItems').on('change', '.item-amount', function(){
		calculateTotalAmount();
	});
	
	$('#transactionItems').on('click', '.removeItem', function(){
		$(this).closest('tr').remove();
		if(!$('#transactionItems .table tbody tr').length){
			addNewRow();
			$('#transactionItems .table tbody tr').last().find('.item-expenseCategoryId').focus();
		}
		calculateTotalAmount();
	});
	$('#btnSave').on('click', function(){
		if(!isSubmited && checkForm()){
			isSubmited = true;
			$('#fTransaction').submit();
		}
	});
	$('#btnInApprove').on('click', function(){
		if(!isSubmited && checkForm()){
			isSubmited = true;
			$('#isInApprove').val('1');
			$('#fTransaction').submit();
		}
	});
});
function addNewRow(){
	var tr = $('<tr/>');
	var index = $('#transactionItems .table tbody tr').length + 1;
	var accountColum = $('<td/>').addClass('colAccount');
	accountColum.append($('<a/>').addClass('changeAccount').attr('data-id', '').text('_ _'));
	accountColum.append($('<input/>').attr('type', 'hidden').addClass('item-accountId'));
	accountColum.append($('<input/>').attr('type', 'hidden').addClass('item-id'));
	
	var expenseCategoryColumn = $('<td/>').addClass('colExpenseCategory');
	expenseCategoryColumn.append($('<a/>').addClass('changeCategory').attr('data-id', '').text('_ _'));
	expenseCategoryColumn.append($('<input/>').attr('type', 'hidden').addClass('item-expenseCategoryId'));
	
	var amount = $('<input/>').addClass('form-control intAutoNumeric item-amount').attr('type', 'text');
	amount.autoNumeric("init", {'mDec':0});
	
	var vat = $('<input/>').addClass('form-control intAutoNumeric item-vat').attr('type', 'text');
	vat.autoNumeric("init", {'mDec':0});
	var description = $('<input/>').addClass('form-control item-description').attr('type', 'text');
	
	var iconDelete = $('<a/>').addClass('fa fa-times-circle icon removeItem icon');
	tr.append($('<td/>').addClass('colIndex').append(index))
		.append(accountColum)
		.append(expenseCategoryColumn)
		.append($('<td/>').addClass('colAmount').append(amount))
		.append($('<td/>').addClass('colVat').append(vat))
		.append($('<td/>').addClass('colDescription').append(description))
		.append($('<td/>').addClass('colHelp colControls').append(iconDelete));
	
	$('#transactionItems .table tbody').append(tr);
	
}
function calculateTotalAmount(){
	var totalAmount = 0;
	$('#transactionItems .table tbody tr .item-amount').each(function(){
		if($(this).val()){
			totalAmount += parseInt($(this).autoNumeric('get'));
		}
	});
	$('#transactionItems .table tfoot .colTotal').text(formatDecimal(totalAmount));
}
function checkForm(){
	var isValid = true;
	$('#fTransaction .error').removeClass('error');
	$('#fTransaction .required').each(function(){
		$(this).closest('.form-group').find('input, select').each(function(){
			if(!$(this).val()){
				$(this).addClass('error');
				isValid = false;
			}
		});
	});
	var itemData = [];
	var index = 0;
	$('#transactionItems .table tbody tr').each(function(){
		if($(this).find('.item-accountId').val() && $(this).find('.item-amount').val() 
				&& $(this).find('.item-expenseCategoryId').val()){
			var item = {
				'id': $(this).find('.item-id').val(),
				'accountId': $(this).find('.item-accountId').val(),
				'expenseCategoryId' : $(this).find('.item-expenseCategoryId').val(),
				'amount' : $(this).find('.item-amount').val(),
				'vat': $(this).find('.item-vat').val(),
				'description' : $(this).find('.item-description').val()
			}
			itemData.push(item);
			index++;
		} else {
			$(this).addClass('error');
			$('#errorModal .alert').html(noItemSelectMsg);
			$('#errorModal').modal('show');
			isValid = false;
		}
	});
	if(!itemData.length){
		$('#errorModal .alert').html(noItemSelectMsg);
		$('#errorModal').modal('show');
		isValid = false;
	}
	if(isValid){
		$('#items').val(JSON.stringify(itemData))
	}
	return isValid;
}