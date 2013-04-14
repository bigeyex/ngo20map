	var map_model = '';
	var map_field = '';
	var map_progress = '';
	var map_res_tags = '';
	var url_detail = app_path+"/Index/ajax_detail";
	var img_path = app_path+"/Public/Img";

	// stores everything retrived from server side.
	var marker_data = '';

	// stores records filtered upon map_field etc. from marker_data
	var filtered_marker_data = [];

	$('#map-filters li').click(function(e){
		var drop_down = $(e.target).find('.dropdown');
		$('#map-filters .dropdown').not(drop_down).hide();
		drop_down.toggle();
		e.stopPropagation();
	});

	$(document.body).click(function(){
		$('#map-filters .dropdown').hide();
	});

	function reset_filter(e, filter){
		if(filter == 'model'){
			map_type = map_all_type;
			map_model = '';
			map_progress = '';
			map_res_tags = '';
			$('#filter-labels span[group="progress"]').remove();
			$('#filter-labels span[group="res_tags"]').remove();
		}
		else if(filter == 'field'){
			map_field = '';
		}
		else if(filter == 'progress'){
			map_progress = '';
		}
		else if(filter == 'res_tags'){
			map_res_tags = '';
		}
		$(e.currentTarget).parent().remove();
		filter_to_condition();
	}

	$('.dropdown span').click(function(e){
		var ct = e.currentTarget;
		var filter_type = '';
		if($(ct).attr('model') != undefined){
			map_model = $(ct).attr('model');
			map_type = $(ct).attr('type');
			filter_type = 'model';
		}
		else if($(ct).attr('field') != undefined){
			map_field = $(ct).attr('field');
			filter_type = 'field';
		}
		else if($(ct).attr('progress') != undefined){
			map_progress = $(ct).attr('progress');
			map_model = 'events';
			$('.dropdown span[model="events"]').click();
			filter_type = 'progress';
		}
		else if($(ct).attr('restags') != undefined){
			map_res_tags = $(ct).attr('restags');
			map_model = 'events';
			$('.dropdown span[model="events"]').eq(0).click();
			filter_type = 'res_tags';
		}
		$('span[group="'+filter_type+'"]').remove();
		if($(ct).attr('all') == undefined){
			$('#filter-labels').append('<span group="'+filter_type+'">'+ct.innerText+'<i onclick="reset_filter(event, \''+filter_type+'\')"></i></span>');
		}
		filter_to_condition();
	});

	var detailViewModel = {
		record_count: ko.observable(10),
		records: ko.observableArray(),
		record_base: [],	// records in user viewport
		pager: ko.observableArray([1,2,3,4,5,6,7,8,9,10,11,12,'...']),
		page: 1,
		gotoPage: function(page){
			var page_size = 12;
			var count = this.record_base.length;
			var total_page = Math.floor(count/page_size)+1;
			
			if(page>total_page){
				page = total_page;
			}
			else if(page<1){
				page = 1;
			}
			this.records.removeAll();
			map.clearOverlays();
			this.page = page;
			// this loop does 2 things:
			// 1. update knockout observed array
			// 2. add markers to the map
			for(var i=0; i<page_size; i++){
				var record_id = (page-1)*page_size+i;
				if(record_id >= count) break;
				var record = this.record_base[record_id]
				record.class_id = 'record-' + (i+1);
				//put a marker on the map
				var myIcon = new BMap.Icon(app_path+"/Public/Img/icons/index-map-markers.png", new BMap.Size(34, 26), {  
					anchor: new BMap.Size(8, 26),  
					imageOffset: new BMap.Size(0, 0 - i * 26)
				});
				var point = new BMap.Point(record.longitude, record.latitude);
				var marker = new BMap.Marker(point, {icon: myIcon}); 
				marker.data = {
					type: record.type,
					id: record.id,
					lng: record.longitude,
					lat: record.latitude
				};
				marker.addEventListener('click', function(){
					var info_window = new BMap.InfoWindow('<div class="detail-container" style=""><img src="'+img_path+'/loading.gif" onload="load_detail(\''+this.data.type+'\','+this.data.id+')"/></div>',{width:318,height:98});
					map.openInfoWindow(info_window, new BMap.Point(this.data.lng, this.data.lat));
				});
				record.marker = marker;
 				map.addOverlay(marker);  
 				this.records.push(record);
			}
			$('#record-set li').mouseenter(function(e){
				var i = $('#record-set li').index(e.currentTarget);
				var marker = detailViewModel.records()[i].marker;
				marker.old_zindex = marker.zIndex;
				marker.setZIndex(100);
			});
			$('#record-set li').mouseleave(function(e){
				var i = $('#record-set li').index(e.currentTarget);
				var marker = detailViewModel.records()[i].marker;
				marker.setZIndex(marker.old_zindex);
			});
			// refresh pager
			var pager_place_left = 12;
			this.pager.removeAll();
			if(page > 5){	// case: ... 2 3 4 5 *6*
				this.pager.push('...');
				for(var i=page-4; i<page; i++){
					this.pager.push(i);
				}	
				pager_place_left -= 5;
			}
			else{	// case: 1 2 3 *4*
				for(var i=1; i<page; i++){
					this.pager.push(i);
					pager_place_left -= 1;
				}
			}
			for(var i=page; i<total_page && pager_place_left>1; i++, pager_place_left--){
				this.pager.push(i);
			}
			if(i == total_page){
				this.pager.push(i);
			}
			else{
				this.pager.push('...');
			}
			$('#pager div span').click(function(e){
				var page = e.target.innerText;
				if(page == '...'){
					if($(e.target).prev().length==0){
						detailViewModel.gotoPage(parseInt($(e.target).next().text())-1);
					}
					else{
						detailViewModel.gotoPage(parseInt($(e.target).prev().text())+1);
					}
				}
				else{
					detailViewModel.gotoPage(parseInt(page));
				}
			});
		},
		reset: function(){
			this.gotoPage(1);
		}
	}

	$('.prev-page').click(function(){detailViewModel.gotoPage(detailViewModel.page-1)});
	$('.next-page').click(function(){detailViewModel.gotoPage(detailViewModel.page+1)});

	ko.applyBindings(detailViewModel);

	var map = new BMap.Map("map");
	var point = new BMap.Point(108.456368, 38.077487);
	var hotspot_data = undefined;
	map.centerAndZoom(point, 4);
	map.addControl(new BMap.NavigationControl());  
	var allLayer = new BMap.TileLayer({transparentPng:true});

	allLayer.getTilesUrl = function(tileCoord, zoom) {
	  var x = tileCoord.x;
	  var y = tileCoord.y;
	  return app_path+'/Runtime/Cache/tile-' + zoom + '-' + x + '-' + y + '-'+map_type+'-'+map_model+'-'+map_field+'-'+map_progress+'-'+map_res_tags+'.gif';
	}
	map.addTileLayer(allLayer);

	

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

	function filter_to_condition(){
		filtered_marker_data = [];
		map.clearHotspots();
		for(var i=0, j=marker_data.length; i<j; i++){
			if(map_type!=map_all_type && marker_data[i].type!=map_type)continue;
			if(map_model!='' && marker_data[i].model!=map_model)continue;
			if(map_progress != ''){
				if(marker_data[i].begin_time=='0')continue;
				// filter out those progress=planning but begin date prior to today
				if(map_progress == 'planning'){
					if(marker_data[i].begin_time == null)continue;
					if(marker_data[i].begin_time == "0000-00-00 00:00:00")continue;
					var begin_time = new Date(Date.parse(marker_data[i].begin_time));
					if(begin_time < new Date())continue;
				}
				if(map_progress == 'finished'){
					if(marker_data[i].end_time == null)continue;
					var end_time = new Date(Date.parse(marker_data[i].end_time));
					if(end_time > new Date())continue;
				}
			}
			if(map_field!='' && (marker_data[i].field==null || marker_data[i].field.indexOf(map_field)==-1))continue;
			if(map_res_tags!='' && (marker_data[i].res_tags==null || marker_data[i].res_tags.indexOf(map_res_tags)==-1))continue;
			var hotspot = new BMap.Hotspot(new BMap.Point(marker_data[i].longitude, marker_data[i].latitude),
					{text:marker_data[i].name, minZoom: 2, maxZoom: 18, userData: {'id':marker_data[i].id, 'type':marker_data[i].type, 'model':marker_data[i].model}});
			map.addHotspot(hotspot);
			filtered_marker_data.push(marker_data[i]);
		}
		filter_to_viewport();
		detailViewModel.record_count(filtered_marker_data.length);
		map.removeTileLayer(allLayer);
		map.addTileLayer(allLayer);
	}

	function filter_to_viewport(){
		var bounds = map.getBounds();
		var minlat = bounds.getSouthWest().lat;
		var minlon = bounds.getSouthWest().lng;
		var maxlat = bounds.getNorthEast().lat;
		var maxlon = bounds.getNorthEast().lng;
		detailViewModel.record_base = [];
		for(var i=0, j=filtered_marker_data.length; i<j; i++){
			lon = parseFloat(filtered_marker_data[i].longitude);
			lat = parseFloat(filtered_marker_data[i].latitude);
			if(lon>minlon && lon<maxlon && lat>minlat && lat<maxlat){
				detailViewModel.record_base.push(filtered_marker_data[i]);
			}
		}
		detailViewModel.reset();
	}

	$.get(app_path+'/Index/viewport_detail', {type: map_type}, function(result){
		marker_data = result.data;
		for(var i=0; i<marker_data.length; i++){
			if(marker_data[i].model == 'users'){
				marker_data[i].url = app_path+'/User/home/id/'+marker_data[i].id;
			}
			else{
				marker_data[i].url = app_path+'/Event/view/id/'+marker_data[i].id;
			}
		}
		filter_to_condition();
	}, 'json');

	map.addEventListener('dragend', function(){
		filter_to_viewport();
	});
	map.addEventListener('zoomend', function(){
		filter_to_viewport();
	});
