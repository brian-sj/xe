
jQuery(function(){


			jQuery("span.audio").each( 
						function() {  
								jQuery(this) .html(   jQuery(this).html() .replace(".mp3","")  );
							}
					) ;
			jQuery("span.audio").jmp3({ 
			backcolor:"ffd700",
			forecolor : "8B4513",
			width:100 ,
			showdownload:false 
			});
			
			

})
function delDocument( document_srl ){

	if(confirm("삭제 하시겠습니까?")){
	
		jQuery.ajax({
			type:"POST"
			,url : "/index.php"
			,data : "module=admin&act=procBookcAdminDeleteDocument&document_srl="+ document_srl 
			, success: function(data){
					alert("삭제 되었습니다. " );
					document.location.reload();
			}
			,error : function (){
					alert("삭제에 실패 했습니다. ");
			}
		});
	
	}
}
