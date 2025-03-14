"use strict";

// Setting Color

$(window).resize(function() {
	$(window).width(); 
});

$('.changeBodyBackgroundFullColor').on('click', function(){
	if($(this).attr('data-color') == 'default'){
		$('body').removeAttr('data-background-full');
	} else {
		$('body').attr('data-background-full', $(this).attr('data-color'));
	}

	$(this).parent().find('.changeBodyBackgroundFullColor').removeClass("selected");
	$(this).addClass("selected");
	layoutsColors();
});

$('.changeLogoHeaderColor').on('click', function(){
	if($(this).attr('data-color') == 'default'){
		$('.logo-header').removeAttr('data-background-color');
	} else {
		$('.logo-header').attr('data-background-color', $(this).attr('data-color'));
		let color = $(this).attr('data-color');
		$.ajax({
			url: '/api/config/create-update',
			method: 'POST',
			header: {
				'Content-Type': 'application/json',
			},
			data: {
				'logo_header_color': color,
			},
			beforeSend: function() {
				console.log("Loading...")
			},
			success: function(msg) {
				if (msg.status == 200) {
					showMessage('success', 'flaticon-alarm-1', 'Sukses', msg.message)
				} else {
					showMessage('warning', 'flaticon-error', 'Peringatan', msg.message)
				}
			},
			error: function(error){
				console.log("error :", error),
				showMessage('danger', 'flaticon-error', 'Error !', error.message)
			}
		})
	}

	$(this).parent().find('.changeLogoHeaderColor').removeClass("selected");
	$(this).addClass("selected");
	customCheckColor();
	layoutsColors();
});

$('.changeTopBarColor').on('click', function(){
	if($(this).attr('data-color') == 'default'){
		$('.main-header .navbar-header').removeAttr('data-background-color');
	} else {
		$('.main-header .navbar-header').attr('data-background-color', $(this).attr('data-color'));
		let color = $(this).attr('data-color');
		$.ajax({
			url: '/api/config/create-update',
			method: 'POST',
			header: {
				'Content-Type': 'application/json',
			},
			data: {
				'topbar_color': color,
			},
			beforeSend: function() {
				console.log("Loading...")
			},
			success: function(msg) {
				if (msg.status == 200) {
					showMessage('success', 'flaticon-alarm-1', 'Sukses', msg.message)
				} else {
					showMessage('warning', 'flaticon-error', 'Peringatan', msg.message)
				}
			},
			error: function(error){
				console.log("error :", error),
				showMessage('danger', 'flaticon-error', 'Error !', error.message)
			}
		})
	}

	$(this).parent().find('.changeTopBarColor').removeClass("selected");
	$(this).addClass("selected");
	layoutsColors();
});

$('.changeSideBarColor').on('click', function(){
	if($(this).attr('data-color') == 'default'){
		$('.sidebar').removeAttr('data-background-color');
	} else {
		$('.sidebar').attr('data-background-color', $(this).attr('data-color'));
		let color = $(this).attr('data-color');
		$.ajax({
			url: '/api/config/create-update',
			method: 'POST',
			header: {
				'Content-Type': 'application/json',
			},
			data: {
				'sidebar_color': color,
			},
			beforeSend: function() {
				console.log("Loading...")
			},
			success: function(msg) {
				if (msg.status == 200) {
					showMessage('success', 'flaticon-alarm-1', 'Sukses', msg.message)
				} else {
					showMessage('warning', 'flaticon-error', 'Peringatan', msg.message)
				}
			},
			error: function(error){
				console.log("error :", error),
				showMessage('danger', 'flaticon-error', 'Error !', error.message)
			}
		})
	}

	$(this).parent().find('.changeSideBarColor').removeClass("selected");
	$(this).addClass("selected");
	layoutsColors();
});

$('.changeBackgroundColor').on('click', function(){
	$('body').removeAttr('data-background-color');
	$('body').attr('data-background-color', $(this).attr('data-color'));
	let color = $(this).attr('data-color');
		$.ajax({
			url: '/api/config/create-update',
			method: 'POST',
			header: {
				'Content-Type': 'application/json',
			},
			data: {
				'bg_color': color,
			},
			beforeSend: function() {
				console.log("Loading...")
			},
			success: function(msg) {
				if (msg.status == 200) {
					showMessage('success', 'flaticon-alarm-1', 'Sukses', msg.message)
				} else {
					showMessage('warning', 'flaticon-error', 'Peringatan', msg.message)
				}
			},
			error: function(error){
				console.log("error :", error),
				showMessage('danger', 'flaticon-error', 'Error !', error.message)
			}
		})
	$(this).parent().find('.changeBackgroundColor').removeClass("selected");
	$(this).addClass("selected");
});

function customCheckColor(){
	var logoHeader = $('.logo-header').attr('data-background-color');
	if (logoHeader !== "white") {
		$('.logo-header .navbar-brand').attr('src', 'https://via.placeholder.com/100x35');
	} else {
		$('.logo-header .navbar-brand').attr('src', 'https://via.placeholder.com/100x35');
	}
}


var toggle_customSidebar = false,
custom_open = 0;

if(!toggle_customSidebar) {
	var toggle = $('.custom-template .custom-toggle');

	toggle.on('click', (function(){
		if (custom_open == 1){
			$('.custom-template').removeClass('open');
			toggle.removeClass('toggled');
			custom_open = 0;
		}  else {
			$('.custom-template').addClass('open');
			toggle.addClass('toggled');
			custom_open = 1;
		}
	})
	);
	toggle_customSidebar = true;
}