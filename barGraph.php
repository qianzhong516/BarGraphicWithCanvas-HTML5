<!DOCTYPE html public"bar graph">
<html>
<head>
<meta charset="utf-8">
<?php include('connection.php');?>
<link rel="stylesheet" href="barGraph.css"/>
<script src="myScripts/jquery.js"></script>
<script>
$(function(){

	//when user opens up the page, display:
	var display=(function(){
			$.ajax({
				type: "get",
				url: "read_insert_Votes.php",
				data: { //read votes,amountOfPeople
					sysMsg: "firstRead"
				},
				success: function(data){
					var dat=jQuery.parseJSON(data);
					var amountOfPeople=0;
					for(var i=0; i<dat.length; i++){
						amountOfPeople+=Number(dat[i]);
					}
					//use callback function
					firstDisplay(dat,amountOfPeople,function(){
						startRefresh();					
					});
				}
			});		
	})();
	//read the votes from database once per 2 seconds
	var startRefresh=function(){
		setInterval(function(){
			$.ajax({
				type: "get",
				url: "read_insert_Votes.php",
				data: { //read votes,amountOfPeople
					sysMsg: "read"
				},
				success: function(data){
					var dat=jQuery.parseJSON(data);
					var amountOfPeople=0;
					for(var i=0; i<dat.length; i++){
						amountOfPeople+=Number(dat[i]);
					}
					update(dat,amountOfPeople);
					draw();
				}
			});		
		},1000);		
	};
	
	//update the vote in database
	$('#vote_form').on('submit',function(e){
		e.preventDefault();
		$.ajax({
			async: true,
			type: "get",
			url: "read_insert_Votes.php",
			data: {
				field: $('#fields').find('option:Selected').val()
			},
			success: function(graph){
			}
		});
	});
});
</script>
</head>
<body>
<form id='vote_form' action='/' method='get'>
	<fieldset>
	<legend>Give your vote!</legend>
	<label for='fields'>Vote:</label>	
	<select id='fields'>"
		<?php			
		$sql="SELECT field_name FROM vote_result";
		$stmt=$conn->query($sql);
		$result=$stmt->fetchAll(PDO::FETCH_COLUMN,0);		
		foreach($result as $value){
			settype($value,'string');
			echo "<option value='{$value}'>{$value}</option>";
			echo $value;
		}
		?>		
	</select><br/>
	<input type='submit' value='submit'/>
	</fieldset>
</form>

<canvas id="canvas" height="300px"></canvas>
<legend for="canvas"></legend>
<script>
// flag for opening animation
var flag=[];
// get arr of select
var nameOfFields=[];
var countOfFields=document.querySelectorAll("option").length;
[].forEach.call(document.querySelectorAll("option"),function(options){
	nameOfFields.push(options.value);
});
var arrOfFields=[];
var colors=['purple','red','green'];
var offset=20;
var bottom_offset=20;
var canvas=document.querySelector('#canvas');
var ctx=canvas.getContext('2d');
//dynamically generates a canvas
canvas.width=55*countOfFields; //35 for bar width, 20 for offset, looks good to me

var init=(function(){
	//generate properties of each field and add them to arrOfFields
	for(var i=0; i<countOfFields; i++){
		var field=({
			x: canvas.width/countOfFields*i, //leftoffset
			y: canvas.height-bottom_offset,
			width: 35,
			height: 0,
			name: nameOfFields[i],
			color: colors[i%colors.length] //repeats the color if it points to the end of array colors	
		});	
		arrOfFields.push(field);
	}	
})();

var firstDisplay=function(votes,amountOfPeople,callback){
	//draw legend
        var legend = document.querySelector("legend[for='canvas']");
        var ul = document.createElement("ul");
        legend.append(ul);
		ul.style.fontSize="15px";
        for (var i=0; i<countOfFields; i++){
            var li = document.createElement("li");
            li.style.listStyle = "none";
            li.style.borderLeft = "25px solid "+colors[i%colors.length];
            li.style.padding = "5px";
            li.textContent = arrOfFields[i].name;
            ul.append(li);
        }
	//draw field_name at the bottom
	ctx.font = "15px Arial";
	ctx.textBaseline="bottom";
	ctx.textAlign="center";
	ctx.fillText("Bar Graphic Examples",canvas.width/2,canvas.height);
	
	//play crawling up animation
	window.play=setInterval(function(){
		for(var i=0; i<countOfFields; i++){
			var field=arrOfFields[i];
			var height=votes[i]/amountOfPeople*(canvas.height-bottom_offset-18)*(-1);
			if(field.height>height){ //because of minus value
				field.height-=1;	
				ctx.clearRect(0,0,canvas.width,canvas.height-bottom_offset); //prevent borders from overlapping
				draw();
			}else{
				if(flag.indexOf(field)===-1){ //in case of duplicates
					flag.push(field);
				}
				if(flag.length===countOfFields){
					if(window.play!=undefined&&window.play!='undefined'){
						window.clearInterval(window.play);					
					}
					callback();
				}
			}		
		}
	},10);				
}

//draw each field bar
function draw(){
	for(var i=0; i<countOfFields; i++){
		var field=arrOfFields[i];
		//fill the rectangle with gradient color
		var grd = ctx.createLinearGradient(field.x, field.y, field.x, field.y+field.height);
		grd.addColorStop(0, "#ffffff");
		grd.addColorStop(1, field.color);
		ctx.fillStyle=grd;
		ctx.fillRect(field.x,field.y,field.width,field.height);
		ctx.strokeStyle="#000";
		ctx.strokeRect(field.x,field.y,field.width,field.height);
		ctx.stroke();
	}
}
//update the graph per period
function update(votes,amountOfPeople){
	//clean the existing graph and percentage
	ctx.clearRect(0,0,canvas.width,canvas.height-bottom_offset);
	var amountOfVotes;
	for(var i=0; i<countOfFields; i++){
		var field=arrOfFields[i];
    //make sure bar height is always less than canvas height, -18 prevent the top number from being covered
		var height=votes[i]/amountOfPeople*(canvas.height-bottom_offset-18)*(-1);				
		field.height= height;
		amountOfVotes=votes[i];					
		//draw the amountOfVotes on the top
		var txt_x=field.width/2+i*(field.width+offset); //txt_x is the middle of each bar
		var txt_y=canvas.height-bottom_offset+field.height;
		ctx.fillStyle="#000";
		ctx.font='12px Arial';
		ctx.fillText(amountOfVotes,txt_x,txt_y);
	}
}
</script>
</body>
</html>
