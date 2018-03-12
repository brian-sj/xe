<?php
    /**
     * @class  bookmarkAdminView
     * @author XE스쿨 북마크 모듈 만들기 예제
     * @brief  bookmark 모듈의 admin view class
     **/

    class eapprvAdminView extends eapprv {

        /**
         * @brief 초기화
         **/
        function init() {

            // module_srl이 있으면 미리 체크하여 존재하는 모듈이면 module_info 세팅
            $module_srl = Context::get('module_srl');
            if(!$module_srl && $this->module_srl) {
                $module_srl = $this->module_srl;
                Context::set('module_srl', $module_srl);
            }
            // module model 객체 생성 
            $oModuleModel = &getModel('module');
            // module_srl이 넘어오면 해당 모듈의 정보를 미리 구해 놓음
            if($module_srl) {
                $module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
                if(!$module_info) {
                    Context::set('module_srl','');
                    $this->act = 'list';
                } else {
                    ModuleModel::syncModuleToSite($module_info);
                    $this->module_info = $module_info;
					$this->module_info->use_status = explode('|@|', $module_info->use_status);
                    Context::set('module_info',$module_info);
                }
            }			
//            if($module_info && $module_info->module != 'bookmark') return $this->stop("msg_invalid_request");
            // 모듈 카테고리 목록을 구함
            $module_category = $oModuleModel->getModuleCategories();
            Context::set('module_category', $module_category);
			// 관리자 템플릿 파일의 경로 설정 (tpl)
            $template_path = sprintf("%stpl/",$this->module_path);
            $this->setTemplatePath($template_path);
		}

		function dispEapprvAdminList(){
debugPrint("eee");		
			$page = Context::get('page');
			$keyword = Context::get('search_keyword');
			$args->site_srl = Context::get('site_srl');
			$args->page 	= $page ;
			$args->browser_title 	= $keyword ;
			//$oBookcModel 	= &getModel('bookc');
			//   검색어를 선택 한다. 
			//$output = $oBookcModel->getBookcModuleList( $args );
			
			                

			// 템플릿에 전해주기 위해 set함
			/*
            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
			Context::set('bookmark_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);			
			Context::set('list' , $output->data);
			*/

			$this->setTemplateFile('index');
			
		}
		/**
		*	 모듈 내용 보기 입니다. 
		*/


		function dispBookcAdminInfo(){
			$page = Context::get('page');
			$module_srl = Context::get('module_srl');


    		$oModuleModel = &getModel("module");
			$oFile     			= &getModel('file');
			debugPrint( $oModuleModel); 
			$oDocumentModel = &getModel('document');
			
/**        해당 모듈의 모든 도큐 먼트를 가지고 온다.              ** */			
			$args->page = $page ;
			$args->module_srl  = $module_srl ; 
			

			$output = $oModuleModel->getModuleInfoByModuleSrl(  $module_srl );

			Context::set('module_info'  , $output);

			$args->order_type = "desc";
			$output = $oDocumentModel->getDocumentList( $args);
/**        일단  HTML 에서 루프를 돌려 보자         **/	
			Context::set('list' , $output->data);

			$files = $oFile->getFiles();
			foreach( $output->data as $value ){
				$value->files = $oFile->getFiles( $value->document_srl );
			}
			
			$this->setTemplateFile("view");
		}

        /**
         * 	@brief 관리자 목록 
         **/
		function dispBookmarkAdminList() {
			// 페이지 네비게이션을 위한 설정
            $page = Context::get('page');
            if(!$page) $page = 1;
			$args->page = $page;

			// Model 파일을 이용하지 않고 직접 쿼리로 목록을 구함
			$output = executeQueryArray('bookmark.getBookmarkAdminList', $args);
            ModuleModel::syncModuleToSite($output->data);

			// 템플릿에 전해주기 위해 set함
            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
			Context::set('bookmark_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);

			// 관리자 목록 보기 템플릿 지정(tpl/index.html)   모든 모듈들만 가지고 온다.... 
            $this->setTemplateFile('index');
		}
		function dispBookcAdminInsert(){
	
			$oEditorModel 	= &getModel('editor');
			$document_srl 	= Context::get('document_srl');
			$user_yn 			= Context::get('user_yn');
			$module_srl  	= Context::get('module_srl');

			// Option 
			$options = new stdClass();
			$options->allow_fileupload = true;
			$options->content_style = 'default';
			$options->content_font = null;
			$options->content_font_size = 12;
			$options->enable_autosave = true;
			$options->enable_default_component = true;
			//$options->enable_component = true;
			$options->disable_html = false;
			$options->height = 50;
			//$options->skin = 'dreditor';
			$options->skin = 'xpresseditor';
			$options->colorset = 'white';
			$options->primary_key_name = 'document_srl';
			$options->content_key_name = 'content';
			
			$options->enable_component = false;
			$options->enable_default_component = false ;

			// 디폴트는 0 입니다. 
			$upload_target_srl = $document_srl  ; 


		  $oModuleModel = &getModel("module");
debugPrint( $oModuleModel); 
			
			$oDocumentModel = &getModel('document');
			$oDocument =  $oDocumentModel->getDocument(0, $this->grant->manager);
			$oDocument->setDocument($document_srl);
            if($oDocument->get('module_srl') == $oDocument->get('member_srl')) $savedDoc = true;
            $oDocument->add('module_srl', $this->module_srl);
			
debugPrint ($oDocument ->document_srl) ; 			
debugPrint( $oDocument->getTitleText());

			// document 정보에 있는 모듈 아이디 찾고 없으면 파라미터로 넘어온 id 찾고 그래도 없으면 0 체크 해준다. 
			if ( !$this->module_srl ){
				if( !$module_srl ) {
					$this->module_srl = 0 ; 
				}else {
					$this->module_srl = $module_srl ;
				}

			}	


			//$normal_category_list = $oDocumentModel->getCategoryList( $this->module_srl  );   // $this->module_srl );
			$category_list  = $oModuleModel->getModuleCategories(); 

		    $module_info  = $oModuleModel->getModuleInfoByModuleSrl( $this->module_srl );
			//$options->content

			//$editor = $oEditorModel->getEditor( $upload_target_srl , $options);
			$editor = $oEditorModel->getEditor($upload_target_srl , $options);
			Context::set('editor' , $editor);

			//Context::set ('oDocument' , $oDocument );
		
			$document_info->category_srl = $oDocument->get('category_srl');
			$document_info->document_srl = $oDocument->get('document_srl');
			$document_info->content = $oDocument->get('content');
			$document_info->title       = $oDocument->getTitleText();
			
			Context::set ('document_info' , $document_info );
									
			Context::set('category_list'		, $category_list			);
			Context::set('user_yn' 			, $user_yn					);
			Context::set('module_info' 		, $this->module_info 	);

			$this->module_info->use_category=true ;
			$logged_info = Context::get('logged_info');
			
debugPrint(  $this->module_info );		
debugPrint(  $document_info );

			//$this->setTemplateFile("write_form");
			$this->setTemplateFile("write_form");
	
		}
		/***/
		function dispBookcAdminInsertStep2(){

			$oEditorModel = &getModel('editor');
			$document_srl = Context::get('document_srl');
			$user_yn 		= Context::get('user_yn');
			$module_srl  	= Context::get('module_srl');

			if (!$user_yn) $user_yn = "Y";
			
			// Option 
			$options = new stdClass();
			$options->allow_fileupload = true;
			$options->content_style = 'default';
			$options->content_font = null;
			$options->content_font_size = 12;
			$options->enable_autosave = true;
			$options->enable_default_component = true;
			//$options->enable_component = true;
			$options->disable_html = false;
			$options->height = 50;
			//$options->skin = 'dreditor';
			$options->skin = 'xpresseditor';
			$options->colorset = 'white';
			$options->primary_key_name = 'document_srl';
			$options->content_key_name = 'content';
			
			$options->enable_component = false;
			$options->enable_default_component = false ;

			// 디폴트는 0 입니다. 
			$upload_target_srl = $document_srl  ; 


		  $oModuleModel = &getModel("module");
debugPrint( $oModuleModel); 
			
			$oDocumentModel = &getModel('document');
			$oDocument =  $oDocumentModel->getDocument(0, $this->grant->manager);
			$oDocument->setDocument($document_srl);
            if($oDocument->get('module_srl') == $oDocument->get('member_srl')) $savedDoc = true;
            $oDocument->add('module_srl', $this->module_srl);
			
debugPrint ($oDocument ->document_srl) ; 			
debugPrint( $oDocument->getTitleText());

			// document 정보에 있는 모듈 아이디 찾고 없으면 파라미터로 넘어온 id 찾고 그래도 없으면 0 체크 해준다. 
			if ( !$this->module_srl ){
				if( !$module_srl ) {
					$this->module_srl = 0 ; 
				}else {
					$this->module_srl = $module_srl ;
				}

			}	


			//$normal_category_list = $oDocumentModel->getCategoryList( $this->module_srl  );   // $this->module_srl );
			$category_list  = $oModuleModel->getModuleCategories(); 

		    $module_info  = $oModuleModel->getModuleInfoByModuleSrl( $this->module_srl );
			//$options->content

			//$editor = $oEditorModel->getEditor( $upload_target_srl , $options);
			$editor = $oEditorModel->getEditor($upload_target_srl , $options);
			Context::set('editor' , $editor);

			//Context::set ('oDocument' , $oDocument );
		
			$document_info->category_srl = $oDocument->get('category_srl');
			$document_info->document_srl = $oDocument->get('document_srl');
			$document_info->content = $oDocument->get('content');
			$document_info->title       = $oDocument->getTitleText();
			
			Context::set ('document_info' , $document_info );
									
			Context::set('category_list', $category_list);
			Context::set('user_yn' , $user_yn);
			Context::set('module_info' , $this->module_info );

			$this->module_info->use_category=true ;
			$logged_info = Context::get('logged_info');
			
debugPrint(  $this->module_info );		
debugPrint(  $document_info );
		
		
		
			$this->setTemplateFile("write_form2");
		}		
        /**
         * @brief 모듈(mid) insert/update 화면 출력
         **/
		function dispBookmarkAdminInsert() {

			if(!in_array($this->module_info->module, array('admin', 'bookmark'))) {
                return $this->alertMessage('msg_invalid_request');
            }

            // 스킨 목록을 구해옴
            $oModuleModel = &getModel('module');
            $skin_list = $oModuleModel->getSkins($this->module_path);
            Context::set('skin_list',$skin_list);

            // 레이아웃 목록을 구해옴
            $oLayoutModel = &getModel('layout');
            $layout_list = $oLayoutModel->getLayoutList();
            Context::set('layout_list', $layout_list);

            // 템플릿 파일 지정
            $this->setTemplateFile('admin_insert');
			
			
		}
		
		
		function dispBookcAdminImageInsert() {
	

			$oEditorModel = &getModel('editor');
			$document_srl = Context::get('document_srl');
			$user_yn 		= Context::get('user_yn');
			$module_srl  	= Context::get('module_srl');

			// Option 
			$options = new stdClass();
			$options->allow_fileupload = true;
			$options->content_style = 'default';
			$options->content_font = null;
			$options->content_font_size = 12;
			$options->enable_autosave = true;
			$options->enable_default_component = true;
			//$options->enable_component = true;
			$options->disable_html = false;
			$options->height = 50;
			//$options->skin = 'dreditor';
			$options->skin = 'xpresseditor';
			$options->colorset = 'white';
			$options->primary_key_name = 'document_srl';
			$options->content_key_name = 'content';
			
			
			$options->enable_component = false;
			$options->enable_default_component = false ;
			
			
			// 디폴트는 0 입니다. 
			$upload_target_srl = $document_srl  ; 		


		  $oModuleModel = &getModel("module");
debugPrint( $oModuleModel); 
			
			$oDocumentModel = &getModel('document');
			$oDocument =  $oDocumentModel->getDocument(0, $this->grant->manager);
			$oDocument->setDocument($document_srl);
            if($oDocument->get('module_srl') == $oDocument->get('member_srl')) $savedDoc = true;
            $oDocument->add('module_srl', $this->module_srl);
			
debugPrint ($oDocument ->document_srl) ; 			
debugPrint( $oDocument->getTitleText());

			// document 정보에 있는 모듈 아이디 찾고 없으면 파라미터로 넘어온 id 찾고 그래도 없으면 0 체크 해준다. 
			if ( !$this->module_srl ){
				if( !$module_srl ) {
					$this->module_srl = 0 ; 
				}else {
					$this->module_srl = $module_srl ;
				}

			}	

			//$normal_category_list = $oDocumentModel->getCategoryList( $this->module_srl  );   // $this->module_srl );
			$category_list  = $oModuleModel->getModuleCategories(); 

		    $module_info  = $oModuleModel->getModuleInfoByModuleSrl( $this->module_srl );
			//$options->content

			$editor = $oEditorModel->getEditor( $upload_target_srl , $options);
			Context::set('editor' , $editor);
			Context::set ('oDocument' , $oDocument );
			Context::set('category_list', $category_list);
			Context::set('user_yn' , $user_yn);
			Context::set('module_info' , $this->module_info );
			
			$this->module_info->use_category=true ;
			$logged_info = Context::get('logged_info');
			
debugPrint(  $this->module_info );		
debugPrint(  $oDocument );
		
			//$this->setTemplateFile("write_form");
			
			$this->setTemplateFile("write_form");
		}
        /**
         * @brief 선택된 모듈의 정보 출력은 곧바로 정보 입력으로 변경한다
         **/


        /**
         * @brief 권한 목록 출력
         **/
        function dispBookmarkAdminGrantInfo() {

            // 공통 모듈 권한 설정 페이지 호출
            $oModuleAdminModel = &getAdminModel('module');
            $grant_content = $oModuleAdminModel->getModuleGrantHTML($this->module_info->module_srl, $this->xml_info->grant);
            Context::set('grant_content', $grant_content);

            $this->setTemplateFile('grant_list');
        }

        /**
         * @brief 스킨 정보 보여줌
         **/
        function dispBookcAdminDeleteModule() {
            // 공통 모듈 권한 설정 페이지 호출
            $oModuleAdminModel = &getAdminModel('module');
            $skin_content = $oModuleAdminModel->getModuleSkinHTML($this->module_info->module_srl);
            Context::set('skin_content', $skin_content);

            $this->setTemplateFile('bookc_delete');
        }
		
		
		
	}
?>
