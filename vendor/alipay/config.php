<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "9021000152624443",

		//商户私钥
		'merchant_private_key' => "MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC+hkBZjk5QlI8YihQnkTKHOffa6o/whk72jkvm/Qib/OPa3CAAzB9jD7v8cx5KIU70hKjggoiSuOXW2k2b/McUJ6bkVdt6+1Y+Usb1CcWQ71pBiITdgYlstr7nq2pu/5eMfjQTD4NfVp0HjgFvDvLrE6VGDFgEPRvqEBJnymjbeFsIadyo5+o5w2OIBYyXpBU2Szg/8heA1RsfU+6moFbgDADaxeRkglmZnh1Vr/rdOkhwe4sRtOgxpglOCXUcD2YFErGKBaTp936GWAucl/IePkBPFgZa9CNjm0Pl7FQcRoWNTs/WDeg5xKu/7kxSewTO60lYKDXnhRlPJ2LotUXhAgMBAAECggEBAKsJtqcvG5s3Yqby/lju/l9raNi8jm+tAyJQaE97hMkUZDFMP+a3WM4DiA2AAwclk83rcffq91RQbPVOkTGh4c50Mlz1vs1O6QOWKEo+dYBBf9MpDa4U5hwUiplLx4bSKWjUu0cJPDTQerQXha3/y4/B2TNjTXiwq2ia+Qr4KN4sR2DQh68ywINtE5umjmCE5Ps5Psi92x8Y01MbRhlP5C+FW+NQbb4NoA6W2hc/QaZ5XS8oSBCw+5mF10gK8Ds6pqnf4F8pXmYBXZ3JR+DD/YyuzUvak0TUHy3nvvcpjJ32F6ZxMVN255yy/Oxhxy33yKVBSqENQTXMWOhKS4xKF2kCgYEA4WIoQI0a92FxanwySV+SLRRo8Y5YUSH6K6E1Qsk3doYZObPxqD4whG+ao/LpKH0XVGF4k8e4GJtxFbhO1As5GwM919DYDkwWh4ZVRA2FJ1HI3sAt0BHQqNL06T8atZypwoKqBR6c8QuIIaUBjJIA31VWN0RYzvoR/L8L2FKINiMCgYEA2GfZ9iDAGKpCifTMP48vRWDsZ+BSRHcDcY9N9sOq+UKlKdEv1RWfVhKVJnWy2IMOGFY+VCqBl3QoFfXG6nPrWngYbId4hu3NmEcNw3N8ucqIo8N843eRnfC8/n0dS+6H3Dub1opTpqiXH/tFW/q37CN/eL0br8VrmSluSy2S+isCgYAxiga48Yr81wE726Kd+BDEMdlDRvtokeRQFLYFQP8c4SrIQSIbzdeSNIm0AZ94E8698FK1gM+ZMQlxMbq55uGda1/7gN8MfXLyRPeW5rXex540P9+R/Wh0wzGr1wAC7TVvGJNQXsM2REeexYsKn8jrTfGOYkp27AZqHH/5A2MHKwKBgBNla4HF+bG8QO6AhHYF7WS4hTfiQT1ltWdOQtylOQPSV19iInlk0L00OS4TSo5hYLgJsth0Xt0mShl9x/Bnp2aacQX5NnJRiiXl6HPmO/2jC5AyC1WP9/tCAo6ExEV0AbVZmmQmTc0YO0NgkBzoYefGXryBpqOgfD5kGkCeuxe1AoGBAI3bOpu9cWPhcH1/yNP19q3xnFZXiKH3LKzxyqbMSVgVf4ep85i6c4WqdyK3P/f5D98OfgtJano4GM4ZNPW9SLfo1JloBNmloVKgmtRDtn9Bjotimb51P4e148q3flcVlA/I3Q67B4OIYGFrrm73MTwXwUODY0tDPo7NO9DPElNc",
		
		//异步通知地址
		'notify_url' => "https://sfjks.wzgnet.cn/index/pay/notify_url",
		
		//同步跳转
		'return_url' => "https://sfjks.wzgnet.cn/index/pay/return_url",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi-sandbox.dl.alipaydev.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAhBuZrjwNMIcbCNRs3r5pd5HKMfhsy8hNSYKt0sryyDzvbLtDSdYhvL24Gk538w4C0LjMGBjed9smp4JebYYCQSzmZ2I89RqQWODjZVTIeNKhyJ0lbX1vOw15t4z9xfP2XMxa+p4NEZUpeslUAOEblD4pNSp+ZiJpkdF3mnu0oM46qk/1B3lyMqI9AssQkmdV1RqtOAxYO8uZt1bfdVoMCvVceKj060zHh+xdGthsZaamrgAqadC8AYt9rbqxJAwMoL1rbiRaZdi61XDH4tQh9rFLjhkr52Aagzvaj5Pa5P8WBqxIGIHfP5PCO9DtLF/zxy6D12PZVq2ZGtEgO3OxFQIDAQAB",

        //日志路径
        'log_path' => "",
);