jQuery(document).ready( function($){
	jQuery.balloon.init();
	jQuery("#my-duty-table .link,#my-order-table .link").on('click',function(){
		$balloon = $(this);
		var e = jQuery.Event('click');
		//var $con = $('#popup_content');
		var project_srl = $(this).data('project_srl');
		var task_srl = $(this).data('task_srl');
		var $url = "{ajax} "+"/?mid="+mid+"&act=dispPmsEditWorkInfoDiv&project_srl="+project_srl+"&task_srl="+task_srl;
		$(this).showBalloon(e,{balloon:$url, nocache : true });
	});
});

function saveWorkTime(){
	
}
function saveProgress(){
	
}
function saveEndDate(){
	
}
function closeBalloon( $balloon){
	jQuery($balloon).hideBalloon();
}