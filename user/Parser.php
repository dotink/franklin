<?php namespace Dotink\Franklin
{
	use RuntimeException;

	/**
	 *
	 */
	class Parser
	{
		const DELIMITER = '#----\s+([^-]+)\s+----#';

		/**
		 *
		 */
		private $file = NULL;


		/**
		 *
		 */
		private $parsed = FALSE;


		/**
		 *
		 */
		public function __construct($file)
		{
			$this->file   = realpath($file);
			$this->parsed = FALSE;

			if (!$this->file) {
				throw new RuntimeException("File $file does not exist or is not readable.");
			}
		}


		/**
		 *
		 */
		public function getSubject()
		{
			$this->parse();

			return $this->subject;
		}


		/**
		 *
		 */
		public function getPart($name, $data)
		{
			$this->parse();

			if (!isset($this->parts[$name])) {
				return NULL;
			}

			return $this->render($this->parts[$name], $data);
		}


		/**
		 *
		 */
		protected function parse()
		{
			if ($this->parsed) {
				return;
			}

			$data  = file_get_contents($this->file);
			$flags = PREG_SPLIT_DELIM_CAPTURE;
			$parts = array_map('trim', preg_split(self::DELIMITER, $data, -1, $flags));

			if (count($parts) % 2 != 1) {
				throw new RunTimeException("Malformed message, must contain subject");
			}

			while (FALSE !== $key = next($parts)) {
				$this->parts[$key] = next($parts);
			}

			$this->subject = array_shift($parts);
		}


		/**
		 *
		 */
		protected function render($template, $data)
		{
			set_include_path(get_include_path() . PATH_SEPARATOR . dirname($this->file));

			ob_start();
			extract($data);
			eval('?>' . $template);

			return ob_get_clean();
		}
	}
}
