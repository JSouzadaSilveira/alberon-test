<?php

namespace App\Jobs;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;
use League\Csv\Statement;

class ProcessCsvImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $headers;
    protected $summary = [
        'totalRows' => 0,
        'importedRows' => 0,
        'duplicateRows' => 0,
        'invalidRows' => 0
    ];

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, array $headers)
    {
        $this->filePath = $filePath;
        $this->headers = explode(';', $headers[0]);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Iniciando processamento do CSV:', [
                'filePath' => $this->filePath,
                'headers' => $this->headers
            ]);

            if (!Storage::exists($this->filePath)) {
                throw new \Exception("Arquivo CSV não encontrado: {$this->filePath}");
            }

            $csvPath = Storage::path($this->filePath);

            $content = file_get_contents($csvPath);

            $content = str_replace("\xEF\xBB\xBF", '', $content);

            $csv = Reader::createFromString($content);
            $csv->setDelimiter(';');

            $records = iterator_to_array($csv->getRecords());
            array_shift($records);

            $this->summary['totalRows'] = count($records);
            foreach ($records as $record) {
                $values = is_array($record) ? array_values($record) : explode(';', $record[0]);

                if (count($values) !== count($this->headers)) {
                    Log::error('Número incorreto de campos:', [
                        'esperado' => count($this->headers),
                        'recebido' => count($values),
                        'valores' => $values
                    ]);
                    $this->summary['invalidRows']++;
                    continue;
                }

                $data = array_combine($this->headers, $values);

                Log::info('Registro:', ['data' => $data]);

                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|max:255',
                    'phone' => 'required|string|max:20',
                    'birthdate' => 'required|date',
                ]);

                if ($validator->fails()) {
                    $this->summary['invalidRows']++;
                    Log::info('Registro inválido:', [
                        'data' => $data,
                        'errors' => $validator->errors()->toArray()
                    ]);
                    continue;
                }

                if (Contact::where('email', $data['email'])->exists()) {
                    $this->summary['duplicateRows']++;
                    Log::info('Email duplicado:', ['email' => $data['email']]);
                    continue;
                }

                try {
                    Contact::create($data);
                    $this->summary['importedRows']++;

                    if ($this->summary['importedRows'] % 100 === 0) {
                        Cache::put('import_summary', $this->summary);
                    }
                } catch (\Exception $e) {
                    Log::error('Erro ao criar contato:', [
                        'data' => $data,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            }

            Cache::put('import_summary', $this->summary);

            Log::info('Importação concluída:', $this->summary);

        } catch (\Exception $e) {
            Log::error('Erro ao processar CSV: ' . $e->getMessage());
            throw $e;
        } finally {
            Storage::delete($this->filePath);
        }
    }
}
