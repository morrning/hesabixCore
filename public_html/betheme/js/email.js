var $j = jQuery.noConflict();

var http = createRequestObject();
var areal = Math.random() + '';
var real = areal.substring(2, 6);

function createRequestObject() {
	var xmlhttp;
	try {
		var xmlhttp = null;
		if (window.XMLHttpRequest) {
			xmlhttp = new XMLHttpRequest();
		} else {
			if (window.ActiveXObject) {
				xmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
			}
		}

		// xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
		try {
			var xmlhttp = null;
			if (window.XMLHttpRequest) {
				xmlhttp = new XMLHttpRequest();
			} else {
				if (window.ActiveXObject) {
					xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
				}
			}
			//xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		} catch (f) {
			xmlhttp = null;
		}
	}
	if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
		xmlhttp = new XMLHttpRequest();
	}
	return xmlhttp;
}

function sendRequest() {
	var rnd = Math.random();
	var name = escape(document.getElementById('name').value);
	var email = escape(document.getElementById('email').value);
	var subject = escape(document.getElementById('subject').value);
	var body = document.getElementById('body').value;

	try {
		http.open('POST', 'php/contactform.php');
		http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		http.onreadystatechange = handleResponse;
		http.send(
			'name=' +
				name +
				'&email=' +
				email +
				'&subject=' +
				subject +
				'&body=' +
				body +
				'&rnd=' +
				rnd
		);
	} catch (e) {
	} finally {
		jQuery('#contactform').slideUp('slow').hide();
		jQuery('#contactWrapper').append(
			'<div class="success"><h4>ایمیل با موفقیت ارسال شد!</h4><br><p>با تشکر از شما برای استفاده از فرم تماس ما <strong>' +
				decodeURIComponent(name) +
				'</strong>! ایمیل شما با موفقیت ارسال شد و به زودی با شما تماس خواهیم گرفت.</p></div>'
		);
	}
}
function sendRequest_booking() {
	var rnd = Math.random();
	var name = escape(document.getElementById('name').value);
	var email = escape(document.getElementById('email').value);

	var surname = escape(document.getElementById('surname').value);
	var arrival = escape(document.getElementById('arrival').value);
	var room = escape(document.getElementById('room').value);
	var departure = escape(document.getElementById('departure').value);
	var children = escape(document.getElementById('children').value);
	var adults = escape(document.getElementById('adults').value);
	var rooms = escape(document.getElementById('rooms').value);

	var body = document.getElementById('body').value;

	try {
		http.open('POST', 'php/bookingform.php');
		http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		http.onreadystatechange = handleResponse;
		http.send(
			'name=' +
				name +
				'&surname=' +
				surname +
				'&email=' +
				email +
				'&arrival=' +
				arrival +
				'&room=' +
				room +
				'&departure=' +
				departure +
				'&children=' +
				children +
				'&adults=' +
				adults +
				'&rooms=' +
				rooms +
				'&body=' +
				body +
				'&rnd=' +
				rnd
		);
	} catch (e) {
	} finally {
		jQuery('#contactform_booking').slideUp('slow').hide();
		jQuery('#contactWrapper').append(
			'<div class="success"><h4>ایمیل با موفقیت ارسال شد!</h4><br><p>با تشکر از شما برای استفاده از فرم تماس ما <strong>' +
				decodeURIComponent(name) +
				'</strong>! ایمیل شما با موفقیت ارسال شد و به زودی با شما تماس خواهیم گرفت.</p></div>'
		);
	}
}

function sendRequest_loans() {
	var rnd = Math.random();
	var name = escape(document.getElementById('name').value);
	var name_s = escape(document.getElementById('name_s').value);
	var subject = escape(document.getElementById('subject').value);

	try {
		http.open('POST', 'php/contactform_loans.php');
		http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		http.onreadystatechange = handleResponse_popup;
		http.send(
			'name=' +
				name +
				'&name_s=' +
				name_s +
				'&subject=' +
				subject +
				'&rnd=' +
				rnd
		);
	} catch (e) {
	} finally {
		jQuery('#contactform_popup').slideUp('slow').hide();
		jQuery('#contactWrapper_popup').append(
			'<div class="success"><h4>ایمیل با موفقیت ارسال شد!</h4><br><p>با تشکر از شما برای استفاده از فرم تماس ما <strong>' +
				name +
				'</strong>! ایمیل شما با موفقیت ارسال شد و به زودی با شما تماس خواهیم گرفت.</p></div>'
		);
	}
}

