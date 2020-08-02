<?php

namespace Tests\Feature;

use App\Contact;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ContactsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function list_of_contact_can_be_fetched_for_authenticated_user()
    {
        $user = factory(User::class)->create();
        $anotheruser = factory(User::class)->create();

        $contact = factory(Contact::class)->create(['user_id' => $user->id]);
        $anothercontact = factory(Contact::class)->create(['user_id' => $anotheruser->id]);

        $response = $this->get('/api/contacts/?api_token='.$user->api_token);

        $response->assertJsonCount(1)->assertJson([
            'data' => [
                [
                    'data' => [
                        'contact_id' => $contact->id
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function unauthenticated_user()
    {
        $response = $this->post('/api/contacts', array_merge($this->data(), ['api_token' => '']));
        $response->assertRedirect('/login');
        $this->assertCount(0, Contact::all());
    }

     /** @test */
     public function only_users_contacts_can_be_retrieved()
     {
         $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);
         $anotheruser = factory(User::class)->create();
         $response = $this->get('/api/contacts/'.$contact->id.'?api_token='.$anotheruser->api_token);
         $response->assertStatus(403);
     }

    /** @test */
    public function an_authenticated_user_can_add_contact()
    {

        $response = $this->post('/api/contacts', $this->data());
        $contact = Contact::first();
        $this->assertEquals('Test Name', $contact->name);
        $this->assertEquals('test@gmail.com', $contact->email);
        $this->assertEquals('11/05/1997', $contact->birthday);
        $this->assertEquals('California Vollay', $contact->company);
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'data' => [
                'contact_id' => $contact->id,
            ],
            'links' => [
                'self' => $contact->path(),
            ]
        ]);

    }



   /** @test */
    public function fields_are_required()
    {
        collect(['name', 'email', 'birthday', 'company',])
            ->each(function ($field){
                $response = $this->post('/api/contacts', array_merge($this->data(), [$field => '']));
                $response->assertSessionHasErrors($field);
                $this->assertCount(0, Contact::all());
            });

    }

    /** @test */
    public function email_validate()
    {
        $response = $this->post('/api/contacts', array_merge($this->data(), ['email' => 'Not en email']));
        $response->assertSessionHasErrors('email');
        $this->assertCount(0, Contact::all());

    }

    /** @test */
    public function birthday_format()
    {

        $response = $this->post('/api/contacts', array_merge($this->data()));
        $this->assertCount(1, Contact::all());
        // $this->assertInstanceOf(Carbon::class, Contact::first()->birthday);
        $this->assertEquals('11/05/1997', Contact::first()->birthday);


    }

    /** @test */
    public function contact_retrive()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);
        $response = $this->get('/api/contacts/'.$contact->id.'?api_token='.$this->user->api_token);
        $response->assertJson([
            'data' => [
                'contact_id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'birthday' => $contact->birthday,
                'company' => $contact->company,
                'last_update' => $contact->updated_at->diffForHumans(),
            ]
        ]);

    }


    /** @test */
     public function contact_patch()
     {
         $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);
         $response = $this->patch('/api/contacts/'.$contact->id, $this->data());
         $contact = $contact->fresh();
         $this->assertEquals('Test Name', $contact->name);
         $this->assertEquals('test@gmail.com', $contact->email);
         $this->assertEquals('30/07/2020', $contact->birthday);
         $this->assertEquals('California Vollay', $contact->company);
         $response->assertStatus(Response::HTTP_OK);
         $response->assertJson([
             'data' => [
                 'contact_id' => $contact->id,
             ],
             'links' => [
                 'self' => $contact->path(),
             ]
         ]);

     }

     public function only_the_owner_can_patch_contact()
     {
        $contact = factory(Contact::class)->create();
        $anotheruser = factory(User::class)->create();
        $response = $this->patch('/api/contacts/'.$contact->id, array_merge($this->data(), ['api_token' => $anotheruser->api_token]));
        $response->assertStatus(403);
     }

    /** @test */
     public function contact_can_be_deleted()
     {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);
        $response = $this->delete('/api/contacts/'.$contact->id, ['api_token' => $this->user->api_token]);
        $this->assertCount(0, Contact::all());
        $response->assertStatus(Response::HTTP_NO_CONTENT);

     }
    /** @test */
     public function only_the_owner_can_delete_contct()
     {
        $contact = factory(Contact::class)->create();
        $response = $this->delete('/api/contacts/'.$contact->id, ['api_token' => $this->user->api_token]);
        $response->assertStatus(403);

     }

     private function data()
     {
         return [
             'name' => 'Test Name',
             'email' => 'test@gmail.com',
             'birthday' => '11/05/1997',
             'company' => 'California Vollay',
             'api_token' => $this->user->api_token,
         ];
     }
}
