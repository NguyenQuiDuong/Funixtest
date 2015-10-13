/**
 * @author	VanCK
 * @param cId (select city ID)
 * @param dId (select district ID)
 * @param sel (selected district option)
 */
var Address = {
    load: function (cId, dId) {
        var c = cId ? cId : '#cityId';
        var d = dId ? dId : '#districtId';
        if($(c).val() && !$(d).val()) {
        	Address.getDistricts($(c).val(), d);
        }
        $(c).change(function () {
            if ($(this).val() && $(d).length) {
                Address.getDistricts($(this).val(), d);
            }
        });
    },
    getDistricts: function (cid, dId, sel) {
        $.post(
            '/address/district/load?cityId=' + cid,
            {},
            function (rp) {
                Address.updateDistrict(dId, rp, sel);
            },
            'json'
        );
    },
    updateDistrict: function (id, d, sel) {
        if ($(id).length) {
            var options = "";
            for (var i in d) {
                if (sel == i) {
                    options += "<option selected value='" + i + "'>" + d[i] + "</option>";
                } else {
                    options += "<option value='" + i + "'>" + d[i] + "</option>";
                }
            }
            if (!$(id).find('option:first').val()) {
                options = "<option value=''>" + $(id).find('option:first').text() + "</option>" + options;
            }
            $(id).html(options);
        }
    }
};

/**
 * @author
 * @param cId (select company ID)
 * @param dId (select department ID)
 * @param sel (selected departments option)
 */
var Department = {
		
    load: function (cId, dId) {
    	
        var c = cId ? cId : '#companyId';
        var d = dId ? dId : '#departmentId';
        if($(c).val() && !$(d).val()) {
        	Department.getDepartments($(c).val(), d);
        }
        $(c).change(function () {
            if ($(this).val() && $(d).length) {
                Department.getDepartments($(this).val(), d);
            }
        });
    },
    getDepartments: function (cid, dId, sel) {
        $.post(
            '/company/department/load?companyId=' + cid,
            {},
            function (rp) {
            	if(rp.code){
            		Department.updateDepartment(dId, rp.data, sel);
            	} else {
            		Department.updateDepartment(dId, [], sel);
            	}
                
            },
            'json'
        );
    },
    updateDepartment: function (id, d, sel) {
        if ($(id).length) {
            var options = "";
            for (var i in d) {
            	var item = d[i];
                if (sel == item.id) {
                    options += "<option selected value='" + item.id + "'>" + item.name + "</option>";
                } else {
                    options += "<option value='" + item.id + "'>" + item.name + "</option>";
                }
            }
            if (!$(id).find('option:first').val()) {
                options = "<option value=''>" + $(id).find('option:first').text() + "</option>" + options;
            }
            $(id).html(options);
        }
    }
};

/**
 * @author Hungpx Asset/Category
 * @param cId (select company ID)
 * @param dId (select department ID)
 * @param sel (selected departments option)
 */
var AssetCategory = {
		
    load: function (cId, dId) {
    	
        var c = cId ? cId : '#companyId';
        var d = dId ? dId : '#categoryId';
        if($(c).val() && !$(d).val()) {
        	AssetCategory.getCategories($(c).val(), d);
        }
        $(c).change(function () {
            if ($(this).val() && $(d).length) {
            	AssetCategory.getCategories($(this).val(), d);
            }
        });
    },
    getCategories: function (cid, dId, sel) {
        $.post(
            '/asset/category/load?companyId=' + cid,
            {},
            function (rp) {
            	if(rp.code){
            		AssetCategory.updateCategory(dId, rp.data, sel);
            	} else {
            		AssetCategory.updateCategory(dId, [], sel);
            	}
                
            },
            'json'
        );
    },
    updateCategory: function (id, d, sel) {
        if ($(id).length) {
            var options = "";
            for (var i in d) {
            	var item = d[i];
                if (sel == item.id) {
                    options += "<option selected value='" + item.id + "'>" + item.name + "</option>";
                } else {
                    options += "<option value='" + item.id + "'>" + item.name + "</option>";
                }
            }
            if (!$(id).find('option:first').val()) {
                options = "<option value=''>" + $(id).find('option:first').text() + "</option>" + options;
            }
            $(id).html(options);
        }
    }
};


