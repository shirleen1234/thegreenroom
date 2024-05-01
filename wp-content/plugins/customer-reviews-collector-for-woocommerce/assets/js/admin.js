if(typeof Trustindex_Collector_JS_loaded == 'undefined')
{
	var Trustindex_Collector_JS_loaded = {};
}

Trustindex_Collector_JS_loaded.common = true;

String.prototype.validateEmail = function() {
	return /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(this);
};

String.prototype.validateEmailDomain = function() {
	return /^@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(this);
};

jQuery.fn.tiAjaxSubmit = function(callback, custom_data) {
	let form = jQuery(this);
	let data = form.serializeArray();

	if(custom_data)
	{
		data.forEach((d) => {
			if(typeof custom_data[ d.name ] != 'undefined')
			{
				d.value = custom_data[ d.name ];
			}
		});
	}

	jQuery.ajax({
		method: form.attr('method') || "",
		url: form.attr('action') || "",
		type: form.attr('type') || "get",
		dataType: 'application/json',
		data: data
	}).always(callback);
};

window.onbeforeunload = () => jQuery('.btn-default-disabled:not(.btn-disabled)').length ? "" : null;

jQuery(document).ready(function($) {
	$('[data-toggle="tooltip"]').each(function() {
		$(this).tooltip();
	});

	// remove loading animation on navigation but stayed
	$('.nav-link.btn-loading-animation-on-click').on('click', function() {
		setTimeout(() => $(this).removeClass('btn-loading-animation'), 2000);
	});

	// Settings page
	let toggleAccordionState = function() {
		$('#ti-settings .accordion-item .accordion-button').addClass('disabled');
		let next_accordion = $('#ti-settings .accordion-item .accordion-button:not(.done):first');
		if(next_accordion.length)
		{
			next_accordion.removeClass('disabled');
		}
		else
		{
			$('#ti-settings .accordion-item .accordion-button:last-of-type').removeClass('disabled');
		}
	};

	// btn default disabled
	$('.btn-default-disabled').each(function() {
		let btn = $(this);

		btn.addClass('btn-disabled');

		setTimeout(() => {
			btn.closest('form').on('input', 'input, textarea', () => btn.removeClass('btn-disabled'));
			btn.closest('form').on('change', 'input, textarea', () => btn.removeClass('btn-disabled'));
			btn.closest('form').on('change', 'select', () => btn.removeClass('btn-disabled'));
		}, 200);
	});

	// editor on change
	if($('#wp-email-text-wrap').hasClass('tmce-active'))
	{
		tinyMCE.get('email-text').on('change', () => $('#ti-email-settings .btn-default-disabled').removeClass('btn-disabled'));
	}

	// switch checkbox
	$(document).on('change', '.form-switch input[type=checkbox]', function(event) {
		let checkbox = $(this);
		let label = checkbox.next();

		label.html(checkbox.prop('checked') ? label.data('on-text') : label.data('off-text'));
	});

	$('.form-switch input[type=checkbox]').trigger('change');

	// next button click
	$(document).on('click', '#ti-settings .btn-next', function(event) {
		event.preventDefault();

		let btn = $(this);
		btn.blur();

		let accordion = btn.closest('.accordion-item');
		let form = accordion.find('form');

		btn.addClass('ti-btn-loading');

		// save form
		form.one('submit-success', (event, success) => {
			btn.removeClass('ti-btn-loading');

			if(success)
			{
				// step done
				accordion.find('.accordion-button').addClass('done');

				// toggle next step
				if(!btn.hasClass('btn-finished') && !btn.hasClass('btn-no-next-step'))
				{
					toggleAccordionState();

					accordion.next().find('.accordion-button').trigger('click');
				}

				// redirect
				if(btn.attr('href') != '#')
				{
					btn.addClass('ti-btn-loading');
					window.location = btn.attr('href');
				}

				// set disabled back
				if(btn.hasClass('btn-default-disabled'))
				{
					btn.addClass('btn-disabled');
				}
			}
		}).submit();
	});

	// test review link
	$(document).on('click', '.btn-test-review-link', function(event) {
		event.preventDefault();

		let btn = $(this);
		btn.blur();

		let input = btn.closest('.platform-url').find('input[type=text]');

		if(!validatePlatformUrl(input))
		{
			return false;
		}

		let url = input.val().trim();
		window.open(url, '_blank');
	});

	// add platform-url
	let addPlatformUrl = function(url, percent) {
		let template = $('#platform-url-template').clone();
		let container = $('#platform-urls');

		template.attr('id', '').removeClass('d-none');

		container.append(template);

		if(url != "")
		{
			template.find('.col-input input').val(url).trigger('input');
			template.find('.col-preview').show();
		}

		if(typeof percent != 'undefined')
		{
			template.find('.col-percent select').val(percent);
		}

		if(url == "" || url.indexOf('search.google.com') != -1 || url.indexOf('googleWriteReview') != -1)
		{
			template.find('.col-input select').val('google').trigger('change', true);

			let btn = template.find('.col-input .btn');
			if(url != "")
			{
				btn.html(btn.data('change-text'));
				btn.removeClass('btn-success').addClass('btn-secondary');
			}
			else
			{
				btn.html(btn.data('connect-text'));
				btn.removeClass('btn-secondary').addClass('btn-success');
			}

		}
		else
		{
			template.find('.col-input select').val('custom').trigger('change', true);
		}

		managePlatformUrlNumbers(typeof percent == 'undefined');
	};

	// manage platform url numbers
	let managePlatformUrlNumbers = function(percent_rearrange) {
		let urls = $('#platform-urls').find('.platform-url');

		$('#platform-urls').find('hr').remove();

		if(urls.length == 1)
		{
			$('.platform-urls-info').addClass('d-none');
			$('#platform-urls').append('<hr />');
			$('#platform-urls').find('.col-remove').hide();
			return urls.find('.counter, .col-percent').hide();
		}

		$('#platform-urls').find('.col-remove').show();
		$('.platform-urls-info').removeClass('d-none');

		urls.each(function(i) {
			$(this).find('.col-percent').show();
			$(this).after('<hr />');
		});

		if(percent_rearrange)
		{
			let percent_per_url = Math.floor(100 / urls.length / 5) * 5;
			urls.find('.col-percent select').val(percent_per_url);

			let percent_left = 100 - percent_per_url * urls.length;
			for(let i = 0; i < percent_left/5; i++)
			{
				let select = urls.eq(i).find('.col-percent select');
				select.val(parseInt(select.val()) + 5);
			}
		}
	};

	// validate/submit platform-url
	let validatePlatformUrl = function(input, remove_error) {
		let form = $('#ti-platform-url');
		let url = input.val().trim();

		if(remove_error !== false)
		{
			form.find('.alert-danger').hide();
			form.find('.platform-url input[type=text]').removeClass('is-invalid');
		}

		if(url == "" || !/(?:https?):\/\/(\w+:?\w*)?(\S+)(:\d+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/.test(url))
		{
			form.find('.alert-url-invalid').fadeIn();
			input.addClass('is-invalid').focus();
			$('#ti-settings .accordion-button.collapsed[data-bs-target="#ti-platform-url"]').trigger('click');

			return false;
		}

		return true;
	};

	// show source select modal
	$(document).on('click', '.platform-url .col-input input[readonly], .platform-url .col-input .btn', function(event) {
		event.preventDefault();

		$(this).blur();

		let modal = $('#modal-source-import');

		modal
			.modal('show')
			.data('input', $(this).hasClass('btn') ? $(this).closest('.col-input').find('input[type="text"]') : $(this))
			.find('.btn-source-connect').prop('disabled', true);

		let iframe = modal.find('iframe');
		let handleIframeMessages = function(e) {
			if((e.data.source || "") != 'ti-integration-iframe')
			{
				return;
			}

			switch(e.data.action || "")
			{
				case 'select-source':
					modal.data('url', e.data.url);
					modal.find('.btn-source-connect').prop('disabled', false);

					break;

				case 'adjust-height':
					iframe.css('height', e.data.height);

					break;

				case 'go-fullscreen':
					iframe.data('old-height', iframe.height());
					iframe.css({
						width: "100%",
						height: "100%",
						position: "fixed",
						top: 0,
						left: 0,
						zIndex: 1000000
					});

					break;

				case "leave-fullscreen":
					iframe.css({
						position: "",
						top: "",
						left: "",
						zIndex: "",
						height: iframe.data('old-height')
					}).data('old-height', '');

					break;
			}
		};

		if(typeof iframe.attr('src') == 'undefined')
		{
			iframe.attr('src', iframe.attr('alt-src') + '&lang=' + $('html').attr('lang'));
			window.addEventListener('message', handleIframeMessages);
		}
		else
		{
			iframe.get(0).contentWindow.postMessage({
				source: 'ti-integration-iframe',
				action: 'reset'
			}, '*');
		}
	});

	// select click in source select modal
	$(document).on('click', '.btn-source-connect', function(event) {
		event.preventDefault();

		$(this).blur();

		let modal = $('#modal-source-import');

		let input = modal.data('input');
		input.val(modal.data('url')).trigger('input').trigger('change');

		let btn = input.closest('.platform-url').find('.col-input .btn');
		btn.html(btn.data('change-text')).removeClass('btn-success').addClass('btn-secondary');

		modal.modal('hide');
	});

	// toggle preview button for platform url input
	$(document).on('input', '.platform-url .col-input input', function(event) {
		event.preventDefault();

		let input = $(this);
		let container = input.closest('.platform-url');

		if(input.val() == "")
		{
			container.find('.col-preview .btn').prop('disabled', true);
		}
		else
		{
			container.find('.col-preview .btn').prop('disabled', false);
		}
	});

	// toggle source type for platform url input
	$(document).on('change', '.platform-url .col-input select', function(event, is_automatic) {
		event.preventDefault();

		let select = $(this);
		let container = select.closest('.col-input');
		let input = container.find('input[type="text"]');

		select.blur();

		if(select.val() == 'custom')
		{
			input.attr('placeholder', input.data('placeholder-custom')).prop('readonly', false);
			container.find('.btn').hide();

			if(is_automatic !== true)
			{
				input.val('').trigger('input').focus();
			}
		}
		else
		{
			input.attr('placeholder', input.data('placeholder')).prop('readonly', true);
			container.find('.btn')
				.show()
				.removeClass('btn-secondary').addClass('btn-success')
				.html(container.find('.btn').data('connect-text'));

			if(is_automatic !== true)
			{
				input.val('').trigger('input').trigger('click');
			}
		}
	});

	// add platform url - DOM
	$(document).on('click', '.btn-add-platform-url', function(event) {
		event.preventDefault();

		$(this).blur();
		addPlatformUrl("");
	});

	// remove platform url - DOM
	$(document).on('click', '.btn-remove-platform-url', function(event) {
		event.preventDefault();

		$(this).blur();
		$(this).closest('.platform-url').remove();

		$('#platform-urls .col-input input:first').trigger('change');

		managePlatformUrlNumbers(true);
	});

	// sync 2 platform url percent on change
	$(document).on('change', '.platform-url .col-percent select', function(event) {
		let select = $(this);
		let percents = $('#ti-platform-url .platform-url .col-percent select');

		if(percents.length == 2)
		{
			percents.not(select).val(100 - parseInt(select.val()));
		}
	});

	// submit platform url
	$(document).on('submit', '#ti-platform-url', function(event) {
		event.preventDefault();

		let form = $(this);
		let is_error = false;
		let total_percent = 0;
		let urls = form.find('.platform-url');

		form.find('.alert-danger').hide();
		form.find('input[type=text], select').removeClass('is-invalid');

		urls.each(function(i) {
			let row = $(this);
			let input = row.find('.col-input input');
			let select = row.find('.col-percent select');

			// fix array indexes
			input.attr('name', input.attr('name').replace(/\[\d*\]/, '['+ i +']'));
			select.attr('name', select.attr('name').replace(/\[\d*\]/, '['+ i +']'));

			if(!validatePlatformUrl(input, false))
			{
				is_error = true;
			}

			if(urls.length == 1)
			{
				select.val(100);
			}

			total_percent += parseInt(select.val());
		});

		if(!is_error && total_percent !== 100)
		{
			form.find('.alert-percent-invalid').fadeIn();
			form.find('.platform-url .col-percent select').addClass('is-invalid').focus();

			is_error = true;
		}

		if(is_error)
		{
			return form.trigger('submit-success', false);
		}

		form.tiAjaxSubmit(() => form.trigger('submit-success', true));
	});

	// submit trigger
	$(document).on('submit', '#ti-trigger', function(event) {
		event.preventDefault();

		let form = $(this);

		form.find('.alert-email-invalid').hide();

		let emailsElement = $('#ti-exclude-emails');
		if (emailsElement.length) {
			emailsElement.removeClass('is-invalid');

			let emails = emailsElement.val().split(',');
			let foundWrongOption = false;

			for (let i = 0; i < emails.length; i++) {
				let email = emails[ i ].trim();

				// continue on check if email is:
				//	- empty
				//	- example@domain.com
				//	- @domain.com
				if (!email || email.validateEmail() || email.validateEmailDomain()) {
					continue;
				}

				foundWrongOption = true;
				break;
			}

			if (foundWrongOption) {
				emailsElement.addClass('is-invalid');
				form.find('.alert-email-invalid').fadeIn();

				return form.trigger('submit-success', false);
			}
		}

		form.tiAjaxSubmit(() => form.trigger('submit-success', true));
	});

	// validate/submit email settings
	let getEmailContent = () => {
		let text = "";

		if($('#wp-email-text-wrap').hasClass('tmce-active'))
		{
			text = tinyMCE.get('email-text').getContent().trim();
		}

		if(!text)
		{
			text = $('#email-text').val().trim();
		}

		return text;
	};

	// validate/submit email settings
	let validateEmailSettings = function() {
		let form = $('#ti-email-settings');

		form.find('.alert-danger').hide();
		form.find('input, textarea').removeClass('is-invalid');

		// check fields
		[
			'input[name=email-sender-email]',
			'textarea[name=email-footer-text]',
			'input[name=email-sender]',
			'input[name=email-subject]'
		].forEach(selector => {
			let element = form.find(selector);
			if(element.val().trim() == "")
			{
				element.addClass('is-invalid');
			}
		});

		if(form.find('input.is-invalid, textarea.is-invalid').length || getEmailContent() == "")
		{
			// scroll page to top
			jQuery(window).scrollTop(0);

			form.find('.alert-empty').fadeIn();
			$('#ti-settings .accordion-button.collapsed[data-bs-target="#ti-email-settings"]').trigger('click');

			return false;
		}

		// check email
		if(!form.find('input[name=email-sender-email]').val().trim().validateEmail())
		{
			// scroll page to top
			jQuery(window).scrollTop(0);

			form.find('.alert-email-invalid').fadeIn();
			form.find('input[name=email-sender-email]').addClass('is-invalid').focus();

			return false;
		}

		return true;
	};

	$(document).on('submit', '#ti-email-settings', function(event) {
		event.preventDefault();

		let form = $(this);

		if(!validateEmailSettings())
		{
			return form.trigger('submit-success', false);
		}

		form.tiAjaxSubmit(() => form.trigger('submit-success', true), { 'email-text': getEmailContent() });
	});

	// sender email warning text
	let toggleSenderEmailWarning = function() {
		let input = $('#ti-email-settings input[name=email-sender-email]');
		let email = input.val().trim().toLowerCase();

		if(location.hostname.indexOf(email.split('@')[1] || "") == -1)
		{
			input.addClass('is-invalid').next().show();
		}
		else
		{
			input.removeClass('is-invalid').next().hide();
		}
	};
	$(document).on('input', '#ti-email-settings input[name=email-sender-email]', toggleSenderEmailWarning);

	// email preview
	let lastEmailData = null;
	let getEmailData = () => {
		let form = $('#ti-email-settings');
		let data = {
			'email-text': getEmailContent(),
			'email-footer-text': form.find('textarea[name=email-footer-text]').val().trim(),
			'email-sender': form.find('input[name=email-sender]').val().trim(),
			'email-sender-email': form.find('input[name=email-sender-email]').val().trim(),
			'email-subject': form.find('input[name=email-subject]').val().trim(),
			'platform-url': [],
			'logo-image': $('#ti-collector-email-logo-image-input').val()
		};

		// add platform url
		let urls = $('#platform-urls .platform-url');
		urls.each(function(i) {
			let row = $(this);
			let url = row.find('input[type=text]').val().trim();
			let percent = parseInt(row.find('select').val());

			if(urls.length)
			{
				percent = 100;
			}

			data['platform-url'].push({
				url: url,
				percent: percent
			});
		});

		return data;
	};

	let setPreviewIframe = () => {
		let iframe = $('#ti-email-preview-iframe');

		if(!iframe.length)
		{
			return false;
		}

		let iframe_inner = iframe.contents().find('html');
		let data = getEmailData();

		// cache optimatization
		if(JSON.stringify(lastEmailData) === JSON.stringify(data))
		{
			return setTimeout(() => iframe.css('height', iframe_inner.find('table.main').height()), 100);
		}

		lastEmailData = data;

		$.post(iframe.data('url'), data, (html) => {
			iframe_inner.html(html);
			setTimeout(() => iframe.css('height', iframe_inner.find('table.main').height()), 200);

			// disable links
			iframe_inner.find('a').css('pointer-events', 'none');
		});
	};

	$(document).on('click', '#ti-email-preview-tab, #ti-settings .accordion-button[data-bs-target="#ti-email-settings"]', () => setTimeout(setPreviewIframe, 200));

	// test email
	$(document).on('click', '.btn-send-test-email', function(event) {
		event.preventDefault();

		let btn = $(this);
		btn.blur();

		// hide errors
		$('.alert-test-email').hide();
		$('#ti-test-email-input').removeClass('is-invalid');

		// check platform urls
		$('#ti-platform-url input[type=text]').removeClass('is-invalid');

		let is_error = false;
		$('#platform-urls .platform-url input[type=text]').each(function() {
			if(!validatePlatformUrl($(this), false))
			{
				is_error = true;
			}
		});

		// check fields
		if(is_error || !validateEmailSettings())
		{
			return btn.removeClass('ti-btn-loading');
		}

		// get data
		let data = getEmailData();

		// add email
		data._wpnonce = btn.data('nonce');
		data.command = 'test-email';
		data.email = $('#ti-test-email-input').val().trim();

		// check email
		if(data.email == "" || !data.email.validateEmail())
		{
			$('#ti-test-email-input').addClass('is-invalid').focus();

			return $('.alert-test-email-invalid').fadeIn();
		}

		// loading animation
		btn.addClass('ti-btn-loading');

		$.ajax({
			type: 'post',
			dataType: 'application/json',
			data: data
		}).always(() => {
			// loading animation
			btn.removeClass('ti-btn-loading');

			// show alert
			$('.alert-test-email.alert-success').fadeIn();

			setTimeout(() => $('.alert-test-email.alert-success').fadeOut(), 5000);
		});
	});

	// email logo upload
	$(document).on('click', '.btn-email-logo-upload', function(event) {
		event.preventDefault();

		let btn = $(this);
		btn.blur();

		let input = btn.prev();

		input.click().off().on('change', function() {
			if(input.get(0).files)
			{
				let image = new Image();
				image.src = URL.createObjectURL(input.get(0).files[0]);

				// Resize the image
				image.onload = function () {
					let canvas = document.createElement('canvas');
					let width = image.width;
					let height = image.height;
					let max_width = 150;

					if(width > max_width)
					{
						height *= max_width / width;
						width = max_width;
					}

					canvas.width = width;
					canvas.height = height;
					canvas.getContext('2d').drawImage(image, 0, 0, width, height);

					$('#ti-collector-email-logo-image-input').val(canvas.toDataURL('image/png')).trigger('change');
					setPreviewIframe();

					// show delete button
					btn.next().removeClass('d-none');
					btn.html(btn.data('change-text'));
				};

				input.val('');
			}
		});
	});

	// email logo delete
	$(document).on('click', '.btn-email-logo-delete', function(event) {
		event.preventDefault();

		let btn = $(this);
		btn.blur();

		$('#ti-collector-email-logo-image-input').val('delete').trigger('change');
		setPreviewIframe();

		btn.addClass('d-none');
		btn.prev().html(btn.prev().data('browse-text'));
	});

	// submit negative invitation
	$(document).on('submit', '#ti-negative-invitation', function(event) {
		event.preventDefault();

		let form = $(this);
		form.tiAjaxSubmit(() => form.trigger('submit-success', true));
	});

	// feedback form language
	$(document).on('change', '.feedback-form-language', function(event) {
		event.preventDefault();

		let select = $(this);
		let lang = select.val();

		// save
		$.post('', {
			_wpnonce: select.data('nonce'),
			command: 'save-support-language',
			'support-language': lang
		});

		// switch lang of preview site's url
		let url = $('#ti-feedback-form-url').attr('href').replace(/lang=[^&]+/, 'lang=' + lang);

		if(url.indexOf('lang') == -1)
		{
			url += '&lang=' + lang;
		}

		$('#ti-feedback-form-url').attr('href', url);
	});

	// toggle campaign
	$(document).on('change', '#ti-campaign-activate-checkbox', function(event) {
		event.preventDefault();

		let input = $(this);
		input.blur();

		input.css('pointer-events', 'none');

		$.ajax({
			type: 'post',
			dataType: 'application/json',
			data: {
				_wpnonce: input.data('nonce'),
				command: 'toggle-campaign'
			}
		}).always(() => {
			input.css('pointer-events', '');
		});
	});

	if($('#ti-email-settings').length)
	{
		let urls = $('#ti-platform-url').data('platform-urls');
		if(urls.length)
		{
			urls.forEach(u => addPlatformUrl(u.url, u.percent));
		}
		else
		{
			addPlatformUrl("", 100);
		}

		toggleAccordionState();
		setPreviewIframe();
		toggleSenderEmailWarning();
	}
});


// - ../../../../_wordpress_source_code/static/js/import/btn-loading.js
// loading on click
jQuery(document).on('click', '.ti-btn-loading-on-click', function() {
	let btn = jQuery(this);

	btn.addClass('ti-btn-loading').blur();
});

// - ../../../../_wordpress_source_code/static/js/import/copy-to-clipboard.js
jQuery(document).on('click', '.btn-copy2clipboard', function(event) {
	event.preventDefault();

	let btn = jQuery(this);
	btn.blur();

	let obj = jQuery(btn.attr('href'));
	let text = obj.html() ? obj.html() : obj.val();

	// parse html
	let textArea = document.createElement('textarea');
	textArea.innerHTML = text;
	text = textArea.value;

	let feedback = () => {
		btn.removeClass('ti-toggle-tooltip').addClass('ti-show-tooltip');

		if (typeof this.timeout !== 'undefined') {
			clearTimeout(this.timeout);
		}

		this.timeout = setTimeout(() => btn.removeClass('ti-show-tooltip').addClass('ti-toggle-tooltip'), 3000);
	};

	if (!navigator.clipboard) {
		// fallback
		textArea = document.createElement('textarea');
		textArea.value = text;
		textArea.style.position = 'fixed'; // avoid scrolling to bottom
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			var successful = document.execCommand('copy');

			feedback();
		}
		catch (err) { }

		document.body.removeChild(textArea);
		return;
	}

	navigator.clipboard.writeText(text).then(feedback);
});

