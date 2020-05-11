<?php

namespace Tests\Feature;

use App\Activity;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreateThreadsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function guests_may_not_create_threads()
    {
        $this->expectException('Illuminate\Auth\AuthenticationException');
        $thread = make('App\Thread');
        //composer dump-autoload

        $this->post('/threads', $thread->toArray());
    }

    /** @test */
    function an_authenticated_user_can_create_new_forum_threads()
    {
        // Given we have a signed in user
        $this->signIn();

        // When we hit the endpoint to create a new thread
        $thread = make('App\Thread');

        $response = $this->post('/threads', $thread->toArray());

        // Then, when we visit the thread page.
        $response = $this->get($response->headers->get('Location'));

        // We should see the new page
        $response->assertSee($thread->title)
            ->assertSee($thread->body);
    }

    /**
     * @expectedException Illuminate\Validation\ValidationException
     * @test */
    function a_thread_requires_a_title()
    {
        $this->publishThread(['title' => null])
            ->assertSessionHasErrors('title');
    }

    /**
     * @expectedException Illuminate\Validation\ValidationException
     * @test */
    function a_thread_requires_a_body()
    {
        $this->publishThread(['body' => null])
            ->assertSessionHasErrors('body');
    }
    
    /**
     * @expectedException Illuminate\Validation\ValidationException
     * @test */
    function a_thread_requires_a_valid_channel()
    {
        $this->publishThread(['channel_id' => null])
            ->assertSessionHasErrors('channel_id');
    }

    /**
     * @expectedException Illuminate\Auth\AuthenticationException
     * @test */
    function guests_may_not_delete_threads()
    {
        $thread = create('App\Thread');
//        $response = $this->json('DELETE', $thread->path());
        $this->delete($thread->path());
    }

    /**
     * @expectedException Illuminate\Auth\Access\AuthorizationException
     * @test */
    function unauthorized_users_may_not_delete_threads()
    {
        $thread = create('App\Thread');
        $this->signIn();
        $this->json('DELETE', $thread->path())
            ->assertStatus(403);
    }

    /** @test */
    function authorized_users_can_delete_threads()
    {
        $this->signIn();

        $thread = create('App\Thread', ['user_id' => auth()->id()]);
        $reply = create('App\Reply', ['thread_id' => $thread->id]);

        $response = $this->json('DELETE', $thread->path());

        $response->assertStatus(204);

        $this->assertDatabaseMissing('threads', ['id' => $thread->id]);
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);

        $this->assertEquals(0, Activity::count());
        // OR
        $this->assertDatabaseMissing('activities', [
            'subject_id' => $thread->id,
            'subject_type' => get_class($thread)
        ]);
        $this->assertDatabaseMissing('activities', [
            'subject_id' => $reply->id,
            'subject_type' => get_class($reply)
        ]);
    }

    public function publishThread($attributes)
    {
        $this->signIn();

        $thread = make('App\Thread', $attributes);

        return $this->post('/threads', $thread->toArray());
    }
    
    /**
     * @expectedException Illuminate\Auth\AuthenticationException
     * @test */
    function guests_cannot_see_the_create_thread_page()
    {
        $this->get('/threads/create')
            ->assertRedirect('/login');
    }
}