/**
 * @author KienNN
 * @param cId (select company ID)
 * @param dId (select department ID)
 * @param sel (selected departments option)
 */
var IdeaCategory = {
    load: function (cId, dId) {
        var c = cId ? cId : '#companyId';
        var d = dId ? dId : '#categoryId';
        if($(c).val() && !$(d).val()) {
        	IdeaCategory.getCategories($(c).val(), d);
        }
        $(c).change(function () {
            if ($(this).val() && $(d).length) {
            	IdeaCategory.getCategories($(this).val(), d);
            }
        });
    },
    getCategories: function (cid, dId, sel) {
        $.post(
            '/idea/category/load',
            {companyId: cid},
            function (rp) {
            	if(rp.code){
            		IdeaCategory.updateCategory(dId, rp.data, sel);
            	} else {
            		IdeaCategory.updateCategory(dId, [], sel);
            	}
                
            },
            'json'
        );
    },
    updateCategory: function (id, d, sel) {
        if ($(id).length) {
            var options = "";
            for (var i in d) {
            	var item = d[i];
                if (sel == item.id) {
                    options += "<option selected value='" + item.id + "'>" + item.name + "</option>";
                } else {
                    options += "<option value='" + item.id + "'>" + item.name + "</option>";
                }
            }
            if (!$(id).find('option:first').val()) {
                options = "<option value=''>" + $(id).find('option:first').text() + "</option>" + options;
            }
            $(id).html(options);
        }
    }
};

/**
 * @author KienNN
 */
var ExpenseCategory = {
	load: function (cId, dId) {
        var c = cId ? cId : '#companyId';
        var d = dId ? dId : '#expenseCategoryId';
        if($(c).val() && !$(d).val()) {
        	ExpenseCategory.getCategories($(c).val(), d);
        }
        $(c).change(function () {
            if ($(this).val() && $(d).length) {
            	ExpenseCategory.getCategories($(this).val(), d);
            }
        });
    },
    getCategories: function (cid, dId, sel) {
        $.post(
            '/accounting/expense/loadcategories',
            {companyId: cid},
            function (rp) {
            	if(rp.code){
            		ExpenseCategory.updateCategory(dId, rp.data, sel);
            	} else {
            		ExpenseCategory.updateCategory(dId, [], sel);
            	}
                
            },
            'json'
        );
    },
    updateCategory: function (id, d, sel) {
        if ($(id).length) {
            var options = "";
            for (var i in d) {
            	var item = d[i];
                if (sel == item.id) {
                    options += "<option selected value='" + item.id + "'>" + item.displayName + "</option>";
                } else {
                    options += "<option value='" + item.id + "'>" + item.displayName + "</option>";
                }
            }
            if (!$(id).find('option:first').val()) {
                options = "<option value=''>" + $(id).find('option:first').text() + "</option>" + options;
            }
            $(id).html(options);
        }
    }	
};

/**
 * @author KienNN
 */
var AccountingAccount = {
	load: function (cId, dId, modeLoad) {
        var c = cId ? cId : '#companyId';
        var d = dId ? dId : '#accountId';
        if($(c).val() && !$(d).val()) {
        	AccountingAccount.getCategories($(c).val(), d, '', modeLoad);
        }
        $(c).change(function () {
            if ($(this).val() && $(d).length) {
            	AccountingAccount.getCategories($(this).val(), d, '', modeLoad);
            }
        });
    },
    getCategories: function (cid, dId, sel, modeLoad) {
        $.post(
            '/accounting/account/load',
            {
            	companyId: cid,
            	modeLoad: modeLoad ? modeLoad : ''
            },
            function (rp) {
            	if(rp.code){
            		AccountingAccount.updateCategory(dId, rp.data, sel);
            	} else {
            		AccountingAccount.updateCategory(dId, [], sel);
            	}
                
            },
            'json'
        );
    },
    updateCategory: function (id, d, sel) {
        if ($(id).length) {
            var options = "";
            for (var i in d) {
            	var item = d[i];
                if (sel == item.id) {
                    options += "<option selected value='" + item.id + "'>" + item.displayName + "</option>";
                } else {
                    options += "<option value='" + item.id + "'>" + item.displayName + "</option>";
                }
            }
            if (!$(id).find('option:first').val()) {
                options = "<option value=''>" + $(id).find('option:first').text() + "</option>" + options;
            }
            $(id).html(options);
        }
    }	
};

