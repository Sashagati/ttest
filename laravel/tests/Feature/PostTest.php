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
  /**
   * @test
   */
  public function _post_can_be_stored()
  {


      $this->withoutExceptionHandling();

      Storage::fake('local');


      $file = File::create('my_image.jpg');



      $data = [
          'title'=>'Some title',
          'description'=>'description',
          'image'=>$file,


      ];



      $res = $this->post('/posts',$data);

      $res->assertStatus(200);

      $this->assertDatabaseCount('posts',1);

      $post = Post ::first();

      $this->assertEquals($data['title'],$post->title);
      $this->assertEquals($data['description'],$post->description);

      $this->assertEquals('images/' . $file->hashName(),$post->image_url);

      Storage::disk('local')->assertExists($post->image_url);

  }
    /**
     * @test
     */
    public function attribute_title_is_required_for_storing_post()
    {

        $data = [
            'title'=>'',
            'description'=>'description',
            'image'=>''

        ];

        $res = $this->post('/posts',$data);
        $res->assertRedirect();
        $res->assertInvalid('title');


    }


}
