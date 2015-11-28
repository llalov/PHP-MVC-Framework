<?php
declare(strict_types=1);

namespace Mvc\Annotations;

use Mvc\HttpContext\HttpContext;

class AnonymousAnnotation extends Annotation
{
    /**
     * AnonymousAnnotation constructor.
     */
    public function __construct() {
        parent::__construct();
    }
    public function dispatch() {
        $this->beforeActionExecute();
    }
    private function beforeActionExecute(){
        $userId = (string) HttpContext::getInstance()->getSession()->userId;
        if ($userId !== "") {
            Helpers::redirect('');
        }
    }
}