/**
 * @author LuongNV
 * @param cId (select company ID)
 * @param tId (select testSubject ID)
 * @param sel (selected testSubjects option)
 */
var TestSubject = {
    load: function (cId, tId) {
        var c = cId ? cId : '#companyId';
        var t = tId ? tId : '#subjectId';
        if($(c).val() && !$(t).val()) {
            TestSubject.getTestSubjects($(c).val(), t);
        }
        $(c).change(function () {
            if ($(this).val() && $(t).length) {
                TestSubject.getTestSubjects($(this).val(), t);
            }
        });
    },
    getTestSubjects: function (cid, tId, sel) {
        $.post(
            '/hrm/test/load?companyId=' + cid,
            {},
            function (rp) {
                TestSubject.updateTestSubject(tId, rp, sel);
            },
            'json'
        );
    },
    updateTestSubject: function (id, t, sel) {
        if ($(id).length) {
            var options = "";
            for (var i in t) {
                if (sel == i) {
                    options += "<option selected value='" + i + "'>" + t[i] + "</option>";
                } else {
                    options += "<option value='" + i + "'>" + t[i] + "</option>";
                }
            }
            if (!$(id).find('option:first').val()) {
                options = "<option value=''>" + $(id).find('option:first').text() + "</option>" + options;
            }
            $(id).html(options);
        }
    }
};

/**
 * @author AnhNV
 * @param proId (select project ID)
 * @param planId (select plan ID)
 * @param sel (selected plan option)
 */
var Plan = {
    load: function (proId, planId) {
        var c = proId ? proId : '#projectId';
        var d = planId ? planId : '#projectPlanId';
        if($(c).val() && !$(d).val()) {
        	Plan.getPlans($(c).val(), d);
        }
        $(c).change(function () {
            console.log('1');
            if ($(this).val() && $(d).length) {
                console.log('2');
                Plan.getPlans($(this).val(), d);
            }
        });
    },
    getPlans: function (proId, planId, sel) {
        $.post(
            '/work/plan/load?projectId=' + proId,
            {},
            function (rp) {
                Plan.updatePlan(planId, rp, sel);
            },
            'json'
        );
    },
    updatePlan: function (id, d, sel) {
        if ($(id).length) {
            var options = "";
            for (var i in d) {
                if (sel == i) {
                    options += "<option selected value='" + i + "'>" + d[i] + "</option>";
                } else {
                    options += "<option value='" + i + "'>" + d[i] + "</option>";
                }
            }
            if (!$(id).find('option:first').val()) {
                options = "<option value=''>" + $(id).find('option:first').text() + "</option>" + options;
            }
            $(id).html(options);
        }
    }
};

/**
 * @author AnhNV
 * @param proId (select project ID)
 * @param parentId (select plan ID)
 * @param sel (selected plan option)
 * Add Task Category (select parentId)
 */
