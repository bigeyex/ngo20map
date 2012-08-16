//专为ngo设计的验证组件
//by Scott Wang
//bigeyex@gmail.com

/*
使用方法：
需要两个变量 validate_require_string (必要字段)
validate_require_error_string (必要字段错误信息)
因为这两个比较常用，所以不适合在每个标签都写一下。

现有功能：
必要字段验证，加class="required"
email验证, class="email" validate-email-error-message
ajax验证，class="ajax-validate", validate-url="验证地址" 返回ok为正常，其他为错误信息
密码确认验证, class="match-validate", match="相同字段id" validate-match-error-message
数字验证, class="numeric"
*/

function validate(item){
	jq_item = $(item);					//当前验证元素的jquery对象
	jq_validate_hint = jq_item.next();	//用来显示错误信息的span
	if(jq_item.hasClass('required')){
		if(jq_item.val() == ''){
			jq_validate_hint.text(validate_require_error_string);
			jq_validate_hint.removeClass();
			jq_validate_hint.addClass('validate-error');
			return false;
		}
	}
	if(jq_item.hasClass('min-checked')){
		if(jq_item.find('input:checked').length < jq_item.attr('min-checked')){
			alert(jq_item.attr('validate-email-error-message'));
			return false;
		}
		else{
			return true;
		}
	}
	if(jq_item.hasClass('email')){
		if(jq_item.val().search(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/) == -1){
			jq_validate_hint.text(jq_item.attr('validate-email-error-message'));
			jq_validate_hint.removeClass();
			jq_validate_hint.addClass('validate-error');
			return false;
		}
	}
	if(jq_item.hasClass('strong-password')){
		var item_val = jq_item.val();
		if(item_val.length<6 || item_val.search(/^\d+$/) != -1 || item_val.search(/\d+/) == -1){
			if(item_val.length<6){
				jq_validate_hint.text(jq_item.attr('validate-min-char-message'));
			}
			else if(item_val.search(/^\d+$/) != -1){
				jq_validate_hint.text(jq_item.attr('validate-has-letter-message'));
			}
			else if(item_val.search(/\d+/) == -1){
				jq_validate_hint.text(jq_item.attr('validate-has-number-message'));
			}
			jq_validate_hint.removeClass();
			jq_validate_hint.addClass('validate-error');
			return false;
		}
	}
	if(jq_item.hasClass('numeric')){
		if(isNaN(jq_item.val())){
			jq_validate_hint.text(jq_item.attr('validate-numeric-error-message'));
			jq_validate_hint.removeClass();
			jq_validate_hint.addClass('validate-error');
			return false;
		}
	}
	if(jq_item.hasClass('match-validate')){
		if(jq_item.val() != $('#'+jq_item.attr('match')).val()){
			jq_validate_hint.text(jq_item.attr('validate-match-error-message'));
			jq_validate_hint.removeClass();
			jq_validate_hint.addClass('validate-error');
			return false;
		}
	}
	
	//注意：ajax验证永远是在最后一位的。
	//这里面做个折中：如果以前有验证过该字段有错误，则返回错误。否则视为正确。
	if(jq_item.hasClass('ajax-validate')){
		if(jq_item.val() != ''){
			$.get(jq_item.attr('validate-url'),{q:jq_item.val()},function(result){
				if(result == 'ok'){
					jq_validate_hint.text(' ');
					jq_validate_hint.removeClass();
					jq_validate_hint.addClass('validate-ok');
				}
				else{
					jq_validate_hint.text(result);
					jq_validate_hint.removeClass();
					jq_validate_hint.addClass('validate-error');
				}
			})
			if(jq_validate_hint.hasClass('validate-error')){
				return false;
			}
		}
		else{	//如果是空字段的话，去掉所有标记
			jq_validate_hint.text(' ');
			jq_validate_hint.removeClass();
		}
	}
	else{		//如果不是ajax验证，前面没有问题就ok了。
		jq_validate_hint.text(' ');
		jq_validate_hint.removeClass();
		jq_validate_hint.addClass('validate-ok');
	}
	return true;
}

//增加验证字段，在失去焦点的时候立即验证
$('.required').after('<span class="validate-required">'+validate_require_string+'</span>');
$('.optional').after('<span></span>');
$('.required,.optional').blur(function(){
	validate(this);
});

$('form.need-validate').submit(function(){
	var validate_ok = true;
	$(this).find('.required,.optional,.check-group').each(function(){
		if(!validate(this)){
			this.focus();
			validate_ok = false;
			return false;
		}
	});
	
	if(!validate_ok){
		return false;
	}

});