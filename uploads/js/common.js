(function($){
	
	if(!document.defaultView || !document.defaultView.getComputedStyle){
		var oldCurCSS = jQuery.curCSS;
		jQuery.curCSS = function(elem, name, force){
			if(name !== 'backgroundPosition' || !elem.currentStyle || elem.currentStyle[ name ]){
				return oldCurCSS.apply(this, arguments);
			}
			var style = elem.style;
			if ( !force && style && style[ name ] ){
				return style[ name ];
			}
			return oldCurCSS(elem, 'backgroundPositionX', force) +' '+ oldCurCSS(elem, 'backgroundPositionY', force);
		};
	}
})(jQuery);

(function($) {
	
	function toArray(strg){
		strg = strg.replace(/left|top/g,'0px');
		strg = strg.replace(/right|bottom/g,'100%');
		strg = strg.replace(/([0-9\.]+)(\s|\)|$)/g,"$1px$2");
		var res = strg.match(/(-?[0-9\.]+)(px|\%|em|pt)\s(-?[0-9\.]+)(px|\%|em|pt)/);
		return [parseFloat(res[1],10),res[2],parseFloat(res[3],10),res[4]];
	}
	
	$.fx.step. backgroundPosition = function(fx) {
		if (!fx.bgPosReady) {
			
			var start = $.curCSS(fx.elem,'backgroundPosition');
			if(!start){//FF2 no inline-style fallback
				start = '0px 0px';
			}
			
			start = toArray(start);
			fx.start = [start[0],start[2]];
			
			var end = toArray(fx.options.curAnim.backgroundPosition);
			fx.end = [end[0],end[2]];
			
			fx.unit = [end[1],end[3]];
			fx.bgPosReady = true;
		}
		
		var nowPosX = [];
		nowPosX[0] = ((fx.end[0] - fx.start[0]) * fx.pos) + fx.start[0] + fx.unit[0];
		nowPosX[1] = ((fx.end[1] - fx.start[1]) * fx.pos) + fx.start[1] + fx.unit[1];           
		fx.elem.style.backgroundPosition = nowPosX[0]+' '+nowPosX[1];

	};
})(jQuery);

var getObj = function(objId) {
	return document.all ? document.all[objId] : document.getElementById(objId);
}

var seditor_ctlent = function(event, form_id) {
	if(event.ctrlKey && event.keyCode == 13 || event.altKey && event.keyCode == 83) {
		getObj(form_id).submit.click();
	}
}

var checkTsukkomiInput = function(doing, status) {
	obj1 = getObj(doing);
	remain = 140 - obj1.value.length;
	obj2 = getObj(status);
	if (obj1.value.length <= 140) {
		obj2.innerHTML = '<small class="grey">还可以输入' + remain + ' 字</small>';
	} else {
		remain2 = obj1.value.length - 140;
		obj2.innerHTML = '<small class="na">还可以输入0 字</small>';
		obj1.value = obj1.value.substring(0, 140);
	}
}

var submitTip = function() {
	$('#submitAjax').html('<img src="'+NWDIR+'/img/loading_s.gif" height="10" width="10" align="absmiddle" /> <span class="tip">请稍候，正在发送请求</span>');
}

var ajaxLoading = function() {
	$('.pager a').html('<img src="'+NWDIR+'/img/loading_s.gif" height="10" width="10" align="absmiddle" /> 载入中');
}

var eraseTml = function() {
	var $tml_list = $('#status_list'),$tml_item=$tml_list.find('li.status_item'),$tml_del =$tml_list.find("a.status_del");
	$tml_del.hide();
	$tml_item.mouseover(function(){
		var tml_id = $(this).attr('id').split('_')[1];
		$('a.status_del',this).show();
	}).mouseout(function(){
		$('a.status_del',this).hide();
	});
	$tml_del.unbind();
	$tml_del.click(function() {
		if (confirm('确认删除这条状态更新？')) {
			var tml_id = $(this).attr('id').split('_')[1];
			$.ajax({
				type: "GET",
				url: (this)+'?ajax=1',
				success: function(html){
					$('#status_'+tml_id).slideUp(500);
				},
				error: function(html){
				}
			});
			
		}
		return false;
	});
}

function AjaxPager() {
	$('a.ajax_page').click(function(){
        ajaxLoading();
		$.ajax({ 
		url: this+'?ajax=1',
		cache: false, 
		success: function(message) {
            $('.pager').hide();
			$('#more_status').append(message).hide().slideDown(800);
			eraseTml();
			AjaxPager();
		}
		});
		return false;
	});
}


var nwlib = {
	main: {
		init: function() {
            eraseTml();
    		$('#SayFrom').submit(function() {
    			var new_say = $('#SayInput').val(),$status_list = $('#status_list'),$sec_item = $status_list.find('li:eq(1)');
    						
    			if(new_say!='') {
                    submitTip();
    				$.ajax({
    					type: "POST",
    					url: NWDIR+"/update?ajax=1",
    					data:({status_input : new_say}),
    					dataType: 'json',
    					success: function(json) {
                            if($sec_item.hasClass('odd') == true){
                                var item_class = 'even';
                            } else {
                                var item_class = 'odd';
                            }
    						$('#UpdateStatus').after('<li id="status_'+json.status_id+'" class="'+item_class+'"><div class="content"><p>'+json.status_content+'</p><span class="tip">刚刚</span></div></li>');
    						$('#status_'+json.status_id).hide().slideDown(500);
                            $('#SayInput').val('');
    						$('#submitAjax').html('<input class="inputBtn" type="submit" name="submit" value="Update" />');
    					},
    					error: function(json){
    					}
    				});
    			 } else {
    			 	$("#SayInput").animate( { marginLeft:"5px" } , 50 ).animate( { marginLeft:"0" ,marginRight:"5px" } , 50 ).animate( {marginRight:"0" ,marginLeft:"5px" } , 50 ).animate( { marginLeft:"0" ,marginRight:"5px" } , 50 );
    			 }
    			return false;
    		});
		}
    }
}
$(document).ready(function(){
    AjaxPager();
});

$(function(){

	// ***
	// Scrolling background
	// ***
		
	// height of background image in pixels
	var backgroundheight = 4000;
	
	// get the current minute/hour of the day
	var now = new Date();
	var hour = now.getHours();
	var minute = now.getMinutes();
	
	// work out how far through the day we are as a percentage - e.g. 6pm = 75%
	var hourpercent = hour / 24 * 100;
	var minutepercent = minute / 60 / 24 * 100;
	var percentofday = hourpercent + minutepercent;
	
	// calculate which pixel row to start graphic from based on how far through the day we are
 	var startoffset = backgroundheight / 100 * percentofday;

	// graphic starts at approx 6am, so adjust offset by 1/4
	// scratch that, IE doesnt like negaive starts.. TODO: find a better way.
	// var startoffset = 0;
	
	// end 1x background height after the start offset so we get a smooth loop
	var endoffset = startoffset + backgroundheight;
	
   	function scrollbackground() {
		// set the background start position
		$('body').css({
			backgroundPosition: '50% -' + startoffset + 'px'
		});
		// animate through to the end
		$('body').animate({
			backgroundPosition:'(50% -' + endoffset + 'px)'
			},
			100000,
			"linear",
			function () {
				// callback to self to loop animation
				scrollbackground();
			}
		);
	}
   
	// start the animation
	scrollbackground();
});