var TaskCategory = {
    load: function (proId, parentId, type) {
        var c = proId ? proId : '#projectId';
        var d = parentId ? parentId : '#categoryId';
        var t = type ? type : 'nomal';
        if($(c).val() && !$(d).val()) {
        	TaskCategory.getTasks($(c).val(), d, '', t);
        }
        $(c).change(function () {
            if ($(this).val() && $(d).length) {
            	TaskCategory.getTasks($(this).val(), d, '', t);
            }
        });
    },
    getTasks: function (proId, parentId, sel, type) {
        $.post(
            '/work/task/loadcategory?projectId=' + proId + '&type=' + type,
            {},
            function (rp) {
            	if(rp.code){
            		TaskCategory.updateTask(parentId, rp.data, sel);
            	} else {
            		alert(rp.messages);
            	}
            },
            'json'
        );
    },
    updateTask: function (id, d, sel) {
        if ($(id).length) {
            var options = "";
            for (var i in d) {
                if (sel == i) {
                    options += "<option selected value='" + d[i].id + "'>" + d[i].name + "</option>";
                } else {
                    options += "<option value='" + d[i].id + "'>" + d[i].name + "</option>";
                }
            }
            if (!$(id).find('option:first').val()) {
                options = "<option value=''>" + $(id).find('option:first').text() + "</option>" + options;
            }
            $(id).html(options);
        }
    }
};

/**
 * @author AnhNV
 * @param proId (select project ID)
 * @param projectUser (selected plan option)
 * Add ProjectUser (select parentId)
 */
var ProjectUser = {
    load: function (proId, assignedToId) {
        var c = proId ? proId : '#projectId';
        var d = assignedToId ? assignedToId : '#assignedToId';
        if($(c).val() && !$(d).val()) {
        	ProjectUser.getProjectUser($(c).val(), d);
        }
        $(c).change(function () {
            if ($(this).val() && $(d).length) {
                ProjectUser.getProjectUser($(this).val(), d);
            }
        });
    },
    getProjectUser: function (proId, assignedToId, sel) {
        $.post(
            '/work/project/loadprojectuser?projectId=' + proId,
            {},
            function (rp) {
            	ProjectUser.updateProjectUser(assignedToId, rp, sel);
            },
            'json'
        );
    },
    updateProjectUser: function (id, d, sel) {
        if ($(id).length) {
            var options = "";
            for (var i in d) {
                if (sel == i) {
                    options += "<option selected value='" + i + "'>" + d[i] + "</option>";
                } else {
                    options += "<option value='" + i + "'>" + d[i] + "</option>";
                }
            }
            if (!$(id).find('option:first').val()) {
                options = "<option value=''>" + $(id).find('option:first').text() + "</option>" + options;
            }
            $(id).html(options);
        }
    }
};

/**
 * @author DuongNQ
 * @param proId (select project ID)
 * @param docCateId (select documentCategory ID)
 * @param sel (selected plan option)
 * Add Document Category (select parentId)
 */
var DocumentCategory = {
    load: function (cId,dId, docCateId) {
        var c = cId ? cId : '#companyId';
        var d = docCateId ? docCateId : '#categoryId';     
        if($(c).val()) {
        	DocumentCategory.getCate($(c).val(), d);
        }
        $(c).change(function () {
            if ($(this).val() && $(d).length) {
            	DocumentCategory.getCate($(this).val(), d, '');
            }
        });
    },
    getCate: function (cId, docCateId, sel) {
        $.post(
            '/document/document/loadcategory?companyId='+cId,
            {},
            function (rp) {
            	if(rp.code){
            		DocumentCategory.updateCate(docCateId, rp.data, sel);
            	} else {
            		alert(rp.messages);
            	}
                
            },
            'json'
        );
    },
    updateCate: function (id, d, sel) {
        if ($(id).length) {
            var options = "";
            for (var i in d) {
                if (sel == i) {
                    options += "<option selected value='" + d[i].id + "'>" + d[i].name + "</option>";
                } else {
                    options += "<option value='" + d[i].id + "'>" + d[i].name + "</option>";
                }
            }
            if (!$(id).find('option:first').val()) {
                options = "<option value=''>" + $(id).find('option:first').text() + "</option>" + options;
            }
            $(id).html(options);
        }
    }
};
/**
 * @author KienNN
 */
