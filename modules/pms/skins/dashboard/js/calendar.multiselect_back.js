Array.prototype.grep = function (key, value) {
    var that = this, ret = [];
    this.forEach(function (elem, index) {
        if (elem[key] == value) {
			 ret.push(that[index]);
        }
    });
    return ret.length < 2 ? ret[0] : ret;
};

/*############## POPUP #############end **/
var myPop;
var myContentPop;
var responses = ['result'];
var form_object = new FormData();

function zoom_tasks(node){
	console.log(node.value);
	switch(node.value){
		case "week":
			gantt.config.scale_unit = "day";
			gantt.config.date_scale = "%m-%d";

			gantt.config.scale_height = 60;
			gantt.config.min_column_width = 30;
			gantt.config.subscales = [
				  {unit:"hour", step:1, date:"%H"}
			];
			show_scale_options("hour");
		break;
		case "trplweek":
			gantt.config.min_column_width = 70;
			gantt.config.scale_unit = "day";
			gantt.config.date_scale = "%m-%d";
			gantt.config.subscales = [ ];
			gantt.config.scale_height = 35;
			show_scale_options("day");
		break;
		case "month":
			gantt.config.min_column_width = 70;
			gantt.config.scale_unit = "week";
			gantt.config.date_scale = "#%W주";
			gantt.config.subscales = [
				  {unit:"day", step:1, date:"%D"}
			];
			show_scale_options();
			gantt.config.scale_height = 60;
		break;
		case "year":
			gantt.config.min_column_width = 70;
			gantt.config.scale_unit = "month";
			gantt.config.date_scale = "%m월";
			gantt.config.scale_height = 60;
			show_scale_options();
			gantt.config.subscales = [
				  {unit:"week", step:1, date:"#%W"}
			];
		break;
	}
	set_scale_units();
	gantt.render();
}

function set_scale_units(mode){
	if(mode && mode.getAttribute){
		mode = mode.getAttribute("value");
	}

	switch (mode){
		case "work_hours":
			gantt.config.subscales = [
				{unit:"hour", step:1, date:"%H"}
			];
			gantt.ignore_time = function(date){
				if(date.getHours() < 9 || date.getHours() > 16){
					return true;
				}else{
					return false;
				}
			};
			break;
		case "full_day":
			gantt.config.subscales = [
				{unit:"hour", step:3, date:"%H"}
			];
			gantt.ignore_time = null;
			break;
		case "work_week":
			gantt.ignore_time = function(date){
				if(date.getDay() == 0 || date.getDay() == 6){
					return true;
				}else{
					return false;
				}
			};

			break;
		default:
			gantt.ignore_time = null;
			break;
	}
	gantt.render();
}

/*  근무일인지 전체인지 보여주는 옵션...  */
function show_scale_options(){

}


function showPopup(inp) {

	return ;

	if (!myPop) {
		myPop = new dhtmlXPopup({mode: "right"});
		myPop.attachHTML( jQuery("#popup_html").html());
	}
	if (myPop.isVisible()) {
		myPop.hide();
	} else {
		var x = dhx4.absLeft(inp);
		var y = dhx4.absTop(inp);
		var w = inp.offsetWidth;
		var h = inp.offsetHeight;
		myPop.show(x,y,w,h);
	}
}

function showContentPopup(inp) {
	if (!myContentPop) {
		myContentPop = new dhtmlXPopup({mode: "right"});

		var a= jQuery(inp).find(".hidden-content").html();
		myContentPop.attachHTML(a);
	}

		var x = dhx4.absLeft(inp);
		var y = dhx4.absTop(inp);
		var w = inp.offsetWidth;
		var h = inp.offsetHeight;
		myContentPop.show(x,y,w,h);

}
/*############## end **/
function callback_function(ret_obj, responses, params, form_object) {
	var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];
    var url = current_url.setQuery('act','dispReleasenoteWrite').setQuery();
	alert("저장되었습니다. 계속 입력 하실 수 있습니다.");
}

