<?php

namespace Test\Feature\Api;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostTest extends TestCase
{

    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->withHeaders([
            'Accept' => 'application/json',
        ]);

    }


    /**
     * @test
     */
    public function _post_can_be_stored()
    {


        $this->withoutExceptionHandling();




        $file = File::create('my_image.jpg');


        $data = [
            'title' => 'Some title',
            'description' => 'description',
            'image' => $file,


        ];


        $res = $this->post('/api/posts', $data);


        $this->assertDatabaseCount('posts', 1);

        $post = Post::first();

        $this->assertEquals($data['title'], $post->title);
        $this->assertEquals($data['description'], $post->description);
        $this->assertEquals('images/' . $file->hashName(), $post->image_url);

        Storage::disk('local')->assertExists($post->image_url);

        $res->assertJson([
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'image_url' => $post->image_url





        ]);

    }
    /**
     * @test
     */
    public function attribute_title_is_required_for_storing_post()
    {

        $data = [
            'title' => '',
            'description' => 'description',
            'image' => ''

        ];

        $res = $this->post('/api/posts', $data);
        $res->assertStatus(422);
        $res->assertInvalid('title');
    }


    /**
     * @test
     */
    public function attribute_image_is_file_for_storing_post()
    {

        $file = File::create('my_image.jpg');

        $data = [
            'title' => 'Title',
            'description' => 'description',
            'image' => 'sdfsdf'

        ];

        $res = $this->post('/posts', $data);
        $res->assertStatus(422);
        $res->assertInvalid('image');
        $res->assertJsonValidationErrors([

            'image' => 'The image field must be a file.'
        ]);
    }

    /**
     * @test
     */
    public function a_post_can_be_updated()
    {
        $this->withoutExceptionHandling();

        $post = Post::factory()->create();
        $file = File::create('image.jpg');

        $data = [
            'title' => 'Title edited',
            'description' => 'description edited',
            'image' => $file

        ];
        $res =  $this->patch('/api/posts/' . $post->id, $data);

        $res->assertJson([
            'id' => $post->id,
            'title' => $data ['title'],
            'description' => $data['description'],
            'image_url' => 'images/' . $file->hashName(),
            ]);

    }
    /**
     * @test
     */
    public function response_for_route_posts_index_is_view_post_index_with_post()
    {
        $this->withoutExceptionHandling();

        $posts = Post::factory()->count(10)->create();

        $response = $this->get('/api/posts');
        $response->assertOk();
        $json = $posts->map(function ($post) {
            return[
                'id' => $post->id,
                'title' => $post->title,
                'description' => $post->description,
                'image_url' => $post->image_url,
            ];


        })->toArray();

        $response->assertExactJson($json);

    }

    /**
     * @test
     */
    public function response_for_route_posts_show_is_view_post_show_with_single_post()
    {
        $this->withoutExceptionHandling();
        $post = Post::factory()->create();

        $response = $this->get('/api/posts/' . $post->id);

        $response->assertJson([
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'image_url' => $post->image_url

        ]);
    }
    /**
     * @test
     */
    public function a_post_can_be_deleted_by_auth_user()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $post = Post::factory()->create();

        $res = $this->ActingAs($user)->delete('/api/posts/' . $post->id);

        $res->assertOk();

        $this->assertDatabaseCount('posts', 0);

        $res->assertJson([
            'message' => 'deleted'
        ]);


    }
    /**
     * @test
     */
    public function a_post_can_be_deleted_by_only_auth_user()
    {


        $post = Post::factory()->create();
        $res = $this->delete('/api/posts/' . $post->id);

        $res->assertUnauthorized();

        $this->assertDatabaseCount('posts', 1);

    }

}
