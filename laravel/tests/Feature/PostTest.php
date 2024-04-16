<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Post;
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


        $res = $this->post('/posts', $data);

        $res->assertStatus(200);

        $this->assertDatabaseCount('posts', 1);

        $post = Post::first();

        $this->assertEquals($data['title'], $post->title);
        $this->assertEquals($data['description'], $post->description);
        $this->assertEquals('images/' . $file->hashName(), $post->image_url);

        Storage::disk('local')->assertExists($post->image_url);

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

        $res = $this->post('/posts', $data);
        $res->assertRedirect();
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
        $res->assertRedirect();
        $res->assertInvalid('image');
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
        $res =  $this->patch('/posts/' . $post->id, $data);

        $res->assertStatus(200);

        $updatePost = Post::first();
        $this->assertEquals($data['title'], $updatePost->title);
        $this->assertEquals($data['description'], $updatePost->description);
        $this->assertEquals('images/' . $file->hashName(), $updatePost->image_url);

        $this->assertEquals($post->id, $updatePost->id);
    }
    /**
     * @test
     */
    public function response_for_route_posts_index_is_view_post_index_with_post()
    {
        $this->withoutExceptionHandling();

        $posts = Post::factory()->count(10)->create();

        $response = $this->get('/posts');

        $response->assertViewIs('posts.index');

        $response->assertSeeText('View page');

        $titles = $posts->pluck('title')->toArray();

        $response->assertSeeText($titles);

    }
    /**
     * @test
     */
    public function response_for_route_posts_show_is_view_post_show_with_single_post()
    {
        $this->withoutExceptionHandling();
        $post = Post::factory()->create();

        $response = $this->get('/posts/' . $post->id);

        $response->assertViewIs('posts.show');
        $response->assertSeeText('Show page');
        $response->assertSeeText($post->title);
        $response->assertSeeText($post->description);

    }












}
