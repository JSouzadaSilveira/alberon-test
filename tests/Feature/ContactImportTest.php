<?php

namespace Tests\Feature;

use App\Jobs\ProcessCsvImport;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContactImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_csv_file()
    {
        Queue::fake();
        Storage::fake('local');

        $user = User::factory()->create();

        $csvContent = "name,email,phone,birthdate\n";
        $csvContent .= "John Doe,john@example.com,1234567890,1990-01-01\n";

        $file = UploadedFile::fake()->createWithContent(
            'contacts.csv',
            $csvContent
        );

        $response = $this->actingAs($user)
            ->post('/contacts/import', [
                'file' => $file
            ]);

        $response->assertRedirect();
        Queue::assertPushed(ProcessCsvImport::class);
    }

    public function test_duplicate_emails_are_ignored()
    {
        Contact::factory()->create([
            'email' => 'john@example.com'
        ]);

        $headers = ['name', 'email', 'phone', 'birthdate'];
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'birthdate' => '1990-01-01'
        ];

        $job = new ProcessCsvImport('test.csv', $headers);
        $job->handle();

        $this->assertEquals(1, Contact::where('email', 'john@example.com')->count());
    }

    public function test_invalid_data_is_not_imported()
    {
        $headers = ['name', 'email', 'phone', 'birthdate'];
        $data = [
            'name' => '', // Invalid: empty name
            'email' => 'not-an-email',
            'phone' => '1234567890',
            'birthdate' => 'invalid-date'
        ];

        $job = new ProcessCsvImport('test.csv', $headers);
        $job->handle();

        $this->assertEquals(0, Contact::count());
    }
}
