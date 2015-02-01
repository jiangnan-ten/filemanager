<?php 
	require('tree.api.php');
	$rootpath = 'D:\book';
	$root = new Tree($rootpath);
	$root = $root->work();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>目录树</title>
	<link rel="stylesheet" type="text/css" href="./Semantic-UI-1.6.2/dist/semantic.min.css">
	<style type="text/css">
		ul, li{list-style: none}
		.dir-path{word-break: break-all;}
		.file-name, .file-size{display: inline-block;width:400px;}
		.file-size{text-align: right}
		#dir-info{}
		.fixed.button{position: fixed;top:63px;right:-1px;}
	</style>
</head>
<body oncontextmenu="return false">
	<!-- 边栏 -->
	<div class="ui left vertical inverted labeled icon sidebar menu">
		<a class="item dirinfo"><i class="info circle icon"></i>属性</a>
	  	<a class="item mkdir"><i class="add circle icon"></i>新建文件夹</a>
	  	<a class="item rmdir"><i class="remove circle icon"></i>删除文件夹</a>
	  	<a class="item rename"><i class="level up icon"></i>重命名</a>
	</div>

	<!-- 内容 -->
	<div class="pusher">
		<div class="ui grid centered">
			<div class="tree ten wide column">
				<ul>
					<?php if(!empty($root['dir']))
					{
						foreach($root['dir'] as $v)
						{ ?>
							<li><i class="folder icon"></i><a href="<?php echo $v?>" class="dir"><?php echo ($v) ?></a></li>
						<?php }
					}
					if(!empty($root['file']))
					{
						foreach($root['file'] as $v)
						{ ?>
							<li class="file"><i class="file text icon purple"></i><?php echo htmlspecialchars($v['file']) ?></li>
						<?php }
					} ?>
				</ul>
			</div>
		</div>
	</div>

	<!-- 目录信息modal -->
	<div class="ui basic modal small dirinfo" id="dir-info">
		<div class="header">
			<i class="circular red folder open outline icon"></i>
			<span class='dir-name'></span>
		</div>
		<div class="ui divider"></div>
		<div class="content">
			<div class="description">
				<div class="ui grid">
					<div class="row">
						<div class="six wide column">路径</div>
						<div class="ten wide column dir-path"></div>
					</div>
					<div class="row">
						<div class="six wide column">大小</div>
						<div class="ten wide column dir-size"></div>
					</div>
					<div class="row">
						<div class="six wide column">包含</div>
						<div class="ten wide column dir-contain"></div>
					</div>
					<div class="row">
						<div class="six wide column">创建时间</div>
						<div class="ten wide column dir-ctime"></div>
					</div>
					<div class="row">
						<div class="six wide column">修改时间</div>
						<div class="ten wide column dir-utime"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="actions">
			<div class="ui negative button">关闭</div>
		</div>
	</div>

	<!-- 新建文件夹modal -->
	<div class="ui basic modal small mkdir" id="mkdir">
		<div class="header">
			<i class="circular red folder open outline icon"></i>
			<span>Create a new folder</span>
		</div>
		<div class="ui divider"></div>
		<div class="content">
			<p class="ui inverted header">当前路径 <span class="now-path"></span></p>
			<div class="ui form">
				<div class="field">
					<input type="text"  class="dirname" placeholder="不能含有\/.?|<>:">
				</div>
			</div>
		</div>
		<div class="actions">
			<div class="ui green button send">确定</div>
			<div class="ui negative button">关闭</div>
		</div>
	</div>

	<!-- js开始 -->
	<script type="text/javascript" src='http://libs.baidu.com/jquery/1.8.3/jquery.min.js'></script>
	<script type="text/javascript" src="./Semantic-UI-1.6.2/dist/semantic.min.js"></script>
	<script type="text/javascript">
		var g_this; //右键目录产生的自己
		var g_origpath; //右键目录产生的原始没有经过转码处理的url
		var g_path; //右键处理后的url

		$('.tree').on('click', '.dir', function() {
			var path = encodeURIComponent($(this).attr('href'));
			var _this = this;

			$(this).addClass('clicked');

			if(!$(this).next('ul').html()) {
				$.getJSON('tree.api.php?path='+path, function(data) {
					if(data) {
						var html = '<ul>';
						var dir = '';
						var file = '';
						var tempdir = '';
						var tempfile = '';

						if(data['dir']) {
							for(var i in data['dir']) {
								tempdir = data['dir'][i];
								dir = tempdir.substr( tempdir.lastIndexOf('\\')+1 );
								html += '<li><i class="folder icon"></i><a class="dir" href="'+tempdir+'">'+dir+'</a></li>'
							}
						}
						if(data['file']) {
							for(var j in data['file']) {
								tempfile = data['file'][j];
								file = tempfile['file'].substr( tempfile['file'].lastIndexOf('\\')+1 );
								html += '<li class="file"><i class="file text icon purple"></i><span class="file-name">'+file+'</span>';
								html +=	'<span class="file-size">'+tempfile['size']+'</span>';
								html += '</li>';
							}
						}

						html += '</ul>';
						$(_this).after(html);

						$(_this).prev().addClass('open');
					}
				});
			}

			return false;
		});

		/*折叠目录*/
		$('.tree').on('click', 'a', function() {
			$(this).prev().removeClass('open');
			$(this).next('ul').remove();
			return false;
		});

		/*右键展开sidebar*/
		$('.tree').on('mousedown', '.dir', function(e) {
			if(e.which == 3) {
				g_this = this;
				g_origpath = $(this).attr('href');
				g_path = encodeURIComponent($(this).attr('href')); //目录路径

				/*展开sidebar*/
				$('.left.sidebar').sidebar('toggle');
			}
		});

		/*打开目录信息*/
		$('.menu.sidebar').on('click', '.item.dirinfo', function() {
			$.getJSON('tree.api.php?dirsize&path='+g_path, function(data) {
				if(data) {
					$('.ui.basic.modal.small.dirinfo').modal({closable: false,}).modal('show');

					/*装填数据*/
					$('.dir-name').text($(g_this).text());
					$('.dir-path').text($(g_this).attr('href'));
					$('.dir-size').text(data['size']);
					$('.dir-ctime').text(data['ctime']);
					$('.dir-utime').text(data['utime']);
					$('.dir-contain').text(data['dir_count']+'文件夹,  '+data['file_count']+'文件');

					$('.ui.basic.modal.small.dirinfo').modal('setting',{
						onHide: function() {
							/*关闭modal清空数据*/
							$('.dir-name').text('');
							$('.dir-path').text('');
							$('.dir-size').text('');
							$('.dir-contain').text('');
							$('.dir-ctime').text('');
							$('.dir-utime').text('');
						}
					});
				}
			});
		});

		/*新建文件夹*/
		$('.item.mkdir').click(function() {
			$('.ui.basic.modal.small.mkdir').modal({closable: false}).modal('show'); //显示

			$('.now-path').text(g_origpath); //设置当前路径

			$('.send').click(function() {
				var dirname = $('.dirname').val();
				if(dirname) {
					var mkdirori = g_origpath+'\\'+dirname;
					var path = encodeURIComponent(mkdirori);

					$.getJSON('tree.api.php?mkdir&path='+path, function(data) {
						if(data) {
							if($(g_this).hasClass('clicked')) { //如果目录展开则显示出新添加的目录
								var html = '<li><i class="folder icon"></i><a class="dir" href="'+mkdirori+'">'+dirname+'</a></li>';
								$(g_this).next().append(html);
							}
						}
					});
				}
				$('.dirname').val(''); //输入框重置为空
			});
		});
	</script>
</body>
</html>
