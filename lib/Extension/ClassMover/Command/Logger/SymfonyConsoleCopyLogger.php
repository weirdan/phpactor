<?php

namespace Phpactor\Extension\ClassMover\Command\Logger;

use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\ClassMover\FoundReferences;
use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\Extension\ClassMover\Application\Logger\ClassCopyLogger;

class SymfonyConsoleCopyLogger implements ClassCopyLogger
{
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function copying(FilePath $srcPath, FilePath $destPath)
    {
        $this->output->writeln(sprintf(
            '<info>[COPY]</info> %s <comment>=></> %s',
            $srcPath->path(),
            $destPath->path()
        ));
    }

    public function replacing(FilePath $path, FoundReferences $references, FullyQualifiedName $replacementName)
    {
        if ($references->references()->isEmpty()) {
            return;
        }

        $this->output->writeln('<info>[REPL]</> <comment>'.$path.'</>');

        foreach ($references->references() as $reference) {
            $this->output->writeln(sprintf(
                '       %s:%s %s <comment>=></> %s',
                $reference->position()->start(),
                $reference->position()->end(),
                (string) $reference->name(),
                (string) $reference->name()->transpose($replacementName)
            ));
        }
    }
}
