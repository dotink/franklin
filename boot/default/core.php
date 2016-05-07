<?php

use Mailgun\Mailgun;
use Http\Adapter\Guzzle6\Client;

return Affinity\Action::create(function($app, $broker) {

	$broker->delegate('Mailgun\Mailgun', function() {
		return new Mailgun(getenv('MG_API_KEY'), new Client());
	});

	foreach ($this->fetch('console', 'commands') as $command) {
		$app->add($broker->make($command));
	}
});
