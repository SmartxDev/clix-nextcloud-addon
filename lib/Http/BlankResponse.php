<?php
namespace OCA\VueExample\Http;

use OCP\AppFramework\Http\Response;

class BlankResponse extends Response {

    public function __construct($statusCode) {
        $this->setStatus($statusCode);
    }

    public function render() {
        return "";
    }

}