function callback_function_delcomment(ret_obj, responses, params, form_object) {

location.reload();

}
function deleteComment( obj ){
	params = {};
	params.comment_srl = obj ;
	exec_xml ( 'pms','procCommentDelete' , params , callback_function_delcomment , responses , form_object );
}
/// favorite 버튼을 클릭하면 실행됨...
function callback_function_favorite(ret_obj , responses , params , form_object ){

}
var project_data;
jQuery(document).ready( function($){
	gantt.config.autosize = "y";
	gantt.init("gantt_here");

//console.log('############');
	exec_json("pms.dispPmsProjectWorkListAPI",params, function(data){
				project_data = data;
				gantt.init("gantt_here");
				gantt.parse(data);
console.log(data);
				// 모든 planed_mh 를 합산한 후에 예샂 작업시간 합산에 추가한다.
				
				$(data.data).each(function(){ project_sum_mh += parseInt($(this)[0].planed_mh ||0 ); });
				$("#project-sum-mh").text(project_sum_mh);
				var progress=0;
				if( parseInt( project_sum_mh) ) {
					progress = Math.round( (parseInt(work_time_mh) ||0 ) *100 / (parseInt(project_sum_mh)||1));	
				} 
				
				$("#cal_progress").text(progress);
				$("#cal_progress_bar").css("width",progress+"%");
				},function(error){ console.log(error);}
			);
//console.log('############');
	gantt.config.grid_width = 380;
	gantt.config.order_branch = true;
	gantt.config.api_date="%Y-%m-%d";
	gantt.config.date_scale="%m-%d";


	var hour_opts =[{key:1 , label :'1시간'}
	,{key:2 , label :'2시간'}
	,{key:3 , label :'3시간'}
	,{key:4 , label :'4시간'}
	,{key:5 , label :'5시간'}
	,{key:6 , label :'6시간'}
	,{key:7 , label :'7시간'}
	,{key:8 , label :'8시간'}
	,{key:9 , label :'9시간'}
	,{key:10 , label :'10시간'}
	,{key:11 , label :'11시간'}
	,{key:12 , label :'12시간'}
	,{key:13 , label :'13시간'}
	];

    gantt.config.columns = [
        {name:"text", label:"작업명", tree:true, width:'*' },
        {name:"progress", label:"진행률", width:50, align: "center",
            template: function(item) {
				if( isNaN(item.progress))
					return "0%";
                if (item.progress >= 1)
                    return "완료";
                if (item.progress == 0)
                    return "0%";

                return Math.round(item.progress*100) + "%";
            }
        },
		{name:'mh', label:"투입시간",align:'center', width:60
		
		},
        {name:"r_user_name", label:"작업자", align: "center", width:80,
            template: function(item) {
                if (!item.work_member_srl) return "-";
				var tmp ;
                tmp = opts.grep("key", item.work_member_srl );  
				return tmp.label ;
            }
        },
		{name:"add" , label : "" , width : 35}
    ];

	gantt.templates.grid_row_class = function(item) {
        if (item.progress  == 0) return "red";
        if (item.progress >= 1) return "green";
    };
    gantt.templates.task_row_class = function(start_date, end_date, item) {
        if (item.progress  == 0) return "red";
        if (item.progress >= 1) return "green";
    };

	gantt.templates.progress_text = function( start , end ,  task ){
		return "<span>"+Math.round(task.progress*100) + "% </span>";
	};

	gantt.form_blocks["wmc_worker"] ={
		render : function(s){
			//var $select_html  = 	$("<select id='users' name='users' multiple></select>");
			var $select_html  = 	$("<select id='users' name='users'></select>");
			$(opts).each(function(){
				$select_html.append("<option value='"+ this.key+"'>"+this.label+"</option>") ;
				});
			return $("<div class='wmc-workers'></div>").append( $select_html ).html()  ;
		}
		,set_value:function(node,value,task,section){
				var users = value +"";
console.log(task);				
				//$("#users option").attr("selected",false);
				//$.each( users.split(","), function(i,e){$("#users option[value='"+$.trim(e)+"']").attr("selected",true);});
				$("#users option[value='"+task.work_member_srl+"']").attr("selected",true);
			}
		,get_value:function(node,task,section){ console.log("get"+ node ) ; return  $("#users").val() ; }
	};

	gantt.config.lightbox.sections = [
		{name:"description" , height : 38 , map_to : "text", type:"textarea" , default_value : "업무이름"  , focus:true },
		{name:"planed_mh" , height : 38 , width: 50 , map_to :"planed_mh" , type : "textarea" , default_value : "10" },
		{name:"workers" , height : 60 , width : 200, map_to :"users" , type : "wmc_worker" , options : opts  },
		{name:"time" , height : 30 , map_to : "auto" , type:"duration" , time_format:["%Y","%m","%d"] },
		{name:"template" , height : 42 , map_to :"my_template"  , type:"template" },
		{name:"memo" , height : 42 , map_to :"memo"  , type:"textarea" },
	];
/* Task  CRUD */
	gantt.attachEvent( "onAfterTaskAdd" ,function(id , item ){
		item.project_srl = task.project_srl ;
		var dt = new Date(item.start_date) ;
		item.s_date = jQuery.datepicker.formatDate("yymmdd" , dt ); //dt.format('YYYYMMDD');
		item.task_srl = 0 ;
		// item.planed_mh =  
		exec_json("pms.procPmsTaskSave", item , function(data){
				gantt.changeTaskId( item.id , data.task_srl );
		},function(error){
			console.log('error');console.log(error);
		});
console.log('onAfterTaskAdd');
console.log( item );
		return true ;
	});
	gantt.attachEvent("onAfterTaskUpdate", function(id, item){
		item.project_srl = task.project_srl ;
		var dt = new Date( item.start_date );
		item.s_date = jQuery.datepicker.formatDate("yymmdd", dt );
		item.update ="Y";
console.log('onAfterTaskUpdate');
console.log( item );
		exec_json("pms.procPmsTaskSave", item , function(data){
			},function(data){console.log('error');console.log(data); } );

    return true;
	});

	gantt.attachEvent("onAfterTaskDelete", function(id,item){
		item.project_srl = task.project_srl ;
		exec_json("pms.procPmsTaskDelete", item , function(data){
		},function(data){alert('업무를 삭제하는 도중 에러가 발생했습니다.');console.log(data); } );
		return true;
	});

/* Link Crud*/
	gantt.attachEvent("onAfterLinkAdd", function(id,item){
		item.link_act = "A";
		item.project_srl = task.project_srl ;
		exec_json("pms.procPmsLinkSave", item , function(data){
		},function(data){alert('업무 링크를 입력하는 도중 에러가 발생했습니다.');console.log(data); } );
	});

	gantt.attachEvent("onAfterLinkDelete", function(id,item){
		item.link_act = "D";
		item.project_srl = task.project_srl ;
		exec_json("pms.procPmsLinkSave", item , function(data){
		},function(data){alert('업무링크를 삭제하는 도중 에러가 발생했습니다.');console.log(data); } );
	});

	gantt.attachEvent("onAfterLinkUpdate", function(id,item){
	});

	gantt.attachEvent("onBeforeLightbox" , function(id){
		var task = gantt.getTask(id);
console.log( task );
		var inputratio = 0;
		inputratio = Math.round(   (parseInt(task.mh) ||0) *100  / (parseInt(task.planed_mh) || 1)   );
		if(typeof task.mh =='undefined') task.mh = 0 ;
		if(typeof task.planed_mh =='undefined') task.my_template = '<span class="text-warning" >작업시간을 설정하지 않았습니다.</span>' ;
		else task.my_template = "<span id='title1'> 예정시간/투자시간 :"+task.planed_mh+":"+task.mh+" 계획대비 인력투자율 :"+inputratio+"%</span><div class='progress'><div class='progress-bar' style='width:"+inputratio+"%'></div></div>"  ;
		
		return true ;
	});

	gantt.attachEvent("onTaskClick" ,function(id , event){
		var filterValue = '.task-'+id; 
		$grid.isotope({ filter: filterValue });
		return true ;
	});

	gantt.locale.labels.section_template ="계획대비 인력투자율(작성자)";
	gantt.locale.labels.section_planed_mh ="예정시간";
	gantt.locale.labels.section_time ="작업기간";
	gantt.locale.labels.section_workers ="작업자(한명만 설정하도록 바뀌었습니다.대표작업자를 선택하세요)";
	gantt.locale.labels.section_description ="작업명";
	gantt.locale.labels.section_work_hour = "업무시간(수정시에만 저장됩니다.)";
	gantt.locale.labels.section_memo = "메모";

});
