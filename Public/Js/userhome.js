/* 
 * cantrols homepage js logic
 */

var map = new BMap.Map("map-container");
var point = new BMap.Point(116.404, 35.915);
map.centerAndZoom(point, 5);
map.addControl(new BMap.NavigationControl());
map.enableScrollWheelZoom();                  // 启用滚轮放大缩小。
map.addEventListener("zoomend", ajax_cluster);

function ClusterOverlay(center, size){
  this._div = document.createElement("div");
  this._center = center;
  this._number = size;
  this._div._parent = this;
  if(size > 100){
        this._size = 70;
  }
  else{
        this._size = 20 + 0.7 * size;
  }
}

function key_search(){
    if(!$("#key").val()){
        bubble_pop($("#key"),'请输入关键字！');
        return;
    }
    $('#detail-container').empty();
    var value = $("#key").val();
    ajax_cluster('key',value);
    $("#key").val('');
}

function ajax_cluster(type,eid){
    var zoom = map.getZoom();
    var data = 'type='+type+'&eid='+eid+'&zoom='+zoom;
    $.ajax({
        type:"POST",
        url:app_path+"/User/ajax_cluster",
        data:data,
        dataType: 'json',
        success:redrawMarkers
    });
}

function switchtab(item,type,eid){
    $('.selected').removeClass('selected');
    $(item).addClass('selected');
    $('#tab-content').empty();
    ajax_cluster(type,eid);
}

// 下面定义一个新的标注物类型：ClusterOverlay
// 继承API的BMap.Overlay
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
  img.setAttribute('src', app_path+'/Public/Img/clusterOverlay.png');
  img.style.width = this._size+"px";
  img.style.height = this._size+"px";
  div.appendChild(img);
  var textdiv = document.createElement('div');
  var text1 = document.createTextNode(this._number);
  textdiv.style.position = "absolute";
  textdiv.style.textAlign = "center";
  textdiv.style.width = this._size+"px";
  textdiv.style.height = this._size+"px";
  textdiv.style.lineHeight = this._size+"px";
  textdiv.style.fontSize = "12px";
  textdiv.style.fontWeight = "bold";
  textdiv.style.color = "white";
  textdiv.style.top = "0px";
  textdiv.style.left = "0px";
  textdiv.style.position = "absolute";
  textdiv.appendChild(text1);
  div.appendChild(textdiv);
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
        if(e.data[unit].minlat){    //如果是标注聚合
            var point = new BMap.Point((parseFloat(e.data[unit]['minlon'])+parseFloat(e.data[unit]['maxlon']))/2,
                                       (parseFloat(e.data[unit]['minlat'])+parseFloat(e.data[unit]['maxlat']))/2);
            var marker = new ClusterOverlay(point, parseInt(e.data[unit].numevents)+parseInt(e.data[unit].numusers));
            marker._div.center = point;
            marker._div.data = e.data[unit];
            marker.addEventListener("click", function(){
                ajax_detail(this.data, 'cluster');
            });
            map.addOverlay(marker);
        }
        else{               //最后是组织类
            var point = new BMap.Point(e.data[unit].longitude, e.data[unit].latitude);
            var marker = new ClusterOverlay(point, 1);
            marker._div.data = e.data[unit];
            marker.addEventListener("click", function(){
                ajax_detail(this.data, 'one');
            });
            map.addOverlay(marker);
        }
    }
}

function ajax_detail(data,type){
    if(type=='cluster'){
        $('#tab-content').load(app_path+'/User/ajax_detail',{ids:data['ids']});
    }
    else{
        $('#tab-content').load(app_path+'/User/ajax_detail',{id:data['id']});
    }
    window.location.href = '#user-map';
}
