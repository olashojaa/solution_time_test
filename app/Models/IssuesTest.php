<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Issue;
use App\Models\Newsletter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IssuesTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  public function test_user_can_save_his_issues()
  {
    $this->actingAs($user);
    $user = User::factory()->create();

    // Auth::login($user);

    $Issue = $user->newsletter()->first()
      ->issues()
      ->create();
    // $this->withoutExceptionHandling();
    $response = $this
      ->json('patch', "api/issues/$Issue->id", [
        'subject' => 'test'
      ])
      ->assertStatus(200);
  }

  public function test_user_can_not_save_other_issues()
  {
    $user = User::factory()->create();

    $newsletter = Newsletter::factory()->create();
    $Issue = $newsletter
      ->issues()
      ->create();

    Auth::login($user);

    $response = $this
      ->json('patch', "api/issues/$Issue->id", [
        'subject' => 'test'
      ])
      ->assertStatus(403);
  }

  public function test_user_can_save_subscribed_issues()
  {
    $user = User::factory()->create();
    $newsletter = Newsletter::factory()->create();

    Auth::login($user);
    //make user editor in newsletter
    $link = $user->allNewsletters()->attach($newsletter->id, ['role' => 'Editor']);
    $Issue = $newsletter->issues()
      ->create();
    // $this->withoutExceptionHandling();
    $response = $this
      ->json('patch', "api/issues/$Issue->id", [
        'subject' => 'test'
      ])
      ->assertStatus(200);
  }

  public function test_user_can_add_issue_to_subscribed_newsletter()
  {
    $user = User::factory()->create();
    $newsletter = Newsletter::factory()->create();
    Auth::login($user);
    //make user editor in newsletter
    $user->allNewsletters()->attach($newsletter->id, ['role' => 'Editor']);
    //set current newsletter
    $this->json('post', "api/newsletter/current/$newsletter->id");
    // $this->withoutExceptionHandling();
    //add issue
    $response = $this
      ->json('post', "api/issues")
      ->assertStatus(201)->getContent();
    $newsletter_id = json_decode($response)->newsletter_id;
    //ensure that issue add in $newsletter
    $this->assertEquals($newsletter_id, $newsletter->id);
  }

  public function test_user_can_not_add_issue_to_other_newsletter()
  {
    $user = User::factory()->create();
    $newsletter = Newsletter::factory()->create();
    Auth::login($user);

    //set current newsletter
    $this->json('post', "api/newsletter/current/$newsletter->id");
    // $this->withoutExceptionHandling();
    //add issue
    $response = $this
      ->json('post', "api/issues")
      ->assertStatus(201)->getContent();
    $newsletter_id = json_decode($response)->newsletter_id;
    //ensure that issue add in $newsletter
    $this->assertNotEquals($newsletter_id, $newsletter->id);
  }

  public function test_user_can_add_issue_to_his_newsletter()
  {
    /** @var mixed */
    $user = User::factory()->create();
    Auth::login($user);
    $newsletter = $user->newsletter()->first();
    $this->actingAs($user);
    //set current newsletter
    $this->json('post', "api/newsletter/current/$newsletter->id");
    // $this->withoutExceptionHandling();
    //add issue
    $response = $this
      ->json('post', "api/issues", [
        'subject' => 'test'
      ])
      ->assertStatus(201)->getContent();
    $newsletter_id = json_decode($response)->newsletter_id;
    //ensure that issue add in $newsletter
    $this->assertEquals($newsletter_id, $newsletter->id);
  }

  public function test_can_get_paginated_current_unpublished_ssues()
  {
    //Pagination issues per page like in controller
    $ISSUES_PER_PAGE = 10;

    // how much data to add to the pagination limit (will create "limit + x" records in the
    // database to test if "x" will be returned when requesting the "second page")
    $additionalData = 2;

    // the data size to be created ("limit + x")
    $dataSize = $ISSUES_PER_PAGE + $additionalData;

    //create usesr and login then get his newsletter
    /** @var mixed */
    $user = User::factory()->create();
    $this->actingAs($user);
    $newsletter = $user->newsletter()->first();

    // create "limit + x" number of fake Issues that not published
    $issues = Issue::factory($dataSize)->create([
      'newsletter_id' => $newsletter->id,
      'published_at' => $this->faker->randomElement([null, Carbon::now()->addDay()]), // to test draft and scheduled issues
    ]);

    $queryParameters = 'page=2';
    //set current newsletter
    $this->json('post', "api/newsletter/current/$newsletter->id");
    //call Issues list with pagination
    $response = $this->json('get', "api/issues/current-issues?" . $queryParameters)
      ->assertStatus(200);

    // convert JSON response string to Array
    $responseArray = json_decode($response->getContent());

    // assert the second page returned the "x" additional data
    $this->assertEquals(count($responseArray->data), $additionalData);
  }

  public function test_can_get_paginated_past_published_issues()
  {

    //Pagination issues per page like in controller
    $ISSUES_PER_PAGE = 10;

    // how much data to add to the pagination limit (will create "limit + x" records in the
    // database to test if "x" will be returned when requesting the "second page")
    $additionalData = 2;

    // the data size to be created ("limit + x")
    $dataSize = $ISSUES_PER_PAGE + $additionalData;

    //create usesr and login then get his newsletter
    /** @var mixed */
    $user = User::factory()->create();
    $newsletter = $user->newsletter()->first();
    $this->actingAs($user);

    // create "limit + x" number of fake Issues that  published
    $issues = Issue::factory($dataSize)->create([
      'newsletter_id' => $newsletter->id,
      'published_at' => Carbon::now()->subDay(),
    ]);

    $queryParameters = 'page=2';

    //set current newsletter
    $this->json('post', "api/newsletter/current/$newsletter->id");
    //call Issues list with pagination
    $response = $this->json('get', "api/issues/past-issues?" . $queryParameters)
      ->assertStatus(200);

    // convert JSON response string to Array
    $responseArray = json_decode($response->getContent());

    // assert the second page returned the "x" additional data
    $this->assertEquals(count($responseArray->data), $additionalData);
  }
}
