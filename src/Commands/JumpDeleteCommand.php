<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class JumpDeleteCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('jump:delete')
            ->addArgument('jump', InputArgument::REQUIRED, 'The jumpbox name / ID')
            ->setDescription('Delete a jumpbox');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if (! Helpers::confirm('Are you sure you want to delete this jumpbox', false)) {
            Helpers::abort('Action cancelled.');
        }

        [$jumpBoxId, $jumpBoxName] = $this->determineJumpBox();

        if (is_null($jumpBoxId)) {
            Helpers::abort('Unable to find a jumpbox with that name / ID.');
        }

        $this->vapor->deleteJumpBox($jumpBoxId);

        Helpers::info('Jumpbox deleted successfully.');

        if (file_exists($path = Helpers::home().'/.ssh/vapor-jump-'.$jumpBoxName)) {
            @unlink($path);
        }
    }

    /**
     * Determine the jump-box that should be deleted.
     *
     * @return array
     */
    protected function determineJumpBox()
    {
        $jumpBoxes = $this->vapor->jumpBoxes();

        if (! is_numeric($jumpBoxId = $this->argument('jump'))) {
            $jumpBoxName = $jumpBoxId;

            $jumpBoxId = $this->findIdByName($jumpBoxes, $jumpBoxId);
        } else {
            $jumpBoxName = collect($jumpBoxes)->first(function ($jumpBox) use ($jumpBoxId) {
                return $jumpBox['id'] == $jumpBoxId;
            })['name'];
        }

        return [$jumpBoxId, $jumpBoxName];
    }
}
