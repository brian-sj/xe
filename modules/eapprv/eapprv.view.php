<?php
class eapprvView extends eapprv
{
	function init(){
		
		
		if ( $this->module_info->skin  && $this->module_info->skin != '/USE_DEFAULT/'   )
		{
			$templatePath = (sprintf($this->module_path.'skins/%s', $this->module_info->skin));
		}
		else
		{
			$templatePath = ($this->module_path.'skins/default');
		}
		//$templatePath = ($this->module_path.'skins/default');
		$this->setTemplatePath($templatePath );
	}
	
	function dispEapprvList(){
		$logged_info = Context::get('logged_info');
		$args = Context::getRequestVars();
		if (!$args->type) $args->type = __AUTH_OWNER__ ;
		
		$type = $args->type ;
		
		$args->member_srl  = $logged_info->member_srl ;
		$args->module_srl  = $this->module_srl ;
		$oModel = &getModel('eapprv'); 

/**  내 문서일때만 모든 것들 다 보여줌. */		
if( $type == __AUTH_OWNER__  )	{	
		$args->type=__AUTH_LINE__;  // 내가 결재해야할 리스트 
///### 내가 결재 해야할 목록을 적어두고... 		
		$output = $oModel->getDocumentList( $args );
		Context::set('list_myturn' , $output->data );

////### 진행중인것 적어 두고 
		$args->type=__AUTH_PROCESSING__;
		$output = $oModel->getDocumentList( $args );
		Context::set('list_processing' , $output->data );
////### 방금 들어온 새 참조 
		$output = executeQueryArray('eapprv.getEapprvsOnlyNewShared', $args );
		Context::set('list_newsharelist' , $output->data);
}
/// ### 완료인것 적어 둡시다. 
		$args->type= $type ;
		$output = $oModel->getDocumentList( $args );
		Context::set('list' , $output->data );

		$type_label = $this->status_label[$type];
		
		/*$memo_xml = simplexml_load_string( $output->data->memo_xml ) ;
		Context::set('memo_xml' , $memo_xml );*/
		
		$oModel = &getModel('wmccode'); 
		Context::set('wmc_members' , $oModel->wmc_members);
		
		Context::set('type', $type );
		Context::set('browser_title', $this->module_info->browser_title);
		Context::set('status_code' , $this->status_code);
		$output->page_navigation->page_size = $output->page_size ;
		Context::set('page_navigation', $output->page_navigation);
		Context::set('type_label', $type_label);

		$this->setTemplateFile('index');
	}
	
