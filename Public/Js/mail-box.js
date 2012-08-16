mailbox_init = false;	//是否已经初始化站内信
last_check_time = 0;
last_message_check_time = 0;
timer_talk = 0;
timer_message = 0;



// 显示站内信框
function expand_mailbox(e, user_id){
	if(!mailbox_init){
		mailbox_init = true;
		$(document.body).click(function(){
			hide_mailbox();
		});		//点击页面其他位置隐藏
		$('#mail-box').click(function(e){
			if(e.stopPropagation){
				e.stopPropagation();
			}
			else{
				window.event.concelBubble = true;
			}
		});		//点击mailbox不隐藏
		$('#message-target').autocomplete(app_path+"/Event/suggest",{
		   	multiple: true,
			matchContains: true
	   });
	   load_talks(0);
	}
	$('#mail-box').toggle();
	$('#mail-box-menu-link').toggleClass('expanded');
	if(user_id != 0){
		show_messages(user_id);
	}
	
	if(e.stopPropagation){	//防止body再收到onclick, 隐藏站内信框
		e.stopPropagation();
	}
	else{
		window.event.concelBubble = true;
	}	
}
function hide_mailbox(){
	$('#mail-box').hide();
	$('#mail-box-menu-link').removeClass('expanded');
}

function load_talks(t){
	if(timer_talk != 0){
		window.clearInterval(timer_talk);
	}
	$.ajax({
        type:"GET",
        url:app_path+"/Message/json_talk_list",
        data:{'t': t},
        dataType: 'json',
        success: function(result){
			for(var key in result.data){
				var item = result.data[key];
				var image_url;
				if(item.image == null){
					image_url = 'Img/default_photo.png';
				}
				else{
					image_url = 'Uploadedthumb/thumbs_'+item.image;
				}
				if(item.unread_count>0){
					item.unread_count = '<span class="unread-count">'+item.unread_count+'</span>';
				}
				$('#talk-item-id-' + item.id).remove();
				$('#talk-list').prepend('<div class="talk-item" id="talk-item-id-'+item.id+'" onclick="show_messages('+item.other_user_id+')">'+
											'<div class="talk-avatar">'+
												'<img src="'+app_path+'/Public/'+image_url+'" width="40" height="40"/>'+
											'</div>'+
											'<div class="talk-item-content">'+
												'<p class="name">'+item.name+'('+item.unread_count+'/'+item.record_count+')</p><p class="date">'+item.create_time+'</p>'+
												'<p class="message">'+item.content+'</p>'+
											'</div>'+
										'<div class="clearfix"></div></div>');
			
			}
		}
	
	}); //$.ajax
	
	timer_talk = window.setInterval("load_talks(1)", 15000);
}

function load_messages(id, t){
	if(timer_message != 0){
		window.clearInterval(timer_message);
	}
	
	$.ajax({
        type:"GET",
        url:app_path+"/Message/json_message_list",
        data:{'t': t, 'id': id},
        dataType: 'json',
        success: function(result){
        	var user = result.data.other_user;
        	var myself = result.data.myself;
        	var image_url;
        	var myself_image_url;
        	$('#message-target').val(user.name);
			if(user.image == null){
				other_image_url = 'Img/default_photo.png';
			}
			else{
				other_image_url = 'Uploadedthumb/thumbs_'+user.image;
			}
			if(myself.image == null){
				my_image_url = 'Img/default_photo.png';
			}
			else{
				my_image_url = 'Uploadedthumb/thumbs_'+myself.image;
			}
			for(var key in result.data.messages){
				var item = result.data.messages[key];
				var my_class;
				var user_home_link;
				if(item.is_mine == 1){
					my_class="myself";
					image_url = my_image_url;
					user_home_link = myself.name;
				}
				else{
					my_class="";
					image_url = other_image_url;
					user_home_link = '<a href="'+app_path+'/User/home/id/'+user.id+'">'+user.name+'</a>';
				}
				$('#message-item-id-' + item.id).remove();
				$('#message-contents').prepend(
					'<div id="message-item-id-'+item.id+'" class="message-item '+my_class+'">'+
						'<div class="message-avatar">'+
							'<img src="'+app_path+'/Public/'+image_url+'" width="40" height="40"/>'+
						'</div>'+
						'<div class="message-item-content">'+
							'<p class="name">'+user_home_link+'</p><p class="date">'+item.create_time+'</p>'+
							'<p class="message">'+item.content+'</p>'+
						'</div>'+
					'<div class="clearfix"></div></div>');
			
			} // for key in messages
		} // success of ajax
	
	});
	
	timer_message = window.setInterval("load_messages("+id+",1)", 15000);
}


//切换到站内信细节
function new_message(){
	//清空message-section里面的信息
	$('#message-target').val('');
	$('#message-writer').val('');
	$('#message-target').attr('disabled', false);
	$('#message-target').addClass('inputbox');
	$('#mail-waiting-ball').hide();
	$('#message-contents').empty();	
	$('#talk-section').hide();
	$('#message-section').show();
}

function show_messages(id){
	$('.message-item').remove();
	load_messages(id,0);
	$('#message-target').attr('disabled', true);
	$('#message-target').removeClass('inputbox');
	$('#mail-waiting-ball').hide();
	
	$('#talk-section').hide();
	$('#message-section').show();
}

//返回对话列表
function back_to_talks(){
	if(timer_message != 0){
		window.clearInterval(timer_message);
	}
	$('#talk-section').show();
	$('#message-section').hide();
}

function send_message(){
	data = {
		'user_name' : $('#message-target').val(),
		'content' : $('#message-writer').val()
	}
	
	if(data.user_name == ''){
		alert('需要指定收件人');
		return;
	}
	if(data.content == ''){
		alert('内容不能为空');
		return;
	}
	$('#message-writer').val("");
	$.post(app_path+"/Message/send_message", data, function(result){
		if(result != 'success'){
			$('#message-contents').prepend(result);
		}
		else{
			$('#message-contents').prepend(
						'<div class="message-item">'+
							'<div class="message-avatar">'+
							'</div>'+
							'<div class="message-item-content">'+
								'<p class="name">'+my_label+'</p>'+
								'<p class="message">'+data.content+'</p>'+
							'</div>'+
						'</div>');
		}
	})
}

function ctrl_enter(e){
	if(e.ctrlKey && e.keyCode == 13){
		send_message();
	}
}