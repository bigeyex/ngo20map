/* 
 * controls homepage js logic
 */
$(function(){

	map = new BMap.Map("home-map-container");
	var point = new BMap.Point(106.027925, 36.549303);
	map.centerAndZoom(point, 4);
	map.addControl(new BMap.NavigationControl());
	//map.enableScrollWheelZoom();                  // 启用滚轮放大缩小。
	map.addEventListener("zoomend", ajax_cluster);
	$('#hide-filter-button,#show-filter-button').click(function(){
		$('#hide-filter-button').toggle();
		$('#show-filter-button').toggle();
		$('#hideable-filters').toggle();
	});
	var my_icon = new BMap.Icon(app_path+'/Public/Img/icons/event_marker.gif', new BMap.Size(16, 16), {   
	   offset: new BMap.Size(8, 16)
	}); 
	float_marker = new BMap.Marker(new BMap.Point(0,0), {icon: my_icon});  
	map.addOverlay(float_marker); 
	float_marker.disableMassClear();
	float_marker.setTop(true);
})

function move_marker(longitude, latitude){
	float_marker.setPosition(new BMap.Point(longitude, latitude));
}


//下面定义一些全局变量，保存每一次的搜索条件。
//若变量值为空则无搜索条件
var filter_model = 'all';	//查询的数据模型类型，users还是events
var filter_type = 'all';	//查询的对象类型，ngo,csr,fund,ind
var old_filter_type = 'all';	//以前的对象类型，用来应对在类型标签临时切换类型的情况
var filter_progress = '';	//事件的进度
var filter_res_tag = '';	//物资需求、资金需求etc.
var filter_res_tag2 = '';	//资源标签.
var filter_field = '';		//从事领域类型
var filter_keyword = '';	//关键词
//下面的是显示细节时所用的搜索条件

//点击“公益组织”、“公益事件” etc时改变搜索条件
//如果只改变model，其他参数可以是null
//eg. filter(null, null, 'users')
function filter(item,type,model){
	if(item){	//代表点击了筛选标签的情况
	    $('.selected').removeClass('selected');
	    $(item).addClass('selected');
	    $('.all').addClass('selected');
	    $('#radio-all').attr('checked',true);	//重置“组织”和“项目”的选项开关
	    $('.tab-only').hide();
	    $('.tab-'+type+'-only').show();
	    $('.tab-only li').removeClass('current');
	    $('.tab-'+type+'-only li:first').addClass('current');
    }
    if(type){
	    filter_type = type;
    }
    filter_model = model;
    ajax_cluster();
    $('.filter-group').show();
    if(model != 'events'){   //根据情况显示筛选项. 只有event才需要筛选进度、需求类型，其他情况则隐藏
        $('.user-only').show();
        $('.event-only').hide();
    }else{
    	if(type != 'ind'){
        	$('.event-only').show();
        }else{
	        $('.filter-group').hide();
	        $('.ind-only').show();
        }
        $('.user-only').hide();
    }
    
}

//点击搜索按钮时执行搜索
function key_search(){
    $('#detail-container').empty();
    var value = $("#key").val();
    filter_keyword = value;
    if(value != ""){	//增加历史条件
          $(".key-condition").remove();
        	$('#filter-history').append('<div class="filter-condition key-condition">'+i18n_search+value
        		+'<span class="close-button" onclick="key_empty()"></span></div>');
        	$("#key").val('');
    }
    ajax_cluster();
}
function key_empty(){
	$('#detail-container').empty();
    filter_keyword = '';
    $('.key-condition').remove();
    ajax_cluster();
}

//清除区域搜索条件
function clear_region(){
	ajax_detail({
    	cluster_id : 0
    },1);
    $('.regional-condition').remove();
}

//从数据库获取数据点
function ajax_cluster(){
    var zoom = map.getZoom();
    var ajax_data = {
    	'model' : filter_model,
    	'type'	: filter_type,
    	'zoom'	: zoom,
    	'progress' : filter_progress,
    	'res_tag': filter_res_tag,
    	'res_tag2': filter_res_tag2,
    	'field'	: filter_field,
    	'key'	: filter_keyword
    };
    $.ajax({
        type:"POST",
        url:app_path+"/Index/ajax_cluster",
        data:ajax_data,
        dataType: 'json',
        success:redrawMarkers
    });
    $('.regional-condition').remove();
    ajax_detail({
    	cluster_id : 0
    },1);	//在绘制完数据点之后直接将详情显示出来。
}

function ajax_detail(data,page){
	var ajax_data = {
    	'model' : filter_model,
    	'type'	: filter_type,
    	'progress' : filter_progress,
    	'res_tag': filter_res_tag,
    	'res_tag2': filter_res_tag2,
    	'field'	: filter_field,
    	'key'	: filter_keyword,
    	'cluster_id' : data['cluster_id'],
    	'page' : page
    };
    $('#detail-container').load(app_path+'/Index/ajax_detail',ajax_data,function(){
    	$('#page-show a').click(function(){
	        var regexp = /\d+$/;		//获取thinkphp生成的分页器中的页面信息
	        var page=regexp.exec($(this).attr('href'));
	        ajax_detail(data, page[0]);
	        return false;
	    });

    });
    if(data['cluster_id'] != 0){
    	$('.regional-condition').remove();
    	$('#filter-history').append('<div class="filter-condition regional-condition">'+i18n_regional
        		+'<span class="close-button" onclick="clear_region()"></span></div>');
    }
}