	/***
	*@  내용 보기 
	*/
	function dispEapprvContent(){

		$logged_info = Context::get("logged_info");
		$args = Context::getRequestVars();
		
		Context::set('browser_title', $this->module_info->browser_title);
		Context::set("member_srl" , $logged_info->member_srl);
	
		$oDocumentModel = getModel('document');
			
		//// ########### 1 권한 체크 
		///  결재 라인인지? 참조 인지를 체크한다. 
		
		$args->authLevel = __AUTH_NONE__ ;
		$output = executeQuery('eapprv.getEapprv' , $args ); //// member_srl 을 넣을 경우 내가 쓴것이 아닌글은 안보인다. 


		$args->member_srl = $logged_info->member_srl ;
		$args->document_srl = $output->data->document_srl ;
	
		Context::set('data', $output->data);
		$document_srl = $output->data->document_srl;
		$memo_xml = simplexml_load_string( $output->data->memo_xml ) ;
		Context::set('memo_xml' , $memo_xml );

		if( $output->data->member_srl  == $logged_info->member_srl  ){
			$args-> authLevel = __AUTH_OWNER__ ;

		}else{
			$output = executeQueryArray('eapprv.getEapprvLines',$args);	
			if(  sizeof( $output->data) >0 ) {
				$args->authLevel = __AUTH_LINE__ ;
			}else {
				$output = executeQueryArray('eapprv.getEapprvShares' , $args );
			
				if(  sizeof( $output->data) >0 ) 
					$args->authLevel = __AUTH_SHARE__ ;
			}
		}

		if( $args->authLevel == __AUTH_NONE__ && $logged_info->is_admin != 'Y' )  return $this->dispBoardMessage('msg_not_permitted');		


		//$oDocument = $oDocumentModel->getDocument( $document_srl, $this->grant->manager);
		$oDocument = $oDocumentModel->getDocument( $document_srl );
		Context::set('oDocument', $oDocument);
	
		/// @@@@  내 번호에 상관없이 문서에 관한 결재라인을 가져와야 하므로 내 번호를 지운다. 
		$args->member_srl = NULL ;

		/// ###### Line  
		$args->line_stt = __LINE_STT_1__ ;
		$output = executeQueryArray('eapprv.getEapprvLines' , $args ); 
		Context::set('line_list1', $output->data);

		$args->line_stt = __LINE_STT_2__ ;
		$output = executeQueryArray('eapprv.getEapprvLines' , $args ); 
		Context::set('line_list2', $output->data);

		$args->line_stt = __LINE_STT_3__ ;
		$output = executeQueryArray('eapprv.getEapprvLines' , $args ); 
		Context::set('line_list3', $output->data);
		/// ###### Shared 

		$output = executeQueryArray('eapprv.getEapprvShares' , $args ); 		

		$shared_list= $this->_getMemberPureArray( $output->data);
		Context::set('shared_members', $shared_list);
		Context::set('shared_answer', $output->data);

		/*
		$shared_list = array();
		foreach( $output->data as $key => $val  ){
			array_push( $shared_list ,  $val->member_srl  ); 
		}
		*/
		$args->member_srl = $logged_info->member_srl;
		$output = executeQueryArray("eapprv.getEapprvLinePres" ,$args );
		Context::set('line_group_list' , $output->data);
		//$output = executeQuery("eapprv.updateEapprvShareToRead", $args);
		Context::addJsFilter($this->module_path.'tpl/filter', 'insert_comment.xml');
		

		$oModel = &getModel('wmccode'); 
		Context::set('is_admin', $logged_info->is_admin);
		Context::set('module_srl' , $this->module_srl);
		Context::set('authLevel' , $args->authLevel);
		Context::set('wmc_members' , $oModel->wmc_members);
		Context::set('pm_members' , $oModel->pm_members);
		Context::set('gm_members' , $oModel->gm_member);
		

		if( $args->prt=='Y'){
			//$this->setLayout("none");
			//Context::set("layout","none");
			$this->setTemplateFile('contentPrint');
		}else{
			$this->setTemplateFile('content');
		}
	}
	/***
	*@  내용 쓰기 및 수정 하기. 
	*/	
	function dispEapprvWrite(){

				// check grant
		if(!$this->grant->write_document)
		{
			return $this->dispBoardMessage('msg_not_permitted');
		}
		
		
		$oDocumentModel = getModel('document');
		$logged_info = Context::get("logged_info");
		$args = Context::getRequestVars();
		$args->member_srl = $logged_info->member_srl ;
		
		$isUpdate = false ;
		if($args->eapprv_srl) $isUpdate = true ;
		
		Context::set('browser_title', $this->module_info->browser_title);
		$output = executeQuery( 'eapprv.getEapprv' , $args ); 
		$args->document_srl = $output->data->document_srl ;
		Context::set('data' , $output->data); 
		
		////###   문서의 Owner 인지 확인 한다. 
		if( $output->data->member_srl  == $logged_info->member_srl  ){
			$args-> authLevel = __AUTH_OWNER__ ;
			if( $output->data->stt1 != __STT_READY__ && $output->data->stt1 != __STT_ACCEPT__   ) return $this->dispBoardMessage('wmcmsg_already_proc');
		}else {
			//// 만약에 번호가 없다면 그냥 새로 쓰기이므로 권한 있음이다. 
			if($isUpdate) return $this->dispBoardMessage('msg_not_permitted');
		}

		try{
			$memo_xml = simplexml_load_string( $output->data->memo_xml ) ;
			Context::set('memo_xml' , $memo_xml );
			
		}catch( Exception $e ){ debugPrint( $e);}
		
		
		/**
		 * check if the category option is enabled not not
		 **/
		if($this->module_info->use_category=='Y')
		{
			// get the user group information
			if(Context::get('is_logged'))
			{
				$logged_info = Context::get('logged_info');
				$group_srls = array_keys($logged_info->group_list);
			}
			else
			{
				$group_srls = array();
			}
			$group_srls_count = count($group_srls);

			// check the grant after obtained the category list
			$normal_category_list = $oDocumentModel->getCategoryList($this->module_srl);
			if(count($normal_category_list))
			{
				foreach($normal_category_list as $category_srl => $category)
				{
					$is_granted = TRUE;
					if($category->group_srls)
					{
						$category_group_srls = explode(',',$category->group_srls);
						$is_granted = FALSE;
						if(count(array_intersect($group_srls, $category_group_srls))) $is_granted = TRUE;

					}
					if($is_granted) $category_list[$category_srl] = $category;
				}
			}
			Context::set('category_list', $category_list);
		}

		// GET parameter document_srl from request
		//$document_srl = Context::get('document_srl');
		$document_srl = $args->document_srl;
		$oDocument = $oDocumentModel->getDocument( $document_srl , $this->grant->manager);
		$oDocument->setDocument($document_srl);

		if($oDocument->get('module_srl') == $oDocument->get('member_srl')) $savedDoc = TRUE;
		$oDocument->add('module_srl', $this->module_srl);

		if($oDocument->isExists() && $this->module_info->protect_content=="Y" && $oDocument->get('comment_count')>0 && $this->grant->manager==false)
		{
			return new Object(-1, 'msg_protect_content');
		}

		// if the document is not granted, then back to the password input form
		$oModuleModel = getModel('module');
		if($oDocument->isExists()&&!$oDocument->isGranted())
		{
			return $this->setTemplateFile('input_password_form');
		}
		
		if(!$oDocument->get('status')) $oDocument->add('status', $oDocumentModel->getDefaultStatus());

		$statusList = $this->_getStatusNameList($oDocumentModel);
		if(count($statusList) > 0) Context::set('status_list', $statusList);

		// get Document status config value
		Context::set('document_srl',$document_srl);
		Context::set('oDocument', $oDocument);

		// apply xml_js_filter on header
		$oDocumentController = getController('document');
		$oDocumentController->addXmlJsFilter($this->module_info->module_srl);

		// if the document exists, then setup extra variabels on context
		if($oDocument->isExists() && !$savedDoc) Context::set('extra_keys', $oDocument->getExtraVars());

		/**
		 * add JS filters
		 **/
		if(Context::get('logged_info')->is_admin=='Y') Context::addJsFilter($this->module_path.'tpl/filter', 'insert_admin.xml');
		else Context::addJsFilter($this->module_path.'tpl/filter', 'insert.xml');

		$oSecurity = new Security();
		$oSecurity->encodeHTML('category_list.text', 'category_list.title');

		Context::set('is_admin' , $logged_info->is_admin);

		$oModel = &getModel('wmccode'); 
		Context::set('wmc_members' , $oModel->wmc_members);
		Context::set('pm_members' , $oModel->pm_members);
		Context::set('cfo_members' , $oModel->cfo_members);
		Context::set('gm_members' , $oModel->gm_member);
		Context::set('ceo' , $oModel->ceo);
	
	    /* DB 구분 가져오기 */
		$oModel = &getModel('wmccode');
		$obj->code_gu = "DBCODE";
		$output = $oModel->getCode( $obj );
		Context::set('codelist', $output->data );
		
		
		/**
		* Line 
		**/
		$obj->eapprv_srl = $args->eapprv_srl;
		
		$output = executeQueryArray("eapprv.getEapprvLinePres" ,$args );

		Context::set('line_group_list' , $output->data);
		
		if($isUpdate){
			$output = executeQueryArray("eapprv.getEapprvLines", $obj ); 
			$this->_setContextLine( $output->data );
		}
		/**
		* Shared 
		*/
		$output = executeQueryArray("eapprv.getEapprvSharePres" , $args ); 
		Context::set('shared_group_list' , $output->data);
		
		if($isUpdate){
			$output = executeQueryArray("eapprv.getEapprvShares", $obj);
			$shared_list = $this->_getMemberPureArray( $output->data);
		}

		Context::set('shared_list',$shared_list);
		
		$this->setTemplateFile('write.html');
	}

