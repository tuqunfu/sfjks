layui.use(['dropdown'], function(){
	var dropdown = layui.dropdown;
	//保存字体
	dropdown.render({
		elem: '.saveFont'
		,data: [
            {title: '单字打包(无损JPEG)',id: 1},
            {title: '单字打包(无损PNG)',id: 2},
            {title: '单字打包(透明PNG)',id: 3},
            {title: '单字打包(无损SVG)',id: 4},
            {title: '单字打包(透明SVG)',id: 5},
            {title: '整体(矢量PDF)',id: 6}
        ]
		,click: function(obj)
		{
			switch(obj.id){
				case 1:
					downloadSvgAsImage('image/jpeg',0,'无损JPEG');
					break;
				case 2:
                    downloadSvgAsImage('image/png',0,'无损PNG');
					break;
                case 3:
                    downloadSvgAsImage('image/png',1,'透明PNG');
					break;
                case 4:
                    downloadSVGAsZip(0,'无损SVG');
                    break;
                case 5:
                    downloadSVGAsZip(1,'透明SVG');
                break;
				case 6:
					generatePDF();
					break;
			}
		}
	});
});

function decodeBase64Svg(base64Img) {
    // 移除 data URL 前缀
    const base64Data = base64Img.split(';base64,').pop();
    // 解码 Base64 数据
    const decodedString = atob(base64Data);
    // 返回解码后的 SVG 字符串
    return decodeURIComponent(escape(decodedString));
}

function svgStr2Base64(svgElement){
    // 序列化 SVG
    var svgString = new XMLSerializer().serializeToString(svgElement);
    // Base64 编码
    const base64 = btoa(unescape(encodeURIComponent(svgString)));
    const imgSrc = 'data:image/svg+xml;base64,' + base64;
    return imgSrc;
}

function parseSVG(svgStr) {
  try {
    const parser = new DOMParser();
    const doc = parser.parseFromString(svgStr, 'image/svg+xml');
    // 检查是否有解析错误
    const parserError = doc.querySelector('parsererror');
    if (parserError) {
      console.error('SVG 解析错误:', parserError.textContent);
      return null;
    }
    return doc.documentElement;
  } catch (e) {
    console.error('转换失败:', e);
    return null;
  }
}

// 生成PDF
function generatePDF() {
	const element = document.getElementById('shufa-img');
	// 设置背景颜色
	var back_color = $("input[name=back_color]").val();
    if(back_color == ""){
        back_color = '#ffffff';
    }
    var name = '字体_'+Date.now()+'.pdf';
	var opt = {
		margin:       1,
		filename:     name,
		image:        { type: 'jpeg', quality: 0.98 },
		html2canvas:  { scale: 2, backgroundColor: back_color },
		jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' ,format: 'a3'}
	};
	html2pdf().set(opt).from(element).save();
}

// 生成图片
/**
 * 
 * @param {*} image_type 图片类型 png jpeg
 * @param {*} type  是否透明 1是 0否
 * @param {*} file_name  文件夹名称
 */
function downloadSvgAsImage(image_type,type,file_name) {
  	var imgSrcList = [];
    var font_setting = get_font_setting();
	// 获取全部图片
	const imgs = $('#shufa-font').find('img');
	for(var i=0;i<imgs.length;i++){
		var src = imgs[i].currentSrc;
        var id = imgs[i].id;
        var imgSrc = setSvgBack2Base64(src,font_setting,type);
		// 可加条件过滤，比如非空
		if (imgSrc) {
            if(id=='' || id == null){
                id = 'image'+'_'+Date.now()
            }
			var item = [imgSrc,image_type,id]
			imgSrcList.push(item);
		}
	}
    console.log(JSON.stringify(imgSrcList));
	if(imgSrcList.length>0){
		downloadImagesAsZip(imgSrcList,file_name);
	}
}

