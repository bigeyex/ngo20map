//全选当前页事件
function CheckAll(strSection)
{
    var i;
    var colInputs = $('.'+strSection).find('input');
    for(i=1;i<colInputs.length;i++)
    {
        colInputs[i].checked=colInputs[0].checked;
    }
}

function over(item){
    $(item).addClass('over');
}

function out(item){
    $(item).removeClass('over');
}

function add(){
    window.location.href=app_path+'/Event/add/';
}

//获取已选事件ids
function getSelectCheckboxValues(){
	var obj = document.getElementsByName('key');
	var result ='';
	for (var i=0;i<obj.length;i++)
	{
		if (obj[i].checked==true)
				result += obj[i].value+",";

	}
	return result.substring(0, result.length-1);
}

//标签点击响应事件
function switchtab(tabid){
    $('.tabpanel li').removeClass('current');
    $('#'+tabid+'-events').addClass('current');
    $('#tabs').attr("class",tabid);
    $('#syllabus').load(app_path+'/Admin/ajaxevent/p/1',{type:tabid});   
}

//点击批量操作标签响应事件
function batch(item,action,type){
	var keyValue= getSelectCheckboxValues();

	if (!keyValue)
	{
		alert(check_word);
		return false;
	}
        else{
            $(item).fastConfirm({
                                    position: "right",
                                    questionText:lock_word,
                                    onProceed: function(trigger) {
                                           window.location.href=app_path+'/Admin/batch/ids/'+keyValue+'/action/'+action+'/type/'+type;
                                    },
                                    onCancel: function(trigger) {
                                    }
                            });
             return false;
        }
}

$(function(){

    
});
    