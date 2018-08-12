<?php
namespace Common\Behaviors;

class ReadHtmlCacheBehavior extends BaseBehavior
{
	//行为执行入口
	public function run(&$param)
	{
		$this->checkPage();
	}

	private function checkPage()
	{
		if(!C('PAGE_STATIC_ON'))
		{
			return;
		}
		
		$pageNeedStatic=C('PAGE_STATIC_RULE'); //规则表
	
		$requestPath=$this->getRequestPath();
		
		$rule=match_path_rule($pageNeedStatic,$requestPath);
		
		if(!$rule)
		{
			return;
		}
		
		$filePath=$this->getCurrentCacheFilePath($rule['time']);	
		
		if(file_exists($filePath))
		{
		    $pageContent=file_get_contents($filePath);
			print_r($pageContent);
			exit();
		}
	}
}