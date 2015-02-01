<?php
//通过api的形式调用数据信息
header("Content-Type: text/html; charset=utf-8");
require('tree.class.php');

$request = isset($_GET) ? $_GET : array();

$query = isset($request['path']) ? $request['path'] : ''; //显示目录,文件
$dirsize = isset($request['dirsize']) ? 1 : 0; //是否显示目录大小
$mkdir = isset($request['mkdir']) ? 1 : 0; //创建目录

if($query)
{
	if($dirsize)
	{
		$data = Tree::getformatdirsize($query);
	}
	elseif($mkdir)
	{
		$data = Tree::newdir($query);
	}
	else
	{
		$ob = new Tree($query);
		$data = $ob->work();
	}

	echo json_encode($data);
}