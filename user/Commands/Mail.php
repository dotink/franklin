<?php namespace Dotink\Franklin\Command
{
	use Symfony\Component\Console\Command\Command;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Input\InputOption;
	use Symfony\Component\Console\Output\OutputInterface;
	use Symfony\Component\Console\Helper\ProgressBar;

	use Http\Adapter\Guzzle6\Client;
	use Mailgun\Mailgun;

	use Dotink\Franklin;

	/**
	 *
	 */
	class Mail extends Command
	{
		const DEFAULT_TEXT = 'Please use an HTML capable e-mail viewer';

		/**
		 *
		 */
		protected function configure()
		{
			$this
				->setName('mail')
				->setDescription('Use this command to send mail')
				->addArgument(
					'list',
					InputArgument::REQUIRED,
					'The list of recipients you wish to send the mail to'
				)
				->addArgument(
					'message',
					InputArgument::REQUIRED,
					'The message you wish to send'
				)
				->addOption(
					'test-recipients',
					't',
					InputOption::VALUE_REQUIRED,
					'If used, the e-mail will be sent to the test recipients list provided'
				)
				->addOption(
					'test-count',
					'c',
					InputOption::VALUE_REQUIRED,
					'The number of test e-mails to send to the recipients',
					1
				)
				->addOption(
					'from',
					'f',
					InputOption::VALUE_REQUIRED,
					'From whom to send the e-mail',
					implode('@', ['noreply', getenv('MG_DOMAIN')])
				)
			;
		}

		/**
		 *
		 */
		protected function execute(InputInterface $input, OutputInterface $output)
		{
			$parser = new Franklin\Parser($input->getArgument('message'));

			if ($input->getOption('test-recipients')) {
				$list    = $input->getOption('test-recipients');
				$subject = sprintf('TEST: %s', $parser->getSubject());
			} else {
				$list    = $input->getArgument('list');
				$subject = $parser->getSubject();
			}

			$count   = 0;
			$total   = $this->getRecipientCount($list);
			$from    = $input->getOption('from');

			$client  = new Client();
			$tracker = new ProgressBar($output, $total);
			$mailgun = new Mailgun(getenv('MG_API_KEY'), $client);

			$output->writeln(sprintf('Sending "%s" to %d recipients', $subject, $total));
			$tracker->start();

			foreach ($this->getRecipient($list) as $recipient) {
				$mailgun->sendMessage(getenv('MG_DOMAIN'), [
					'from'    => $from,
					'subject' => $subject,
					'to'      => $recipient['email'],
					'text'    => $parser->getPart('text', $recipient) ?: self::DEFAULT_TEXT,
					'html'    => $parser->getPart('html', $recipient) ?: ''
				]);

				$tracker->advance();
			}

			$tracker->finish();
			$output->writeln('');
		}


		/**
		 *
		 */
		protected function getRecipient($list)
		{
			$handle  = fopen($list, 'r');
			$headers = fgetcsv($handle);

			while ($data = fgetcsv($handle)) {
				$data = array_combine($headers, $data);

				yield $data;
			}
		}


		/**
		 *
		 */
		protected function getRecipientCount($list)
		{
			$handle = fopen($list, 'rb');
			$lines  = 0;

			while (!feof($handle)) {
				$lines += substr_count(fread($handle, 8192), "\n");
			}

			fclose($handle);

			return $lines ? $lines - 1 : 0;
		}
	}
}
