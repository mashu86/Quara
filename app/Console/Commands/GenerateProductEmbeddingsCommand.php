<?php

namespace App\Console\Commands;

use App\Services\VisualEmbeddingService;
use Illuminate\Console\Command;

class GenerateProductEmbeddingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visual-search:generate-embeddings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate vector embeddings for all product images in the catalog';

    /**
     * Execute the console command.
     */
    public function handle(VisualEmbeddingService $embeddingService): int
    {
        $this->info('Generating visual embeddings for product catalog images...');

        $stats = $embeddingService->generateEmbeddingsForCatalog();

        $this->info(sprintf(
            'Completed! Processed/Updated: %d, Failed/Skipped: %d',
            $stats['processed'],
            $stats['failed']
        ));

        return Command::SUCCESS;
    }
}
