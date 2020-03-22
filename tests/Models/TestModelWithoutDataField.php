<?php


namespace Tests\Models;


use robertogallea\EloquentApi\ApiModel;

class TestModelWithoutDataField extends TestModel
{
    protected $dataField = null;
}