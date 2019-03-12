<?php

namespace App\Command;

use App\Entity\Quote;
use App\Entity\Author;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function fclose;
use function fgetcsv;
use function file_exists;
use function fopen;
use function is_readable;
use const PHP_EOL;

final class ImportQuotesCommand extends Command
{
    private $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
    }

    protected function configure()
    {
        $this
            ->setName('quotes:import')
            ->addArgument('file', InputArgument::REQUIRED, 'Location of the CSV-file to read quotes from.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('file');
        $io = new SymfonyStyle($input, $output);
        $io->title('Quotes Importer');
        $io->text([
            'Reads a CSV file and imports the contained quotes into our database.',
            'This command will alter your database! Please be careful when using it in production.',
        ]);
        if(!file_exists($filename) || !is_readable($filename)) {
            $io->error(sprintf('The provided filename "%s" is not readable!', $filename));
            return 1;
        }
        /** @var EntityManager $em */
        $em = $this->doctrine->getManagerForClass(Quote::class);
        $authorRepository = $this->doctrine->getRepository(Author::class);
        $handle = fopen($filename, 'rb');
        $io->newLine();
        $name = '.';

        while (false !== ($row = fgetcsv($handle, 1000, ";"))  )
        {
            if($authorRepository->findOneBy(['fullName' => $row[1]]) !== null)
            {
                $author = $authorRepository->findOneBy(['fullName' => $row[1]]);
            } else {
                $author = new Author();
                $author->setFullName($row[1]);
                $emAuthor = $this->doctrine->getManagerForClass(Author::class);
                $emAuthor->persist($author);
                $emAuthor->flush();
            }

            $quote = new Quote();
            $quote->setText($row[0])
                  ->setAuthor($author);

            if ($io->isVerbose()) {
                $name = (string) $quote . PHP_EOL;
            }
            $io->write($name);
            $em->persist($quote);
        }
        fclose($handle);
        $em->flush();
        $io->newLine();
        $io->success('Finished importing Quotes.');
        return 0;
    }
}