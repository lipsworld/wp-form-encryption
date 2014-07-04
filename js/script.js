jQuery(function() {
	jQuery.jCryption.defaultOptions = {
			getKeysURL:"?generate=jcryption&esrnd=" + encryptSubmissionsRandomString(),
			submitElement:false,
			submitEvent:"click",
			beforeEncryption:function(){return true},
			postVariable:"jCryption",
			formFieldSelector:":input"
		};
	if(typeof forms != 'undefined')
	{
		for(var i in forms) {
			if(typeof jQuery("#" + forms[i]).attr('id') != 'undefined') {
				jQuery("#" + forms[i]).jCryption();
			}
		}
	}
});

function encryptSubmissionsRandomString() {
	var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
	var length = 20;
	var randomstring = '';
	for (var i = 0; i < length; i++) {
		var rnum = Math.floor(Math.random() * chars.length);
		randomstring += chars.substring(rnum,rnum+1);
	}
	return randomstring;
}