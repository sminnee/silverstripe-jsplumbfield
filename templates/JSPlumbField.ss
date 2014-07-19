<style>

.jsPlumbFieldScroller {
	width: 100%;
	padding: 0;
	margin: 0;
	height: 600px;
	overflow: scroll;
	border: 1px #ccc solid;
}
.jsPlumbField {
	position: relative;
	/*height: 2000px;
	width: 100px;*/
	background-color:white;    
	margin: 0;
}

.jsPlumbField-node {   

	z-index:24;
	position:absolute;    

	text-align:center;
	background-color:white;
	border:1px solid #346789;
	color:black;
	padding:20px;

	cursor:pointer;
	width: 8em;

	box-shadow: 2px 2px 19px #aaa;
	-o-box-shadow: 2px 2px 19px #aaa;
	-webkit-box-shadow: 2px 2px 19px #aaa;
	-moz-box-shadow: 2px 2px 19px #aaa;

	-moz-border-radius:0.5em;
	border-radius:0.5em;        

	-webkit-transition: -webkit-box-shadow 0.15s ease-in;
	-moz-transition: -moz-box-shadow 0.15s ease-in;
	-o-transition: -o-box-shadow 0.15s ease-in;
	transition: box-shadow 0.15s ease-in;
}
    

.jsPlumbField-node:hover {
	border:1px solid #123456;

	box-shadow: 2px 2px 19px #444;
	-o-box-shadow: 2px 2px 19px #444;
	-webkit-box-shadow: 2px 2px 19px #444;
	-moz-box-shadow: 2px 2px 19px #fff;
	opacity:0.9;
	filter:alpha(opacity=90);
}

</style>

<div class="jsPlumbFieldScroller">
<div class="jsPlumbField" id="asdfasdfsd" data-href="$Link">
<% loop Nodes %>
	<div class="jsPlumbField-node" id="$NodeID" data-linkto="$DestNodeIDs" style="left: {$PosLeft}px; top: {$PosTop}px"><a href="$Link">$Label</a></div>
<% end_loop %>
</div>
<div>