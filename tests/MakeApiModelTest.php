<?php

namespace Tests;

use Illuminate\Support\Facades\File;

class MakeApiModelTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (File::exists(__DIR__.'/Models/MyApiModel.php')) {
            File::delete(__DIR__.'/Models/MyApiModel.php');
        }
        config(['sushi.cache-path' => $this->cachePath = __DIR__.'/cache']);
    }

    /** @test */
    public function it_creates_a_sheet_model_without_committing_any_crimes()
    {
        $this->artisan('make:api-model')
            ->expectsQuestion('Where would you like to create your api model?', __DIR__.'/Models')
            ->expectsQuestion('What do you want the class name of your new model to be?', 'MyApiModel')
            ->expectsQuestion('Copy and paste the full URL of your endpoint:', 'https://some-endpoint')
            ->expectsQuestion('If your data is wrapped into a resource field, type the name of the field (i.e. data):', 'data')
            ->expectsQuestion('If your resource is paginated, type the name of the field containing the next page url (i.e. next_page_url):', '')
            ->expectsQuestion('We were unable to determine the namespace you want to use for your model. Please provide it:', 'Tests\Models')
            ->expectsQuestion('Ready to write model Tests\\Models\\MyApiModel at '.__DIR__.'/Models/MyApiModel.php'.'?', 'yes')
            ->assertExitCode(0);

        $this->assertFileExists(__DIR__.'/Models/MyApiModel.php');
    }
}