// 下面定义一个新的标注物类型：ClusterOverlay
// 继承API的BMap.Overlay
function ClusterOverlay(center, size){
  this._div = document.createElement("div");
  this._center = center;
  this._number = size;
  this._div._parent = this;
  this._size = 20 + 12*Math.log(size);
}

ClusterOverlay.prototype = new BMap.Overlay();
// 实现初始化方法
ClusterOverlay.prototype.initialize = function(map){

  // 保存map对象实例
  this._map = map;

  // 创建div元素，作为自定义覆盖物的容器
  var div = this._div;
  div.style.position = "absolute";
  // 可以根据参数设置元素外观
  div.style.width = this._size + "px";
  div.style.height = this._size + "px";
  var img = document.createElement("img");
  var text1 = document.createTextNode(this._number);
  
  //对于ie6使用gif

  if(is_ie6){
  	  img.setAttribute('src', app_path+'/Public/Img/overlay/cluster_overlay.gif');
  }
  else{
  	  img.setAttribute('src', app_path+'/Public/Img/overlay/cluster_'+filter_type+'_'+filter_model+'.png');
  }
  img.style.width = this._size+"px";
  img.style.height = this._size+"px";
  div.appendChild(img);
  if(this._number > 1){
	  var textdiv = document.createElement('div');
	  var text1 = document.createTextNode(this._number);
	  textdiv.style.position = "absolute";
	  textdiv.style.textAlign = "center";
	  textdiv.style.width = this._size+"px";
	  textdiv.style.height = this._size+"px";
	  textdiv.style.lineHeight = this._size+"px";
	  textdiv.style.fontSize = "12px";
	  textdiv.style.fontFamily = '"arial",serif';
	  textdiv.style.fontWeight = "bold";
	  textdiv.style.color = "white";
	  textdiv.style.top = "0px";
	  textdiv.style.left = "0px";
	  textdiv.style.position = "absolute";
	  textdiv.appendChild(text1);
	  div.appendChild(textdiv);
  }
  div.style.cursor = "pointer";
  // 将div添加到覆盖物容器中
  map.getPanes().markerPane.appendChild(div);

  // 保存div实例
  this._div = div;
  this._text = textdiv;

  // 需要将div元素作为方法的返回值，当调用该覆盖物的show、
  // hide方法，或者对覆盖物进行移除时，API都将操作此元素。
  return div;
}
ClusterOverlay.prototype.draw = function(){

  // 根据地理坐标转换为像素坐标，并设置给容器
  var position = this._map.pointToOverlayPixel(this._center);
  this._div.style.left = position.x - this._size / 2 + "px";
  this._div.style.top = position.y - this._size / 2 + "px";
}
ClusterOverlay.prototype.addEventListener = function(e,f,u){
  $(this._div).bind(e,f,u);
}

function redrawMarkers(e){
    map.clearOverlays();
    //var userIcon = new BMap.Icon
    for(var unit in e.data){
        var point = new BMap.Point( parseFloat(e.data[unit]['longitude']), parseFloat(e.data[unit]['latitude']) );
        var marker = new ClusterOverlay(point, parseInt(e.data[unit].num));
        marker._div.center = point;
        marker._div.data = e.data[unit];
        marker.addEventListener("click", function(){
            ajax_detail(this.data, 1);
            //var destination = $('#detail-list').offset().top;
   			//$("html:not(:animated),body:not(:animated)").animate({ scrollTop: destination-20}, 500 );
        });
        map.addOverlay(marker);
    }
    //jiathis_config.url=base_url+"?type="+filter_type+"&model="+filter_model+"&progress="+filter_progress+"&res_tag="+filter_res_tag+"&res_tag2="+filter_res_tag2+"&field="+filter_field+"&keyword="+filter_keyword;
}


$(function(){
    ajax_cluster();
    //如果点击对应的筛选条件按钮，则
    $('.filter-group a').click(function(){
    	$(this).parent().find('a').removeClass('selected');//仅高亮当前筛选条件
    	$(this).addClass('selected');
        $('#detail-container').empty();
        var key = $(this).attr('var');
        var value = $(this).attr('value');
        eval(key + '="' + value + '"');	//修改相应的条件参数
        $('div.filter-condition.'+key).remove();
        if(value != ""){	//增加历史条件
        	$('#filter-history').append('<div class="filter-condition '+key+'">'+$(this).text()
        		+'<span class="close-button" onclick="clear_filter(\''+key+'\')"></span></div>');
        }
        ajax_cluster();
    });
    $('#item-type-select a').click(function(){
    	var model = $(this).attr('model');
    	var type = $(this).attr('type');
    	filter(null, type, model);
    	$('#item-type-select li').removeClass('current');
    	$(this).parent().addClass('current');
    });
    $('.event-only').hide();

});

function clear_filter(key){
	eval(key +'=""');
	$('div.filter-condition.'+key).remove();
	$('a[var="'+key+'"]').removeClass('selected');
	$('a.all[var="'+key+'"]').addClass('selected');
	$('#detail-container').empty();
	ajax_cluster();
}

// 显示筛选条件框
function expand_filterbox(e){
	$(document.body).click(function(){
		$('#hideable-filters').hide();
	});		//点击页面其他位置隐藏
	$('#hideable-filters').click(function(e){
		e.stopPropagation();
	});		//点击mailbox不隐藏
	$('#hideable-filters').toggle();
	
	e.stopPropagation();	//防止body再收到onclick, 隐藏站内信框
}