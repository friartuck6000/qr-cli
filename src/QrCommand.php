<?php

namespace Tuckerrr\Qr;

use chillerlan\QRCode\Output\QRImage;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Helper\QuestionHelper;
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
			)
			->addOption(
				'scale',
				null,
				InputOption::VALUE_REQUIRED,
				'Output scale for raster formats; the size of a single block in pixels.',
				10
			);
	}

	protected function interact(InputInterface $in, OutputInterface $out)
	{
		$url = $in->getArgument('url');
		if (!$url) {
			// Prompt the user for an output file path
			/** @var QuestionHelper $questionHelper */
			$questionHelper = $this->getHelper('question');

			$question = new Question('What URL should I encode? ', null);
			do {
				$url = $questionHelper->ask($in, $out, $question);
			} while ($url === null);
		}

		if (filter_var($url, FILTER_VALIDATE_URL) === false) {
			throw new InvalidArgumentException(sprintf('Invalid URL "%s".', $url));
		}
		$in->setArgument('url', $url);

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
			$fullPath = sprintf('%s.%s', $filePath, $format);
			$file = new \SplFileObject($fullPath, 'w');

			$outputType = $this->outputTypes[$format];
			$optionData = [
				'outputType' => $outputType,
			];
			// Update module values for image output types
			if (in_array($outputType, QRCode::OUTPUT_MODES[QRImage::class])) {
				$optionData['imageBase64'] = false;
				$optionData['scale'] = $in->getOption('scale');
			}

			$options = new QROptions(array_merge($defaultOptions, $optionData));
			$generator->setOptions($options);

			$bytesWritten = $file->fwrite($generator->render($in->getArgument('url')));
			$out->writeln(sprintf('Wrote %d bytes to <info>%s</info>', $bytesWritten, $fullPath));
			$file = null;
		}
	}
}
