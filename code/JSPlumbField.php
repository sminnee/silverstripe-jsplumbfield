<?php

class JSPlumbField extends FormField {

	private static $url_handlers = array(
		'$Action!/$ID' => '$Action'
	);

	private static $allowed_actions = array(
		'index',
		'addLink',
		'removeLink',
		'setPosition',
	);

	protected $list;
	protected $editLinkPattern;
	protected $posLeftField = 'PosLeft';
	protected $posTopField = 'PosTop';
	protected $labelField = 'Name';
	protected $linksOutRelation = 'LinksOut';
	protected $linksOutIDField = 'ID';

	public function FieldHolder($properties = array()) {
		Requirements::javascript("jsplumbfield/thirdparty/jsplumb/js/jquery.jsPlumb-1.6.2.js");
		Requirements::javascript("jsplumbfield/javascript/JSPlumbField.js");

		return $this->renderWith('JSPlumbField');
	}

	/**
	 * Set a DataList of nodes for this this JSPlumbField to operate on
	 */
	public function setList($list) {
		$this->list = $list;
		return $this;
	}

	public function getList() {
		return $this->list;
	}

	/**
	 * Set the names of the fields used to store node positions
	 */
	function setPositionFields($posLeftField, $posTopField) {
		$this->posLeftField = $posLeftField;
		$this->posTopField = $posTopField;
		return $this;
	}

	/**
	 * Set the names of the field used to store node label
	 */
	function setLabelField($labelField) {
		$this->labelField = $labelField;
		return $this;
	}

	/**
	 * Set a string from which edit links will be fromed.
	 * '$ID' in the string will be replaced with the ID of the node.
	 */
	function setEditLinkPattern($editLinkPattern) {
		$this->editLinkPattern = $editLinkPattern;
		return $this;
	}

	function getEditLinkPattern() {
		return $this->editlinkPattern;
	}

	/**
	 * Set the name of a relation on each node that will point to the list of nodes for which
	 * there are outbound links. Defaults to "LinksOut"
	 * 
	 * The second argument is an ID field name. If linksOutRelation directly lists node objects,
	 * then the default value of "ID" will suffice. This would be the case if, for example, you used
	 * a many-many relation.
	 *
	 * If, however, linksOutRelation lists an intermediary class with a has_one relation to nodes, then
	 * idField can be set to the ID field for that has one, e.g. 'DestID'.
	 * 
	 * The relation should have functioning add() and removeById() methods; these will be used
	 * to edit the links.
	 */
	function setLinksOutRelation($linksOutRelation, $idField = 'ID') {
		$this->linksOutRelation = $linksOutRelation;
		$this->linksOutIDField = $idField;
		return $this;
	}

	function getLinksOutRelation() {
		return $this->linksOutRelation;
	}

	function getLinksOutIDField() {
		return $this->linksOutIDField;
	}

	function Nodes() {
		$data = new ArrayList;

		foreach($this->getList() as $item) {
			$destNodes = $item->{$this->linksOutRelation}()->column($this->linksOutIDField);
			$destNodeIDs = $destNodes ? ('node-' . implode(',node-', $destNodes)) : '';

			$data->push(new ArrayData(array(
				"Label" => $item->{$this->labelField},
				"NodeID" => "node-$item->ID",
				"DestNodeIDs" => $destNodeIDs,
				"Link" => str_replace('$ID', $item->ID, $this->editLinkPattern),
				"PosLeft" => $item->{$this->posLeftField},
				"PosTop" => $item->{$this->posTopField},
			)));

		}
		return $data;
	}

	function setPosition($request) {
		$nodeID = str_replace('node-', '', $request->getVar('node'));
		if(!is_numeric($nodeID)) throw new SS_HTTPResponse_Exception('Bad node ID: ' . $request->getVar('node'), 400);
		$node = $this->getList()->byID($nodeID);
		if(!$node) throw new SS_HTTPResponse_Exception("Can't find node #$nodeID", 400);

		$left = $request->getVar('left');
		$top = $request->getVar('top');

		if(!is_numeric($left) || !is_numeric($top)) throw new SS_HTTPResponse_Exception("Bad pos $left,$top", 400);

		$node->PosLeft = max(0,$left);
		$node->PosTop = max(0,$top);
		$node->write();

		$response = new SS_HTTPResponse(json_encode(array(
			"node" => "node-$nodeID",
			"left" => $node->PosLeft,
			"top" => $node->PosTop,
		)));

		$response->addHeader('Content-type', 'application/json');
		return $response;

	}

	function addLink($request) {
		$fromID = str_replace('node-','',$request->getVar('from'));
		$toID = str_replace('node-','',$request->getVar('to'));

		$fromNode = $this->getList()->byID($fromID);
		if(!$fromNode) throw new SS_HTTPResponse_Exception(400, "Can't find node #$fromID");

		$linksOut = $fromNode->{$this->linksOutRelation}();

		// many-many relation
		if($this->linksOutIDField == 'ID') {
			$linksOut->add($toID);

		// relation managed with intermediary object
		} else {
			$newRelation = new $linksOut->dataClass();
			$newRelation->{$this->linksOutIDField} = $toID;
			$linksOut->add($newRelation);
		}

		$response = new SS_HTTPResponse(json_encode(array(
			"from" => "node-$fromID",
			"to" => "node-$toID"
		)));

		$response->addHeader('Content-type', 'application/json');
		return $response;
	}

	function removeLink($request) {
		$fromID = str_replace('node-','',$request->getVar('from'));
		$toID = str_replace('node-','',$request->getVar('to'));

		$fromNode = $this->getList()->byID($fromID);
		if(!$fromNode) throw new SS_HTTPResponse_Exception(400, "Can't find node #$fromID");

		$linksOut = $fromNode->{$this->linksOutRelation}();

		// many-many relation
		if($this->linksOutIDField == 'ID') {
			$linksOut->removeByID($toID);

		// relation managed with intermediary object
		} else {
			$linksOut->filter($this->linksOutIDField,$toID)->removeAll();
		}

		$response = new SS_HTTPResponse(json_encode(array(
			"from" => "node-$fromID",
			"to" => "node-$toID"
		)));

		$response->addHeader('Content-type', 'application/json');
		return $response;
	}
}