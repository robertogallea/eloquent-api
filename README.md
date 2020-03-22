# Eloquent Models for json APIs
### A package that lets you lay Eloquent on top of a json web API.

This package provides an Eloquent model that sits on top of a json web API endpoint.

When you use this package, an initial invocation of the model will make one or more requests to an HTTP endpoint, data 
are read and converted in an array of rows. Each row is stored as a record in a table inside a file-based sqlite 
database. Subsequent invocations of the model use that sqlite database so changes underlying data won't be reflected in 
the database. However, there are two ways that you can invalidate the sqlite cache and cause it to be recreated:

1. You can call the `invalidateCache()` method on the model with something like 
`YourApiModel::first()->invalidateCache()` 

2. By sending a request to a route provided by this package that deletes the sqlite database forcing a fresh load the 
next time the model is used.

### Installation
```shell script
composer require robertogallea/eloquent-api
```  

### Usage
Consider the following JSON response, provided by the endpoint `https://ghibliapi.herokuapp.com/films` 

```json
[
  {
    "id": "2baf70d1-42bb-4437-b551-e5fed5a87abe",
    "title": "Castle in the Sky",
    "description": "The orphan Sheeta inherited a mysterious crystal that links her to the mythical sky-kingdom of Laputa. With the help of resourceful Pazu and a rollicking band of sky pirates, she makes her way to the ruins of the once-great civilization. Sheeta and Pazu must outwit the evil Muska, who plans to use Laputa's science to make himself ruler of the world.",
    "director": "Hayao Miyazaki",
    "producer": "Isao Takahata",
    "release_date": "1986",
    "rt_score": "95",
    "people": [
        "https://ghibliapi.herokuapp.com/people/"
    ],
    "species": [
        "https://ghibliapi.herokuapp.com/species/af3910a6-429f-4c74-9ad5-dfe1c4aa04f2"
    ],
    "locations": [
        "https://ghibliapi.herokuapp.com/locations/"
    ],
    "vehicles": [
      "https://ghibliapi.herokuapp.com/vehicles/"
    ],
    "url": "https://ghibliapi.herokuapp.com/films/2baf70d1-42bb-4437-b551-e5fed5a87abe"
  },
  {
    "...": "..."
  }
]
```

We want to lay an Eloquent model on top of it.

```shell script
php artisan make:api-model
``` 
**Step 1 - Enter the full path to the directory where you want to create the model file (defaults to `app_path()`):**

---

**Step 2 - Enter the name you want to use for your model class:**

---

**Step 3 - Paste the endpoint of your resource:** 

---

**Step 4 - If your data is wrapped in a particular field, type its name:** 

---

**Step 5 - If your resource is paginated, type the json field containing the url of the next page:** 

---

**Step 6 - Confirm that the path and full classname look right:**

### The Resulting Model Class

```php
use robertogallea\EloquentApi\ApiModel;

class YourApiModel extends ApiModel
{
    protected $endpoint = 'https://ghibliapi.herokuapp.com/films'; // The endpoint
    protected $nextPageField = null;
    protected $dataField = null;
}
```

### Dealing with wrapped resources
If your resource data is wrapped into a particular json key, you need to tell your model, what this key is, by setting 
the `$dataField` model attribute. For example

```php
protected $dataField = 'data';
```

### Dealing with paginated data
If your endpoint provides paginated data, the package support multiple page fetching. In this case all you need to do, 
is setting the `$nextPageField` attribute as a string containing the name of the json field containing the next page 
link, for example
```php
protected $nextPageField = 'next_page_url';
```

This model can do your basic Eloquent model stuff because it really is an Eloquent model. Though it's currently limited 
to read / list methods. Update and insert don't currently work.

# Issues, Questions and Pull Requests
You can report issues and ask questions in the 
[issues section](https://github.com/robertogallea/eloquent-api/issues).
Please start your issue with ISSUE: and your question 
with QUESTION:

If you have a question, check the closed issues first. Over time, I've been able to answer quite a few.

To submit a Pull Request, please fork this repository, create a new branch and commit your new/updated code in there. 
Then open a Pull Request from your new branch. Refer to 
[this guide](https://help.github.com/articles/about-pull-requests/) for more info.
