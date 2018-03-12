<?
/**
 * @calss elfinderxeAdminView
 * @author SeungJun Lee(TUW) (temflic@gmail.com)
 * @brief elFinderXE File Manager Admin view class
 **/

class elfinderxeAdminView extends elfinderxe
{	/* 클래스 : 관리자 페이지 */

	function init()
	{	/* 관리자 페이지 초기화 */
		// module_srl의 존재 여부 평가
		$module_srl = Context::get('module_srl');
		if(!$module_srl && $this->module_srl)
		{	// module_srl이 전달되지 않았지만 내부에 저장된 값이 있을 경우
			$module_srl = $this->module_srl;
			Context::set('module_srl', $module_srl);
		}

		// module model 객체 생성
		$oModuleModel = &getModel('module');

		// module_srl을 토대로 모듈 정보 추출
		if($module_srl)
		{
			$module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
			if(!$module_info)
			{
				Context::set('module_srl', '');
				$this->act = 'list';
			}
			else
			{
				ModuleModel::syncModuleToSite($module_info);
				$this->module_info = $module_info;
				$this->module_info->elfinderxe_ui = explode('|@|', $module_info->elfinderxe_ui);
				Context::set('module_info', $module_info);
			}
		}

		// 요청한 mid의 module이 elfinderxe가 아니면 정지
		if($module_info && $module_info->module != 'elfinderxe')
			return $this->stop('msg_invalid_request');

		// Module 카테고리 목록 가져오기
		$module_category = $oModuleModel->getModuleCategories();
		Context::set('module_category', $module_category);

		// Module Info와 Module Category 정보를 보안 입출력을 통해 읽어들임
		$security = new Security();
		$security->encodeHTML('module_info.');
		$security->encodeHTML('module_category..');

		// Template Path 설정
		$template_path = sprintf("%stpl/",$this->module_path);
		$this->setTemplatePath($template_path);

		// 정렬 옵션 적용
		foreach($this->order_target as $key)
			$order_target[$key] = Context::getLang($key);
		$order_target['list_order']		= Context::getLang('document_srl');
		$order_target['update_order']	= Context::getLang('last_update');
		Context::set('order_target', $order_target);
	}

	function dispElFinderxeAdminContent()
	{	/* 메소드 : 관리자 페이지 표시 */
		// 모듈 일반 정보 추출
		$args->sort_index			= 'module_srl';	// 정렬 방법
		$args->page					= Context::get('page');
		$args->list_count			= 20;
		$args->page_count			= 10;
		$args->s_module_category_srl = Context::get('module_category_srl');

		$search_target	= Context::get('search_target');
		$search_keyword	= Context::get('search_keyword');

		switch($search_target)
		{
			case 'mid' : 
				$args->s_mid = $search_keyword;
				break;
			case 'browser_title' : 
				$args->s_browser_title = $search_keyword;
				break;
		}

		// 모듈 목록 얻기
		$output = executeQueryArray('elfinderxe.getElfinderxeList', $args);
		ModuleModel::syncModuleToSite($output->data);

		// 스킨 경로 얻기 :: 사용 안 함
		/*
		$oModuleModel = &getModel('module');
		$skin_list = $oModuleModel->getSkins($this->module_path);
		Context::set('skin_list',$skin_list);

		$mskin_list = $oModuleModel->getSkins($this->module_path,'m.skins');
		Context::set('mskin_list', $mskin_list);
		*/

		// 레아아웃 경로 얻기
		$oLayoutModel = &getModel('layout');
		$layout_list = $oLayoutModel->getLayoutList();
		Context::set('layout_list', $layout_list);

		$mlayout_list = $oLayoutModel->getLayoutList(0, 'M');
		Context::set('mlayout_list', $mlayout_list);

		Context::set('module_srls', 'dummy');
		$context = '';

		// 관리자 모델 얻기
		$oModuleAdminModel = &getAdminModel('module');
		$grant_content = $oModuleAdminModel->getModuleGrantHTML($this->module_info->module_srl, $this->xml_info->grant);
		Context::set('grant_content', $grant_content);

		// 관리자 뷰에서 사용할 변수들 세팅
		Context::set('total_count',		$output->total_count);
		Context::set('total_page',		$output->total_page);
		Context::set('page',			$output->page);
		Context::set('elfinderxe_list',	$output->data);
		Context::set('page_navigation',	$output->page_navigation);

		$security = new Security();
		$security->encodeHTML('elfinderxe_list..browser_title', 'elfinderxe_list..mid');
		//$security->encodeHTML('skin_list..title', 'mskin_list..title');
		$security->encodeHTML('layout_list..title', 'layout_list..layout');
		$security->encodeHTML('mlayout_list..title', 'mlayout_list..layout');

		// 템플릿 파일 지정
		$this->setTemplateFile('index');
	}
	
	function dispElfinderxeAdminInsertFileManager()
	{	/* 메소드 : 파일 관리기 추가 */
		// 요청한 모듈이 admin 혹은 elfinderxe가 아니면 반사;
		if(!in_array($this->module_info->module, array('admin', 'elfinderxe')))
			return $this->alertMessage('msg_invalid_request');

		// 레이아웃 목록 얻기
		$oLayoutModel = &getModel('layout');

		$layout_list = $oLayoutModel->getLayoutList();
		Context::set('layout_list', $layout_list);
		
		$mlayout_list = $oLayoutModel->getLayoutList(0, 'M');
		Context::set('mlayout_list', $mlayout_list);

		// 레이아웃 정보를 보안 입출력을 이용하여 읽어들임
		$security = new Security();
		$security->encodeHTML('skin_list..title','mskin_list..title');
		$security->encodeHTML('layout_list..title','layout_list..layout');
		$security->encodeHTML('mlayout_list..title','mlayout_list..layout');

		// 문서 상태 목록 읽기
		$oDocumentModel = &getModel('document');
		$documentStatusList = $oDocumentModel->getStatusNameList();
		Context::set('document_status_list', $documentStatusList);

		// 템플릿 파일 지정
		$this->setTemplateFile('elfinderxe_insert');
	}

	function alertMessage($message)
	{	/* 메소드 : alert 메시지 출력 */
		$script =  sprintf('<script type="text/javascript"> xAddEventListener(window,"load", function() { alert("%s"); } );</script>', Context::getLang($message));
		Context::addHtmlHeader( $script );
	}

	function dispElfinderxeAdminDeleteFileManager()
	{	/* 메소드 : 파일 관리기 삭제 */
		// module_srl이 전달되지 않은 경우 관리자 메인페이지로
		if(!Context::get('module_srl')) return $this->dispElfinderxeAdminContent();
		// 요청 모듈이 admin이나 elfinderxe가 아닌 경우 반사
		if(!in_array($this->module_info->module, array('admin', 'elfinderxe')))
			return $this->alertMessage('msg_invalid_request');

		$security = new Security();
		$security->encodeHTML('module_info..mid','module_info..module');

		// 템플릿 파일 지정
		$this->setTemplateFile('elfinderxe_delete');
	}

	function dispElfinderxeAdminFileManagerInfo()
	{	/* 메소드 : 파일 관리기 정보 표시 */
		$this->dispElfinderxeAdminInsertFileManager();
	}

	function dispElfinderxeAdminGrantInfo()
	{	/* 메소드 : 권한 관리 */
		$oModuleAdminModel = &getAdminModel('module');
		$grant_content = $oModuleAdminModel->getModuleGrantHTML($this->module_info->module_srl, $this->xml_info->grant);
		Context::set('grant_content', $grant_content);	
	
		// 템플릿 파일 지정
		$this->setTemplateFile('grant_list');	
	}
}
?>