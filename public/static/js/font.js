$(document).ready(function(){
	// 初始化
	$("select[name=font_size]").val('128');
	layui.use(['form'], function(){
		var form = layui.form;
		form.render('select'); // 重新渲染 select 组件
	});
	// 获取登录用户信息
	getuser();
	// 创建字体
	create_font(GV.font_url,GV.font_content,"red",128,null);
	
});

// 生成字体
function create_font(font_url,content,font_color,font_size,index){
	if(content==null || content==''){
		layer.msg('请输入内容！');
		if(index !=null){
			layer.close(index);
		}
		return;
	}
	opentype.load(font_url, function(err, font) {
		layer.close(index);
		if (err) {
			layer.msg('选择的字体加载失败！');
			if(index !=null){
				layer.close(index);
			}
			return;
		}
		// 设置字体背景色
		var font_back =  $("select[name=font_back]").val();
		// 设置行字数,获取行数
		var font_number = $("select[name=font_number]").val();
		var line_text = [];
		if(font_number == ""){
			line_text = content.split(/\r\n|\n/).filter(Boolean); // 简洁写法过滤空行
			console.log(line_text)
		}else{
			var line_text = splitTextByChars(content,parseInt(font_number));
		}
		// 设置字体方向
		var font_show = $("select[name=font_show]").val();
		var show_float = 'left';
		var show_class = "f_h"
		if(font_show == ""){
			show_float = 'left';
			show_class = "f_h";
		}else{
			if(font_show=="横排"){
				show_float = 'left';
				show_class = "f_h";
			}else{
				show_float = 'right';
				show_class = "f_v";
			}
		}
		var html = '';
		for(let i = 0; i < line_text.length; i++)
		{
			html+="<div class='f_line'>";
			var text = line_text[i];
			for (let j = 0; j < text.length; j++) 
			{
				const char = text[j];
				var id = (i+1)+"_"+(j+1)+"_"+char;
				html+="<dl class='"+show_class+"' style='float: "+show_float+"; display: block;'>";
				
				// 生成路径（注意：x=0, y=0，但我们要居中）
				const path = font.getPath(char, 0, 0, font_size); // 先不设置 y 偏移
				// 获取路径的边界框
				const bounds = path.getBoundingBox();
				const pathWidth = bounds.x2 - bounds.x1;
				const pathHeight = bounds.y2 - bounds.y1;
				// 计算居中偏移
				const offsetX = (font_size - pathWidth) / 2 - bounds.x1; // 居中 X
				const offsetY = (font_size - pathHeight) / 2 - bounds.y1; // 居中 Y
				// 注意：SVG 的 Y 轴向下为正，字体的 baseline 在顶部附近，所以通常 bounds.y0 是负值
				// 生成带居中 transform 的 SVG 路径
				const svgPath = path.toPathData(3);
				const pathElement = document.createElementNS("http://www.w3.org/2000/svg", "path");
				pathElement.setAttribute("d", svgPath);
				pathElement.setAttribute("fill", font_color);
				pathElement.setAttribute("transform", `translate(${offsetX}, ${offsetY})`);
				// 创建 SVG 容器
				const svgNS = "http://www.w3.org/2000/svg";
				const svgElement = document.createElementNS(svgNS, "svg");
				svgElement.setAttribute("width", font_size);
				svgElement.setAttribute("height", font_size);
				svgElement.setAttribute("viewBox", `0 0 ${font_size} ${font_size}`);
				svgElement.setAttribute("xmlns", svgNS);
				svgElement.appendChild(pathElement);
				// 序列化 SVG
				var svgString = new XMLSerializer().serializeToString(svgElement);
				// Base64 编码
				const base64 = btoa(unescape(encodeURIComponent(svgString)));
				const imgSrc = 'data:image/svg+xml;base64,' + base64;
				if(font_back!==""){
					var grid_style = "width:"+font_size+"px;" + "height:"+font_size+"px;" ;
					var backszie = parseInt(font_size);
					grid_style+= " background-size:"+backszie+"px"+" "+font_size+"px;";
					grid_style+= " background-image:url(/public/static/images/bg/"+font_size+"_1_"+font_back+".png);";
					grid_style+= " display:block;overflow:hidden;";
					html+="<dt><span style= '"+grid_style+"' class='grid'><img id='"+id+"' class='font' src='"+imgSrc+"' /></span></dt>";
				}else{
					html+="<dt><span class='grid'><img id='"+id+"' src='"+imgSrc+"' /></span></dt>";
				}
				html+="</dl>";
			}
			html+="</div>";
		}
		$("#shufa-img").html(html);
		// 设置背景颜色
		var back_color = $("input[name=back_color]").val();
		if(back_color!==""){
			$("#shufa-img").css('background-color',back_color);
		}
		if(index !=null){
			layer.close(index);
		}
	});
}
/**
 * 将文本按指定字数分行
 * @param {string} text - 要分行的文本
 * @param {number} charsPerLine - 每行的字数（字符数）
 * @returns {string[]} 分行后的字符串数组
 */
