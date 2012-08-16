
var map = new BMap.Map("map-display-container");
var point = new BMap.Point(116.404, 39.915);
map.centerAndZoom(point, 5);
map.addControl(new BMap.NavigationControl());

$(function(){
	add_labels(event_points);

});

function center_map(longitude, latitude){
	var point = new BMap.Point(longitude, latitude);
	map.centerAndZoom(point, 5);
}

function add_labels(labels){
	var min_lat,max_lat,min_lon,max_lon;
	min_lat=min_lon=999;
	max_lat=max_lon=0;
	for(var key in labels){
		var myCompOverlay = new ComplexCustomOverlay(new BMap.Point(labels[key][0],labels[key][1]), labels[key][2], labels[key][3]);
		map.addOverlay(myCompOverlay);
		if(labels[key][0] < min_lon) min_lon = labels[key][0];
		if(labels[key][0] > max_lon) max_lon = labels[key][0];
		if(labels[key][1] < min_lat) min_lat = labels[key][1];
		if(labels[key][1] > max_lat) max_lat = labels[key][1];
	}
	//合理调整地图缩放层级和显示范围
	if(max_lon != 0){
		map.setViewport([new BMap.Point(min_lon, min_lat), new BMap.Point(max_lon, max_lat)]);
	}
	
}

// 复杂的自定义覆盖物
function ComplexCustomOverlay(point, text, url){
	this._point = point;
	this._text = text;
	this._link_url = url;
}
ComplexCustomOverlay.prototype = new BMap.Overlay();
ComplexCustomOverlay.prototype.initialize = function(map){
	this._map = map;
	var div = this._div = document.createElement("div");
	div.style.position = "absolute";
	div.style.zIndex = BMap.Overlay.getZIndex(this._point.lat);
	div.style.backgroundColor = "#EE5D5B";
	div.style.border = "1px solid #BC3B3A";
	div.style.color = "white";
	div.style.height = "18px";
	div.style.padding = "2px";
	div.style.lineHeight = "18px";
	div.style.whiteSpace = "nowrap";
	div.style.MozUserSelect = "none";
	div.style.fontSize = "12px";
	var a = this._span = document.createElement("a");
	a.href=this._link_url;
	a.style.color = "white";
	div.appendChild(a);
	a.appendChild(document.createTextNode(this._text));      
	var that = this;
	
	var arrow = this._arrow = document.createElement("div");
	arrow.style.background = "url(http://map.baidu.com/fwmap/upload/r/map/fwmap/static/house/images/label.png) no-repeat";
	arrow.style.position = "absolute";
	arrow.style.width = "11px";
	arrow.style.height = "10px";
	arrow.style.top = "22px";
	arrow.style.left = "10px";
	arrow.style.overflow = "hidden";
	div.appendChild(arrow);
	
	map.getPanes().labelPane.appendChild(div);
	
	return div;
}
ComplexCustomOverlay.prototype.draw = function(){
	var map = this._map;
	var pixel = map.pointToOverlayPixel(this._point);
	this._div.style.left = pixel.x - parseInt(this._arrow.style.left) + "px";
	this._div.style.top  = pixel.y - 30 + "px";
}
    