function sendRequest_popup() {
	var rnd = Math.random();
	var name = escape(document.getElementById('name_popup').value);
	var email = escape(document.getElementById('email_popup').value);
	var subject = escape(document.getElementById('subject_popup').value);
	var body = escape(document.getElementById('body_popup').value);

	try {
		http.open('POST', 'php/contactform.php');
		http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		http.onreadystatechange = handleResponse_popup;
		http.send(
			'name=' +
				name +
				'&email=' +
				email +
				'&subject=' +
				subject +
				'&body=' +
				body +
				'&rnd=' +
				rnd
		);
	} catch (e) {
	} finally {
		jQuery('#contactform_popup').slideUp('slow').hide();
		jQuery('#contactWrapper_popup').append(
			'<div class="success"><h4>ایمیل با موفقیت ارسال شد!</h4><br><p>با تشکر از شما برای استفاده از فرم تماس ما <strong>' +
				name +
				'</strong>! ایمیل شما با موفقیت ارسال شد و به زودی با شما تماس خواهیم گرفت.</p></div>'
		);
	}
}

function sendRequest_news() {
	var rnd = Math.random();
	if (document.getElementById('name_news') instanceof Object) {
		var name = escape(document.getElementById('name_news').value);
	} else {
		var name = 'noname';
	}
	var email = escape(document.getElementById('email_news').value);

	try {
		http.open('POST', 'php/newsletter.php');
		http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		http.onreadystatechange = handleResponse_news;

		http.send(
			'name_news=' + name + '&email_news=' + email + '&rnd_news=' + rnd
		);
	} catch (e) {
	} finally {
		jQuery('#newsletterform').slideUp('slow').hide();
		jQuery('#newsletterform')
			.parent()
			.append(
				'<div class="success"><h4>اشتراک با موفقیت ارسال شد!</h4><br><p>Your ایمیل: ' +
					email +
					' ثبت شده است.</p></div>'
			);
	}
}
function sendRequest_news_simple() {
	var rnd = Math.random();

	var email = escape(document.getElementById('email_news').value);

	try {
		http.open('POST', 'php/newsletter.php');
		http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		http.onreadystatechange = handleResponse_news;

		http.send('email_news=' + email + '&rnd_news=' + rnd);
	} catch (e) {
	} finally {
		jQuery('#newsletterform').slideUp('slow').hide();
		jQuery('#newsletterform')
			.parent()
			.append(
				'<div class="success"><h4>اشتراک با موفقیت ارسال شد!</h4><br><p>Your ایمیل: ' +
					email +
					' ثبت شده است.</p></div>'
			);
	}
}

function validate_email(address) {
	var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	if (reg.test(address) == false) {
		return false;
	} else return true;
}

function validate_phone(phone) {
	var reg = /^[\:\-\.\_\(\) 0-9]+$/;
	if (reg.test(phone) == false) {
		return false;
	} else return true;
}