	function dispEapprvGroupList()
	{
		//  그룹 목록을 가져 온다.   
		// 그룹에 대한 회원 목록은 api 에서 가져 온다. 
		$output = executeQuery ('eapprv.getGroupsByMember', $args ); 
//debugPrint( $output );
		$this->setTemplateFile(index_group);
	}

	function dispEapprvUsers(){
		/**				API  로 갑니다....     */
		debugPrint('view로 왔나?');
	}
	function dispEapprvUsersByGroup(){
	   /**				API  로 갑니다....     */


		$args->group_srl = 314;
	    $output = executeQuery('eapprv.getMembersByGroup',$args); 
		
		foreach( $output -> data as $val )
		{
			debugPrint( $val ); 
		}
	}
	function dispEapprvLineSetting(){
		//$output = executeQueryArray("");
		$args = Context::getRequestVars();
		
		$logged_info = Context::get('logged_info'); 
		$oModel = &getModel('wmccode'); 
		$pm_members 	= $oModel->pm_members ;
		$wmc_members    = $oModel->wmc_members ;
		$gm_member      = $oModel->gm_member;
		Context::set('pm_members' , $pm_members);
		Context::set('wmc_members' , $wmc_members);
		Context::set('gm_member' , $gm_member);
		Context::set('member_srl' , $logged_info->member_srl );
		
		$args->member_srl = $logged_info->member_srl;
		$output = executeQueryArray('eapprv.getEapprvLinePres', $args);
		Context::set('list', $output->data);
		$output = executeQueryArray('eapprv.getEapprvSharePres', $args);
		Context::set('list_s', $output->data);	

		// share setting ... 
		$this->setTemplateFile("line_setting");
	}
	
	
	
	
	/***
	* auth Check
	*/
	
