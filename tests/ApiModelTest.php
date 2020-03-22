<?php


namespace Tests;


use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\Models\TestModel;
use Tests\Models\TestModelWithoutDataField;

class ApiModelTest extends TestCase
{
    /**
     * @var string
     */
    private $cachePath;

    public function setUp(): void
    {
        parent::setUp();
        config(['sushi.cache-path' => $this->cachePath = __DIR__ . '/cache']);
    }

    /** @test */
    public function can_read_from_single_page_api()
    {
        File::cleanDirectory(config('sushi.cache-path'));
        $this->assertFileNotExists('tests/cache/sushi-tests-models-test-model.sqlite');

        $this->createSinglePageHttpMockResponse();

        $model = new TestModel();
        $this->assertIsArray($model->getRows());
    }

    /** @test */
    public function can_read_from_multi_page_api()
    {
        File::cleanDirectory(config('sushi.cache-path'));
        $this->assertFileNotExists('tests/cache/sushi-tests-models-test-model.sqlite');

        $this->createTwoPagesHttpMockResponse();

        $this->assertCount(4, TestModel::all());
    }

    /** @test */
    public function can_read_from_single_page_api_without_data_field()
    {
        File::cleanDirectory(config('sushi.cache-path'));
        $this->assertFileNotExists('tests/cache/sushi-tests-models-test-model.sqlite');

        $this->createMockResponseWithDataInRoot();

        $model = new TestModelWithoutDataField();
        $this->assertIsArray($model->getRows());
    }

    /** @test */
    public function can_read_sub_arrays()
    {
        File::cleanDirectory(config('sushi.cache-path'));
        $this->assertFileNotExists('tests/cache/sushi-tests-models-test-model.sqlite');

        Http::fake([
            'http://test-endpoint.com' => Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'field' => [
                            'field_a' => 'bab',
                            'field_b' => 'cac',
                        ]
                    ]
                ]
            ], 200, ['Headers']),
        ]);

        $this->assertEquals([
            'field_a' => 'bab',
            'field_b' => 'cac',
        ], TestModel::first()->field);
    }

    /** @test */
    public function does_not_hit_endpoint_if_cache_exists()
    {
        $this->createSinglePageHttpMockResponse();

        $sheet = new TestModel();
        $this->assertFileExists(config('sushi.cache-path') . '/sushi-tests-models-test-model.sqlite');
        $this->assertStringContainsString(
            'tests/cache/sushi-tests-models-test-model.sqlite',
            $sheet->getConnection()->getDatabaseName()
        );
    }

    /** @test */
    public function can_do_basic_eloquent_stuff()
    {
        File::cleanDirectory(config('sushi.cache-path'));

        $this->createSinglePageHttpMockResponse();

        $model = TestModel::find(1);
        $this->assertEquals('foo', $model->field);

        $sheet = TestModel::where('field', 'bar')->first();
        $this->assertEquals('dax', $sheet->other_field);
    }

    /** @test */
    public function can_invalidate_cache()
    {
        $model = TestModel::find(1);
        $this->assertFileExists('tests/cache/sushi-tests-models-test-model.sqlite');
        $model->invalidateCache();
        $this->assertFileNotExists('tests/cache/sushi-tests-models-test-model.sqlite');
        $model = TestModel::find(2);
        $this->assertEquals('bar', $model->field);
    }

    private function createSinglePageHttpMockResponse()
    {
        Http::fake([
            'http://test-endpoint.com' => Http::response([
                'data' => [
                    ['id' => 1, 'field' => 'foo', 'other_field' => 'baz'],
                    ['id' => 2, 'field' => 'bar', 'other_field' => 'dax'],
                ]
            ], 200, ['Headers']),
        ]);
        Http::fake([
            'http://test-endpoint.com' => Http::response([
                'data' => [
                    ['id' => 1, 'field' => 'foo', 'other_field' => 'baz'],
                    ['id' => 2, 'field' => 'bar', 'other_field' => 'dax'],
                ]
            ], 200, ['Headers']),
        ]);
    }

    private function createTwoPagesHttpMockResponse()
    {
        $mockSequence = Http::sequence();

        $page1 = [
            'next_page_url' => 'http://test-endpoint.com/?page=2',
            'data' => [
                ['id' => 1, 'field' => 'foo', 'other_field' => 'baz'],
                ['id' => 2, 'field' => 'bar', 'other_field' => 'dax'],
            ]
        ];

        $page2 = [
            'data' => [
                ['id' => 3, 'field' => 'foo', 'other_field' => 'baz'],
                ['id' => 4, 'field' => 'bar', 'other_field' => 'dax'],
            ]
        ];

        $mockSequence->push($page1);
        $mockSequence->push($page2);


        Http::fake([
            'http://test-endpoint.com*' => $mockSequence
        ]);
    }

    private function createMockResponseWithDataInRoot()
    {
        Http::fake([
            'http://test-endpoint.com' => Http::response([
                ['id' => 1, 'field' => 'foo', 'other_field' => 'baz'],
                ['id' => 2, 'field' => 'bar', 'other_field' => 'dax'],
            ], 200, ['Headers']),
        ]);
    }
}