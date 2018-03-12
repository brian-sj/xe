var responses = ['result','pms_file_srl','data','gu3'];
var form_object = new FormData();
var per_g_cls =" class='description-percentage text-green'"
    ,per_y_cls =" class='description-percentage text-yellow'";


jQuery(document).ready(function($){
	getWeekReportProject();
});

function getWeekReportProject(srl , comment ){
	params ={};
	params.gu3= 'A';
	exec_xml ( 'pms','dispPmsWeeklyReportProjectAPI' , params , callback_function_report , responses , form_object );
	
	params.gu3= 'D';
	exec_xml ( 'pms','dispPmsWeeklyReportProjectAPI' , params , callback_function_report , responses , form_object );
	
	params.gu3= 'M';
	exec_xml ( 'pms','dispPmsWeeklyReportProjectAPI' , params , callback_function_report , responses , form_object );
	
	params.area=2;
	params.dt ="2016-06-06";
	exec_xml ( 'pms','dispPmsWeeklyReportHelpAPI' , params , callback_function_help , responses , form_object );	
	params.area=3;
	exec_xml ( 'pms','dispPmsWeeklyReportHelpAPI' , params , callback_function_help , responses , form_object );	
	params.area=1;
	exec_xml ( 'pms','dispPmsWeeklyReportHelpAPI' , params , callback_function_help , responses , form_object );	
	params.area=0;
	exec_xml ( 'pms','dispPmsWeeklyReportHelpAPI' , params , callback_function_help , responses , form_object );	

}

/**  진행 업무 */
function callback_function_report( ret_obj, responses, params, form_object ){
	var error = ret_obj['error'];
    var message = ret_obj['message'];
	var gu3 =ret_obj['gu3'];
	
	jQuery.each( ret_obj.data.item , function( key , value ){
		// title , 주담당 , 시작일 , 종료일 , 전주 , 금주 , 누적 , 진행율 
		jQuery("#report_table_tbody_"+gu3 ).append("<tr><td>"+value.item[0]+"</td><td>"
											 + value.item[1]+"</td><td>"+value.item[2]+"</td><td>"+value.item[3]
											 +"</td><td>"+value.item[4]+"</td><td>"+value.item[5]+"</td><td>"+value.item[6]+"</td><td><span "+per_g_cls+">"+value.item[7]+"%</span><span "+per_y_cls+">(◀"+value.item[8]+"%)</span></td></tr>");
	} );
}

/** HELP 통계  */
function callback_function_help( ret_obj, responses, params, form_object ){
	var error = ret_obj['error'];
    var message = ret_obj['message'];
	var h_sum ="#report_table_tbody_help";
	var h_cnt ="#report_table_tbody_help_cnt";
	var sum_total = 0;
	var cnt_total = 0 ;
	var area ;
	jQuery.each(ret_obj.data.item , function(key , val){
		
		var selector = " .tr"+val.area+ " .td"+val.kind2 ;
		sum_total += parseInt(val.sum) ;
		cnt_total += parseInt(val.cnt) ;
		area = val.area ;
		jQuery(h_sum+selector).text( val.sum); 
		jQuery(h_cnt+selector).text( val.cnt); 
	});
		jQuery(h_sum + " .tr"+area +" .tdtotal").text( sum_total );
		jQuery(h_cnt + " .tr"+area +" .tdtotal").text( cnt_total );
	
}

