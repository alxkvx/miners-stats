$(document).ready(function() {
	
	$("#s9pass").click(function(){
		$('#divedit,#s9pflds').show();
		$('#l3pflds').hide();
	});
	$("#l3pass").click(function(){
		$('#divedit,#l3pflds').show();
		$('#s9pflds').hide();
	});
	$("#editclose").click(function(){
		$('#divedit').hide();
	});
	$("#s9savepass").click(function(){
		var pass = $('#s9passwd').val(),
			url = 'passwdsave.php?type=s9&passwd='+pass;
		$.get(url, function(data){
			$('#editfld').append(data);
		});
	});
	$("#l3savepass").click(function(){
		var pass = $('#l3passwd').val(),
			url = 'passwdsave.php?type=l3&passwd='+pass;
		$.get(url, function(data){
			$('#editfld').append(data);
		});
	});


});
