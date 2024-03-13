<?php

namespace App\Command;

use App\Repository\DestinationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'app:export:destinations',
    description: 'Export destinations as CSV file',
)]
class ExportDestinationsCommand extends Command
{
    private SerializerInterface $serializer;
    private DestinationRepository $destinationRepository;

    public function __construct(SerializerInterface $serializer, DestinationRepository $destinationRepository)
    {
        parent::__construct();
        $this->serializer = $serializer;
        $this->destinationRepository = $destinationRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Retrieve all destinations from the repository
        $destinations = $this->destinationRepository->findAll();

        // Prepare CSV data
        $csvData = [];
        foreach ($destinations as $destination) {
            // Populate data for each destination
            $csvData[] = [
                'Name' => $destination->getName(),
                'Description' => $destination->getDescription(),
                'Price' => $destination->getPrice(),
                'Duration' => $destination->getDuration(),
            ];
        }

        // Serialize CSV data
        $csv = $this->serializer->encode($csvData, 'csv', [
            'csv_delimiter' => ';',
            'csv_headers' => ['Name', 'Description', 'Price', 'Duration'],
        ]);

        // Define filename for the CSV file
        $filename = 'destinations.csv';

        // Write CSV data to the file
        file_put_contents($filename, $csv);

        // Output success message
        $output->writeln(sprintf('CSV file "%s" has been generated.', $filename));

        return Command::SUCCESS;
    }
}
