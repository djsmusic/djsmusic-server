<?php

class UsersTest extends LocalWebTestCase
{
	public function testUserDoesntExist(){
    	$this->client->get('/users/100');
        $this->assertEquals(404, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);
    }

    public function testUserExists(){

    	$this->client->get('/users/1');
        $this->assertEquals(200, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);

        $data = json_decode($this->client->response->body());

        $this->assertSame("Admin", $data->artist->name);
    }
}