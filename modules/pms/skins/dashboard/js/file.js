var responses = ['result','pms_file_srl'];
var form_object = new FormData();
function saveFileComment(srl , comment ){
	params ={};
	params.pms_file_srl = srl ;
	params.comment = comment ;
	exec_xml ( 'pms','procPmsFileCommentUpdate' , params , callback_function_savecomment , responses , form_object );
}
function deleteFile(srl){
    if(confirm("정말 삭제 하시겠습니까?")){
		params ={};
		params.pms_file_srl = srl ;
		exec_xml ( 'pms','procPmsFileDelete' , params , callback_function_delfile , responses , form_object );
	}
}
 
function callback_function_delfile( ret_obj, responses, params, form_object  ){
	var error = ret_obj['error'];
    var message = ret_obj['message'];
	jQuery("#card-div-"+ret_obj['result']).remove;
	document.location.reload();	
	//jQuery("#card-div-"+)
}	
function callback_function_savecomment( ret_obj, responses, params, form_object ){
	var error = ret_obj['error'];
    var message = ret_obj['message'];
	alert(" 파일설명이 저장 되었습니다.");
}