var Project = {
	load: function(cId, pId, options){
		var defaultOptions = {'type': 'tree'};
		var loadOptions = $.extend(defaultOptions, options);
		var companySelector = cId ? cId : '#companyId';
		var projectSelector = pId ? pId : '#projectId';
        if($(companySelector).val()) {
        	Project.getProject($(companySelector).val(), projectSelector);
        }
		$(companySelector).change(function(){
			loadOptions.companyId = $(this).val();
			Project.getProject(projectSelector, loadOptions);
		});
	},
	getProject: function(projectSelector, options){
		 $.post(
            '/work/project/load',
            options,
            function (rp) {
            	if(rp.code){
            		Project.updateProject(projectSelector, rp.data, '');
            	} else {
            		alert(rp.messages);
            	}
            },
            'json'
        );
	},
	updateProject: function(projectSelector, projects, sel){
		if ($(projectSelector).length) {
            var options = "";
            if (!$(projectSelector).find('option:first').val()) {
                options = "<option value=''>" + $(projectSelector).find('option:first').text() + "</option>" + options;
            }
            for (var i in projects) {
                if (sel == projects[i].id) {
                    options += "<option selected value='" + projects[i].id + "'>" + projects[i].name + "</option>";
                } else {
                    options += "<option value='" + projects[i].id + "'>" + projects[i].name + "</option>";
                }
            }
            $(projectSelector).html(options);
        }
	}
};

var Product = {
	load: function(cId, pId, options){
		var defaultOptions = {'type': 'tree'};
		var loadOptions = $.extend(defaultOptions, options);
		var companySelector = cId ? cId : '#companyId';
		var productSelector = pId ? pId : '#productId';
		$(companySelector).change(function(){
			loadOptions.companyId = $(this).val();
			Product.getProduct(productSelector, loadOptions);
		});
	},
	getProduct: function(productSelector, options){
		 $.post(
           '/company/product/load',
           options,
           function (rp) {
           	if(rp.code){
           		Product.updateProduct(productSelector, rp.data);
           	} else {
           		Product.updateProduct(productSelector, []);
           	}
           },
           'json'
       );
	},
	updateProduct: function(productSelector, products){
		if ($(productSelector).length) {
            var options = "";
            if (!$(productSelector).find('option:first').val()) {
                options = "<option value=''>" + $(productSelector).find('option:first').text() + "</option>";
            }
            for (var i in products) {
                options += "<option value='" + products[i].id + "'>" + products[i].name + "</option>";
            }
            $(productSelector).html(options);
        }
	}
};

var CompanyRole = {
	load: function(companySelector, roleSelector, options){
		companySelector = companySelector ? companySelector : '#companyId';
		roleSelector = roleSelector ? roleSelector : '#role';
		var defaultOptions = {};
		var loadOptions = $.extend(defaultOptions, options);
		$(companySelector).change(function(){
			loadOptions.companyId = $(this).val();
			CompanyRole.getRole(roleSelector, loadOptions);
		});
	},
	getRole: function(roleSelector, loadOptions){
		$.post(
           '/company/role/load',
           loadOptions,
           function (rp) {
           	if(rp.code){
           		CompanyRole.updateRole(roleSelector, rp.data);
           	} else {
           		CompanyRole.updateRole(roleSelector, []);
           	}
           },
           'json'
       );
	},
	updateRole: function(roleSelector, roles){
		if ($(roleSelector).length) {
			 var options = "";
	            if (!$(roleSelector).find('option:first').val()) {
	                options = "<option value=''>" + $(roleSelector).find('option:first').text() + "</option>";
	            }
	            for (var i in roles) {
	                options += "<option value='" + roles[i].id + "'>" + roles[i].name + "</option>";
	            }
	            $(roleSelector).html(options);
		}
	}
};

