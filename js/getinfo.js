$(document).ready(function() {
	
	$("#grestart").click(function(){
		//console.log("clicked add");
		var ip = $('[name=ip]').val(),
			url = 'command.php?command=restart&ip='+ip;
		$.get(url, function(data){
			$('body').append('SOFT restart: '+data);
		});
	});
	$("#restartminer").click(function(){
		var ip = $('[name=ip]').val(),
			url = 'command.php?command=restartminer&ip='+ip;
		$.get(url, function(data){
			$('body').append('cgminer restart: '+data);
		});
	});
	$("#edit").click(function(){
		$('#divedit').show();
	});
	$("#editclose").click(function(){
		$('#divedit').hide();
	});
	$("#confclose").click(function(){
		$('#divconf').hide();
	});
	$("#editsave").click(function(){
		var id = $('.wid').html(),
			ip = $('[name=ip]').val(),
			model = $('[name=model]').val(),
			fanck = $('[name=fanck]').val(),
			fanmod= $('[name=fanmod]').val(),
			disabled = $('[name=disabled]').val(),
			com = $('[name=comment]').val(),
			type = $('[name=type]').val(),
			url = 'editsave.php?id='+id+'&type='+type+'&ip='+ip+'&model='+model+'&fanck='+fanck+'&fanmod='+fanmod+'&comment='+encodeURIComponent(com)+'&disabled='+disabled;
		$.get(url, function(data){
			$('#divedit').append(data);
		});
	});
	$("#confsave").click(function(){
		var id = $('.wid').html(),
			pool0 = $('[name=pool0]').val(),
			pool1 = $('[name=pool1]').val(),
			pool2 = $('[name=pool2]').val(),
			user0 = $('[name=user0]').val(),
			user1 = $('[name=user1]').val(),
			user2 = $('[name=user2]').val(),
			freq0 = $('[name=freq0]').val(),
			freq1 = $('[name=freq1]').val(),
			freq2 = $('[name=freq2]').val(),
			volt0 = $('[name=volt0]').val(),
			volt1 = $('[name=volt1]').val(),
			volt2 = $('[name=volt2]').val(),
			temp = $('[name=temp]').val(),
			sensor = $('[name=sensor]').val(),
			fanck = $('[name=fanck]').val(),
			type = $('[name=type]').val(),
			url = 'configsave.php?id='+id+'&type='+type+'&pool0='+pool0+'&pool1='+pool1+'&pool2='+pool2+'&user0='+user0+'&user1='+user1+'&user2='+user2+'&freq0='+freq0+'&freq1='+freq1+'&freq2='+freq2+'&volt0='+volt0+'&volt1='+volt1+'&volt2='+volt2+'&temp='+temp+'&sensor='+sensor+'&fanck='+fanck;
		$.get(url, function(data){
			$('#divconf').append(data);
		});
	});
	$("#config").click(function(){
		$('#divconf').show();
	});
	$(".pooldisable").click(function(){
		var ip = $('[name=ip]').val(),
			opt = $(this).attr('opt'),
			url = 'command.php?command=disablepool&opt='+opt+'&ip='+ip;
		$.get(url, function(data){
			$('body').append(data);
		});
	});
	$(".poolenable").click(function(){
		var ip = $('[name=ip]').val(),
			opt = $(this).attr('opt'),
			url = 'command.php?command=enablepool&opt='+opt+'&ip='+ip;
		$.get(url, function(data){
			$('body').append(data);
		});
	});
	$(".pooldel").click(function(){
		var ip = $('[name=ip]').val(),
			opt = $(this).attr('opt'),
			url = 'command.php?command=removepool&opt='+opt+'&ip='+ip;
		$.get(url, function(data){
			$('body').append(data);
		});
	});
	$(".poolprio").click(function(){
		var ip = $('[name=ip]').val(),
			opt = $(this).attr('opt'),
			url = 'command.php?command=switchpool&opt='+opt+'&ip='+ip;
		$.get(url, function(data){
			$('body').append(data);
		});
	});
	$("#pooladd").click(function(){
		var ip = $('[name=ip]').val(),
			user = $('[name=user]').val(),
			poolurl = $('[name=url]').val(),
			url = 'command.php?command=addpool&url='+encodeURIComponent(poolurl)+'&user='+user+'&ip='+ip;
		$.get(url, function(data){
			$('body').append(data);
		});
	});
	$("#pooladdnsw").click(function(){
		var ip = $('[name=ip]').val(),
			user = $('[name=user]').val(),
			poolurl = $('[name=url]').val(),
			url = 'command.php?command=addnswtchpool&url='+encodeURIComponent(poolurl)+'&user='+user+'&ip='+ip;
		$.get(url, function(data){
			$('body').append(data);
		});
	});
});