// - ../../../../_wordpress_source_code/static/js/import/rate-us.js
// remember on hover
jQuery(document).on('mouseenter', '.ti-quick-rating', function(event) {
	let container = jQuery(this);
	let selected = container.find('.ti-star-check.ti-active, .star-check.active');

	if (selected.length) {
		// add index to data & remove all active stars
		container.data('selected', selected.index()).find('.ti-star-check, .star-check').removeClass('ti-active active');

		// give back active star on mouse enter
		container.one('mouseleave', () => container.find('.ti-star-check, .star-check').eq(container.data('selected')).addClass('ti-active active'));
	}
});

// star click
jQuery(document).on('click', '.ti-rate-us-box .ti-quick-rating .ti-star-check', function(event) {
	event.preventDefault();

	let star = jQuery(this);
	let container = star.parent();

	// add index to data & remove all active stars
	container.data('selected', star.index()).find('.ti-star-check').removeClass('ti-active');

	// select current star
	star.addClass('ti-active');

	// show modals
	if (parseInt(star.data('value')) >= 4) {
		// open new window
		window.open(location.href + '&command=rate-us-feedback&_wpnonce='+ container.data('nonce') +'&star=' + star.data('value'), '_blank');

		jQuery('.ti-rate-us-box').fadeOut();
	}
	else {
		let feedbackModal = jQuery('#ti-rateus-modal-feedback');

		if (feedbackModal.data('bs') == '5') {
			feedbackModal.modal('show');
			setTimeout(() => feedbackModal.find('textarea').focus(), 500);
		}
		else {
			feedbackModal.fadeIn();
			feedbackModal.find('textarea').focus();
		}

		feedbackModal.find('.ti-quick-rating .ti-star-check').removeClass('ti-active').eq(star.index()).addClass('ti-active');
	}
});

