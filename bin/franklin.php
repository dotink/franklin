<?php namespace Dotink\Franklin
{
	const ROOT = __DIR__ . '/../';

	require ROOT . 'vendor/autoload.php';

	use Symfony\Component\Console\Application;
	use Auryn\Injector;
	use Dotenv\Dotenv;
	use Affinity;

	$app     = new Application();
	$broker  = new Injector();
	$dotenv  = new Dotenv(ROOT);
	$kernel  = new Affinity\Engine(
		new Affinity\NativeDriver(ROOT . 'config'),
		new Affinity\NativeDriver(ROOT . 'boot')
	);

	$dotenv->load();

	$kernel->start('prod', [
		'app'    => $app,
		'broker' => $broker
	]);

	$app->run();
}
