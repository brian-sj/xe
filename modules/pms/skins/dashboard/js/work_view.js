//// 통계 업무 시간을 합산해 줍니다.
jQuery(document).ready(function($){
	var total =0;
	for( var i=1 ; i < 8 ; i++){
		total =0;
		$(".data .w"+i).each(function(){
			total += parseInt( $(this).text() ) ||0 ;
		});
		$("#total_w"+i).text(total);
	}
});
jQuery(document).ready( function($){
			/**  기본 목록 뽑아내기  */
			getProjectList("F", function(first_project_srl){
				getTaskList(first_project_srl ,"F" );
			});  // 기본으로 증겨 찾기 목록을 보인다. .

			jQuery("#project-category a").on('click' , function(){
				var _cat = $(this).data('category');
				getProjectList( _cat , function(first_project_srl){ getTaskList( first_project_srl , _cat )} );
				$(this).siblings().removeClass('active').end().addClass('active');
			} );
			jQuery("#project").on('click','a' , function(){
				getTaskList( jQuery(this).data("project_srl"), jQuery(this).data("category")  ) ;
				$(this).siblings().removeClass('active').end().addClass('active');
			});
			$(".bookmark-close").on('click',function(){ bookmarkDismiss( $(this).data("task_srl") , $(this).data("project_srl")  );});
			//$("#title").on('click', function(){ var project_srl =$(this).data('project_srl') ; if(  project_srl !='' ) goContentPage( project_srl); });
			jQuery("#project_srl_list").on('click','li',function(){
				getTaskList(  $(this).data('project_srl') , $(this).data('category'));

			});

			/** 주간 내가한 업무 데이터 보여 주기   */
			var args ={};
			getWorkViewWeeklydata(args);

			/** 해당 업무에 대한 정보를 가지고 와서 수정하기   **/
			jQuery(document).on('click',"#report_table div.work" ,function(){
				$parent = $(this).parent().parent();
				task.project_srl = $parent.data("project-srl");
				jQuery("#report_date").val(  $(this).data('report-date') );

				getProjectList( $parent.data("category")  ,function( first_project_srl){
					getTaskList( $parent.data("project-srl") , $parent.data("category"),function(){
						wmcTree.selectItem( $parent.data("task-srl") , true , false );
					} );
				} );
				//tonclick( $parent.data("task-srl") , $parent.find("td:first").html() ,"bookmark");
			});

			wmcCalendar = new dhtmlXCalendarObject("work_calendar");
			wmcCalendar.setWeekStartDay(0);
			wmcCalendar.setPosition("right");
			wmcCalendar.show();
			
			/** 날짜를 바꾸어도 정보를 다시 가져온다.*/
			wmcCalendar.attachEvent('onClick',function(){
				$("#report_date").val( wmcCalendar.getDate(true) );
				//console.log(' on change '  );
				//console.log( jQuery("#comment_srl").val() );
				tonclick(  jQuery("#task_srl").val() , jQuery("#title").html() , "bookmark" );
				// 날짜를 바꾸면 하단 날짜 보기 테이블도 바꾼다.
				callback_function_save({"comment_srl":0});

			});
			ySlider_work_time = new dhtmlXSlider({parent:"work_time_obj" ,size:200 , min :0 ,max:13 , value:0 });
			ySlider_progress = new dhtmlXSlider({parent:"progress_obj" ,size:200 , min :0 ,max:100 , value : 0});

			/**일일 업무 보고 저장*/
		/*
			$("#saveDailyReport").bind('click',function(){
				params.comment_srl  = $("#comment_srl").val();
				params.content  = $("#content").val();
				params.type= TYPE_DAILYREPORT;
				params.reg_date = $("#report_date").val();
				exec_xml ( 'pms','procPmsCommentSave' , params , function(ret_obj){ console.log(ret_obj);} , responses , form_object );
			});
		*/
		
			/** 업무 시간 저장 */
			$("#cancel").bind("click",function(){
				jQuery("#work_time").val(0);
				jQuery("#time_del").val('Y');
				jQuery("#save").trigger('click');
			});
			$("#save").bind('click',function( ){
				params.task_srl = jQuery("#task_srl").val();
				if(  params.task_srl === undefined || params.task_srl === null || params.task_srl ==""){
					if( !wmcPopup){
						wmcPopup = new dhtmlXPopup();
						wmcPopup.attachHTML("업무를 지정하세요.<br>(좌측에서 프로젝트를 선택하시고 해당 업무를 지정하시면됩니다.  ) <span class='label label-error'>New</span>");
					}
					if( wmcPopup.isVisible() ){
						wmcPopup.hide();
					}else{
						var x = window.dhx4.absLeft(this);
						var y = window.dhx4.absTop(this);
						var w =this.offsetWidth;
						var h = this.offsetHeight ;
						wmcPopup.show(x,y,w,h);
					}
					return false ;
				}
				params.work_date = $("#report_date").val();
				params.work_time = $("#work_time").val();
				params.progress = $("#progress").val();
				params.time_del = $("#time_del").val();
				$("#time_del").val("N");// 정보를 뽑아 내고 다시 "N으로 복구 한다. "
				
				params.report_work_srl = $("#report_work_srl").val();
				params.count = $("#count").val();
				params.favorite = $("#switch-favorite:checked").val(); // ("checked");
				params.content  = $("#content").val();
				params.comment_srl  = $("#comment_srl").val();
				params.member_srl  = $("#member_srl").val();
				
				

				var opts = {  "progressBar": false
				, "positionClass": "toast-bottom-center"
				, "timeOut": "1000"};

				if( jQuery.trim(params.work_time) == "0" && params.time_del =='N'  )  {
					alert("업무 시간을 입력해 주세요");
					return false ;
				}
				if( jQuery.trim(params.content) == "")  {
					//toastr.warning("업무 내용을 기록해 주세요.", null ,opts);
					alert("업무내용을 입력해주세요.");
					return false ;
				}				
				
				if( params.time_del=='Y'){
					toastr.success('삭제되었습니다. ', null , opts);	
					$("#content").val("");
					$("#progress").val(0);
					$("#count").val("");
					
				}				
				else{
					toastr.success('저장되었습니다. ', null , opts);
				}
				

				exec_xml ( 'pms','procPmsWorkTimeSave' , params , callback_function_save , responses , form_object );
				console.log( params);
			});

});
///  업무 시간을 저장합니다.
function callback_function_save( ret_obj ){

	$("#comment_srl").val( ret_obj.comment_srl);
	// refresh는 일단 하지 않습니다. ..
	var args={};
	args.report_date= jQuery("#report_date").val();
	getWorkViewWeeklydata( args );
}

