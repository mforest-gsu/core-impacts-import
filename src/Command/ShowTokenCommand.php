<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\Command;

use Gadget\Console\Command\Command;
use Gadget\Http\Client\Client;
use Gadget\Io\JSON;
use Gadget\Oauth\Model\Token;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:show-token')]
final class ShowTokenCommand extends Command
{
    /**
     * @param Client $client
     */
    public function __construct(private Client $client)
    {
        parent::__construct();
    }


    /** @inheritdoc */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $cache = $this->client->getCache()->getObject('token', Token::class);
        $output->writeln(JSON::encode($cache, JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
