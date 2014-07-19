(function($) {

	$.entwine('ss.jsplumbfield', function($){
	
		/**
		 * Class: #Form_BatchActionsForm
		 * 
		 * Batch actions which take a bunch of selected pages,
		 * usually from the CMS tree implementation, and perform serverside
		 * callbacks on the whole set. We make the tree selectable when the jQuery.UI tab
		 * enclosing this form is opened.
		 * 
		 * Events:
		 *  register - Called before an action is added.
		 *  unregister - Called before an action is removed.
		 */
		$('div.jsPlumbField').entwine({
			onadd: function() {
				var $this = this;

				jsPlumb.ready(function() {

				var instance = jsPlumb.getInstance({
					// default drag options
					DragOptions : { cursor: 'pointer', zIndex:2000 },
					// the overlays to decorate each connection with.  note that the label overlay uses a function to generate the label text; in this
					// case it returns the 'labelText' member that we set on each connection in the 'init' method below.
					ConnectionOverlays : [
						[ "Arrow", { location:1 } ],
						[ "Label", { 
							location:0.5,
							id:"label",
							cssClass:"aLabel"
						}]
					],
					Container: $this.attr('id')
				});

				// this is the paint style for the connecting lines..
				var connectorPaintStyle = {
					lineWidth:4,
					strokeStyle:"#61B7CF",
					joinstyle:"round",
					outlineColor:"white",
					outlineWidth:2
				},
				// .. and this is the hover style. 
				connectorHoverStyle = {
					lineWidth:4,
					strokeStyle:"#216477",
					outlineWidth:2,
					outlineColor:"white"
				},
				endpointHoverStyle = {
					fillStyle:"#216477",
					strokeStyle:"#216477"
				},

				// the definition of source endpoints (the small blue ones)
				sourceEndpoint = {
					endpoint:"Dot",
					paintStyle:{ 
						strokeStyle:"#7AB02C",
						fillStyle:"transparent",
						radius:7,
						lineWidth:3 
					},				
					isSource:true,
					connectionsDetachable: false,
					maxConnections: -1,
					//connector:[ "Bezier", { stub:[40, 60], gap:10, cornerRadius:5, alwaysRespectStubs:true } ],								                
					connectorStyle:connectorPaintStyle,
					hoverPaintStyle:endpointHoverStyle,
					connectorHoverStyle:connectorHoverStyle,
			        dragOptions:{},
			        overlays:[
			        	[ "Label", { 
			            	location:[0.5, 1.5], 
			            	label:"Drag",
			            	cssClass:"endpointSourceLabel" 
			            } ]
			        ]
				},		

				// the definition of target endpoints (will appear when the user drags a connection) 
				targetEndpoint = {
					endpoint:"Dot",					
					paintStyle:{ fillStyle:"#7AB02C",radius:11 },
					hoverPaintStyle:endpointHoverStyle,
					maxConnections:-1,
					dropOptions:{ hoverClass:"hover", activeClass:"active" },
					isTarget:true,			
			        overlays:[
			        	[ "Label", { location:[0.5, -0.5], label:"Drop", cssClass:"endpointTargetLabel" } ]
			        ]
				};

				$this.parent().css('width', $this.parent().width() + 'px');
				$this.css('width', '2000px');
				$this.css('height', '2000px');

				// suspend drawing and initialise.
				instance.doWhileSuspended(function() {

					// Set up endpoints of each node
					$this.find('div').each(function() {
						instance.addEndpoint(this.id, { anchor:'RightMiddle', uuid:this.id + '-Source1' }, sourceEndpoint);
						instance.addEndpoint(this.id, { anchor:'LeftMiddle', uuid:this.id + '-Dest1' }, targetEndpoint);
					})

					// make all the nodes moveable
					instance.draggable(
						$this.find('div'),
						{
							grid: [20, 20],
						//	containment:true,
							stop: function(e) {
								var left = parseInt(e.target.style.left),
									top = parseInt(e.target.style.top);
								var url = $this.data('href') + '/setPosition?node=' + e.target.id + '&left=' + left + '&top=' + top;
								$.get(url);
							}

						}
					);
				});

				instance.doWhileSuspended(function() {
					// Make existing connections
					$this.find('div').each(function() {
						var sourceID = $(this).attr('id');
						if($(this).data('linkto')) {
							$($(this).data('linkto').split(',')).each(function(i, destID) {
								if($('#' + destID).length) {
									console.log(sourceID, destID);
									instance.connect({
										uuids: [sourceID + "-Source1", destID + "-Dest1"],
										editable:true,
										label: 'hi'
									});
								} else {
									console.log("Can't find " + destID);
								}
							});
						}
					});

					// On new connections, send them to the server
					instance.bind("connection", function(connInfo, originalEvent) { 
						var url = $this.data('href') + '/addLink?from=' + connInfo.connection.sourceId + '&to=' + connInfo.connection.targetId;
						$.get(url);
					});			
								

					//
					// listen for clicks on connections, and offer to delete connections on click.
					//
					instance.bind("click", function(conn, originalEvent) {
						if (confirm("Delete connection from " + conn.sourceId + " to " + conn.targetId + "?")) {
							jsPlumb.detach(conn); 
							var url = $this.data('href') + '/removeLink?from=' + conn.sourceId + '&to=' + conn.targetId;
							$.get(url);
						}
					});	
					
					instance.bind("connectionDrag", function(connection) {
						console.log("connection " + connection.id + " is being dragged. suspendedElement is ", connection.suspendedElement, " of type ", connection.suspendedElementType);
					});		
					
					instance.bind("connectionDragStop", function(connection) {
						console.log("connection " + connection.id + " was dragged");
					});

					instance.bind("connectionMoved", function(params) {
						console.log("connection " + params.connection.id + " was moved");
					});
				});

				});	
			}
		});
	});
}(jQuery));