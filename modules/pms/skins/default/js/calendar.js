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
				console.log(data);
				project_data = data;
				gantt.init("gantt_here");	
				gantt.parse(data);
				},function(error){ console.log(error);}
			);
//console.log('############');				
	gantt.config.grid_width = 380;
	gantt.config.order_branch = true;
	gantt.config.api_date="%Y%m%d";
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
        {name:"progress", label:"진행율", width:80, align: "center",
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
        {name:"assigned", label:"작업자", align: "center", width:100,
            template: function(item) {
				//	console.log( item.users);	 드래그 할때 이 함수가 실행되는 것은 이상하군요... 
                if (!item.users) return "Nobody";
				var names =[]  , tmp ;

				$( item.users).each( function(){  
						tmp = opts.grep("key", this.toString()) ;
						if( typeof tmp != 'undefined' && typeof  tmp.label != 'undefined') {names.push(tmp.label) ;  console.log("iiiiiii"); }

						});
				
                //return item.users.join(", ");
				return names.join(", ");
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
//console.log( s);
			var $select_html  = 	$("<select id='users' name='users' multiple></select>");
			$(opts).each(function(){  $select_html.append("<option value='"+ this.key+"'>"+this.label+"</option>") ;   }); 
//console.log(  $select_html.html()  );			
			return $("<div class='wmc-workers'></div>").append( $select_html).html()   ; 
		}
		,set_value:function(node,value,task,section){ console.log(value); $("#users").val(value);  }
		,get_value:function(node,task,section){ console.log( node ) ; return  $("#users").val() ; }
	};

	gantt.config.lightbox.sections = [
		{name:"description" , height : 38 , map_to : "text", type:"textarea" , focus:true },
		{name:"user" , height : 22 , map_to :"user" , type : "select" , options : opts },
		{name:"workers" , height : 60 , width : 200, map_to :"users" , type : "wmc_worker" , options : opts  },
		{name:"template" , height : 22 , map_to :"my_template"  , type:"template" },
		{name:"time" , height : 72 , map_to : "auto" , type:"duration" }
	];
/* Task  CRUD */
	gantt.attachEvent( "onAfterTaskAdd" ,function(id , item ){
		item.project_srl = task.project_srl ;
		var dt = new Date(item.start_date) ;
		item.s_date = jQuery.datepicker.formatDate("yymmdd" , dt ); //dt.format('YYYYMMDD');
		item.task_srl = 0 ;
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
//console.log( item );		
		
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
		task.my_template = "<span id='title1'> 담당자 : "+task.users+"</span>"  ;
		return true ;
	});

	gantt.locale.labels.section_template ="상세";
	gantt.locale.labels.section_user ="작업자";
	gantt.locale.labels.section_workers ="작업자";
	gantt.locale.labels.section_description ="제목";
	gantt.locale.labels.section_work_hour = "업무시간(수정시에만 저장됩니다.)";

});	

	