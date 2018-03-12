var responses = ['result','pms_file_srl','data','gu3'];
var form_object = new FormData();

jQuery(document).ready(function($){
	getWeekReportProject();
});

function getWeekReportProject(srl , comment ){
	params ={};
	params.gu3= 'A';
	params.report_date = report_date;
	exec_xml ( 'pms','dispPmsWeeklyReportProjectAPI' , params , callback_function_report , responses , form_object );

	params.gu3= 'D';
	exec_xml ( 'pms','dispPmsWeeklyReportProjectAPI' , params , callback_function_report , responses , form_object );

	params.gu3= 'M';
	exec_xml ( 'pms','dispPmsWeeklyReportProjectAPI' , params , callback_function_report , responses , form_object );
}

/**  진행 업무 */
function callback_function_report( ret_obj, responses, params, form_object ){
	var error = ret_obj['error'];
    var message = ret_obj['message'];
	var gu3 =ret_obj['gu3'];
	var total = {lastweek:0 , thisweek:0, accumulate: 0 };
	var i = 0;
	if( ret_obj.data ){
		jQuery.each( ret_obj.data.item , function( key , value ){
			// title , 주담당 , 시작일 , 종료일 , 전주 , 금주 , 누적 , 진행율
			i++;
			if( typeof value.item !='undefined' ){
				value = value.item ;
			}

			try{
			jQuery("#report_table_tbody_"+gu3 ).append("<tr><td>"+(i)+"</td><td class='title'>"+value[0]+"</td><td>"
												 + value[1]+"</td><td>"+value[2]+"</td><td>"+value[3]
												 +"</td><td>"+value[4]+"</td><td>"+value[5]+"</td><td>"+value[6]+"</td><td>"+value[7]+"%(◀"+value[8]+"%)</td></tr>");
			}catch(e){console.log(e);}

			total.lastweek += parseInt( value[4])||0;
			total.thisweek += parseInt( value[5])||0;
			total.accumulate += parseInt( value[6])||0;
		} );
		jQuery("#report_table_tbody_"+gu3).parent().find("td#lastweek").text( total.lastweek)
				.end().find("td#thisweek").text(total.thisweek)
				.end().find("td#accumulate").text(total.accumulate);
	}
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
		jQuery(h_sum+selector).text( humanReadable(val.sum, 'N'));
		jQuery(h_cnt+selector).text( val.cnt);
	});
		jQuery(h_sum + " .tr"+area +" .tdtotal").text( humanReadable(sum_total, 'N') );
		jQuery(h_cnt + " .tr"+area +" .tdtotal").text( cnt_total );

}

function humanReadable(mins ,need_day) {
var HOUR = 60,
    DAY = 60* 24 ,timestr;
	mins = Math.floor(mins);

   if(mins < HOUR){
		timestr = mins + '분';
   }else if(mins < DAY){
		timestr =""+ Math.floor(mins/HOUR) + '시간 ' + mins%HOUR + '분';
   }else {
		if (typeof need_day == "undefined"  || need_day == "Y") {
			var mins_ = Math.floor(mins%DAY);
			timestr = ""+ Math.floor(mins/DAY) + '일 ' + Math.floor(mins_/HOUR) + '시간 ' + mins_%HOUR + '분';
		} else {
			timestr =""+ Math.floor(mins/HOUR) + '시간 ' + mins%HOUR + '분';
		}
   }
   return timestr;
}
