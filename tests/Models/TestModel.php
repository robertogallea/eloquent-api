<?php


namespace Tests\Models;


use robertogallea\EloquentApi\ApiModel;

class TestModel extends ApiModel
{
    protected $endpoint = 'http://test-endpoint.com';
    protected $nextPageField = 'next_page_url';
    protected $dataField = 'data';
}