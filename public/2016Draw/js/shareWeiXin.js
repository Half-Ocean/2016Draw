var getUrl = window.location.href;
getUrl = encodeURI(getUrl);

var rand = Math.round(Math.random()*1000);

$.ajax({
	type: "POST",
	dataType:'jsonp',  
	url:'http://m.idaddy.cn/ad/wxShareConfig.php?comeback=jsonp&rand='+rand,
	data:{url:getUrl},
	timeout:3000, 
	success: function (result) {
		var appId = result.appId;
		var timestamp = result.timestamp;
		var nonceStr = result.nonceStr;
		var signature = result.signature;
		
		wx.config({
			debug: true, 
			appId: appId, 
			timestamp: timestamp, 
			nonceStr: nonceStr,
			signature: signature,
			jsApiList: [        
				'onMenuShareTimeline',
				'onMenuShareAppMessage',
				'onMenuShareQQ'
				] 
		});
		

		wx.ready(function () {
						   
			wx.onMenuShareAppMessage({
				'title': shareTitle,
				'desc': descContent,
				'link': lineLink,
				'imgUrl': imgUrl,
				'success': function (res) {}
				
			});
			
			wx.onMenuShareTimeline({
				'title': shareTitle,
				'link': lineLink,
				'imgUrl': imgUrl,
				'success': function (res) {}
				
			});
		
			wx.onMenuShareQQ({
				'title': shareTitle,
				'desc': descContent,
				'link': lineLink,
				'imgUrl': imgUrl,
				'success': function (res) {}
			});
		
		});
		
		
		wx.error(function (res) {
		   
		});
		
	},
	error:function () {}
});







