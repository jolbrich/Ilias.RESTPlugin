<?php


class ilRequestLib {

    var $app;
    var $content_type;

    public function ilRequestLib($app) {
	$this->app = $app;
	$this->content_type = $app->request()->headers()->get('Content-Type');
    }

    public function getParam($param) {
	if ($this->content_type == 'application/xml') {
	    throw new Exception('Can not get parameters from xml');
	}

	    return $this->app->request()->params($param);
    }

    public function getObject() {
	if ($this->content_type != 'application/json'){
	    throw new Exception('Can not get object from non-JSON');
	}

	return json_decode($this->app->request()->getBody());
    }

    public function getRaw() {
	if ($this->content_type == 'application/xml' or
	    $this->content_type == 'application/json') {  // warum sollte man das wollen?
	    return $this->app->request()->getBody();
	} else {
	    throw new Exception('getRaw only supported for xml or json');
	}

  

}



?>


