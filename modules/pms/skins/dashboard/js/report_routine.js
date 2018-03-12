var responses = ['result','data'];
var form_object = new FormData();

jQuery(document).ready(function($){
	getWeekReportProject();
});

function getWeekReportProject(srl , comment ){
	params ={};
	params.gu3= 'A';
	params.report_date =report_date;
	exec_xml ( 'pms','dispPmsWeeklyReportRoutineProjectAPI' , params , callback_function_report , responses , form_object );
}

/** 일상 업무 */
function callback_function_report( ret_obj, responses, params, form_object ){
	var error = ret_obj['error'];
    var message = ret_obj['message'];
console.log( ret_obj );
	var last_project={};
	last_project.project_srl = 0 ;
	last_project.last_time_sum = 0;
	last_project.last_cnt_sum = 0;
	last_project.this_time_sum = 0;
	last_project.this_cnt_sum = 0;

	var total = $(ret_obj.data.item).length ;

	var i = 0;
console.log("-----"+total);
	jQuery.each( ret_obj.data.item , function( key , value ){
		// title , 주담당 , 시작일 , 종료일 , 전주 , 금주 , 누적 , 진행율
		i++;
		if( typeof value.item !='undefined' ){
			value = value.item ;
		}
		try{
		if( last_project.project_srl == 0) last_project.project_srl = value[1] ;
console.log(i);
console.log(value);
		if( last_project.project_srl != value[1] )  /** 중간 합계...  */
		{
			jQuery("#report_table_tbody" ).append("<tr class='total-row total-row-underline'><td colspan='2'>합계</td><td>"
											 + last_project.last_time_sum +"</td><td>"+last_project.last_cnt_sum+"</td><td>"
											 + last_project.this_time_sum +"</td><td>"+last_project.this_cnt_sum+"</td></tr>");
			last_project.project_srl = value[1] ;
			last_project.last_time_sum = 0;
			last_project.last_cnt_sum = 0;
			last_project.this_time_sum = 0;
			last_project.this_cnt_sum = 0;

		}else{

		}
			last_project.last_time_sum += parseInt(value[7])||0;
			last_project.last_cnt_sum  += parseInt(value[8])||0;
			last_project.this_time_sum += parseInt(value[5])||0;
			last_project.this_cnt_sum  += parseInt(value[6])||0;

		jQuery("#report_table_tbody" ).append("<tr><td>"+(key+1)+"</td><td class='title text-left'>"+value[4]+"</td><td>"
											 + value[7]+"</td><td>"+value[8]+"</td><td>"+value[5]
											 +"</td><td>"+value[6]+"</td></tr>");

		/*  맨마지막이면 중간에서 합계를 구한다... */
		if(i === total){
			jQuery("#report_table_tbody" ).append("<tr class='total-row'><td colspan='2'>합계</td><td>"
											 + last_project.last_time_sum +"</td><td>"+last_project.last_cnt_sum+"</td><td>"
											 + last_project.this_time_sum +"</td><td>"+last_project.this_cnt_sum+"</td></tr>");
		}
		}catch(e){console.log(e);}

	} );
}
