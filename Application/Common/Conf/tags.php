<?php
return array(
	 'app_begin'=>array('Common\Behaviors\ReadHtmlCacheBehavior'),
	 'view_filter'=>array('Common\Behaviors\WriteHtmlCacheBehavior'),
);