function splitTextByChars(text, charsPerLine) {
    if (!text || typeof text !== 'string') return [];
    if (charsPerLine <= 0) return [];
    
    text = text.trim(); // 去除首尾空白
    const lines = [];
    for (let i = 0; i < text.length; i += charsPerLine) {
        lines.push(text.slice(i, i + charsPerLine));
    }
    return lines;
}

layui.use('colorpicker', function(){
	var colorpicker = layui.colorpicker;
	colorpicker.render({
	elem: '#font_color'
		,done: function(color){
			$("input[name=font_color]").val(color);
		}
	});
	// 更新字体背景颜色
	colorpicker.render({
	elem: '#back_color'
		,done: function(color){
			$("input[name=back_color]").val(color);
			$("#shufa-img").css('background-color',color);
		}
	});
});

layui.use(['dropdown', 'util', 'layer', 'table'], function(){
	var dropdown = layui.dropdown
	,util = layui.util
	,layer = layui.layer
	,table = layui.table
	,$ = layui.jquery;
	var form = layui.form;
	// 选择字体
	form.on('select(font_category)', function(data){
		var elem = data.elem; // 获得 select 原始 DOM 对象
		var value = data.value; // 获得被选中的值
		var othis = data.othis; // 获得 select 元素被替换后的 jQuery 对象
		// layer.msg(this.innerHTML + ' 的 value: '+ value); // this 为当前选中 <option> 元素对象
		$.ajax({
			url: "/index/font/get_font_list",
			type: "post",
			dataType: "json",
			cache: false,
			data: {
				category_id: value
			},
			success: function (data) {
				let html = '';
				data.forEach(item => {
					html += `<option value="${item.file_path}">${item.name}</option>`;
				});
				$("select[name=font_type]").html(html);
				form.render();
			}
		});
	});

	layui.use(['layer'], function(){
		// 生成字体
		$('#create_font').on('click', function(){
			// 获取字体文件
			var file_path = $("select[name=font_type]").val();
			if(file_path==null || file_path == ""){
				var id =  $("select[name=font_category]").val();
				// 使用默认字体
				if(GV.font_categroy_list!=null){
					GV.font_categroy_list.forEach((item,index)=>{
						if(item.id == id){
							file_path = item.file_path;
						}
					});
				}
				// 没有字体
				if(file_path==null || file_path == ""){
					layer.msg('请选择字体！');
					return;
				}
			}
			var url = GV.file_url+file_path;
			// 获取字体颜色
			var font_color = $("input[name=font_color]").val();
			if(font_color==null || font_color==""){
				font_color = "red";
			}
			// 获取字体大小
			var font_size = $("select[name=font_size]").val();
			if(font_size==null || font_size == ""){
				font_size = 128;
			}
			// 弹出一个无限循环的 loading 层
			var index = layer.load(1, {
				shade: 0 // 0.1 透明度的白色背景
			});

			$.ajax({
				url: "/index/user/getstatus",
				type: "post",
				dataType: "json",
				cache: false,
				success: function (res) {
					if(res){
						create_font(url,GV.font_content,font_color,font_size,index);
					}else{
						layer.msg('已试用完，请注册会员账号！');
						if(index !=null){
							layer.close(index);
						}
					}
				}
			});
			
		});
	})

	// 重置字体
	$("#reset_font").on('click', function(){
		$("select[name=font_back]").val('');
		$("select[name=font_size]").val('');
		$("select[name=text_margin]").val('');
		$("select[name=v_margin]").val('');
		$("select[name=font_margin]").val('');
		$("select[name=font_color]").val('');
		$("select[name=font_number]").val('');
		$("select[name=font_show]").val('');
		$("input[name=back_color]").val('');
		$("input[name=font_color]").val('');
		$("select[name=font_size]").val('128');
		create_font(GV.font_url,GV.font_content,"red",128,null);
	});
	
	// 选择背景图片
	form.on('select(font_back)', function(data){
		if(data.value!=""){
			var font_size = $("select[name=font_size]").val();
			if(font_size!==""){
				var backszie = parseInt(font_size);
				$(".grid").css({
				'width': font_size+"px",
				'height': font_size+"px",
				'background-size': backszie+"px"+" "+font_size+"px",
				'background-image':"url('/public/static/images/bg/"+font_size+"_1_"+data.value+".png')",
				"display":"block",
				"overflow":"hidden"
			});
		}
		}else{
			$(".grid").css({
				'width': font_size+"px",
				'height': font_size+"px",
				'background-size': backszie+"px"+" "+font_size+"px",
				"display":"block",
				"overflow":"hidden"
			});
		}
		form.render();
	});

	form.on('select(text_margin)', function(data){
		var elem = data.elem;
		var value = data.value;
		var othis = data.othis;
		$("#font-content").css({
			'padding-left': value+"px",
			'padding-right': value+"px"
		});
	});

	form.on('select(v_margin)', function(data){
		var elem = data.elem;
		var value = data.value; 
		var othis = data.othis;
		$("#font-content").css({
			'padding-top': value+"px",
			'padding-bottom': value+"px"
		});
	});

	form.on('select(font_margin)', function(data){
		var elem = data.elem;
		var value = data.value;
		var othis = data.othis;
		var font_margin = parseInt(value)/2;
		$("#shufa-img dl").css({
			'padding-left': font_margin+"px",
			'padding-right': font_margin+"px"
		});
	});

	form.on('select(row_margin)', function(data){
		var elem = data.elem; 
		var value = data.value; 
		var othis = data.othis;
		var row_margin = parseInt(value)/2;
		$("#shufa-img dl").css({
			'padding-top': row_margin+"px",
			'padding-bottom': row_margin+"px"
		});
	});

	var $input = $('#font_content');
	var $popup = $('#textareaPopup');
	var $textarea = $('#detailTextarea');
	// 聚焦时显示 textarea
	$input.on('focus', function() {
		$popup.show();  // 显示弹出层
		$textarea.focus(); // 自动聚焦到 textarea
	});

	// 监听 textarea 失去焦点
	$textarea.on('blur', function(e) {
		// 延迟隐藏，确保点击其他地方能正常响应
		setTimeout(function() {
			$('#font_content').val($('#detailTextarea').val());
			GV.font_content = $('#detailTextarea').val();
			$popup.hide();
		}, 200);
	});

	// 可选：input 本身失焦时不立即隐藏，留给 textarea 处理
	$input.on('blur', function(e) {
		// 不做隐藏操作，由 textarea 控制
		$('#detailTextarea').val($('#font_content').val());
	});
	
});

// 获取字体设置参数
function get_font_setting(){
	var font_setting  = {};
	font_setting.font_back = $("select[name=font_back]").val();
	font_setting.font_size =$("select[name=font_size]").val();
	font_setting.text_margin = $("select[name=text_margin]").val();
	font_setting.v_margin = $("select[name=v_margin]").val();
	font_setting.font_margin = $("select[name=font_margin]").val();
	font_setting.font_color = $("select[name=font_color]").val();
	font_setting.font_number = $("select[name=font_number]").val();
	font_setting.font_show = $("select[name=font_show]").val();
	font_setting.back_color = $("input[name=back_color]").val();
	font_setting.font_color = $("input[name=font_color]").val();
	font_setting.font_size = $("select[name=font_size]").val();
	return font_setting;
}