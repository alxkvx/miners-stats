$(document).ready(function() {
	
	$("#scaner").click(function(){
		$('#singlediv').hide();
		$('#scanerdiv').show();
	});
	$("#manual").click(function(){
		$('#singlediv').show();
		$('#scanerdiv,#scandiv').hide();
	});
	$("#scanbtn").click(function(){
		var oct1 = $('[name=oct1]').val(),
			oct2 = $('[name=oct2]').val(),
			oct3 = $('[name=oct3]').val(),
			oct4 = $('[name=oct4]').val(),
			oct4b = $('[name=oct4b]').val();
		if ($('#skip').is(":checked")) { var skipval = 1;}
		else	{ var skipval = 0;}
		var	url = 'scan.php?oct1='+oct1+'&oct2='+oct2+'&oct3='+oct3+'&oct4='+oct4+'&oct4b='+oct4b+'&skip='+skipval;
		$.get(url, function(data){
			$('#scandiv').show();
			$('#scandiv').html('');
			$('#scandiv').append(data);
		});
	});
	$("#savesingle").click(function(){
		var id = $('[name=id]').val(),
			ip = $('[name=ip]').val(),
			model = $('[name=model]').val(),
			fanck = $('[name=fanck]').val(),
			fanmod= $('[name=fanmod]').val(),
			disabled = $('[name=disabled]').val(),
			com = $('[name=comment]').val(),
			type = $('[name=type]').val(),
			url = 'addsave.php?id='+id+'&type='+type+'&ip='+ip+'&model='+model+'&fanck='+fanck+'&fanmod='+fanmod+'&comment='+encodeURIComponent(com)+'&disabled='+disabled;
		$.get(url, function(data){
			$('body').append(data);
		});
	});
	$("#addrig").click(function(){
		var name = $('[name=rigname]').val(),
			id = $('[name=rigid]').val(),
			ip = $('[name=ip]').val(),
			port = $('[name=port]').val(),
			gputype = $('[name=gputype]').val(),
			memtype = $('[name=memtype]').val(),
			gpusnum = $('[name=gpusnum]').val(),
			url = 'addsave.php?name='+name+'&type=rig&ip='+ip+'&port='+port+'&gputype='+gputype+'&memtype='+memtype+'&gpusnum='+gpusnum+'&id='+id;
		$.get(url, function(data){
			$('body').append(data);
		});
	});
	$("#addavalon").click(function(){
		var id = $('[name=avalonid]').val(),
			ip = $('[name=ip]').val(),
			groups = $('[name=groups]').val(),
			url = 'addsave.php?type=avalon&ip='+ip+'&id='+id+'&groups='+groups;
		$.get(url, function(data){
			$('body').append(data);
		});
	});
	$("body").on('click','#all', function(){
		$('input:checkbox').not(this).prop('checked', this.checked);
	});
	$("body").on('click','#addmass', function(){
		$('input[type=checkbox]').each(function () {
			if ($(this).is(':checked') && $(this).hasClass('miners')) {
				var ip = $(this).attr('ip'),
					stype = $(this).attr('miner'),
					chkid = $(this).attr('chkid'),
					id = $('#inp_'+chkid).val();
				if (/S9/i.test(stype)) {type = 's9';}
				else {type = 'l3';}
				var	url = 'addsave.php?id='+id+'&type='+type+'&ip='+ip+'&model='+stype;

				$.get(url, function(data){
					$('body').append(data);
				});
			}
		});
	});


});