var CompanyTitle = {
		load: function(companySelector, titleSelector, options){
			companySelector = companySelector ? companySelector : '#companyId';
			titleSelector = titleSelector ? titleSelector : '#titleId';
			var defaultOptions = {};
			var loadOptions = $.extend(defaultOptions, options);
			$(companySelector).change(function(){
				loadOptions.companyId = $(this).val();
				CompanyTitle.getTitle(titleSelector, loadOptions);
			});
		},
		getTitle: function(titleSelector, loadOptions){
			$.post(
	           '/company/title/load',
	           loadOptions,
	           function (rp) {
	           	if(rp.code){
	           		CompanyTitle.updateTitle(titleSelector, rp.data);
	           	} else {
	           		CompanyTitle.updateTitle(titleSelector, []);
	           	}
	           },
	           'json'
	       );
		},
		updateTitle: function(titleSelector, roles){
			if ($(titleSelector).length) {
				 var options = "";
		            if (!$(titleSelector).find('option:first').val()) {
		                options = "<option value=''>" + $(titleSelector).find('option:first').text() + "</option>";
		            }
		            for (var i in roles) {
		                options += "<option value='" + roles[i].id + "'>" + roles[i].name + "</option>";
		            }
		            $(titleSelector).html(options);
			}
		}	
}
var CompanyUser = {
		init: function(nameSelector, idSelector, companySelector, options){
			nameSelector = nameSelector ? nameSelector : '#userName';
			idSelector = idSelector ? idSelector : '#userId';
			companySelector = companySelector ? companySelector : '#companyId';
			var defaultOptions = {
				'page' : 'default' 
			};
			var loadOptions = $.extend(defaultOptions, options);
			$(nameSelector).autocomplete({
				source: function(request, response){
					$.post(
						'/system/user/suggest',
						$.extend({
							'q' : request.term,
							'companyId' : $(companySelector).val()
						}, loadOptions),
						function(rs){
							if(rs.code){
								response(rs.data);
							} else {
								response([]);
							}
						}
					);
				},
				minLength: 2,
				select: function(event, ui){
					$(idSelector).val(ui.item.id);
				},
			});
		}	
	}