	function _authCheck(){
		$auth = __AUTH_NONE__ ;
		
		
		
		return $auth ;
	}
	
	function _getStatusNameList(&$oDocumentModel)
	{
		$resultList = array();
		if(!empty($this->module_info->use_status))
		{
			$statusNameList = $oDocumentModel->getStatusNameList();
			$statusList = explode('|@|', $this->module_info->use_status);

			if(is_array($statusList))
			{
				foreach($statusList as $key => $value)
				{
					$resultList[$value] = $statusNameList[$value];
				}
			}
		}
		return $resultList;
	}
	/**
	*  output 은 오브젝트 배열 이므로  순수 배열로 바꾸어줘야 in_array 함수를 쓸 수 있다. 
	*/
	function _getMemberPureArray( &$datas ){
		
		$shared_list = array();
		foreach( $datas as $key => $val  ){
			//$shared_list[$val->member_srl]  = $oModel->wmc_members[$val->member_srl] ;
			array_push( $shared_list ,  $val->member_srl  ); 
		}
		return $shared_list ;
	}
	/**
	*   각 결재 라인별로 ContextSet을 해 주어야 한다. 
	*/
	function _setContextLine( &$datas){
		$line_list = array();
		foreach( $datas as $key => $val  ){
			switch ($val->position){
				case __LINE1_CODE__: 
					$line_list['line_' . $val->line_stt . __LINE1_CODE__ ] = $val->member_srl ;
					break;
				case __LINE2_CODE__: 
					$line_list['line_' . $val->line_stt . __LINE2_CODE__ ] = $val->member_srl ;
					break;
				case __LINE3_CODE__ :
					$line_list['line_' . $val->line_stt . __LINE3_CODE__ ] = $val->member_srl ;
					break;
				case __LINE4_CODE__ :
					$line_list['line_' . $val->line_stt . __LINE4_CODE__ ] = $val->member_srl ;
					break;
				case __LINE5_CODE__ :
					$line_list['line_' . $val->line_stt . __LINE5_CODE__ ] = $val->member_srl ;
					break;
			}
		}
		
		Context::set('line_member_list', $line_list  );
	}
	
	
	function dispEapprvModifyComment(){
		
		$this->setTemplateFile('modify_comment_form');
	}
	function dispEapprvDeleteComment(){
		
		
		// get the comment_srl to be deleted
		$comment_srl = Context::get('comment_srl');

		// if the comment exists, then get the comment information
		if($comment_srl)
		{
			$oCommentModel = getModel('comment');
			$oComment = $oCommentModel->getComment($comment_srl, $this->grant->manager);
		}
		
				// if the comment is not granted, then back to the password input form
		if(!$oComment->isGranted())
		{
			return $this->setTemplateFile('input_password_form');
		}

		Context::set('oComment',$oComment);
		/**
		 * add JS filters
		 **/
		Context::addJsFilter($this->module_path.'tpl/filter', 'delete_comment.xml');
		$this->setTemplateFile('delete_comment_form');
	}

	/**
	 * @brief display board message
	 **/
	function dispBoardMessage($msg_code)
	{
		$msg = Context::getLang($msg_code);
		if(!$msg) $msg = $msg_code;
		Context::set('message', $msg);
		$this->setTemplateFile('message');
	}

	/**
	 * @brief the method for displaying the warning messages
	 * display an error message if it has not  a special design
	 **/
	function alertMessage($message)
	{
		$script =  sprintf('<script> jQuery(function(){ alert("%s"); } );</script>', Context::getLang($message));
		Context::addHtmlFooter( $script );
	}
	
	function dispEapprvLineByPreAPI(){}
	function dispEapprvSharedByPreAPI(){}
	


}

?>
