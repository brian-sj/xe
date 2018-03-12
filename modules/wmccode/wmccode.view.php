<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * @class  tagController
 * @author NAVER (developers@xpressengine.com)
 * @brief tag module's controller class
 */
class wmccodeView extends wmccode
{
	function getWmccodeBySubCodeAPI(){}
	function dispWmccodeIndex()
	{
		debugPrint('eeee');
		$this->setTemplateFile('index');
	}

}// wmccode Code end 
