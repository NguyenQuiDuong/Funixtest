var isSubmited = false;
$(function(){
	//ExpenseCategory.load('#companyId', '.prepair-expenseCategory');
	ExpenseCategory.load('#companyId', '.item-expenseCategory');
	AccountingAccount.load('#companyId', '#accountId');
	addNewRow();
	$('#items').after($('#transactionItems'));
	$(document).on('keypress', function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode==13){
			addNewRow();
			$('#transactionItems .table tbody tr').last().find('.item-expenseCategory').focus();
		}
	});
	
	$('#companyId').on('change', function(){
		$('#transactionItems .table tbody tr').remove();
		addNewRow();
	});
	
	$('#accountId').select2();
	calculateTotalAmount();
	
	$('#transactionItems').on('change', '.item-amount', function(){
		calculateTotalAmount();
	});
	
	$('#transactionItems').on('click', '.removeItem', function(){
		$(this).closest('tr').remove();
		if(!$('#transactionItems .table tbody tr').length){
			addNewRow();
			$('#transactionItems .table tbody tr').last().find('.item-expenseCategory').focus();
		}
	})
	
	$('#btnSave').on('click', function(){
		if(!isSubmited && checkForm()){
			isSubmited = true;
			$('#fTransaction').submit();
		}
	});
});
function addNewRow(){
	var tr = $('<tr/>');
	var index = $('#transactionItems .table tbody tr').length + 1;
	var expenseCategory = $('#prepairElement .prepair-expenseCategory').clone();
	expenseCategory.removeClass('prepair-expenseCategory').addClass('item-expenseCategory');
	var amount = $('<input/>').addClass('form-control intAutoNumeric item-amount').attr('type', 'text');
	amount.autoNumeric("init", {'mDec':0});
	
	var vat = $('<input/>').addClass('form-control intAutoNumeric item-vat').attr('type', 'text');
	vat.autoNumeric("init", {'mDec':0});
	var description = $('<input/>').addClass('form-control item-description').attr('type', 'text');
	var iconHelp = $('<i/>').addClass('fa fa-level-down fa-rotate-90 addNewRow')
		.attr('data-original-title', 'Nhấn enter để thêm dòng mới')
		.attr('data-toggle', 'tooltip');
	var iconDelete = $('<a/>').addClass('fa fa-times-circle icon removeItem icon');
	tr.append($('<td/>').addClass('colIndex').append(index))
		.append($('<td/>').addClass('colExpenseCategory').append(expenseCategory))
		.append($('<td/>').addClass('colAmount').append(amount))
		.append($('<td/>').addClass('colVat').append(vat))
		.append($('<td/>').addClass('colDescription').append(description))
		.append($('<td/>').addClass('colHelp colControls').append(iconDelete));
	
	$('#transactionItems .table tbody').append(tr);
	$('#transactionItems .table tbody .addNewRow').remove();
	$('#transactionItems .table tbody tr').last().find('.colHelp').append(iconHelp);
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
		if($(this).find('.item-amount').val() && $(this).find('.item-expenseCategory').val()){
			var item = {
				'expenseCategoryId' : $(this).find('.item-expenseCategory').val(),
				'amount' : $(this).find('.item-amount').val(),
				'vat': $(this).find('.item-vat').val(),
				'description' : $(this).find('.item-description').val()
			}
			itemData.push(item);
			index++;
		}
	});
	console.log(itemData);
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
