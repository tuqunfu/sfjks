$("#report").on("click",function(){
	var url  = $(this).attr('url');
	var data = {};
	data.group_id = $('select[name="group_id"]').val();
	data.status = $('select[name="status"]').val();
	data.pkgname = $('select[name="pkgname"]').val();
	data.year = $('select[name="year"]').val();
	data.month = $('select[name="month"]').val();
	data.user_id = $('select[name="user_id"]').val();
	
	layer.open({
		type: 1
		,title: "系统提示" //不显示标题栏
		,closeBtn: false
		,area: '300px;'
		,shade: 0.5
		,id: 'LAY_layuipro' //设定一个id，防止重复弹出
		,btn: ['确定', '取消']
		,btnAlign: 'c'
		,moveType: 1 //拖拽模式，0或者1
		,content: '<div style="padding: 10px; line-height: 22px; color: #333; font-weight: 300;">是否导出报表</div>'
		,yes: function(index, layero){
			layer.close(index);	
			var load = layer.msg('正在提交请稍候...', {icon: 16,time: 1000,shade : [0.5 , '#000' , true]}); 
			$.ajax({
				url:url,
				data : data,
				type:"Post",
				dataType:"json",
				success:function(ret){
					layer.close(load);
					layer.msg('生成成功！',{icon:1,time: 2000},function(){
						document.location.href =(ret.url); 
					});			
				},
				error:function(err){
					layer.close(load);
					layer.msg('审核失败！',{icon: 2,time: 2000});						
				}
			});
		},
		cancel:function(){
		},
	});
});




$("#rankreport").on("click",function(){
	var url  = $(this).attr('url');
	var city_id = $('select[name="city_id"]').val();
	var ranktype = $('select[name="ranktype"]').val();
	layer.open({
		type: 1
		,title: "系统提示" //不显示标题栏
		,closeBtn: false
		,area: '300px;'
		,shade: 0.5
		,id: 'LAY_layuipro' //设定一个id，防止重复弹出
		,btn: ['确定', '取消']
		,btnAlign: 'c'
		,moveType: 1 //拖拽模式，0或者1
		,content: '<div style="padding: 10px; line-height: 22px; color: #333; font-weight: 300;">是否导出报表</div>'
		,yes: function(index, layero){
			layer.close(index);
			var data = {};
			var load = layer.msg('正在提交请稍候...', {icon: 16,time: 1000,shade : [0.5 , '#000' , true]}); 
			data.city_id = city_id;
			data.ranktype = ranktype;
			$.ajax({
				url:url,
				data : data,
				type:"Post",
				dataType:"json",
				success:function(ret){
					layer.close(load);
					layer.msg('生成成功！',{icon:1,time: 2000},function(){
						document.location.href =(ret.url); 
					});			
				},
				error:function(err){
					layer.close(load);
					layer.msg('审核失败！',{icon: 2,time: 2000});						
				}
			});
		},
		cancel:function(){
		},
	});
});