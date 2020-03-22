<?php

namespace robertogallea\EloquentApi\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeApiModelCommand extends Command
{
    /** @var string */
    protected $signature = 'make:api-model';
    /** @var string */
    private $modelPath;
    /** @var string */
    private $endpoint;
    /** @var string */
    private $dataField;
    /** @var string */
    private $nextPageField;
    /** @var string */
    private $stub;
    /** @var string */
    private $modelNamespace = null;
    /** @var string */
    private $modelName;
    /** @var string */
    private $fullyQualfiedModelName;
    /** @var string */
    private $forgetUri;

    public function handle()
    {
        $this->modelPath = $this->ask('Where would you like to create your api model?', config('eloquent-api.app_path'));
        $this->modelName = $this->ask('What do you want the class name of your new model to be?');
        $this->endpoint = $this->ask('Copy and paste the full URL of your endpoint:');
        $this->dataField = $this->ask('If your data is wrapped into a resource field, type the name of the field (i.e. data):') ?? null;
        $this->nextPageField = $this->ask('If your resource is paginated, type the name of the field containing the next page url (i.e. next_page_url):') ?? null;
        $this->stub = $this->getStub();
        $this->modelPathToNamespace();
        if (is_null($this->modelNamespace)) {
            $this->modelNamespace = $this->ask('We were unable to determine the namespace you want to use for your model. Please provide it:');
        }
        $this->fullyQualfiedModelName = $this->modelNamespace.'\\'.$this->modelName;
        $this->modelPath .= '/'.$this->modelName.'.php';
        $this->calculateForgetUri();

        if (!$this->confirm('Ready to write model '.$this->fullyQualfiedModelName.' at '.$this->modelPath.'?')) {
            return;
        }
        File::put($this->modelPath, $this->makeSubstitutions());
        $this->line('Model created!');
    }

    protected function getStub()
    {
        return File::get(__DIR__.'/ApiModel.stub');
    }

    protected function makeSubstitutions()
    {
        return str_replace(
            ['API_MODEL_NAMESPACE', 'API_MODEL_NAME', 'ENDPOINT_URL', 'DATA_FIELD', 'NEXT_PAGE_FIELD'],
            [$this->modelNamespace, $this->modelName, $this->endpoint, empty($this->dataField) ? 'null' : "'{$this->dataField}'", empty($this->nextPageField) ? 'null' : "'{$this->nextPageField}'"],
            $this->stub
        );
    }

    protected function modelPathToNamespace(): void
    {
        if ($this->modelPath === app_path()) {
            $this->modelNamespace = 'App';

            return;
        }
        $diff = str_replace(app_path(), '', $this->modelPath);
        if ($diff != $this->modelPath) {
            $studlyArray = [];
            $diffArray = explode('\\', $diff);
            foreach ($diffArray as $part) {
                array_push($studlyArray, Str::studly($part));
            }
            $this->modelNamespace = implode('\\', $studlyArray);

            return;
        }
    }

    protected function calculateForgetUri()
    {
        $this->forgetUri = '/eloquent_sheets_forget/sushi-'.Str::kebab(stripslashes($this->fullyQualfiedModelName));
    }
}