var Datagrid = {
	init: function(tableName){
		tableName = tableName ?tableName:'.dgContainer table.table';
		$(tableName).each(function(){
	        $(this).find('tr').each(function(i, tr){
	            $(tr).find('td').each(function(j, td){
	                var rowspan = $(td).attr('rowspan');
	                if (rowspan){
	                    var next = $(tr);
	                    for(var k = 1; k <= rowspan; k++){
	                        next.attr('trid', i);
	                        next.addClass('trid-'+i);
	                        next = next.next();
	                    }
	                }
	            });
	        });
			$(this).find('tr:odd').removeClass('even');
			$(this).find('tr:even').addClass('even');
			$(this).find('tr').hover(
				function(){
	                var trid = $(this).attr('trid');
	                if (trid) {
	                	$('tr.trid-'+trid).addClass('h');
	                } else {
	                	$(this).addClass('h');
	                }
	            },
				function(){
	                var trid = $(this).attr('trid');
	                if (trid) {
	                	$('tr.trid-'+trid).removeClass('h');
	                } else {
	                	$(this).removeClass('h');
	                }
	            }
			);
		});
		setTimeout(Datagrid.stickHeader(tableName), 1000);
	},
	stickHeader: function(tableName){
		tableName = tableName ?tableName:'.dgContainer table.table';
		$('.stickyHeader').remove();
		$(tableName).each(function(i, table){
			if ($(table).hasClass('notStickHeader')){
				return;
			}
			var theadClone = $(table).find('thead').clone(false, false);
			theadClone.find('input').each(function(){
				$(this).attr('id', '');
			});
			var stickyHeader =  $('<div style="display:none"></div>').addClass('stickyHeader');
			stickyHeader.append($('<table class="table table-bordered" cellspacing=0 cellpadding=0></table>')).find('table').append(theadClone);
			if ($(table).next().hasClass('stickyHeader')){
				$(table).next().remove();
			}
			$('.dgContainer').prepend(stickyHeader);

			var tableHeight = $(table).height();
			var tableWidth = $(table).width() + Number($(table).css('padding-left').replace(/px/ig,"")) + Number($(table).css('padding-right').replace(/px/ig,"")) + Number($(table).css('border-left-width').replace(/px/ig,"")) + Number($(table).css('border-right-width').replace(/px/ig,""));

			var headerCells = $(table).find('thead th');
			var headerCellHeight = $(headerCells[0]).height();

			var stickyHeaderCells = stickyHeader.find('th');
			stickyHeader.css('width', tableWidth);

			for (i=0; i<headerCells.length; i++) {
				var headerCell = $(headerCells[i]);
				var cellWidth = headerCell.outerWidth();
				$(stickyHeaderCells[i]).css({'width': cellWidth}).removeClass('role-header');
			}
			$(window).scroll(function() {
				var currentPosition = $(window).scrollTop();
				var cutoffTop = $(table).offset().top;
				var cutoffBottom = tableHeight + cutoffTop - headerCellHeight;
				if (currentPosition > cutoffTop && currentPosition < cutoffBottom) {
					stickyHeader.show();
				} else {
					stickyHeader.hide();
				}
			});
		});
	},
	destroy: function(tableName){
		tableName = tableName ?tableName:'.dgContainer table.table';
		$(tableName).each(function(){
			$(this).closest('div.dgContainer').find('.stickyHeader').remove();
		});
	}
}
var Company = {
	init: function(suggestInput){
		suggestInput = suggestInput?suggestInput:'.erp-form-companyIdSuggest';
		$('.erp-form-companyIdSuggest').each(function(){
			var id_lookup = $(this).attr('data-lookup');
			$(this).autocomplete({
				source: function(request, response){
					$.post(
						'/company/manage/suggest',
						{
							'q' : request.term
						},
						function(rs){
							if(rs.code){
								response(rs.data);
							} else {
								response([]);
							}
						}
					);
				},
				minLength: 2,
				select: function(event, ui){
					$('#' + id_lookup).val(ui.item.id);
					
				},
			});
			$(this).on('change', function(){
				if(!$(this).val()){
					var id_lookup = $(this).attr('data-lockup');
					$('#' + id_lookup).val('');
				}
			});
		});
	}
}
var User = {
		init: function(suggestInput){
			suggestInput = suggestInput?suggestInput:'.erp-form-userIdSuggest';
			$(suggestInput).each(function(){
				var id_lookup = $(this).attr('data-lookup');
				$(this).autocomplete({
					source: function(request, response){
						$.post(
							'/system/user/suggest',
							{
								'q' : request.term,
								'page': 'user.manageable'
							},
							function(rs){
								if(rs.code){
									response(rs.data);
								} else {
									response([]);
								}
							}
						);
					},
					minLength: 2,
					select: function(event, ui){
						$('#' + id_lookup).val(ui.item.id);
						
					},
				});
				$(this).on('change', function(){
					if(!$(this).val()){
						var id_lookup = $(this).attr('data-lockup');
						$('#' + id_lookup).val('');
					}
				});
			});
		}
	}
var ERP_Type = {
	formatDecimal: function(n){
		n += '';
		if(!$.trim(n)){
			return '';
		}
		// /^\d+$/
	    if(ERP_Type.isInt(n)){
	        if(/^-{0,1}\d*\.{0,1}\d+$/.test(n)){
	            var result = '';
	            while(n.length > 3){
	                result = ERP_Type.nfs + n.substr(n.length-3, 3) + result;
	                n = n.substring(0, n.length-3);
	            }
	            return (n + result).replace('-' + ERP_Type.nfs, '-');
	        } else {
	            return '';
	        }
	    }else{
	        return n;
	    }
	},
	isInt: function(i){
		return /^\d+$/.test(i);
	},
	nfs: '.'
}

/**
 * @author LuongNV
 * @param cId (select company ID)
 * @param rId (select reason ID)
 * @param sel (selected reasons option)
 */
