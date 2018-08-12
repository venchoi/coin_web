<?php
namespace Common\Behaviors;
class WriteHtmlCacheBehavior extends BaseBehavior
{
	
	private $htmlContent='';
	
	//行为执行入口
	public function run(&$param)
	{
		$this->htmlContent=$param;
		$this->checkPage();
	}
	
	
	private function checkPage()
	{
		if(!C('PAGE_STATIC_ON'))
		{
			return;
		}
		
		$pageNeedStatic=C('PAGE_STATIC_RULE');
		
		$requestPath=$this->getRequestPath();
		
		$rule=match_path_rule($pageNeedStatic,$requestPath);
		
		if(!$rule)
		{
			return;
		}
		
		$filePath=$this->getCurrentCacheFilePath($rule['time']);		
		$htmlContent=$this->htmlContent;
		
		if(C('MINIFY'))
		{
			$htmlContent=$this->contatRes($htmlContent);  //页面压缩
		}
		
		file_put_contents($filePath,$htmlContent);
	}
	
	//处理资源
	private function contatRes($htmlContent)
	{
		$htmlContent=preg_replace('/<!-.+?->/i',"",$htmlContent);  //去掉html注释
		$htmlContent=preg_replace('/>\s*?</i',"><",$htmlContent);  //压缩多余的空行
		
		return $htmlContent;
	}

	
}