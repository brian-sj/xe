<?
/**
 * @class  elfinderxeController
 * @author SeungJun Lee(TUW) (temflic@gmail.com)
 * @brief  elFinderXE module admin Controller class
 **/

class elfinderxeAdminController extends elfinderxe
{
	function init()
	{	/* 초기화 */
	}

	function procElfinderxeAdminInsertFileManager($ars = null)
	{	/* 메소드 : 파일 관리기 모듈 삽입 */
		// Controller 및 Model 얻기
		$oModuleController = &getController('module');
		$oModuleModel = &getModel('module');

		// 모듈 정보 업데이트
		$args = Context::getRequestVars();
		$args->module	= 'elfinderxe';
		$args->mid		= $args->elfinderxe_name;
		if(is_array($args->elfinderxe_ui))
			$args->elfinderxe_ui = implode('|@|', $args->elfinderxe_ui);
		unset($args->elfinderxe_name);

		// 환경설정 변수 Preprocessing
		if(empty($args->elfinderxe_width)) unset($args->elfinderxe_width);
		if($args->elfinderxe_width_auto!='Y')	$args->elfinderxe_width_auto = 'N';

		// module_srl이 존재하는 경우(즉, 모듈 업데이트 모드인 경우)
		if($args->module_srl)
		{
			$module_info = $oModuleModel->getModuleInfoByModuleSrl($args->module_srl);
			if($module_info->module_srl!=$args->module_srl) unset($args->module_srl);
		}

		// 모듈 등록/업데이트 실행
		if(!$args->module_srl)
		{
			$output = $oModuleController->insertModule($args);
			$msg_code = 'success_registed';
		}
		else
		{
			$output = $oModuleController->updateModule($args);
			$msg_code = 'success_updated';
		}

		if(!$output->toBool()) return $output;
		$this->setMessage($msg_code);

		if(Context::get('success_return_url'))
		{	// 이동할 URL이 지정된 경우
			$this->setRedirectUrl(Context::get('success_return_url'));
		}
		else
		{	// 지정되지 않은 경우 정보 표시 페이지로 이동
			$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispElfinderxeAdminFileManagerInfo', 'module_srl', $output->get('module_srl')));
		}
	}

	function procElfinderxeAdminDeleteFileManager()
	{	/* 메소드 : 파일 관리기 삭제 */
		$module_srl = Context::get('module_srl');

		// 모듈 제어 핸들러 얻기
		$oModuleController = &getController('module');
		$output = $oModuleController->deleteModule($module_srl);

		// 삭제에 실패한 경우 
		if(!$output->toBool()) return $output;

		$this->add('module', 'elfinderxe');
		$this->add('page', Context::get('page'));
		$this->setMessage('success_deleted');
	}
}
?>