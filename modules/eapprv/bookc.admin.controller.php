<?php
class bookcAdminController extends bookc
{
	function procBookcAdminInsert(){
	
		// module 모듈의 model/controller 객체 생성
		$oModuleController = &getController('module');
		$oModuleModel = &getModel('module');

		// request 값을 모두 받음
		$args = Context::getRequestVars();
		$args->module 	= 'bookc';
		$args->mid			= $args->key ;
		$args->name      = "NAME";
		$args->browser_title = $args->title ;
		// module_srl이 넘어오면 원 모듈이 있는지 확인
		if($args->module_srl) {
			$module_info = $oModuleModel->getModuleInfoByModuleSrl($args->module_srl);
			if($module_info->module_srl != $args->module_srl) unset($args->module_srl);
		}

/*
	

		// module_srl 값의 존재여부에 따라 insert/update
		if(!$args->module_srl) {
			$output = $oModuleController->insertModule($args);
			$msg_code = 'success_registed';
		} else {
			$output = $oModuleController->updateModule($args);
			// 타이틀 이미지 바꾸는 것이 있겠군요...
			$msg_code = 'success_updated';
		}
		
		// 오류가 있으면 리턴
		if(!$output->toBool()) return $output;
		$module_srl = $output->get('module_srl');
	
*/
	
		$args->document_srl 	= Context::get("document_srl");
		$args->content 			= Context::get("content");
		$args->title 				= Context::get('title');
		
		if($module_srl) $args->module_srl       = $module_srl ;
		$logged_info = Context::get('logged_info');

		if( $args->user_yn !="Y" ) { $args->is_notice ="M";}
            // generate document module model object
        $oDocumentModel = &getModel('document');
          // generate document module의 controller object
        $oDocumentController = &getController('document');
		
		$oDocument = $oDocumentModel->getDocument($args->document_srl, $this->grant->manager);
						

		
		
		$args->notify_message = 'N';
        $this->module_info->admin_mail = '';
        $args->member_srl = $logged_info->member_srl; 
        //$args->email_address = $args->homepage = $args->user_id = '';
        $args->user_name = $logged_info->user_name ;
		$args->nick_name =  $logged_info->nick_name ;

        $bAnonymous = false;
		$oDocument->add('member_srl', $args->member_srl);
		
debugPrint(   $args );		
		if($oDocument->isExists() && $oDocument->document_srl == $args->document_srl) {
			// Update 
			$output = $oDocumentController->updateDocument($oDocument, $args);
            $msg_code = 'success_updated';
		}
		else {
			// Insert 
			$output = $oDocumentController->insertDocument($args, $bAnonymous);
			$msg_code = 'success_registed';
		}

	debugPrint( $output);		

		
	debugPrint('adkjfhaksdfh');		
		$url =  Context::get('success_return_url') ;
	debugPrint( $url );		
	
	
	$this->setRedirectUrl( $url );	
	
	$args->message = $msg_code ;
	$this->add('module_srl'  , $args->module_srl  );
	$this->setMessage($msg_code);
	
	return $output ;
	}
	
	
	function procBookcAdminInsertTitleImage ()
	{

		$up_param->name_length= 35;  // 이름길이 
		$up_param->upload_root_path='/www/html/xe/files/module/'; // 업로드 경로
		$args->module_srl  = Context::get("module_srl");
		$args->mid 			 = Context::get('key');
		$args->is_notice 	 = Context::get('user_yn');
		$args->title_img 	 = Context::get('title_img');
		$args->browser_title = Context::get("browser_title");
		$args->module_category_srl 	= Context::get('category_srl');
		
		// Cafe에서 만든 모듈은 이게 들어 간다. ...    카페가 아닌 전체 관리자가 만든거는 0이 들어간다.
		$args->site_srl = Context::get("site_srl");
		if(!$args->site_srl)  $args->site_srl  = 0 ;
		
		
		$args->module = 'bookc';
		if ($args->is_notice == 'Y' ) $args-> is_notice = 'N';  // user_yn y => 그냥 
		else $args->is_notice = 'M';                                     // User_un ='N' --> 관리자만 보아야 한다. 

		$oModuleController = &getController('module');

	    if (isset($_FILES['upload']) && !$_FILES['upload']['error']) {
			$output = fileUploadOnlyImage( $up_param);
			$msg = $lang->save_success;
			$args->title_img = "/files/module/". $output->file_real_name .".". $output->ext  ;
			
		}else{
			$msg_code = $lang->no_file;
		}
//debugPrint( $args );
		if(!$args->module_srl) {
			$output = $oModuleController->insertModule($args);
			$msg_code = 'success_registed';
		} else {
			$output = $oModuleController->updateModule($args);
			$msg_code = 'success_updated';  
		}
		$url =  Context::get('success_return_url') ;
		$output->module_srl =  $output->variables['module_srl'] ;		

		//성공하면 module_srl 이 나오고 아니면 나오지 않는다.  돌아 가야지...
		if(!$output->toBool() )
		{  $url .=  "&msg=" .$output->message ;}
		else {
			$url .=  "&module_srl=". $output->variables['module_srl']  ;
		}
		
		if($args->site_srl ==0)$this->setRedirectUrl( $url );
		return $output ;
		
	}
	
	function   procBookcAdminDeleteModule(){
			$oModuleController = &getController('module');
            $output = $oModuleController->deleteModule($module_srl);
            if(!$output->toBool()) return $output;
			
	}
	function procBookcAdminDeleteDocument(){
			
			$document_srl = Context::get("document_srl");
			$oDocumentController = &getController('document');
			$output = $oDocumentController->deleteDocument($document_srl, $this->grant->manager);
            if(!$output->toBool()) return $output;

		    ///$this->setMessage("WWW");	
	}
}

?>
