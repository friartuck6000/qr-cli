<?php

namespace Tuckerrr\Qr;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QrCommand extends Command
{
	protected function configure()
	{
		$this->setName('create');
	}

	protected function execute(InputInterface $in, OutputInterface $out)
	{}
}