var Reason = {
    load: function (cId, rId) {
        var c = cId ? cId : '#companyId';
        var r = rId ? rId : '#reasonId';
        if ($(c).val() && !$(r).val()) {
            Reason.getReasons($(c).val(), r);
        }
        $(c).change(function () {
            if ($(this).val() && $(r).length) {
                Reason.getReasons($(this).val(), r);
            }
        });
    },
    getReasons: function (cid, rId, sel) {
        $.post(
            '/crm/reason/load?companyId=' + cid,
            {},
            function (rp) {
                Reason.updateReason(rId, rp, sel);
            },
            'json'
        );
    },
    updateReason: function (id, r, sel) {
        if ($(id).length) {
            var options = "";
            for (var i in r) {
                if (sel == i) {
                    options += "<option selected value='" + i + "'>" + r[i] + "</option>";
                } else {
                    options += "<option value='" + i + "'>" + r[i] + "</option>";
                }
            }
            if (!$(id).find('option:first').val()) {
                options = "<option value=''>" + $(id).find('option:first').text() + "</option>" + options;
            }
            $(id).html(options);
        }
    }
};

var ContractTemplate = {
	load: function (companyId, documentId) {
        var c = companyId ? companyId : '#companyId';
        var d = documentId ? documentId : '#documentId';
        if ($(c).val() && !$(d).val()) {
        	ContractTemplate.getTemplate($(c).val(), d, '');
        }
        $(c).change(function () {
            if ($(this).val() && $(d).length) {
            	ContractTemplate.getTemplate($(this).val(), d, '');
            }
        });
    },	
    getTemplate: function (cid, documentId, sel){
    	$.post(
                '/document/contract/load',
                {
                	'companyId': cid
                },
                function (rp) {
                	if(typeof rp.code != 'undefined' && rp.code){
                		ContractTemplate.updateTemplate(documentId, rp.data, sel);
                	} else {
                		ContractTemplate.updateTemplate(documentId, [], sel);
                	}
                    
                },
                'json'
            );
    },
    updateTemplate: function(id, r, sel){
    	if ($(id).length) {
            var options = "";
            for (var i in r) {
            	var ri = r[i];
                if (sel == ri.id) {
                    options += "<option selected value='" + ri.id + "'>" + ri.name + "</option>";
                } else {
                    options += "<option value='" + ri.id + "'>" + ri.name + "</option>";
                }
            }
            if (!$(id).find('option:first').val()) {
                options = "<option value=''>" + $(id).find('option:first').text() + "</option>" + options;
            }
            $(id).html(options);
        }
    }
    
}
var Campaign = {
		load: function (companyId, campaignId) {
	        var c = companyId ? companyId : '#companyId';
	        var d = campaignId ? campaignId : '#campaignId';
	        if ($(c).val() && !$(d).val()) {
	        	Campaign.getTemplate($(c).val(), d, '');
	        }
	        $(c).change(function () {
	            if ($(this).val() && $(d).length) {
	            	Campaign.getTemplate($(this).val(), d, '');
	            }
	        });
	    },	
	    getTemplate: function (cid, documentId, sel){
	    	$.post(
	                '/crm/campaign/load',
	                {
	                	'companyId': cid
	                },
	                function (rp) {
	                	if(typeof rp.code != 'undefined' && rp.code){
	                		Campaign.updateTemplate(documentId, rp.data, sel);
	                	} else {
	                		Campaign.updateTemplate(documentId, [], sel);
	                	}
	                    
	                },
	                'json'
	            );
	    },
	    updateTemplate: function(id, r, sel){
	    	if ($(id).length) {
	            var options = "";
	            for (var i in r) {
	            	var ri = r[i];
	                if (sel == ri.id) {
	                    options += "<option selected value='" + ri.id + "'>" + ri.name + "</option>";
	                } else {
	                    options += "<option value='" + ri.id + "'>" + ri.name + "</option>";
	                }
	            }
	            if (!$(id).find('option:first').val()) {
	                options = "<option value=''>" + $(id).find('option:first').text() + "</option>" + options;
	            }
	            $(id).html(options);
	        }
	    }	
}