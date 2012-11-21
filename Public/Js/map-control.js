  url_detail = app_path+"/Index/ajax_detail";
  img_path = app_path+"/Public/Img";
  var map = new BMap.Map("map");
  var point = new BMap.Point(108.456368, 38.077487);
  var hotspot_data = undefined;
  map.centerAndZoom(point, 4);
  map.addControl(new BMap.NavigationControl());  

  var allLayer = new BMap.TileLayer({transparentPng:true});
  allLayer.getTilesUrl = function(tileCoord, zoom) {
      var x = tileCoord.x;
      var y = tileCoord.y;
      return app_path+'/Runtime/Cache/tile-' + zoom + '-' + x + '-' + y + '-.gif';
  }
  map.addTileLayer(allLayer);
  var csrLayer = new BMap.TileLayer({transparentPng:true});
  csrLayer.getTilesUrl = function(tileCoord, zoom) {
      var x = tileCoord.x;
      var y = tileCoord.y;
      return app_path+'/Runtime/Cache/tile-' + zoom + '-' + x + '-' + y + '-excsr.gif';
  }
  var ngoLayer = new BMap.TileLayer({transparentPng:true});
  ngoLayer.getTilesUrl = function(tileCoord, zoom) {
      var x = tileCoord.x;
      var y = tileCoord.y;
      return app_path+'/Runtime/Cache/tile-' + zoom + '-' + x + '-' + y + '-ngo.gif';
  }
  var caseLayer = new BMap.TileLayer({transparentPng:true});
  caseLayer.getTilesUrl = function(tileCoord, zoom) {
      var x = tileCoord.x;
      var y = tileCoord.y;
      return app_path+'/Runtime/Cache/tile-' + zoom + '-' + x + '-' + y + '-case.gif';
  }
  var myCity = new BMap.LocalCity();
  myCity.get(function(result){
    var marker = new BMap.Marker(result.center, 
      {icon: new BMap.Icon(app_path+"/Public/Img/icons/current-position.png",
        new BMap.Size(32,28))});
    map.addOverlay(marker);
  });

  var recent_event_slider = $('#recent-event-list').bxSlider({
    auto: true,
      controls: false
  });
  var hot_tags_slider = $('#hot-tag-pages').bxSlider({
      controls: false
  });

  map.addEventListener('hotspotclick', function(e){
    var user_data = e.spots[0].getUserData();
    var id = user_data.id;
    var type = user_data.model;
    var info_window = new BMap.InfoWindow('<div class="detail-container" style=""><img src="'+img_path+'/loading.gif" onload="load_detail(\''+type+'\','+id+')"/></div>',{width:318,height:98});
    map.openInfoWindow(info_window, e.spots[0].getPosition());
  });

  function load_detail(type, id){
    $('.detail-container').load(url_detail+'/type/'+type+'/id/'+id,function(){
      $('.BMap_bubble_title').parent().css('top',0);
      $('.BMap_bubble_title').parent().css('left',0);
      $('.BMap_bubble_title').parent().css('width',350);
      $('.BMap_bubble_title').parent().css('height',130);
      $('.BMap_bubble_title').parent().css('overflow','hidden');

      $('.next-event').click(function(){
            if(!$('.event-images li').last().prev().hasClass('current')){
                var current = $('.event-images li.current');
                current.next().addClass('current');
                current.removeClass('current');
                $('.event-images').animate({
                    'left': '-=158'
                });
            }
        });
        $('.prev-event').click(function(){
            if(!$('.event-images li').first().next().hasClass('current')){
                var current = $('.event-images li.current');
                current.prev().addClass('current');
                current.removeClass('current');
                $('.event-images').animate({
                    'left': '+=158'
                });
            }
        });
    });
    
  }

  function set_hotspots(type){
    map.clearHotspots();
    for(var hk in hotspot_data){
      hs = hotspot_data[hk];
      if(type == 'ngo' && hs.type != 'ngo')continue;
      if(type == 'csr' && !(hs.type == 'csr' || hs.type == 'ind' && hs.model == 'events'))continue;
      if(type == 'case' && hs.type != 'case')continue;
      var hotspot = new BMap.Hotspot(new BMap.Point(hs.longitude, hs.latitude),
          {text:hs.name, minZoom: 2, maxZoom: 18, userData: {'id':hs.id, 'type':hs.type, 'model':hs.model}});
      map.addHotspot(hotspot);
    }
  }

  $(function(){
    $('#map-legend li.switch').click(function(e){
      var target = $(e.target);
      if(target.parent().parent().hasClass("all") || target.hasClass("inactive")){
        target.parent().find("li").addClass("inactive");
        target.parent().parent().removeClass("all");
        target.removeClass("inactive");
        map.removeTileLayer(ngoLayer);
        map.removeTileLayer(csrLayer);
        map.removeTileLayer(caseLayer);
        map.removeTileLayer(allLayer);
        switch(target.attr("id")){
          case 'legend-ngo':
            map.addTileLayer(ngoLayer);
            set_hotspots('ngo');
            break;
          case 'legend-csr':
            map.addTileLayer(csrLayer);
            set_hotspots('csr');
            break;  
          case 'legend-case':
            map.addTileLayer(caseLayer);
            set_hotspots('case');
            break;  
        }
      }
      else{
        target.parent().find("li").removeClass("inactive");
        target.parent().parent().addClass("all");
        map.removeTileLayer(ngoLayer);
        map.removeTileLayer(csrLayer);
        map.removeTileLayer(caseLayer);
        map.addTileLayer(allLayer);
        set_hotspots('all');
      }
    }); //click switch event

    $.get(app_path+"/Index/ajax_hotspots", function(result){
      hotspot_data = result.data;
      set_hotspots('all');

    },'json');
  });