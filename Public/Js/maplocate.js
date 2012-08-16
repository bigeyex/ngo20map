
var map = new BMap.Map("map-locate-container");
var point = new BMap.Point(116.404, 39.915);
var gc = new BMap.Geocoder();
map.centerAndZoom(point, 5);
map.addControl(new BMap.NavigationControl());

$(function(){
   localsearch();
});

function map_default(lon, lat){
    var point = new BMap.Point(lon, lat);
    map.panTo(point);
    addPointMarker(point);
}

function searchmap(){
    var myGeo = new BMap.Geocoder();
    position=$('#map-locate-searchbox').val().split(' ');
    // 将地址解析结果显示在地图上,并调整地图视野
    myGeo.getPoint(position[1], function(point){
      if (point) {
        map.centerAndZoom(point, 16);
        addPointMarker(point);
      }
    }, position[0]);
}
map.addEventListener("click",function(e){
    addPointMarker(e.point);
});

function addPointMarker(p){
    map.clearOverlays();
    var marker = new BMap.Marker(p);
    map.addOverlay(marker);
    $('#latitude').val(p.lat);
    $('#longitude').val(p.lng);

    //get province and city data
    gc.getLocation(p, function(rs){
        var addComp = rs.addressComponents;
        $('#city').val(addComp.city);
        $('#province').val(addComp.province);;
    });
}
var currentCity = null;
//页面初始化时new LocalSearch 并且为button绑定事件
function localsearch(){
	//createCopyBt();//在非IE浏览器中生成复制按钮
	var markers = [];
	var address = $("#localvalue").val();
	var l_options = {
		onSearchComplete: function(results){
			map.clearOverlays();
			if (l_local.getStatus() == BMAP_STATUS_SUCCESS){
				if(l_local._json&&l_local._json.content&&!!l_local._json.content.length){

					try{
						var p = results.getPoi(0).point;
						map.centerAndZoom(p,17);
                                                addPointMarker(p);
					}
					catch(e){

					}
				}
				else if(l_local._json&&l_local._json.content&&l_local._json.content.cname&&(!!l_local._json.content.level||l_local._json.content.level == 0)){
					var level = l_local._json.content.level;
					if(l_local._json.content.cname == "全国"){
						level = 4;
					}
					if(l_local._json.content.cname == currentCity || l_local._json.content.cname == $("#curCity").text()){
						s = "";
					}
					else{
						s = '已切换至'+l_local._json.content.cname;
						map.centerAndZoom(results.getPoi(0).point,level);
                                                addPointMarker(results.getPoi(0).point);
					}
					//$("txtPanel").innerHTML = s;
					//showMessage(s);
                                        $("#map-message").text(s);
				}
			}
			else{
				var str = "";
				//Fe.G("resultNum").innerHTML = "";
                                $("#curCity").text('');
				str = '没有找到相关的地点。';
				//$("txtPanel").innerHTML = str;
				//showMessage(str);         //搜索提示
                                $("#map-message").text(str);
			}
			//setZoom();
			//setCurCity(results.city);
                        currentCity = results.city
			//Fe.G("curCity").innerHTML=results.city;
                        $("#curCity").text(results.city);
			setTimeout(function(){
				searchType = 0;
			},1500)

		}
	};
	var l_local = new BMap.LocalSearch(map,l_options);
	window.l_local = l_local;
	local_search_timeout = 0;
	//在停止输入一段时间后出发计时器
	$("#localvalue").keyup(function(){
		if(local_search_timeout){
			clearTimeout(local_search_timeout);
		}
		setTimeout(function(){
			beginsearch(l_local);
		}, 2000);
		
	});

}
function filtQuery(q){
  q = q || "";
  return q.replace(/[\uac00-\ud7a3]/g, "")
          .replace(/\u2022|\u2027|\u30FB/g, String.fromCharCode(183))
          .replace(/^\s*|\s*$/g, "");
}

//按钮查询函数
function beginsearch(local){
	var str = filtQuery($("#localvalue").val());
	if(!str || str == "请输入问题数据的关键字"){
		return;
	}
	//参数初始化 end
	local.search(str);
}