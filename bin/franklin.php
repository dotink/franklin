<?php namespace Dotink\Franklin
{
	require __DIR__ . '/../vendor/autoload.php';

	use Symfony\Component\Console\Application;
	use Dotenv\Dotenv;

	$app     = new Application();
	$dotenv  = new Dotenv(__DIR__ . '/../');

	$dotenv->load();

	$app->add(new Command\Mail());
	$app->run();
}