// write to support
jQuery(document).on('click', '.btn-rateus-support', function(event) {
	event.preventDefault();

	let btn = jQuery(this);
	btn.blur();

	let container = jQuery('#ti-rateus-modal-feedback');
	let email = container.find('input[type=text]').val().trim();
	let text = container.find('textarea').val().trim();

	// hide errors
	container.find('input[type=text], textarea').removeClass('is-invalid');

	// check email
	if (email === "" || !/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email)) {
		container.find('input[type=text]').addClass('is-invalid').focus();
	}

	// check text
	if (text === "") {
		container.find('textarea').addClass('is-invalid').focus();
	}

	// there is error
	if (container.find('.is-invalid').length) {
		return false;
	}

	// show loading animation
	btn.addClass('ti-btn-loading');
	container.find('a, button').css('pointer-events', 'none');

	// ajax request
	jQuery.ajax({
		type: 'post',
		dataType: 'application/json',
		data: {
			command: 'rate-us-feedback',
			_wpnonce: btn.data('nonce'),
			email: email,
			text: text,
			star: container.find('.ti-quick-rating .ti-star-check.ti-active').data('value')
		}
	}).always(() => location.reload(true));
});

// - ../../../../_wordpress_source_code/static/js/import/feature-request.js
jQuery(document).on('click', '.btn-send-feature-request', function(event) {
	event.preventDefault();

	let btn = jQuery(this);
	btn.blur();

	let container = jQuery('.ti-feature-request');
	let email = container.find('input[name="email"]').val().trim();
	let text = container.find('textarea[name="description"]').val().trim();

	// hide errors
	container.find('.is-invalid').removeClass('is-invalid');

	// check email
	if (email === "" || !/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email)) {
		container.find('input[name="email"]').addClass('is-invalid');
	}

	// check text
	if (text === "") {
		container.find('textarea[name="description"]').addClass('is-invalid');
	}

	// there is error
	if (container.find('.is-invalid').length) {
		return false;
	}

	// show loading animation
	btn.addClass('ti-btn-loading');

	let data = new FormData(jQuery('.ti-feature-request form').get(0));

	// ajax request
	jQuery.ajax({
		type: 'POST',
		data: data,
		cache: false,
		contentType: false,
		processData: false
	}).always(function() {
		btn.removeClass('ti-btn-loading');

		btn.addClass('ti-show-tooltip').removeClass('ti-toggle-tooltip');
		setTimeout(() => btn.removeClass('ti-show-tooltip').addClass('ti-toggle-tooltip'), 3000);
	});
});