// 상세 업무 트리를 클릭할때 실행 된다.
function tonclick(id , title , type ){
	var node = wmcTree.getUserData(id);
	jQuery("#loading_spinner").removeClass("is-inactive").addClass("is-active");
	params.task_srl = id ;
	params.project_srl =  task.project_srl ;
	params.work_date   = jQuery("#report_date").val();
	params.report_work_srl = jQuery("#report_work_srl").val();
	params.member_srl = jQuery("#member_srl").val();
	/*
	//// 일단 막았다가...  ajax 통신 이후에 task_srl 을 넣는다.
	*/
	jQuery("#save").attr("disabled",true);
	jQuery("#cancel").attr("disabled",true);


	//jQuery("#btn-gocontent").attr("data-project_srl", id);  // 프로젝트 바로가기에

	var work_title = "";
	if( type =="bookmark") work_title = title ;
	else work_title = wmcTree.getItemText(id) ;
	jQuery("#title").html(work_title) ;
	jQuery("#title").attr('data-project_srl' ,task.project_srl ) ;
	// 업무 진행률과 , 업무 시간을 서버로 부터 가져 와서 뿌려줌. 3가지 필요사항은 member_srl , project_srl , task_srl

	exec_json("pms.dispPmsReportWorkTimeAPI",params, function(data){
		if(data.mh=="") data.mh = 0 ;
		if(typeof data.extra_vars.title =='undefined'){
			data.extra_vars ={title :'-'};
		}
		if(data.stt =="N"){// 일반 task 일경우만
			jQuery("#task_srl").val( id );
			jQuery("#save").removeAttr("disabled");
			jQuery("#cancel").removeAttr("disabled");
			jQuery("#work-title-memo").html("");
		}else{
			jQuery("#work-title-memo").html("* 업무그룹에는 시간을 추가하지 못합니다. 하위업무를 선택해주세요");
		}

		if(typeof data.content =='undefined' ) data.content ='';
		ySlider_work_time.setValue( data.mh);
		ySlider_progress.setValue( data.progress);
		if(data.extra_vars.title.length >2 ) jQuery("#content").val( data.extra_vars.title );
		else jQuery("#content").val( data.content );
		jQuery("#report_work_srl").val(data.report_work_srl);
		jQuery("#count").val(data.count);
		jQuery("#loading_spinner").removeClass("is-active").addClass("is-inactive");
	});
}