function check_values() {
	//Form
	var valid = '';

	var $j = jQuery.noConflict();

	var name = '';
	var email = '';
	var subject = '';
	var body = '';

	if (typeof $j('#contactform #name').val() != 'undefined') {
		name = document.getElementById('name').value;
	}
	if (typeof $j('#contactform #email').val() != 'undefined') {
		email = document.getElementById('email').value;
	}
	if (typeof $j('#contactform #subject').val() != 'undefined') {
		subject = document.getElementById('subject').value;
	}
	if (typeof $j('#contactform #body').val() != 'undefined') {
		body = document.getElementById('body').value;
	}

	var errors = 0;
	if ($j('#contactform #name').val() != undefined)
		if ($j('#contactform #name').val() == '') {
			var hasClass = $j('#contactform #name')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#contactform #name')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا نام خود را وارد کنید</label>'
					);
			$j('#contactform #name').focus();
			//return false;
			errors++;
		} else $j('#contactform #name').parent().find('.error').remove();

	if ($j('#contactform #email').val() != undefined)
		if (validate_email($j('#contactform #email').val()) == false) {
			var hasClass = $j('#contactform #email')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#contactform #email')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا یک نشانی ایمیل معتبر وارد کنید</label>'
					);
			$j('#contactform #email').focus();
			//return false;
			errors++;
		} else $j('#contactform #email').parent().find('.error').remove();

	if ($j('#contactform #subject').val() != undefined)
		if ($j('#contactform #subject').val() == '') {
			var hasClass = $j('#contactform #subject')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#contactform #subject')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا یک موضوع وارد کنید</label>'
					);
			$j('#contactform #subject').focus();
			//return false;
			errors++;
		} else $j('#contactform #subject').parent().find('.error').remove();

	if ($j('#contactform #body').val() != undefined)
		if ($j('#contactform #body').val() == '') {
			var hasClass = $j('#contactform #body')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#contactform #body')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا یک پیام وارد کنید</label>'
					);
			$j('#contactform #body').focus();
			//return false;
			errors++;
		} else $j('#contactform #body').parent().find('.error').remove();

	if (errors == 0) {
		document.getElementById('submit').disabled = true;
		document.getElementById('submit').value = 'لطفا صبر کنید...';
		sendRequest();
	}
}
function check_values_loans() {
	//Form
	var valid = '';

	var $j = jQuery.noConflict();

	var name = '';
	var subject = '';
	var name_s = '';

	if (typeof $j('#contactform #name').val() != 'undefined') {
		name = document.getElementById('name').value;
	}
	if (typeof $j('#contactform #subject').val() != 'undefined') {
		subject = document.getElementById('subject').value;
	}
	if (typeof $j('#contactform #name_s').val() != 'undefined') {
		name_s = document.getElementById('name_s').value;
	}

	var errors = 0;
	if ($j('#contactform #name').val() != undefined)
		if ($j('#contactform #name').val() == '') {
			var hasClass = $j('#contactform #name')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#contactform #name')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا نام خود را وارد کنید</label>'
					);
			$j('#contactform #name').focus();
			//return false;
			errors++;
		} else $j('#contactform #name').parent().find('.error').remove();

	if ($j('#contactform #subject').val() != undefined)
		if ($j('#contactform #subject').val() == '') {
			var hasClass = $j('#contactform #subject')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#contactform #subject')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا یک موضوع وارد کنید</label>'
					);
			$j('#contactform #subject').focus();
			//return false;
			errors++;
		} else $j('#contactform #subject').parent().find('.error').remove();

	if ($j('#contactform #name_s').val() != undefined)
		if ($j('#contactform #name_s').val() == '') {
			var hasClass = $j('#contactform #name_s')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#contactform #name_s')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا نام خانوادگی خود را وارد کنید</label>'
					);
			$j('#contactform #name_s').focus();
			//return false;
			errors++;
		} else $j('#contactform #name_s').parent().find('.error').remove();

	if (errors == 0) {
		document.getElementById('submit').disabled = true;
		document.getElementById('submit').value = 'لطفا صبر کنید...';
		sendRequest_loans();
	}
}

