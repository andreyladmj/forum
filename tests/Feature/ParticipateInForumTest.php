<?php

namespace Tests\Feature;

use App\Reply;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ParticipateInForumTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * expectedException app\Exceptions\SomeException
     */
    function unauthenticated_user_may_not_add_replies()
    {
        $this->expectException('Illuminate\Auth\AuthenticationException');

        $this->post('/threads/some-channel/1/replies', []);
    }

    /** @test */
    function an_authenticated_user_may_participate_in_forum_threads()
    {
        // Given we have a authenticated user
        // And an existing thread
        // When the user adds a reply to the thread
        // Then their reply should be visible on the page

        $this->be(create('App\User'));

        $thread = create('App\Thread');

        $reply = make('App\Reply');

        //if(app()->environment() === 'testing') throw $exception;
        //added to handler because it not throw exception on non existing method
        $this->post($thread->path().'/replies', $reply->toArray());

        $this->get($thread->path())
            ->assertSee($reply->body);
    }

    /**
     * @expectedException Illuminate\Validation\ValidationException
     * @test */
    function a_reply_requires_a_body()
    {
        $this->signIn();

        $thread = create('App\Thread');
        $reply = make('App\Reply', ['body' => null]);

        $this->post($thread->path().'/replies', $reply->toArray())
            ->assertSessionHasErrors('title');
    }

    /**
     * @expectedException Illuminate\Auth\AuthenticationException
     * @test */
    function unauthorized_users_cannot_delete_replies()
    {
        $reply = create(Reply::class);

        $this->delete("/replies/{$reply->id}");
    }

    /**
     * @expectedException Illuminate\Auth\Access\AuthorizationException
     * @test */
    function another_user_cannot_delete_replies()
    {
        $reply = create(Reply::class);

        $this->signIn()
            ->delete("/replies/{$reply->id}")
            ->assertStatus(403);
    }

    /** @test */
    function authorized_users_can_delete_replies()
    {
        $this->signIn();
        $reply = create(Reply::class, ['user_id' => auth()->id()]);

        $this->delete("/replies/{$reply->id}")->assertStatus(302);

        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
    }

    /** @test */
    function authorized_users_can_update_replies()
    {
        $this->signIn();
        $reply = create(Reply::class, ['user_id' => auth()->id()]);
        $body = 'You been changed, fool.';

        $this->patch("/replies/{$reply->id}", ['body' => $body]);

        $this->assertDatabaseHas('replies', ['id' => $reply->id, 'body' => $body]);
    }

    /**
     * @expectedException Illuminate\Auth\AuthenticationException
     * @test */
    function unauthorized_users_cannot_update_replies()
    {
        $reply = create(Reply::class);
        $body = 'You been changed, fool.';

        $this->patch("/replies/{$reply->id}", ['body' => $body]);
    }

    /**
     * @expectedException Illuminate\Auth\Access\AuthorizationException
     * @test */
    function another_user_cannot_update_replies()
    {
        $reply = create(Reply::class);
        $body = 'You been changed, fool.';

        $this->signIn()
            ->patch("/replies/{$reply->id}", ['body' => $body])
            ->assertStatus(403);
    }
}
