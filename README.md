# imgplunk

This is a simple public taggad pastebin for images written with PHP using the Laravel web framework.

Files are stored by their sha256 hash to prevent storage duplication, and additional tags can be added to existing files.

## Configuration

### Requirements
    - PHP 7
    - [composer](https://getcomposer.org/)
    - [Laravel PHP requirements](https://laravel.com/docs/5.7/installation#server-requirements)
    - sqlite

### Usage

#### Uploading a file

Request

```sh
$ curl -F "tags={\"tags\": [\"tag1\"]}" -F "image=@/home/conrad/images/rose_gold_glenda.png" localhost:8000/api/image 
```

Response

```json
{"url":"http:\/\/localhost:8000\/storage\/c6b614708925deac60ea1c3c273d4c4e9f64ddac4d77064fbc51080ecbd0dacb.png","tags":["tag1"]}
```

#### Fetching files by tags

Request

```
$ curl -d "tags={\"tags\": [\"tag1\"]}" localhost:8000/api/image
```

Response

```json
{"urls":["http:\/\/localhost:8000\/storage\/c6b614708925deac60ea1c3c273d4c4e9f64ddac4d77064fbc51080ecbd0dacb.png"],"tags":["tag1"]}
```

#### Retrieving a file by tags

### Setup

1. Clone and enter the repository folder

```
$ git clone https://github.com/clukawski/imgplunk
$ cd imgplunk
```

2. Configure app

Here you can set the app url, and override the DB configuration

```
$ cp .env.example .env
$ nano .env
$ composer install
$ php artisan key:generate
```

3. Setup the database and file storage

By default, sqlite is used, but the db backend can be configured in `.env` or `config/database.php`)

```
$ touch database/database.sqlite
$ php artisan migrate
$ php artisan storage:link
```

4. Serve

Laravel can be served locally via:

```
$ php artisan serve
```

By default this will serve to http://localhost:8000

Alternatively Apache/Nginx configuration is relatively simple, and is [documented here](https://laravel.com/docs/5.7#web-server-configuration).