function check_values_booking() {
	//Form
	var valid = '';

	var $j = jQuery.noConflict();

	var name = '';
	var email = '';
	var body = '';

	var surname = '';
	var arrival = '';
	var departure = '';
	var room = '';
	var adults = '';
	var children = '';
	var rooms = '';

	if (typeof $j('#contactform_booking #name').val() != 'undefined') {
		name = document.getElementById('name').value;
	}
	if (typeof $j('#contactform_booking #email').val() != 'undefined') {
		email = document.getElementById('email').value;
	}
	if (typeof $j('#contactform_booking #body').val() != 'undefined') {
		body = document.getElementById('body').value;
	}

	if (typeof $j('#contactform_booking #surname').val() != 'undefined') {
		surname = document.getElementById('surname').value;
	}
	if (typeof $j('#contactform_booking #arrival').val() != 'undefined') {
		arrival = document.getElementById('arrival').value;
	}
	if (typeof $j('#contactform_booking #departure').val() != 'undefined') {
		departure = document.getElementById('departure').value;
	}
	if (typeof $j('#contactform_booking #room').val() != 'undefined') {
		room = document.getElementById('room').value;
	}
	if (typeof $j('#contactform_booking #adults').val() != 'undefined') {
		adults = document.getElementById('adults').value;
	}
	if (typeof $j('#contactform_booking #children').val() != 'undefined') {
		children = document.getElementById('children').value;
	}
	if (typeof $j('#contactform_booking #rooms').val() != 'undefined') {
		rooms = document.getElementById('rooms').value;
	}

	var errors = 0;
	if ($j('#contactform_booking #name').val() != undefined)
		if ($j('#contactform_booking #name').val() == '') {
			var hasClass = $j('#contactform_booking #name')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#contactform_booking #name')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا نام خود را وارد کنید</label>'
					);
			$j('#contactform_booking #name').focus();
			//return false;
			errors++;
		} else $j('#contactform_booking #name').parent().find('.error').remove();

	if ($j('#contactform_booking #email').val() != undefined)
		if (validate_email($j('#contactform_booking #email').val()) == false) {
			var hasClass = $j('#contactform_booking #email')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#contactform_booking #email')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا یک نشانی ایمیل معتبر وارد کنید</label>'
					);
			$j('#contactform_booking #email').focus();
			//return false;
			errors++;
		} else $j('#contactform_booking #email').parent().find('.error').remove();

	if ($j('#contactform_booking #surname').val() == '') {
		var hasClass = $j('#contactform_booking #surname')
			.parent()
			.find('.error')
			.hasClass('error');
		if (!hasClass)
			$j('#contactform_booking #surname')
				.parent()
				.append(
					'<label for="contactname" generated="true" class="error">لطفا نام خانوادگی خود را وارد کنید</label>'
				);
		$j('#contactform_booking #surname').focus();
		//return false;
		errors++;
	} else $j('#contactform_booking #surname').parent().find('.error').remove();
	if ($j('#contactform_booking #arrival').val() == '') {
		var hasClass = $j('#contactform_booking #arrival')
			.parent()
			.find('.error')
			.hasClass('error');
		if (!hasClass)
			$j('#contactform_booking #arrival')
				.parent()
				.append(
					'<label for="contactname" generated="true" class="error">لطفا تاریخ ورود را وارد کنید</label>'
				);
		$j('#contactform_booking #arrival').focus();
		//return false;
		errors++;
	} else $j('#contactform_booking #arrival').parent().find('.error').remove();
	if ($j('#contactform_booking #departure').val() == '') {
		var hasClass = $j('#contactform_booking #departure')
			.parent()
			.find('.error')
			.hasClass('error');
		if (!hasClass)
			$j('#contactform_booking #departure')
				.parent()
				.append(
					'<label for="contactname" generated="true" class="error">لطفا تاریخ حرکت را وارد کنید</label>'
				);
		$j('#contactform_booking #departure').focus();
		//return false;
		errors++;
	} else $j('#contactform_booking #departure').parent().find('.error').remove();

	if ($j('#contactform_booking #room').val() == '') {
		var hasClass = $j('#contactform_booking #room')
			.parent()
			.find('.error')
			.hasClass('error');
		if (!hasClass)
			$j('#contactform_booking #room')
				.parent()
				.append(
					'<label for="contactname" generated="true" class="error">لطفا تعداد اتاق خود را وارد کنید</label>'
				);
		$j('#contactform_booking #room').focus();
		//return false;
		errors++;
	} else $j('#contactform_booking #room').parent().find('.error').remove();
	if ($j('#contactform_booking #adults').val() == '') {
		var hasClass = $j('#contactform_booking #adults')
			.parent()
			.find('.error')
			.hasClass('error');
		if (!hasClass)
			$j('#contactform_booking #adults')
				.parent()
				.append(
					'<label for="contactname" generated="true" class="error">لطفا تعداد بزرگسالان را وارد کنید</label>'
				);
		$j('#contactform_booking #adults').focus();
		//return false;
		errors++;
	} else $j('#contactform_booking #adults').parent().find('.error').remove();
	if ($j('#contactform_booking #children').val() == '') {
		var hasClass = $j('#contactform_booking #children')
			.parent()
			.find('.error')
			.hasClass('error');
		if (!hasClass)
			$j('#contactform_booking #children')
				.parent()
				.append(
					'<label for="contactname" generated="true" class="error">لطفا تعداد کودکان را وارد کنید</label>'
				);
		$j('#contactform_booking #children').focus();
		//return false;
		errors++;
	} else $j('#contactform_booking #children').parent().find('.error').remove();
	if ($j('#contactform_booking #rooms').val() == '') {
		var hasClass = $j('#contactform_booking #rooms')
			.parent()
			.find('.error')
			.hasClass('error');
		if (!hasClass)
			$j('#contactform_booking #rooms')
				.parent()
				.append(
					'<label for="contactname" generated="true" class="error">لطفا تعداد اتاق را وارد کنید</label>'
				);
		$j('#contactform_booking #rooms').focus();
		//return false;
		errors++;
	} else $j('#contactform_booking #rooms').parent().find('.error').remove();

	if ($j('#contactform_booking #body').val() != undefined)
		if ($j('#contactform_booking #body').val() == '') {
			var hasClass = $j('#contactform_booking #body')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#contactform_booking #body')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا یک پیام وارد کنید</label>'
					);
			$j('#contactform_booking #body').focus();
			//return false;
			errors++;
		} else $j('#contactform_booking #body').parent().find('.error').remove();

	if (errors == 0) {
		document.getElementById('submit').disabled = true;
		document.getElementById('submit').value = 'لطفا صبر کنید...';
		sendRequest_booking();
	}
}

