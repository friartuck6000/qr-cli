<?php

namespace Tuckerrr\Qr;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class QrCommand extends Command
{
	const DEFAULT_OUTPUT_DIR = 'generated';

	/**
	 * @var array
	 */
	protected $outputTypes = [
		'svg' => QRCode::OUTPUT_MARKUP_SVG,
		'png' => QRCode::OUTPUT_IMAGE_PNG,
		'jpg' => QRCode::OUTPUT_IMAGE_JPG,
	];

	protected function configure()
	{
		$this->setName('create')
			->setDescription('Generate a new QR code from the given URL.')
			->addArgument('url', InputArgument::REQUIRED, 'The URL to encode.')
			->addOption(
				'format',
				'f',
				InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
				'Output formats (svg, png or jpg).',
				['svg']
			)
			->addOption(
				'out',
				'o',
				InputOption::VALUE_REQUIRED,
				'Output file path. Extension will be managed automatically based on output formats.'
			);
	}

	protected function interact(InputInterface $in, OutputInterface $out)
	{
		$filename = $in->getOption('out');
		if (!$filename) {
			// Prompt the user for an output file path
			$questionHelper = $this->getHelper('question');

			$defaultFilename = sprintf('./%1$s/qr-%2$s', self::DEFAULT_OUTPUT_DIR, date('Ymd_His'));
			$question = new Question(
				sprintf('Where should I save this code? <comment>[%s]</comment>', $defaultFilename),
				$defaultFilename
			);
			$filename = $questionHelper->ask($in, $out, $question);
		}

		// Remove extension
		$in->setOption('out', preg_replace('/\.[a-z0-9_\-]+$/i', '', trim($filename)));

		// Validate output types
		foreach ($in->getOption('format') as $format) {
			if (!array_key_exists($format, $this->outputTypes)) {
				throw new InvalidOptionException(sprintf('"%s" is not a valid output format.', $format));
			}
		}
	}

	protected function execute(InputInterface $in, OutputInterface $out)
	{
		$generator = new QrCode();
		$filePath = $in->getOption('out');

		$defaultOptions = [
			'version' => 5,
			'eccLevel' => QRCode::ECC_L,
		];

		foreach ($in->getOption('format') as $format) {
			$options = new QROptions(array_merge($defaultOptions, [
				'outputType' => $this->outputTypes[$format],
			]));
			$generator->setOptions($options);
			$rendered = $generator->render($in->getArgument('url'));
			$out->writeln(sprintf('Wrote %s.%s: %d bytes', $filePath, $format, strlen($rendered)));
		}
	}
}
