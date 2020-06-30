<?php

namespace Apps\Start\Middlewares;

class SecurityMiddleware
{
    /**
     * @Middleware("beforeRequest")
    */
    public function beforeRequestTest()
    {
        // Enable this line to enable automatic renewal of the
        // user session / cookie on each request
        // renewAccess();
    }

    /**
     * @Middleware("afterRequest")
    */
    public function afterRequestTest()
    {
    }
}