function check_values_popup() {
	//Form
	var valid = '';

	var $j = jQuery.noConflict();
	var name = document.getElementById('name_popup').value;
	var email = document.getElementById('email_popup').value;
	var subject = document.getElementById('subject_popup').value;
	var body = document.getElementById('body_popup').value;

	var errors = 0;
	if ($j('#contactform_popup #name_popup').val() != undefined)
		if ($j('#contactform_popup #name_popup').val() == '') {
			var hasClass = $j('#contactform_popup #name_popup')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#contactform_popup #name_popup')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا نام خود را وارد کنید</label>'
					);
			$j('#contactform_popup #name_popup').focus();
			//return false;
			errors++;
		} else
			$j('#contactform_popup #name_popup').parent().find('.error').remove();

	if ($j('#contactform_popup #email_popup').val() != undefined)
		if (validate_email($j('#contactform_popup #email_popup').val()) == false) {
			var hasClass = $j('#contactform_popup #email_popup')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#contactform_popup #email_popup')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا یک نشانی ایمیل معتبر وارد کنید</label>'
					);
			$j('#contactform_popup #email_popup').focus();
			//return false;
			errors++;
		} else
			$j('#contactform_popup #email_popup').parent().find('.error').remove();

	if ($j('#contactform_popup #subject').val() != undefined)
		if ($j('#contactform_popup #subject_popup').val() == '') {
			var hasClass = $j('#contactform_popup #subject_popup')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#contactform_popup #subject_popup')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا یک موضوع وارد کنید</label>'
					);
			$j('#contactform_popup #subject_popup').focus();
			//return false;
			errors++;
		} else
			$j('#contactform_popup #subject_popup').parent().find('.error').remove();

	if ($j('#contactform_popup #body_popup').val() != undefined)
		if ($j('#contactform_popup #body_popup').val() == '') {
			var hasClass = $j('#contactform_popup #body_popup')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#contactform_popup #body_popup')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا یک پیام وارد کنید</label>'
					);
			$j('#contactform_popup #body_popup').focus();
			//return false;
			errors++;
		} else
			$j('#contactform_popup #body_popup').parent().find('.error').remove();

	if (errors == 0) {
		document.getElementById('submit_popup').disabled = true;
		document.getElementById('submit_popup').value = 'لطفا صبر کنید...';
		sendRequest_popup();
	}
}

