/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
isIE6 = navigator.userAgent.toLowerCase().indexOf('msie 6') != -1;
$(function(){
        //根据窗口大小选择不同方案
        right_type();

        //提取传递参数
       var parameter_all = $('#parameter_all').val();
       parameter = new Array();
       parameter = parameter_all.split(",");

      //获取公益事件相关细节
    $('.event .result-content').click(function(){
        get_detail(this,'Event','view');
    });

    //获取公益组织相关细节
    $('.user .result-content').click(function(){
        get_detail(this,'User','home');
    });

    //窗口大小变化事件处理
    window.onresize=function(){
        right_type();
    }
        
});

//创建地图
function createMap(){
    var map = new BMap.Map("map-locate-container");
    var point = new BMap.Point(116.404, 39.915);
    map.centerAndZoom(point, 5);
    map.addControl(new BMap.NavigationControl());
    map.enableScrollWheelZoom();   // 启用滚轮放大缩小。
    window.map=map;
}

//地图标注添加
function AddOverlay(type,iconname,index){
    //提取搜索结果坐标
    markers = new Array();

    $('.result-content').each(function(){
            var lon = parseFloat($(this).attr('lon'));
            var lat = parseFloat($(this).attr('lat'));
            var eid = $(this).attr('eid')
            var name = $(this).find('.content-title').text();
            var message = $(this).find('.map-message').text();
            var point = new BMap.Point(lon,lat);
            var marker = new BMap.Marker(point,{
            icon : new BMap.Icon(app_path+'/Public/Img/icons/'+iconname+'.png',new BMap.Size(18, 18))
        });
            map.addOverlay(marker);
            marker.addEventListener("click", function(){
                this.openInfoWindow(new BMap.InfoWindow('<a target="_blank" href="'+app_path+'/'+type+'/'+index+'/id/'+eid+'">'+
                                    name+'</a><br/>'+message));
                map.panTo(this.getPoint());
            });

            markers[eid] = marker;
        });
}

function pagelink(type){
    
    $('#page-show a').click(function(){
        var regexp = /\d+$/;
        var page=regexp.exec($(this).attr('href'));
        document.documentElement.scrollTop=0;
        $('#detail-container').empty();
        map.clearOverlays();
        if(parameter[1])
            $('#result-container').load(app_path+'/Search/ajax'+type+'Result/p/'+page,{s:parameter[0],e:parameter[1],lats:parameter[2],late:parameter[3],lons:parameter[4],lone:parameter[5],page:page[0]});
        else if(parameter[0])
            $('#result-container').load(app_path+'/Search/ajax'+type+'Result/p/'+page,{q:parameter[0],page:page[0]});
        else
            $('#result-container').load(app_path+'/Search/ajax'+type+'Result/p/'+page,{q:'_ALLEVENTS',page:page[0]});
        return false;
    });
}

//标签点击响应事件
function switchtab(tabid){
    $('.result-content').removeClass('on');
    $('#detail-container').empty();
    map.clearOverlays();
    $('#result-filter a').removeClass('on');
    $('#'+tabid+'-filter').addClass('on');
    if(parameter[1])
        $('#result-container').load(app_path+'/Search/ajax'+tabid+'Result',{s:parameter[0],e:parameter[1],lats:parameter[2],late:parameter[3],lons:parameter[4],lone:parameter[5],page:'1'});
    else if(parameter[0])
        $('#result-container').load(app_path+'/Search/ajax'+tabid+'Result',{q:parameter[0],page:'1'});
    else
        $('#result-container').load(app_path+'/Search/ajax'+tabid+'Result',{q:'_ALLEVENTS',page:'1'});
}

function get_detail(item,type,index){
    $('.result-content').removeClass('on');
    $(item).addClass('on');
    var eid = $(item).attr('eid');
    var name = $(item).find('.content-title').text();
    var message = $(item).find('.map-message').text();
    var marker = markers[eid];
    marker.openInfoWindow(new BMap.InfoWindow('<a target="_blank" href="'+app_path+'/'+type+'/'+index+'/id/'+eid+'">'+
                                name+'</a><br/>'+message));
    map.panTo(marker.getPoint());
    $('#detail-container').load(app_path+"/Search/"+type+"Details",{id:eid});
}

function right_type(){
        var nScreenHeight = document.documentElement.clientHeight || document.body.clientHeight;
        var nScreenWidth = document.documentElement.clientWidth || document.body.clientWidth;
        var rightHeight = nScreenHeight-100;
        $('#right-element').css("height", rightHeight);
        if(nScreenWidth<1000 && !isIE6){
            $('#right-element').css({
                "float":'right',
                "position":"absolute",
                "margin-top": document.documentElement.scrollTop-13
            });
            window.onscroll=function(){
                $('#right-element').css({
                        "margin-top": document.documentElement.scrollTop-13
                    })
            };
        }
        else{
            $('#right-element').css({
                "position":"fixed",
                "margin-top":"-13px"
            });
            window.onscroll=function(){

            };
        }
}