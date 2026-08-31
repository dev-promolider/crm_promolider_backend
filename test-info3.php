<?php
echo app(\Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::create('/api/v1/course/details/153', 'GET'))->getContent();
