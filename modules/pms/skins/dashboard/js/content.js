
function onChange(a){
	//params.project_srl = params.project_srl ;
	if( jQuery("#favorite").attr("checked") =='checked'){
		params.add ="Y"; // ��� ã�⿡ �߰�
	}else{
		params.add ="N"; // ��� ã�⿡�� ����
	}
	//exec_xml ( 'pms','procPmsFavoriteToggle' , params , callback_function_favorite , responses , form_object );
	exec_json("pms.procPmsFavoriteToggle",params, function(data){
		var opts = {   "positionClass": "toast-bottom-center" };
		toastr.warning('���ã�� ������ ����Ǿ����ϴ�. ', null , opts);
	});
}
function callback_function_favorite( ret_obj, responses, params, form_object ){
	var error = ret_obj['error'];
    var message = ret_obj['message'];
	var opts = {  "progressBar": true
				, "positionClass": "toast-bottom-center" };
	toastr.warning('���ã�� ������ ����Ǿ����ϴ�. ', null , opts);
}
function getWorkTimeCommentList( type,  page , selector , project_srl ,task_srl  ){
    params.page = page ;
    params.type = type ;
    params.mid = mid;
    params.project_srl = project_srl;
    params.task_srl = task_srl;
	params.skintype ="timeline";

    //params.act = "dispPmsCommentList";
    params.act = "dispPmsWorkTimeCommentList";

    jQuery.ajax({
      type: "POST"
      , url : "/"
      , data : params
      , success : function (data){
        jQuery( selector ).html(data);
		$grid_isotope_h = jQuery(".grid").isotope({});;
      }
      , dataType: "html"
    });
}
function getCommentList( type,  page , selector , extra_vars ){
	params.page = page ;
	params.type = type ;
	params.mid = mid;
	if( extra_vars != null ) params.keyword = extra_vars.keyword;
	params.act = "dispPmsCommentList";
console.log( params );
	jQuery.ajax({
		type: "POST"
		, url : "/"
		, data : params
		, success : function (data){
			jQuery( selector ).html(data);
			$grid_isotope_m = jQuery(".grid").isotope({});;
		}
		, dataType: "html"
		});
}
function saveComment(){
	if(  jQuery("#comment-title").val() ==""  || jQuery("#comment-content").val() ==""  ){
		alert("����� ������ �Է� �ϼ���. ");
		return false ;
	}
	return true ;
}

function viewMemoComment( comment_srl , type ){
	jQuery("#history").removeData(); 
//console.log( "$$"+	current_url.setQuery('act','dispPmsCommentWorkLogDetail').setQuery('comment_srl' , comment_srl) );
	jQuery("#history").modal({
		remote : current_url.setQuery('act','dispPmsCommentWorkLogDetail').setQuery('comment_srl' , comment_srl).setQuery("type", type)
		});
}

function insert_confirm(msg){
	params.category = 'E';
	exec_json("pms.procPmsProjectConfirmSave", params, function(data){
		if (!msg) alert("�����߽��ϴ�.");
			window.location.reload();
			},function(error){ console.log(error);}
		);

}
/*  ���� ���簡 ������ �ؾ� �ϴ� function...  */
var eapprvHandler=function( params ){
	console.log( params );
	exec_json( "pms.procPmsEapprvProc", params , function(ret_obj){
		document.location.reload();	
	});
};