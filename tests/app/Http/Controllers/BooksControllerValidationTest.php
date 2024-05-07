<?php

namespace Tests\App\Http\Controllers;

use Tests\TestCase;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Models\Book;

class BooksControllerValidationTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function it_validates_required_fields_when_creating_a_new_book()
    {
        $this->post('/books', [], ['Accept' => 'application/json']);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->response->getStatusCode());

        $body = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('title', $body);
        $this->assertArrayHasKey('description', $body);
        $this->assertArrayHasKey('author', $body);

        $this->assertEquals(["The title field is required."], $body['title']);
        $this->assertEquals(
            ["Please provide a description."],
            $body['description']
        );
        $this->assertEquals(["The author field is required."], $body['author']);
    }

    /** @test **/
    public function it_validates_validates_passed_fields_when_updating_a_book()
    {
        // $book = factory(\App\Book::class)->create();
        $book = Book::factory()->create();

        $this->put("/books/{$book->id}", [], ['Accept' => 'application/json']);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->response->getStatusCode());

        $body = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('title', $body);
        $this->assertArrayHasKey('description', $body);
        $this->assertArrayHasKey('author', $body);

        $this->assertEquals(["The title field is required."], $body['title']);
        $this->assertEquals(
            ["Please provide a description."],
            $body['description']
        );
        $this->assertEquals(["The author field is required."], $body['author']);
    }

    /** @test **/
public function title_fails_create_validation_when_just_too_long()
{
    // Creating a book
    $book = Book::factory()->create();
    $book->title = str_repeat('a', 256);

    $this->post("/books", [
        'title' => $book->title,
        'description' => $book->description,
        'author' => $book->author,
    ], ['Accept' => 'application/json']);

    $this
        ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->seeJson([
            'title' => ["The title must not be greater than 255 characters."] // Adjusted assertion
        ])
        ->notSeeInDatabase('books', ['title' => $book->title]);
}

/** @test **/
public function title_fails_update_validation_when_just_too_long()
{
    // Updating a book
    $book = Book::factory()->create(); 
    $book->title = str_repeat('a', 256);

    $this->put("/books/{$book->id}", [
        'title' => $book->title,
        'description' => $book->description,
        'author' => $book->author
    ], ['Accept' => 'application/json']);

    $this
        ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->seeJson([
            'title' => ["The title must not be greater than 255 characters."] // Adjusted assertion
        ])
        ->notSeeInDatabase('books', ['title' => $book->title]);
}

    /** @test **/
    public function title_passes_create_validation_when_exactly_max()
    {
        // Creating a new Book
        // $book = factory(\App\Book::class)->make();
        $book = Book::factory()->create(); 
        $book->title = str_repeat('a', 255);

        $this->post("/books", [
            'title' => $book->title,
            'description' => $book->description,
            'author' => $book->author,
        ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_CREATED)
            ->seeInDatabase('books', ['title' => $book->title]);
    }

    /** @test **/
    public function title_passes_update_validation_when_exactly_max()
    {
        // Updating a book
        // $book = factory(\App\Book::class)->create();
        $book = Book::factory()->create(); 
        $book->title = str_repeat('a', 255);

        $this->put("/books/{$book->id}", [
            'title' => $book->title,
            'description' => $book->description,
            'author' => $book->author
        ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_OK)
            ->seeInDatabase('books', ['title' => $book->title]);
    }
}