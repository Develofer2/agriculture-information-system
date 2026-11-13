<?php

declare(strict_types=1);
http_response_code(404);
layout('Page not found', fn () => print('<section class="panel"><p>The requested page does not exist.</p></section>'));