// 바로가기 삭제 버튼을 눌렀을때 실행 된다.
function bookmarkDismiss(  task_srl , project_srl ){
	//params.project_srl = params.project_srl ;
	params.add="X";
	params.project_srl = project_srl ;
	params.task_srl = task_srl ;
	exec_xml ( 'pms','procPmsFavoriteToggle' , params , function(){} , responses , form_object );
}
// 바로가기 업무 시작 버튼을 눌렀을때 실행된다.
function bookmarkAddWorkTime( project_srl , task_srl , obj  , category  ){
	jQuery("#project-category a[data-category='"+category+"']").siblings().removeClass("active").end().addClass("active");
	getProjectList( category  ,function( first_project_srl){
	    getTaskList( project_srl, category , function(){
			wmcTree.selectItem( task_srl , true , false );
			});
	} );
}

function hidePopup(){
	if(wmcPopup) wmcPopup.hide();
}
function getProjectList( category , callbackfunction, project_srl  ){
	params.category = category; //jQuery("#category").val();
	params.member_srl = jQuery("#member_srl").val();
	exec_json("pms.dispPmsProjectListAPI",params
		, function(data){

			jQuery("#project").html("");// 초기화 한다.
			var _project_srl =0;
			jQuery(data.data).each(function(index){
				// element 추가 한다.
				var $dom= jQuery('<a class="divi-list-group" href="#">').attr("href","#")
					.attr("data-project_srl", this.project_srl)
					.attr("data-category", this.category)
					.html( this.title) ;
				if(index ==0){  _project_srl = this.project_srl;
					$dom.addClass('active');
				}
				jQuery("#project").append(  $dom );
			});
			if(project_srl !=null )  _project_srl = project_srl;
			if( typeof callbackfunction ==='function') callbackfunction(_project_srl);
		}
		,function(error){ console.log(error);}
	);
}
function getTaskList( project_srl , category ,callbackfunction  ){
	jQuery("#loading_spinner").removeClass("is-inactive").addClass("is-active");
	jQuery("#btn-gocontent").attr("data-project_srl", project_srl);  // 프로젝트 바로가기에
	jQuery("#project a[data-project_srl='"+project_srl+"']").siblings().removeClass("active").end().addClass("active");
    params = {};
    params.project_srl = project_srl ;

	jQuery("#save,#cancel").attr("disabled", true );
	try{
		wmcTree.deleteChildItems(0);
	}catch( e){
		console.log(e);
	}

	if( project_srl == 0 ) {

		return 0;
	}

	task.project_srl = project_srl;
	task.category = category ;  // 이 category는 진행률을 디스 에이블 할때 쓴다...
	if(task.category =="R"){jQuery("#progress").val(0).attr("disabled", true); ySlider_progress.disable();}
	else {jQuery("#progress").attr("disabled", false);ySlider_progress.enable();}

	exec_json("pms.dispPmsTaskListAPI",params, function(data){
		try{ if(typeof wmcTree !='undefined' ) wmcTree.destructor(); } catch(e){ console.log(e);}
		try{
			wmcTree = new dhtmlXTreeObject("treeboxbox_tree","100%","100%",0);
			wmcTree.setImagePath("/common/js/plugins/dhtmlx/skins/terrace/imgs/dhxtree_terrace/");
			wmcTree.setOnClickHandler(tonclick);
			wmcTree.loadJSArray( data.data );

		}catch(e){ console.log(e); alert("해당 프로젝트는 업무가 등록되지 않아서 시간 배정을 할 수 없습니다. "); }
		jQuery("#loading_spinner").removeClass("is-active").addClass("is-inactive");

		if( typeof callbackfunction ==='function') callbackfunction();
	});
}

function goContentPage( project_srl){
	if(project_srl==null){ alert("상세보기할 프로젝트가 선택되지 않았습니다."); }
	else{
		document.location.href = current_url.setQuery('mid',mid).setQuery('act','dispPmsCalendarView').setQuery('project_srl', project_srl);
	}
}
function getWorkViewWeeklydata(args){
	params.act = "dispPmsCommentListWeeklyData";
	params.report_date = args.report_date ;
	jQuery.ajax({
	type: "POST"
	, url : "/"
	, data : params
	, success : function (data){
		jQuery("#weeklydata").html(data);
		sumWorkTime();
	}
	, dataType: "html"
	});
}

/** 합산 하기. */
function sumWorkTime(){
	var total =0;
	for( var i=1 ; i < 8 ; i++){
		total =0;
		$(".data .w"+i).each(function(){
			total += parseInt( $(this).text() ) ||0 ;
		});
		$("#total_w"+i).text(total);
	}
}