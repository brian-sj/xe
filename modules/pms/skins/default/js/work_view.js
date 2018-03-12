// 달력 그리기... 
jQuery(document).ready( function($){
	var week = [ 'sun' , 'mon' , 'tue' , 'wed' , 'thu' , 'fri'] ;
	var sum = 0 ;
	$ .each(week ,function( key , value ){
		sum = 0 ;
		$(".d-"+ value).each( function(){ sum += parseInt ( $(this).text()) ;} );
		$("#"+value) .text(  sum ); 	
	});	
});

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
			
			//jQuery("#work_time").slider({});
			//wmcTree.loadXML("/common/sampledata/tree.xml");
			//wmcTree.loadJSArray( treeArray );
			jQuery("#category").on('change' , function(){ getProjectList()} );
			jQuery("#project_srl").on('change' , function(){getTaskList( jQuery(this).val(), jQuery(this).find(":selected").data("category")  ) });
			$(".bookmark-close").on('click',function(){ bookmarkDismiss( $(this).data("task_srl") , $(this).data("project_srl")  );});
			
			wmcCalendar = new dhtmlXCalendarObject("report_date");
			ySlider_work_time = new dhtmlXSlider({parent:"work_time_obj" ,size:200 , min :0 ,max:13 , value:0 });
			ySlider_progress = new dhtmlXSlider({parent:"progress_obj" ,size:200 , min :0 ,max:100 , value : 0});
			
			$("#save").bind('click',function( ){ 
			
				if( params.task_srl === undefined || params.task_srl === null ){
					if( !wmcPopup){
//console.log("wmcPopup is null ");					
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
				params.favorite = $("#switch-favorite:checked").val(); // ("checked");
				alert("저장됨." );
				exec_xml ( 'pms','procPmsWorkTimeSave' , params , callback_function_save , responses , form_object );
				console.log( params);
			} );
			
});	
///  업무 시간을 저장합니다. 
function callback_function_save( ret_obj ){
	console.log( ret_obj );
	// refresh는 일단 하지 않습니다. ..
}

// 상세 업무 트리를 클릭할때 실행 된다.
function tonclick(id , title , type ){
	jQuery("#loading_spinner").removeClass("is-inactive").addClass("is-active");
	params.task_srl = id ;
	params.project_srl =  task.project_srl ;
	params.work_date   = jQuery("#report_date").val();
	
	var work_title = "";
	if( type =="bookmark") work_title = title ;
	else work_title = wmcTree.getItemText(id) ;
	jQuery("#title").html(work_title) ;
	// 업무 진행률과 , 업무 시간을 서버로 부터 가져 와서 뿌려줌. 3가지 필요사항은 member_srl , project_srl , task_srl 

	exec_json("pms.dispPmsReportWorkTimeAPI",params, function(data){ 
							if(data.mh=="") data.mh = 0 ;
//console.log( data);								
							ySlider_work_time.setValue( data.mh);
							ySlider_progress.setValue( data.progress);
							jQuery("#loading_spinner").removeClass("is-active").addClass("is-inactive");
						});
}

// 바로가기 삭제 버튼을 눌렀을때 실행 된다. 
function bookmarkDismiss(  task_srl , project_srl ){
	//params.project_srl = params.project_srl ;
	params.add="X";
	params.project_srl = project_srl ;
	params.task_srl = task_srl ;
console.log( params);	
	exec_xml ( 'pms','procPmsFavoriteToggle' , params , function(){} , responses , form_object );
}
// 바로가기 업무 시작 버튼을 눌렀을때 실행된다. 
function bookmarkAddWorkTime( task_srl , obj   ){

console.log( jQuery(obj).text()   );
	tonclick(task_srl , "바로 가기:"+jQuery(obj).text() ,"bookmark");
}

function hidePopup(){
	if(wmcPopup) wmcPopup.hide();
}

function getProjectList(){
	params.category = jQuery("#category").val();
	exec_json("pms.dispPmsProjectListAPI",params
		, function(data){ 
			jQuery("#project_srl").html("");
			jQuery(data.data).each(function(index){
				jQuery("#project_srl").append(jQuery("<option>").attr("value", this.project_srl).attr("data-category" , this.category).html( this.title )   );
				if(index ==0){  getTaskList(this.project_srl ); } 
			});
		}
		,function(error){ console.log(error);}
	);
}
function getTaskList( project_srl , category ){
	jQuery("#loading_spinner").removeClass("is-inactive").addClass("is-active");
    params = {};
    params.project_srl = project_srl ;
	task.project_srl = project_srl; 
	task.category = category ;  // 이 category는 진행률을 디스 에이블 할때 쓴다... 
	if(task.category =="R") jQuery("#progress").val(0).attr("disabled", true);
	else jQuery("#progress").attr("disabled", false);

	exec_json("pms.dispPmsTaskListAPI",params, function(data){ 
		console.log(data);
		try{ wmcTree.destructor(); } catch(e){ console.log(e);}
		try{
			wmcTree = new dhtmlXTreeObject("treeboxbox_tree","100%","100%",0);
			wmcTree.setImagePath("/common/js/plugins/dhtmlx/skins/terrace/imgs/dhxtree_terrace/");
			//wmcTree.detachAllEvents();
			wmcTree.setOnClickHandler(tonclick);
			wmcTree.loadJSArray( data.data );		
			jQuery("#loading_spinner").removeClass("is-active").addClass("is-inactive");
		}catch(e){}
	console.log('end.....' + task.category); 	
	});
}