function check_values_news() {
	//Form
	var valid = '';
	var $j = jQuery.noConflict();

	var name = '';
	var email = '';

	if (typeof $j('#newsletterform #name_news').val() != 'undefined') {
		name = document.getElementById('name_news').value;
	}
	if (typeof $j('#newsletterform #email_news').val() != 'undefined') {
		email = document.getElementById('email_news').value;
	}

	var errors = 0;

	if ($j('#newsletterform #name_news').val() != undefined)
		if ($j('#newsletterform #name_news').val() == '') {
			var hasClass = $j('#newsletterform #name_news')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#newsletterform #name_news')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا نام خود را وارد کنید</label>'
					);
			$j('#newsletterform #name_news').focus();
			//return false;
			errors++;
		} else $j('#newsletterform #name_news').parent().find('.error').remove();

	if ($j('#newsletterform #email_news').val() != undefined)
		if (validate_email($j('#newsletterform #email_news').val()) == false) {
			var hasClass = $j('#newsletterform #email_news')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#newsletterform #email_news')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا یک نشانی ایمیل معتبر وارد کنید</label>'
					);
			$j('#newsletterform #email_news').focus();
			//return false;
			errors++;
		} else $j('#newsletterform #email_news').parent().find('.error').remove();

	if (errors == 0) {
		document.getElementById('submit').disabled = true;
		document.getElementById('submit').value = 'لطفا صبر کنید...';
		sendRequest_news();
	}
}

function check_values_news_simple() {
	console.log('dsa');
	//Form
	var valid = '';
	var $j = jQuery.noConflict();

	var email = '';

	if (typeof $j('#newsletterform #email_news').val() != 'undefined') {
		email = document.getElementById('email_news').value;
	}

	var errors = 0;

	if ($j('#newsletterform #email_news').val() != undefined)
		if (validate_email($j('#newsletterform #email_news').val()) == false) {
			var hasClass = $j('#newsletterform #email_news')
				.parent()
				.find('.error')
				.hasClass('error');
			if (!hasClass)
				$j('#newsletterform #email_news')
					.parent()
					.append(
						'<label for="contactname" generated="true" class="error">لطفا یک نشانی ایمیل معتبر وارد کنید</label>'
					);
			$j('#newsletterform #email_news').focus();
			return false;
			errors++;
		} else $j('#newsletterform #email_news').parent().find('.error').remove();

	if (errors == 0) {
		document.getElementById('submit').disabled = true;
		document.getElementById('submit').value = 'لطفا صبر کنید...';
		sendRequest_news_simple();
	}
}
function handleResponse() {
	try {
		if (http.readyState == 4 && http.status == 200) {
			var response = http.responseText;
			document.getElementById('confirmation').innerHTML = response;
			document.getElementById('confirmation').style.display = '';
		}
	} catch (e) {
	} finally {
	}
}
function handleResponse_popup() {
	try {
		if (http.readyState == 4 && http.status == 200) {
			var response = http.responseText;
			document.getElementById('confirmation_popup').innerHTML = response;
			document.getElementById('confirmation_popup').style.display = '';
		}
	} catch (e) {
	} finally {
	}
}

function handleResponse_news() {
	try {
		if (http.readyState == 4 && http.status == 200) {
			var response = http.responseText;
			document.getElementById('confirmation').innerHTML = response;
			document.getElementById('confirmation').style.display = '';
		}
	} catch (e) {
	} finally {
	}
}

function isUndefined(a) {
	return typeof a == 'undefined';
}

function trim(a) {
	return a.replace(/^s*(S*(s+S+)*)s*$/, '$1');
}

function isEmail(a) {
	return a.indexOf('.') > 0 && a.indexOf('@') > 0;
}