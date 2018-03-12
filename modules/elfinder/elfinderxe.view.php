<?
/**
 * @class  elfinderxeView
 * @author SeungJun Lee(TUW) (temflic@gmail.com)
 * @brief  elFinderXE module View class
 **/

class elfinderxeView extends elfinderxe
{

	function init()
	{	/* 초기화 */

		// 환경설정 로드
		$this->module_info->elfinderxe_ui = explode('|@|', $this->module_info->elfinderxe_ui);
		Context::set('module_info', $this->module_info);

		// 사용 언어 정보 로드
		$db_info = Context::getDBInfo();
		Context::set('lang_pack', $db_info->lang_type);

		// 사용 가능 명령 리스트 생성
		$allowed_cmd = array('reload', 'home', 'up', 'back', 'forward', 'search', 'help', 'sort');
		if($this->grant->fread)
		{	// 파일 열기 및 다운로드 권한이 있는 경우
			array_push($allowed_cmd, 'info', 'view', 'open', 'getfile', 'quicklook', 'download');
		}
		if($this->grant->fwrite)
		{	// 파일 쓰기 및 업로드 권한이 있는 경우
			array_push($allowed_cmd, 'duplicate', 'mkfile', 'upload', 'copy', 'paste');
		}
		if($this->grant->fedit)
		{	// 파일 편집 및 삭제 권한이 있는 경우
			array_push($allowed_cmd, 'rename', 'cut', 'rm', 'edit', 'resize');
		}
		if($this->grant->mkdir)
		{	// 폴더 생성 권한이 있는 경우
			array_push($allowed_cmd, 'mkdir');
		}
		if($this->grant->archive)
		{	// 압축 권한이 있는 경우
			array_push($allowed_cmd, 'archive');
		}
		if($this->grant->extract)
		{	// 파일 열기 및 다운로드 권한이 있는 경우
			array_push($allowed_cmd, 'extract');
		}
		Context::set('allowed_cmd', $allowed_cmd);
	}

	function dispElfinderxeFileManager()
	{	/* 파일 관리기 표시 */
		// Template Path 설정
		$template_path = sprintf("%sincludes/",$this->module_path);
		$this->setTemplatePath($template_path);

		// 템플릿 파일 로드
		$this->setTemplateFile('elfinderxe');
	}
}

?>