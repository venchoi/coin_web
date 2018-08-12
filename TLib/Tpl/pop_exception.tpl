<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>你所访问的页面不存在-POPCOIN首页</title>
	<meta name="keywords" content="POPCOIN_最快最全的数字货币资讯聚合平台">
	<meta name="description" content="POPCOIN，数字货币，区块链，数字货币资讯，爆米花财经，比特币消息，以太坊新闻，莱特币，爆米花资讯">
	<meta name="content" content="POPCOIN，数字货币，区块链，数字货币资讯，爆米花财经，比特币消息，以太坊新闻，莱特币，爆米花资讯">
	<style type="text/css">
		body {
			margin: 0px;
			padding: 0px;
			font-family: "微软雅黑", Arial, "Trebuchet MS", Verdana, Georgia, Baskerville, Palatino, Times;
			font-size: 16px;
		}

		div {
			position: absolute;
			left: 50%;
			top: 50%;
			transform: translate(-50%, -50%);
		}

		img {
			width: 758px;
			height: 419px;
		}

		a {
			position: absolute;
			top: 338px;
			left: 433px;
			font-size: 20px;
			color: #394551;
			font-weight: bold;
			text-decoration: none;
		}

		a:any-link {
			color: inherit;
		}
	</style>
</head>

<body>
	<div>
		<img src="/res/pc/static/images/404.png" alt="POPCOIN">
		<a href="/index.html"></a>
	</div>

	<div class="error"  style="display:none">
		<p class="face">:(</p>
		<h1><?php echo strip_tags($e['message']);?></h1>
		<div class="content">
			<?php if(isset($e['file'])) {?>
			<div class="info">
			<div class="title">
			<h3>错误位置</h3>
			</div>
			<div class="text">
			<p>FILE: <?php echo $e['file'] ;?> &#12288;LINE: <?php echo $e['line'];?></p>
			</div>
			</div>
			<?php }?>
			<?php if(isset($e['trace'])) {?>
			<div class="info">
			<div class="title">
			<h3>TRACE</h3>
			</div>
			<div class="text">
			<p><?php echo nl2br($e['trace']);?></p>
			</div>
			</div>
			<?php }?>
		</div>
	</div>
	
</body>
</html>