async  function downloadSVGAsZip(type,file_name){
    var font_setting = get_font_setting();
	// 获取全部图片
	const imgs = $('#shufa-font').find('img');
    const serializer = new XMLSerializer();
    const zip = new JSZip();
	for(var i=0;i<imgs.length;i++){
		var src = imgs[i].currentSrc;
        var id = imgs[i].id;
        var svgElement = parseSVG(decodeBase64Svg(src));
        if(type == 0){
            // 创建一个矩形作为背景
            if(font_setting.back_color!==''){
                const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                // 获取 viewBox 或宽高
                rect.setAttribute('width', font_setting.font_size);
                rect.setAttribute('height',font_setting.font_size);
                rect.setAttribute('fill', font_setting.back_color);
                rect.setAttribute('fill-opacity', 0.3);
                // 插入到最底层（确保背景在所有元素之下）
                svgElement.insertBefore(rect, svgElement.firstChild);
            }

            if(font_setting.font_back!==''){
                const image = document.createElementNS('http://www.w3.org/2000/svg', 'image');
                image.setAttribute('x', '0');
                image.setAttribute('y', '0');
                image.setAttribute('width', font_setting.font_size);
                image.setAttribute('height', font_setting.font_size);
                var font_back_index = font_setting.font_size+"_1_"+font_setting.font_back;
                image.setAttribute('href', GV.font_back_data[font_back_index]);
                // 兼容旧浏览器可用 xlink:href（已废弃但仍支持）
                image.setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href',GV.font_back_data[font_back_index]);
                svgElement.insertBefore(image, svgElement.firstChild);
            }
        }
		const filename = id+'.svg';
        const svgContent = '<?xml version="1.0" standalone="no"?>\r\n' +
                        '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">\r\n' +
                        serializer.serializeToString(svgElement);
        zip.file(filename, svgContent);
	}
    // 生成 blob 并下载
    const content = await zip.generateAsync({ type: 'blob' });
    saveAs(content, file_name+'.zip');
}

function setSvgBack2Base64(base64Image,font_setting,type){
    var svgElement = parseSVG(decodeBase64Svg(base64Image));
    if(type == 0){
        if(font_setting.back_color!==''){
            // 创建一个矩形作为背景
            const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            // 获取 viewBox 或宽高
            rect.setAttribute('width', font_setting.font_size);
            rect.setAttribute('height',font_setting.font_size);
            rect.setAttribute('fill', font_setting.back_color);
            rect.setAttribute('fill-opacity', 0.3);
            // 插入到最底层（确保背景在所有元素之下）
            svgElement.insertBefore(rect, svgElement.firstChild);
        }

        if(font_setting.font_back!==''){
            const image = document.createElementNS('http://www.w3.org/2000/svg', 'image');
            image.setAttribute('x', '0');
            image.setAttribute('y', '0');
            image.setAttribute('width', font_setting.font_size);
            image.setAttribute('height', font_setting.font_size);
            // 使用 href（现代浏览器）
            var font_back_index = font_setting.font_size+"_1_"+font_setting.font_back;
            image.setAttribute('href', GV.font_back_data[font_back_index]);
            // 兼容旧浏览器可用 xlink:href（已废弃但仍支持）
            image.setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href',GV.font_back_data[font_back_index]);
            svgElement.insertBefore(image, svgElement.firstChild);
        }
        
    }
    const imgSrc = svgStr2Base64(svgElement);
    return imgSrc;
}

// 转化成图片
function base64ToBlob(base64, mimeType) {
    const sliceSize = 512;
    const byteCharacters = atob(base64.split(',')[1]);
    const byteArrays = [];
    for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
        const slice = byteCharacters.slice(offset, offset + sliceSize);

        const byteNumbers = new Array(slice.length);
        for (let i = 0; i < slice.length; i++) {
            byteNumbers[i] = slice.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);
        byteArrays.push(byteArray);
    }
    return new Blob(byteArrays, { type: mimeType });
}

// 压缩包下载
function downloadImagesAsZip(images,file_name) {
    const zip = new JSZip();
    images.forEach((img, index) => {
        const [base64, type, name] = img;
        const blob = base64ToBlob(base64, type);
        zip.file(`${name}.${type === 'image/png' ? 'png' : 'jpg'}`, blob);
    });
    zip.generateAsync({ type: 'blob' }).then(function(content) {
        var name = file_name+'_'+Date.now()+'.zip';
        saveAs(content, name);
    });
}